<?php

namespace App\Services;

use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingAutoPostingService
{
    protected const SOURCE_PAYMENT = 'payment';
    protected const SOURCE_EXPENSE = 'expense';

    protected const ACCOUNT_CASH = '1001';
    protected const ACCOUNT_BANK = '1002';
    protected const ACCOUNT_INVENTORY_MATERIAL = '1201';
    protected const ACCOUNT_CUSTOMER_CHANGE = '2002';
    protected const ACCOUNT_REVENUE = '4001';
    protected const ACCOUNT_EXPENSE_GENERAL = '6001';

    public function postPayment(Order $order, Payment $payment, float $remainingBeforePayment): void
    {
        $branchId = (int) $order->branch_id;
        $paymentAmount = (float) $payment->amount;
        if ($paymentAmount <= 0) {
            return;
        }

        $settledAmount = max(0, min($paymentAmount, $remainingBeforePayment));
        $changeAmount = max(0, $paymentAmount - $settledAmount);

        $lines = [];
        $lines[] = [
            'account_id' => $this->resolveCashBankAccountId($branchId, (string) ($payment->method ?? 'cash')),
            'debit' => $paymentAmount,
            'credit' => 0,
            'description' => 'Pembayaran order ' . $order->order_no,
        ];

        if ($settledAmount > 0) {
            $lines[] = [
                'account_id' => $this->resolveAccountId($branchId, self::ACCOUNT_REVENUE),
                'debit' => 0,
                'credit' => $settledAmount,
                'description' => 'Pendapatan order ' . $order->order_no,
            ];
        }

        if ($changeAmount > 0) {
            $lines[] = [
                'account_id' => $this->resolveAccountId($branchId, self::ACCOUNT_CUSTOMER_CHANGE),
                'debit' => 0,
                'credit' => $changeAmount,
                'description' => 'Kewajiban kembalian pelanggan ' . $order->order_no,
            ];
        }

        $method = strtoupper((string) ($payment->method ?? 'CASH'));
        $description = "Auto Posting Payment {$method} - {$order->order_no}";

        $this->upsertJournalBySource(
            branchId: $branchId,
            sourceType: self::SOURCE_PAYMENT,
            sourceId: (int) $payment->id,
            journalDate: Carbon::parse($payment->paid_at ?? now())->toDateString(),
            description: $description,
            lines: $lines
        );
    }

    public function syncExpense(Expense $expense): void
    {
        $branchId = (int) $expense->branch_id;
        $amount = (float) $expense->amount;
        if ($amount <= 0) {
            $this->deleteBySource(self::SOURCE_EXPENSE, (int) $expense->id);
            return;
        }

        $debitCode = $expense->type === 'material'
            ? self::ACCOUNT_INVENTORY_MATERIAL
            : self::ACCOUNT_EXPENSE_GENERAL;

        $expenseDate = $expense->expense_date
            ? Carbon::parse($expense->expense_date)->toDateString()
            : now()->toDateString();

        $lines = [
            [
                'account_id' => $this->resolveAccountId($branchId, $debitCode),
                'debit' => $amount,
                'credit' => 0,
                'description' => $expense->type === 'material'
                    ? 'Pembelian bahan'
                    : 'Beban operasional',
            ],
            [
                'account_id' => $this->resolveCashBankAccountId($branchId, (string) ($expense->payment_method ?? 'cash')),
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Pembayaran expense',
            ],
        ];

        $description = 'Auto Posting Expense ' . strtoupper((string) ($expense->type ?? 'general'));
        if (!empty($expense->notes)) {
            $description .= ' - ' . trim((string) $expense->notes);
        }

        $this->upsertJournalBySource(
            branchId: $branchId,
            sourceType: self::SOURCE_EXPENSE,
            sourceId: (int) $expense->id,
            journalDate: $expenseDate,
            description: $description,
            lines: $lines
        );
    }

    public function deleteBySource(string $sourceType, int $sourceId): void
    {
        AccountingJournal::query()
            ->withoutGlobalScopes()
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->delete();
    }

    public function deleteBySources(string $sourceType, array $sourceIds): void
    {
        $sourceIds = array_values(array_filter(array_map('intval', $sourceIds)));
        if (empty($sourceIds)) {
            return;
        }

        AccountingJournal::query()
            ->withoutGlobalScopes()
            ->where('source_type', $sourceType)
            ->whereIn('source_id', $sourceIds)
            ->delete();
    }

    protected function upsertJournalBySource(
        int $branchId,
        string $sourceType,
        int $sourceId,
        string $journalDate,
        string $description,
        array $lines
    ): void {
        $normalizedLines = collect($lines)
            ->map(function (array $line) {
                return [
                    'account_id' => (int) Arr::get($line, 'account_id'),
                    'description' => Arr::get($line, 'description'),
                    'debit' => round((float) Arr::get($line, 'debit', 0), 2),
                    'credit' => round((float) Arr::get($line, 'credit', 0), 2),
                ];
            })
            ->filter(fn (array $line) => $line['account_id'] > 0 && ($line['debit'] > 0 || $line['credit'] > 0))
            ->values();

        $totalDebit = round((float) $normalizedLines->sum('debit'), 2);
        $totalCredit = round((float) $normalizedLines->sum('credit'), 2);

        if ($totalDebit <= 0 || $totalCredit <= 0 || abs($totalDebit - $totalCredit) > 0.009) {
            throw ValidationException::withMessages([
                'general' => 'Auto posting gagal karena jurnal tidak balance.',
            ]);
        }

        AccountingJournal::query()
            ->withoutGlobalScopes()
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->delete();

        $journal = AccountingJournal::query()->create([
            'branch_id' => $branchId,
            'journal_no' => $this->generateJournalNo($branchId, $journalDate),
            'journal_date' => $journalDate,
            'description' => $description,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'posted_by' => auth()->id(),
        ]);

        $journal->lines()->createMany(
            $normalizedLines
                ->map(fn (array $line) => [
                    'branch_id' => $branchId,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                ])
                ->all()
        );
    }

    protected function resolveCashBankAccountId(int $branchId, string $paymentMethod): int
    {
        $method = strtolower(trim($paymentMethod));
        $code = in_array($method, ['transfer', 'qris'], true)
            ? self::ACCOUNT_BANK
            : self::ACCOUNT_CASH;

        return $this->resolveAccountId($branchId, $code);
    }

    protected function resolveAccountId(int $branchId, string $code): int
    {
        $account = AccountingAccount::query()
            ->withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->where('code', $code)
            ->first();

        if ($account) {
            return (int) $account->id;
        }

        $template = AccountingAccount::query()
            ->withoutGlobalScopes()
            ->where('code', $code)
            ->orderByRaw('CASE WHEN branch_id = 1 THEN 0 ELSE 1 END')
            ->orderBy('id')
            ->first();

        if (!$template) {
            throw ValidationException::withMessages([
                'general' => "Auto posting gagal: akun {$code} belum tersedia.",
            ]);
        }

        $cloned = AccountingAccount::query()->create([
            'branch_id' => $branchId,
            'code' => $template->code,
            'name' => $template->name,
            'type' => $template->type,
            'normal_balance' => $template->normal_balance,
            'is_active' => true,
            'notes' => $template->notes,
        ]);

        return (int) $cloned->id;
    }

    protected function generateJournalNo(int $branchId, string $journalDate): string
    {
        $datePrefix = 'AUTO' . Carbon::parse($journalDate)->format('Ymd');

        $lastNo = AccountingJournal::query()
            ->withoutGlobalScopes()
            ->where('branch_id', $branchId)
            ->whereDate('journal_date', $journalDate)
            ->where('journal_no', 'like', $datePrefix . '-%')
            ->orderByDesc('id')
            ->value('journal_no');

        $nextSequence = 1;
        if (filled($lastNo) && str_contains((string) $lastNo, '-')) {
            $lastSequence = (int) str($lastNo)->afterLast('-');
            $nextSequence = $lastSequence + 1;
        }

        return $datePrefix . '-' . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }
}


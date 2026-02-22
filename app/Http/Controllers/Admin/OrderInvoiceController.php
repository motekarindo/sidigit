<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BankAccountService;
use App\Services\OrderService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class OrderInvoiceController extends Controller
{
    use AuthorizesRequests;

    protected OrderService $service;
    protected BankAccountService $bankAccountService;

    public function __construct(OrderService $service, BankAccountService $bankAccountService)
    {
        $this->service = $service;
        $this->bankAccountService = $bankAccountService;
    }

    public function show(Request $request, int $order)
    {
        $this->authorize('order.view');

        $orderModel = $this->service->find($order);
        if (!$this->canAccessInvoice($orderModel->status)) {
            session()->flash('toast', [
                'message' => 'Invoice hanya tersedia mulai status Approval. Status Draft dan Quotation belum bisa melihat invoice.',
                'type' => 'warning',
            ]);
            return redirect()->route('orders.index');
        }
        $print = $request->boolean('print');

        $bankAccounts = $this->bankAccountService
            ->query()
            ->where('branch_id', $orderModel->branch_id)
            ->orderBy('bank_name')
            ->orderBy('rekening_number')
            ->get(['bank_name', 'rekening_number', 'account_name']);

        return view('admin.orders.invoice', [
            'order' => $orderModel,
            'print' => $print,
            'bankAccounts' => $bankAccounts,
        ]);
    }

    public function pdf(int $order)
    {
        $this->authorize('order.view');

        $orderModel = $this->service->find($order);
        if (!$this->canAccessInvoice($orderModel->status)) {
            session()->flash('toast', [
                'message' => 'Invoice hanya tersedia mulai status Approval. Status Draft dan Quotation belum bisa melihat invoice.',
                'type' => 'warning',
            ]);
            return redirect()->route('orders.index');
        }

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return response()
                ->view('admin.orders.invoice', [
                    'order' => $orderModel,
                    'print' => true,
                ], 200)
                ->header('X-Pdf-Generator', 'missing');
        }

        $bankAccounts = $this->bankAccountService
            ->query()
            ->where('branch_id', $orderModel->branch_id)
            ->orderBy('bank_name')
            ->orderBy('rekening_number')
            ->get(['bank_name', 'rekening_number', 'account_name']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.orders.invoice-pdf', [
            'order' => $orderModel,
            'bankAccounts' => $bankAccounts,
        ]);

        return $pdf->download('Invoice-' . $orderModel->order_no . '.pdf');
    }

    public function quotation(Request $request, int $order)
    {
        $this->authorize('order.view');

        $orderModel = $this->service->find($order);
        if ($orderModel->status === 'draft') {
            session()->flash('toast', [
                'message' => 'Quotation belum dibuat.',
                'type' => 'warning',
            ]);
            return redirect()->route('orders.index');
        }

        $print = $request->boolean('print');

        return view('admin.orders.quotation', [
            'order' => $orderModel,
            'print' => $print,
        ]);
    }

    public function quotationPdf(int $order)
    {
        $this->authorize('order.view');

        $orderModel = $this->service->find($order);
        if ($orderModel->status === 'draft') {
            session()->flash('toast', [
                'message' => 'Quotation belum dibuat.',
                'type' => 'warning',
            ]);
            return redirect()->route('orders.index');
        }

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return response()
                ->view('admin.orders.quotation', [
                    'order' => $orderModel,
                    'print' => true,
                ], 200)
                ->header('X-Pdf-Generator', 'missing');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.orders.quotation-pdf', [
            'order' => $orderModel,
        ]);

        return $pdf->download('Quotation-' . $orderModel->order_no . '.pdf');
    }

    protected function canAccessInvoice(?string $status): bool
    {
        return !in_array((string) $status, ['draft', 'quotation'], true);
    }
}

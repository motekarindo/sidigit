<?php

namespace App\Livewire\Admin\Expenses;

use App\Livewire\BaseTable;
use App\Livewire\Forms\ExpenseForm;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\ExpenseService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;

class Table extends BaseTable
{
    protected ExpenseService $service;

    public ExpenseForm $form;

    public string $type = 'general';

    #[Url(except: null)]
    public ?int $edit = null;

    public function boot(ExpenseService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        if ($this->edit) {
            $this->openEdit((int) $this->edit);
        }
    }

    protected function query()
    {
        return $this->applySearch(
            $this->service->queryByType($this->type),
            ['notes', 'payment_method']
        );
    }

    public function getMaterialOptionsProperty()
    {
        return Material::orderBy('name')->get();
    }

    public function getSupplierOptionsProperty()
    {
        return Supplier::orderBy('name')->get();
    }

    public function getUnitOptionsProperty()
    {
        $materialId = $this->form->material_id;
        if (!$materialId) {
            return Unit::orderBy('name')->get();
        }

        $material = Material::find($materialId);
        if (!$material) {
            return Unit::orderBy('name')->get();
        }

        $ids = array_filter([$material->unit_id, $material->purchase_unit_id]);
        if (empty($ids)) {
            return Unit::orderBy('name')->get();
        }

        return Unit::query()->whereIn('id', $ids)->orderBy('name')->get();
    }

    protected function resetForm(): void
    {
        $this->form->reset();
        $this->form->type = $this->type;
        $this->form->expense_date = now()->format('Y-m-d');
    }

    public function updatedFormMaterialId($value): void
    {
        if ($this->type !== 'material') {
            return;
        }

        $material = Material::find($value);
        if ($material) {
            $this->form->unit_id = $material->purchase_unit_id ?: $material->unit_id;
        }
    }

    protected function loadForm(int $id): void
    {
        $expense = $this->service->find($id);
        $this->form->fillFromModel($expense);
    }

    public function create(): void
    {
        try {
            $this->form->type = $this->type;
            $this->form->store($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Expense berhasil dibuat.', type: 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal membuat expense.', type: 'error');
        }
    }

    public function update(): void
    {
        try {
            $this->form->type = $this->type;
            $this->form->update($this->service);
            $this->closeModal();
            $this->dispatch('toast', message: 'Expense berhasil diperbarui.', type: 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal memperbarui expense.', type: 'error');
        }
    }

    public function delete(): void
    {
        try {
            $this->service->destroy($this->activeId);
            $this->closeModal();
            $this->dispatch('toast', message: 'Expense berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus expense.', type: 'error');
        }
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            $this->dispatch('toast', message: 'Pilih minimal 1 data.', type: 'warning');
            return;
        }

        try {
            $this->service->destroyMany($this->selected);
            $this->selected = [];
            $this->selectAll = false;
            $this->closeModal();
            $this->dispatch('toast', message: 'Expense terpilih berhasil dihapus.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Gagal menghapus expense terpilih.', type: 'error');
        }
    }

    protected function formView(): ?string
    {
        return 'livewire.admin.expenses.form';
    }

    protected function rowActions(): array
    {
        return [
            ['label' => 'Edit', 'method' => 'openEdit', 'class' => 'text-brand-500', 'icon' => 'pencil'],
            ['label' => 'Delete', 'method' => 'confirmDelete', 'class' => 'text-red-600', 'icon' => 'trash-2'],
        ];
    }

    protected function tableActions(): array
    {
        return [
            ['label' => $this->actionLabel(), 'method' => 'openCreate', 'class' => 'bg-brand-500 hover:bg-brand-600 text-white', 'icon' => 'plus'],
        ];
    }

    protected function bulkActions(): array
    {
        return [
            'delete' => ['label' => 'Delete selected', 'method' => 'confirmBulkDelete'],
        ];
    }

    protected function columns(): array
    {
        if ($this->type === 'material') {
            return [
                ['label' => 'Bahan', 'field' => 'material.name', 'sortable' => false],
                ['label' => 'Supplier', 'field' => 'supplier.name', 'sortable' => false],
                ['label' => 'Qty', 'field' => 'qty', 'sortable' => false],
                [
                    'label' => 'Harga/Unit',
                    'field' => 'unit_cost',
                    'sortable' => false,
                    'format' => fn ($row) => number_format((float) $row->unit_cost, 0, ',', '.'),
                ],
                [
                    'label' => 'Total',
                    'field' => 'amount',
                    'sortable' => false,
                    'format' => fn ($row) => number_format((float) $row->amount, 0, ',', '.'),
                ],
                ['label' => 'Tanggal', 'field' => 'expense_date', 'sortable' => false],
                ['label' => 'Metode', 'field' => 'payment_method', 'sortable' => false],
            ];
        }

        return [
            [
                'label' => 'Total',
                'field' => 'amount',
                'sortable' => false,
                'format' => fn ($row) => number_format((float) $row->amount, 0, ',', '.'),
            ],
            ['label' => 'Tanggal', 'field' => 'expense_date', 'sortable' => false],
            ['label' => 'Metode', 'field' => 'payment_method', 'sortable' => false],
            ['label' => 'Catatan', 'field' => 'notes', 'sortable' => false],
        ];
    }

    protected function selectionColumnCheckbox(): bool
    {
        return true;
    }

    protected function createModalWidth(): string
    {
        return '3xl';
    }

    protected function editModalWidth(): string
    {
        return '3xl';
    }

    protected function createModalTitle(): string
    {
        return $this->actionLabel();
    }

    protected function actionLabel(): string
    {
        return $this->type === 'material'
            ? 'Tambah Expense Bahan'
            : 'Tambah Expense Umum';
    }
}

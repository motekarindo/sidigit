<?php

namespace App\Livewire\Admin\Expenses;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Expense Bahan')]
class MaterialIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public string $type = 'material';

    public function mount(): void
    {
        $this->authorize('expense-material.view');

        $this->setPageMeta(
            'Expense Bahan',
            'Catat pembelian bahan dan biaya terkait.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Expense Bahan', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.expenses.index');
    }
}

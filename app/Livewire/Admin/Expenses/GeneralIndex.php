<?php

namespace App\Livewire\Admin\Expenses;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Expense Umum')]
class GeneralIndex extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public string $type = 'general';

    public function mount(): void
    {
        $this->authorize('expense-general.view');

        $this->setPageMeta(
            'Expense Umum',
            'Catat pengeluaran operasional.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Expense Umum', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.expenses.index');
    }
}

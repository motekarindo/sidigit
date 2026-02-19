<?php

namespace App\Livewire\Admin\EmployeeLoans;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Kasbon Karyawan')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('employee-loan.view');

        $this->setPageMeta(
            'Kasbon Karyawan',
            'Kelola kasbon atau pinjaman karyawan.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Kasbon', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.employee-loans.index');
    }
}

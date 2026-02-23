<?php

namespace App\Livewire\Admin\Attendances;

use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Absensi Karyawan')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPageMeta;

    public function mount(): void
    {
        $this->authorize('attendance.view');

        $this->setPageMeta(
            'Absensi Karyawan',
            'Kelola data absensi karyawan.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Absensi', 'current' => true],
            ]
        );
    }

    public function render()
    {
        return view('livewire.admin.attendances.index');
    }
}

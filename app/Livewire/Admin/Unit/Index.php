<?php

namespace App\Livewire\Admin\Unit;

use App\Services\UnitService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Satuan')]
class Index extends Component
{
    use AuthorizesRequests;

    protected UnitService $service;
    public function mount(UnitService $service)
    {
        $this->service = $service;
    }

    public function render()
    {
        $this->authorize('unit.view');

        return view('livewire.admin.unit.index', [
            'units' => $this->service->getPaginated()
        ]);
    }
}

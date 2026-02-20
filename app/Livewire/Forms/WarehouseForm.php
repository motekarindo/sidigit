<?php

namespace App\Livewire\Forms;

use App\Models\Warehouse;
use App\Services\WarehouseService;
use Livewire\Form;

class WarehouseForm extends Form
{
    public ?int $id = null;

    public string $name = '';
    public ?string $location_lat = null;
    public ?string $location_lng = null;
    public ?string $description = null;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:128'],
            'location_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'location_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama gudang wajib diisi.',
            'name.string' => 'Nama gudang harus berupa teks.',
            'name.max' => 'Nama gudang maksimal 128 karakter.',
            'location_lat.numeric' => 'Latitude harus berupa angka.',
            'location_lat.between' => 'Latitude harus di antara -90 sampai 90.',
            'location_lng.numeric' => 'Longitude harus berupa angka.',
            'location_lng.between' => 'Longitude harus di antara -180 sampai 180.',
            'description.string' => 'Deskripsi harus berupa teks.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'Nama gudang',
            'location_lat' => 'Latitude',
            'location_lng' => 'Longitude',
            'description' => 'Deskripsi',
        ];
    }

    public function fillFromModel(Warehouse $warehouse): void
    {
        $this->id = $warehouse->id;
        $this->name = $warehouse->name;
        $this->location_lat = $warehouse->location_lat !== null ? (string) $warehouse->location_lat : null;
        $this->location_lng = $warehouse->location_lng !== null ? (string) $warehouse->location_lng : null;
        $this->description = $warehouse->description;
    }

    public function store(WarehouseService $service): void
    {
        $this->validate();

        $service->store($this->payload());
    }

    public function update(WarehouseService $service): void
    {
        $this->validate();

        $service->update($this->id, $this->payload());
    }

    protected function payload(): array
    {
        return [
            'name' => $this->name,
            'location_lat' => $this->location_lat,
            'location_lng' => $this->location_lng,
            'description' => $this->description,
        ];
    }
}

<div class="space-y-4">
    <div>
        <x-forms.searchable-select
            label="Bahan"
            :options="$this->materialOptions"
            placeholder="Pilih bahan"
            wire:model="form.material_id"
            required
        />
        @error('form.material_id')
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <x-forms.searchable-select
            label="Satuan Input"
            :options="$this->unitOptions"
            placeholder="Pilih satuan"
            wire:model="form.unit_id"
            required
        />
        @error('form.unit_id')
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
        @php
            $material = $form->material_id ? \App\Models\Material::with(['unit', 'purchaseUnit'])->find($form->material_id) : null;
            $baseUnit = \App\Support\UnitFormatter::label($material?->unit?->name);
            $purchaseUnit = \App\Support\UnitFormatter::label($material?->purchaseUnit?->name);
            $conversion = $material?->conversion_qty ?? null;
        @endphp
        @if ($material && $purchaseUnit && $baseUnit && $conversion)
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Konversi: 1 {{ $purchaseUnit }} = {{ number_format((float) $conversion, 2, ',', '.') }} {{ $baseUnit }}.
            </p>
        @endif
    </div>

    <x-forms.input
        :label="$type === 'opname' ? 'Selisih Stok (+/-)' : 'Qty'"
        name="form.qty"
        placeholder="0"
        wire:model.blur="form.qty"
        type="number"
        step="0.01"
        inputmode="decimal"
        required
    />

    @if ($type === 'opname')
        <p class="text-xs text-gray-500 dark:text-gray-400">
            Masukkan selisih stok. Gunakan angka negatif jika stok berkurang.
        </p>
    @endif

    <div>
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Catatan</label>
        <textarea
            class="form-input mt-3"
            rows="3"
            placeholder="Catatan tambahan"
            wire:model.blur="form.notes"
        ></textarea>
        @error('form.notes')
            <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="space-y-6">
    @if ($type === 'material')
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
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
                    label="Supplier (Opsional)"
                    :options="$this->supplierOptions"
                    placeholder="Pilih supplier"
                    wire:model="form.supplier_id"
                />
                @error('form.supplier_id')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-forms.searchable-select
                    label="Satuan Pembelian"
                    :options="$this->unitOptions"
                    placeholder="Pilih satuan"
                    wire:model="form.unit_id"
                    required
                />
                @error('form.unit_id')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <x-forms.input label="Tanggal" name="form.expense_date" wire:model.blur="form.expense_date" type="date" required />
        </div>

        @php
            $material = $form->material_id ? \App\Models\Material::with(['unit', 'purchaseUnit'])->find($form->material_id) : null;
            $baseUnit = \App\Support\UnitFormatter::label($material?->unit?->name);
            $purchaseUnit = \App\Support\UnitFormatter::label($material?->purchaseUnit?->name);
            $conversion = $material?->conversion_qty ?? null;
        @endphp
        @if ($material && $purchaseUnit && $baseUnit && $conversion)
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Konversi: 1 {{ $purchaseUnit }} = {{ number_format((float) $conversion, 2, ',', '.') }} {{ $baseUnit }}.
            </p>
        @endif

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <x-forms.input label="Qty" name="form.qty" placeholder="0" wire:model.blur="form.qty" type="number" step="1" min="1" inputmode="numeric" required />
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Harga per Unit</label>
                <div class="mt-2" x-data="rupiahField(@entangle('form.unit_cost').live)">
                    <input type="text" inputmode="numeric" x-model="display" @input="onInput"
                        class="form-input" />
                </div>
                @error('form.unit_cost')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Total (Opsional)</label>
                <div class="mt-2" x-data="rupiahField(@entangle('form.amount').live)">
                    <input type="text" inputmode="numeric" x-model="display" @input="onInput"
                        class="form-input" />
                </div>
                @error('form.amount')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
                @enderror
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Total</label>
                <div class="mt-2" x-data="rupiahField(@entangle('form.amount').live)">
                    <input type="text" inputmode="numeric" x-model="display" @input="onInput"
                        class="form-input" />
                </div>
                @error('form.amount')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
                @enderror
            </div>
            <x-forms.input label="Tanggal" name="form.expense_date" wire:model.blur="form.expense_date" type="date" required />
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @php
            $paymentMethods = collect([
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'transfer', 'label' => 'Transfer'],
                ['value' => 'qris', 'label' => 'QRIS'],
            ]);
        @endphp
        <div>
            <x-forms.searchable-select
                label="Metode Pembayaran"
                :options="$paymentMethods"
                optionValue="value"
                optionLabel="label"
                placeholder="Pilih metode"
                wire:model="form.payment_method"
            />
            @error('form.payment_method')
                <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="lg:col-span-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Catatan</label>
            <textarea class="form-input mt-3" rows="4" placeholder="Catatan" wire:model.blur="form.notes"></textarea>
            @error('form.notes')
                <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

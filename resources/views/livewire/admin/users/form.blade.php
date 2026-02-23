<div class="space-y-4">
    <div x-data="{ withoutEmployee: @entangle('form.without_employee') }">
        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
            <label class="flex items-center gap-3">
                <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                    wire:model.live="form.without_employee">
                <span>Akun tanpa pegawai</span>
            </label>
            <p class="mt-1 text-xs text-gray-400">Aktifkan jika akun ini tidak terhubung ke data pegawai.</p>
        </div>

        <div x-cloak x-show="!withoutEmployee" class="mt-4">
            <x-forms.searchable-select
                label="Pegawai"
                :options="$this->availableEmployees"
                placeholder="Pilih pegawai"
                wire:model.live="form.employee_id"
                required
            />
            @error('form.employee_id')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <x-forms.input label="Nama" name="form.name" placeholder="Nama lengkap"
        wire:model.blur="form.name" required />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <x-forms.input label="Username" name="form.username" placeholder="Username"
            wire:model.blur="form.username" required />
        <x-forms.input label="Email" name="form.email" placeholder="Email"
            wire:model.blur="form.email" required />
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <x-forms.input label="Password" name="form.password" placeholder="Password"
            wire:model.blur="form.password" type="password" @if ($showCreateModal) required @endif />
        <x-forms.input label="Konfirmasi Password" name="form.password_confirmation" placeholder="Konfirmasi Password"
            wire:model.blur="form.password_confirmation" type="password" @if ($showCreateModal) required @endif />
    </div>

    <div>
        <x-forms.searchable-select
            label="Role"
            :options="$this->availableRoles"
            placeholder="Pilih role"
            wire:model="form.role_id"
            required
        />
        @error('form.role_id')
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <x-forms.searchable-select
            label="Cabang Default"
            :options="$this->availableBranches"
            placeholder="Pilih cabang default"
            wire:model="form.branch_id"
            required
        />
        @error('form.branch_id')
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <x-forms.searchable-multiselect
            label="Akses Cabang"
            :options="$this->availableBranches"
            placeholder="Pilih cabang"
            wire:model="form.branch_ids"
            required
        />
        @error('form.branch_ids')
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

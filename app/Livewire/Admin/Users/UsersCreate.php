<?php

namespace App\Livewire\Admin\Users;

use App\Services\RoleService;
use App\Services\UserService;
use App\Services\BranchService;
use App\Services\EmployeeService;
use App\Traits\WithErrorToast;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tambah User')]
class UsersCreate extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;

    protected UserService $service;
    protected RoleService $roleService;
    protected BranchService $branchService;
    protected EmployeeService $employeeService;

    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $roles = [];
    public ?int $branch_id = null;
    public array $branch_ids = [];
    public ?int $employee_id = null;
    public bool $without_employee = true;

    public function boot(UserService $service, RoleService $roleService, BranchService $branchService, EmployeeService $employeeService): void
    {
        $this->service = $service;
        $this->roleService = $roleService;
        $this->branchService = $branchService;
        $this->employeeService = $employeeService;
    }

    public function mount(): void
    {
        $this->authorize('users.create');
        $defaultBranchId = auth()->user()?->branch_id
            ?? $this->branchService->query()->where('is_main', true)->value('id');
        $this->branch_id = $defaultBranchId;
        $this->branch_ids = $defaultBranchId ? [$defaultBranchId] : [];
    }

    protected function rules(): array
    {
        $uniqueEmployee = Rule::unique('users', 'employee_id');
        $employeeRules = $this->without_employee
            ? ['nullable', 'integer', 'exists:mst_employees,id', $uniqueEmployee]
            : ['required', 'integer', 'exists:mst_employees,id', $uniqueEmployee];

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'branch_ids' => ['required', 'array', 'min:1'],
            'branch_ids.*' => ['exists:branches,id'],
            'employee_id' => $employeeRules,
        ];
    }

    protected function toastValidation(ValidationException $e, ?string $fallback = null): void
    {
        $errors = $e->validator->errors()->all();
        if (!empty($errors)) {
            $message = "Periksa input:\n• " . implode("\n• ", $errors);
        } else {
            $message = $fallback ?: 'Periksa kembali input. Ada data yang belum sesuai.';
        }

        $this->dispatch('toast', message: $message, type: 'warning');
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.string' => 'Username harus berupa teks.',
            'username.max' => 'Username maksimal 255 karakter.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'password.min' => 'Password minimal 8 karakter.',
            'roles.required' => 'Pilih minimal 1 role.',
            'roles.array' => 'Role harus berupa daftar.',
            'roles.min' => 'Pilih minimal 1 role.',
            'roles.*.exists' => 'Role yang dipilih tidak valid.',
            'branch_id.required' => 'Cabang default wajib dipilih.',
            'branch_id.exists' => 'Cabang default tidak valid.',
            'branch_ids.required' => 'Pilih minimal 1 cabang.',
            'branch_ids.array' => 'Cabang harus berupa daftar.',
            'branch_ids.min' => 'Pilih minimal 1 cabang.',
            'branch_ids.*.exists' => 'Cabang yang dipilih tidak valid.',
            'employee_id.required' => 'Pegawai wajib dipilih.',
            'employee_id.integer' => 'Pegawai tidak valid.',
            'employee_id.exists' => 'Pegawai yang dipilih tidak valid.',
            'employee_id.unique' => 'Pegawai sudah memiliki akun.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'Nama',
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'roles' => 'Role',
            'branch_id' => 'Cabang Default',
            'branch_ids' => 'Akses Cabang',
            'employee_id' => 'Pegawai',
        ];
    }

    public function updatedWithoutEmployee(bool $value): void
    {
        if ($value) {
            $this->employee_id = null;
        }
    }

    public function updatedEmployeeId(?int $value): void
    {
        if (empty($value)) {
            return;
        }

        $employee = $this->employeeService->find($value);
        $this->name = $employee->name ?? $this->name;
        $this->username = $this->suggestUsername($employee->name, null);
        $this->email = $employee->email ?? '';
    }

    public function save(): void
    {
        $data = $this->validate();
        if (!in_array($this->branch_id, $this->branch_ids, true)) {
            $this->branch_ids[] = $this->branch_id;
        }

        try {
            $user = $this->service->store([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'branch_id' => $this->branch_id,
                'employee_id' => $this->without_employee ? null : $this->employee_id,
            ]);

            $user->roles()->sync($this->roles);
            $this->service->syncBranches($user->id, $this->branch_ids);

            session()->flash('success', 'User baru berhasil ditambahkan.');

            $this->redirectRoute('users.index');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $th) {
            report($th);
            $this->toastError($th, 'Terjadi kesalahan saat menambahkan user.');
        }
    }

    public function render()
    {
        return view('livewire.admin.users.create', [
            'availableRoles' => $this->roleService->all(),
            'availableBranches' => $this->branchService->query()->orderBy('name')->get(),
            'availableEmployees' => $this->getAvailableEmployees(),
        ])->layoutData([
            'title' => 'Tambah User',
        ]);
    }

    protected function getAvailableEmployees()
    {
        $assigned = $this->service->query()->whereNotNull('employee_id')->pluck('employee_id')->all();

        return $this->employeeService->query()
            ->when(!empty($assigned), fn ($query) => $query->whereNotIn('id', $assigned))
            ->orderBy('name')
            ->get()
            ->map(function ($employee) {
                $label = $employee->email ? "{$employee->name} - {$employee->email}" : $employee->name;
                return ['id' => $employee->id, 'name' => $label];
            });
    }

    protected function suggestUsername(string $name, ?int $ignoreId): string
    {
        $base = Str::slug($name, '.');
        $base = $base !== '' ? $base : 'user';

        $username = $base;
        $suffix = 1;

        $exists = fn ($value) => User::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('username', $value)
            ->exists();

        while ($exists($username)) {
            $suffix++;
            $username = $base . $suffix;
        }

        return $username;
    }
}

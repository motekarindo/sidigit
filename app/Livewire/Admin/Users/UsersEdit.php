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
#[Title('Edit User')]
class UsersEdit extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;

    public int $userId;
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

    public function mount(int $user): void
    {
        $this->authorize('users.edit');

        $this->userId = $user;
        $userModel = $this->service->findWithRoles($this->userId);

        $this->name = $userModel->name;
        $this->username = $userModel->username;
        $this->email = $userModel->email;
        $this->roles = $userModel->roles->pluck('id')->map(fn($id) => (int) $id)->toArray();
        $this->branch_id = $userModel->branch_id;
        $this->branch_ids = $userModel->branches->pluck('id')->map(fn($id) => (int) $id)->toArray();
        if ($this->branch_id && !in_array($this->branch_id, $this->branch_ids, true)) {
            $this->branch_ids[] = $this->branch_id;
        }
        $this->employee_id = $userModel->employee_id;
        $this->without_employee = $userModel->employee_id === null;
    }

    protected function rules(): array
    {
        $uniqueEmployee = Rule::unique('users', 'employee_id')->ignore($this->userId);
        $employeeRules = $this->without_employee
            ? ['nullable', 'integer', 'exists:mst_employees,id', $uniqueEmployee]
            : ['required', 'integer', 'exists:mst_employees,id', $uniqueEmployee];

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($this->userId)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)],
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
        $this->username = $this->suggestUsername($employee->name, $this->userId);
        $this->email = $employee->email ?? '';
    }

    public function update(): void
    {
        $data = $this->validate();
        if (!in_array($this->branch_id, $this->branch_ids, true)) {
            $this->branch_ids[] = $this->branch_id;
        }

        try {
            $payload = [
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'branch_id' => $this->branch_id,
                'employee_id' => $this->without_employee ? null : $this->employee_id,
            ];

            if (! empty($data['password'])) {
                $payload['password'] = $data['password'];
            }

            $this->service->update($this->userId, $payload);
            $this->service->syncRoles($this->userId, $this->roles);
            $this->service->syncBranches($this->userId, $this->branch_ids);

            session()->flash('success', 'User berhasil diperbarui.');

            $this->redirectRoute('users.index');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $th) {
            report($th);
            $this->toastError($th, 'Terjadi kesalahan saat memperbarui user.');
        }
    }

    public function render()
    {
        return view('livewire.admin.users.edit', [
            'availableRoles' => $this->roleService->all(),
            'availableBranches' => $this->branchService->query()->orderBy('name')->get(),
            'availableEmployees' => $this->getAvailableEmployees(),
        ])->layoutData([
            'title' => 'Edit User',
        ]);
    }

    protected function getAvailableEmployees()
    {
        $assigned = $this->service->query()
            ->whereNotNull('employee_id')
            ->where('id', '!=', $this->userId)
            ->pluck('employee_id')
            ->all();

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

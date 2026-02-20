<?php

namespace App\Livewire\Admin\Users;

use App\Services\RoleService;
use App\Services\UserService;
use App\Traits\WithErrorToast;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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

    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $roles = [];

    public function boot(UserService $service, RoleService $roleService): void
    {
        $this->service = $service;
        $this->roleService = $roleService;
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
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($this->userId)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
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
        ];
    }

    public function update(): void
    {
        $data = $this->validate();

        try {
            $payload = [
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
            ];

            if (! empty($data['password'])) {
                $payload['password'] = $data['password'];
            }

            $this->service->update($this->userId, $payload);
            $this->service->syncRoles($this->userId, $this->roles);

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
        ])->layoutData([
            'title' => 'Edit User',
        ]);
    }
}

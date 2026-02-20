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
#[Title('Tambah User')]
class UsersCreate extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;

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

    public function mount(): void
    {
        $this->authorize('users.create');
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        try {
            $user = $this->service->store([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $user->roles()->sync($this->roles);

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
        ])->layoutData([
            'title' => 'Tambah User',
        ]);
    }
}

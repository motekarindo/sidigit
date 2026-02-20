<?php

namespace App\Livewire\Profile;

use App\Traits\WithErrorToast;
use App\Traits\WithPageMeta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Profil Saya')]
class Edit extends Component
{
    use AuthorizesRequests;
    use WithErrorToast;
    use WithPageMeta;

    public int $userId;
    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->userId = (int) $user->id;
        $this->name = (string) $user->name;
        $this->username = (string) $user->username;
        $this->email = (string) $user->email;

        $this->setPageMeta(
            'Profil Saya',
            'Kelola data akun dan perbarui informasi yang dibutuhkan.',
            [
                ['label' => 'Dashboard', 'url' => Route::has('dashboard') ? route('dashboard') : '#', 'icon' => true],
                ['label' => 'Profil', 'current' => true],
            ]
        );
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($this->userId),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->userId),
            ],
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        try {
            $payload = [
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
            ];

            if (!empty($data['password'])) {
                $payload['password'] = $data['password'];
            }

            $user = Auth::user();
            $user->update($payload);

            $this->password = '';
            $this->password_confirmation = '';

            session()->flash('toast', ['message' => 'Profil berhasil diperbarui.', 'type' => 'success']);
            $this->redirectRoute('profile.edit');
        } catch (ValidationException $e) {
            $this->toastValidation($e);
            throw $e;
        } catch (\Throwable $th) {
            report($th);
            $this->toastError($th, 'Terjadi kesalahan saat memperbarui profil.');
        }
    }

    public function render()
    {
        return view('livewire.profile.edit');
    }
}

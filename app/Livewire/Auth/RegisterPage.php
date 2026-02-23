<?php

namespace App\Livewire\Auth;

use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Daftar')]
class RegisterPage extends Component
{
    protected UserService $service;

    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function boot(UserService $service): void
    {
        $this->service = $service;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:255', Rule::unique('users', 'username')],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];
    }

    public function register(): void
    {
        $validated = $this->validate();

        $user = $this->service->store([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        Auth::login($user);

        session()->regenerate();

        $this->redirectIntended(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.register-page')
            ->layoutData([
                'badge' => 'Create Account',
                'aside' => view('livewire.auth.partials.login-aside'),
                'title' => 'Daftar',
            ]);
    }
}

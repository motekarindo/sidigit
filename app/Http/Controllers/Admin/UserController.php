<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;



class UserController extends Controller
{
    use AuthorizesRequests;
    protected UserService $userService;
    protected RoleService $roleService;

    public function __construct(UserService $userService, RoleService $roleService)
    {
        $this->userService = $userService;
        $this->roleService = $roleService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize("users.view");

        $users = $this->userService->query()->latest()->paginate(10);

        return view("admin.users.index", compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize("users.create");

        $roles = $this->roleService->all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize("users.create");
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'roles' => 'required',
            'roles.*' => 'exists:roles,id',
        ]);

        // Buat user baru
        $user = $this->userService->store($validated);

        // Berikan role
        $this->userService->syncRoles($user->id, $validated['roles']);

        return redirect()->route('users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $user)
    {
        $this->authorize("users.edit");

        $roles = $this->roleService->all();

        return view('admin.users.edit', [
            'user' => $this->userService->findWithRoles($user),
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $user)
    {
        $this->authorize("users.create");
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => 'required',
            'role.*' => 'exists:roles,id',
        ]);

        // Jika password diisi, hash dan update. Jika tidak, abaikan.
        $payload = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
        ];
        if (!empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }
        $this->userService->update($user, $payload);

        // Update role
        $this->userService->syncRoles($user, $validated['role']);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $user)
    {
        $this->authorize("users.delete");
        if (Auth::id() == $user) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $this->userService->destroy($user);
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}

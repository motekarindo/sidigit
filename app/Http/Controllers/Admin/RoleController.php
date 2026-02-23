<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MenuService;
use App\Services\RoleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class RoleController extends Controller
{
    use AuthorizesRequests;
    protected RoleService $roleService;
    protected MenuService $menuService;

    public function __construct(RoleService $roleService, MenuService $menuService)
    {
        $this->roleService = $roleService;
        $this->menuService = $menuService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize(('role.view'));
        $roles = $this->roleService->query()->latest()->paginate(10);


        return view("admin.roles.index", compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('role.create');

        $menus = $this->menuService->treeWithPermissions();

        return view('admin.roles.create', compact('menus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('role.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'slug' => 'required|string|max:255|unique:roles,slug',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'menus' => 'nullable|array',
            'menus.*' => 'exists:menus,id'
        ]);

        // Buat Role baru
        $role = $this->roleService->store([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
        ]);

        $this->roleService->syncPermissionsMenus(
            $role->id,
            $validated['permissions'] ?? [],
            $validated['menus'] ?? []
        );

        return redirect()->route('roles.index')->with('success', 'Role baru berhasil ditambahkan.');
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
    public function edit(int $role)
    {
        $this->authorize('role.edit');

        $roleModel = $this->roleService->findWithRelations($role);
        $menus = $this->menuService->treeWithPermissions();


        return view('admin.roles.edit', [
            'role' => $roleModel,
            'menus' => $menus,
        ]);
    }


    public function update(Request $request, int $role)
    {
        $this->authorize('role.edit');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role,
            'slug' => 'required|string|max:255|unique:roles,slug,' . $role,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'menus' => 'nullable|array', // Pastikan ini ada
            'menus.*' => 'exists:menus,id' // Pastikan ini ada
        ]);

        $this->roleService->update($role, [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
        ]);

        $this->roleService->syncPermissionsMenus(
            $role,
            $validated['permissions'] ?? [],
            $validated['menus'] ?? []
        );

        return redirect()->route('roles.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(int $role)
    {
        $this->authorize('role.delete');

        $this->roleService->destroy($role);

        // 3. Redirect kembali dengan pesan sukses
        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PermissionController extends Controller
{
    use AuthorizesRequests;
    protected PermissionService $permissionService;
    protected MenuService $menuService;

    public function __construct(PermissionService $permissionService, MenuService $menuService)
    {
        $this->permissionService = $permissionService;
        $this->menuService = $menuService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize("permission.view");
        $permissions = $this->permissionService->query()->latest()->paginate(10);

        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        $this->authorize("permission.create");
        $menus = $this->menuService->query()
            ->whereNotNull('route_name')
            ->orWhere('route_name', '!=', '')
            ->get();
        return view('admin.permissions.create', compact('menus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $this->authorize("permission.create");
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug',
            'menu_id' => 'required|exists:menus,id',
        ]);

        $this->permissionService->store($validated);

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
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
    public function edit(int $permission)
    {

        $this->authorize("permission.edit");
        $menus = $this->menuService->query()->orderBy('name')->get();

        return view('admin.permissions.edit', [
            'permission' => $this->permissionService->find($permission),
            'menus' => $menus,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $permission)
    {

        $this->authorize("permission.edit");
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug,' . $permission,
            'menu_id' => 'required|exists:menus,id',
        ]);

        $this->permissionService->update($permission, $validated);

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $permission)
    {

        $this->authorize("permission.delete");
        $this->permissionService->destroy($permission);
        return redirect()->route('permissions.index')->with('success','Permission deleted successfully.');
    }
}

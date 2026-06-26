<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AksesController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        $users = User::with('roles', 'permissions')->latest()->get();
        return view('pages.superadmin.akses-user.index', compact('roles', 'permissions', 'users'));
    }

    public function storeRole(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'guard_name' => 'required|string',
        ]);

        DB::beginTransaction();
        try {

            Role::create([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'],
            ]);
            DB::commit();
            return redirect()->back()->with('success', 'Role berhasil ditambahkan');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Role gagal ditambahkan ' . $th->getMessage());
        }
    }

    public function storePermission(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'guard_name' => 'required|string',
        ]);

        DB::beginTransaction();
        try {

            Permission::create([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'],
            ]);
            DB::commit();
            return redirect()->back()->with('success', 'Permission berhasil ditambahkan');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Permission gagal ditambahkan ' . $th->getMessage());
        }
    }

    public function updateRole(Role $role, Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
        ]);

        DB::beginTransaction();
        try {

            $role->update($data);
            DB::commit();
            return redirect()->route('superadmin.akses-user.index')->with('success', 'Role berhasil diperbarui');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('superadmin.akses-user.index')->with('error', 'Role gagal diperbarui ' . $th->getMessage());
        }
    }

    public function updatePermission(Permission $permission, Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
        ]);

        DB::beginTransaction();
        try {

            $permission->update($data);
            DB::commit();
            return redirect()->route('superadmin.akses-user.index')->with('success', 'Permission berhasil diperbarui');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('superadmin.akses-user.index')->with('error', 'Permission gagal diperbarui ' . $th->getMessage());
        }
    }

    public function destroyRole(Role $role, Request $request)
    {
        $role = Role::findOrFail($role->id);

        DB::beginTransaction();
        try {

            if ($role) {
                $role->delete();
            }
            DB::commit();
            return redirect()->route('superadmin.akses-user.index')->with('success', 'Role berhasil dihapus');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('superadmin.akses-user.index')->with('error', 'Role gagal dihapus ' . $th->getMessage());
        }
    }

    public function destroyPermission(Permission $permission, Request $request)
    {
        $permission = Permission::findOrFail($permission->id);

        DB::beginTransaction();
        try {

            if ($permission) {
                $permission->delete();
            }
            DB::commit();
            return redirect()->route('superadmin.akses-user.index')->with('success', 'Permission berhasil dihapus');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('superadmin.akses-user.index')->with('error', 'Permission gagal dihapus ' . $th->getMessage());
        }
    }

    public function user()
    {
        $users = User::with('roles', 'permissions')->latest()->get();
        $roles = Role::all();
        $permissions = Permission::all();

        return view('pages.superadmin.akses-user.assign', compact('users', 'roles', 'permissions'));
    }

    public function userStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => 'array|exists:roles,name',
            'permissions' => 'array|exists:permissions,name',
        ]);

        DB::beginTransaction();
        try {

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'original_password' => 'kepoyah',
            ]);

            $user->assignRole($data['roles']);
            if ($data['permissions']) {
                $user->givePermissionTo($data['permissions']);
            }
            DB::commit();
            return redirect()->back()->with('success', 'User berhasil ditambahkan');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'User gagal ditambahkan ' . $th->getMessage());
        }
    }

    public function updateAssign(User $user, Request $request)
    {
        $data = $request->validate([
            'roles' => 'array|exists:roles,name',
            'permissions' => 'array|exists:permissions,name',
        ]);

        DB::beginTransaction();
        try {
            // Update roles dan permissions
            $user->syncRoles($data['roles']);
            $user->syncPermissions($data['permissions']);

            DB::commit();
            return redirect()->route('superadmin.akses-user.user.index')->with('success', 'Berhasil memperbarui akses user');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('superadmin.akses-user.user.index')->with('gagal', 'Gagal memperbarui ' . $th->getMessage());
        }
    }
}

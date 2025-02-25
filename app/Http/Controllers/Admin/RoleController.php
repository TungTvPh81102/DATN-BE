<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Roles\StoreRoleRequest;
use App\Http\Requests\Admin\Roles\UpdateRoleRequest;
use App\Imports\RolesImport;
use App\Traits\LoggableTrait;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use LoggableTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $roles = Role::all();

            $title = 'Quản lý vai trò';
            $subTitle = 'Danh sách vai trò của hệ thống';

            return view('roles.index', compact([
                'title',
                'subTitle',
                'roles',
            ]));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $title = 'Quản lý vai trò';
            $subTitle = 'Thêm mới vai trò';

            $permissions = Permission::all()->groupBy(function ($permission) {
                return Str::before($permission->name, '.');
            });

            return view('roles.create', compact([
                'title',
                'subTitle',
            ]));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $role = Role::query()->create($data);

            DB::commit();
            return redirect()->route('admin.roles.index')->with('success', 'Thao tác thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::query()->findOrFail($id);
        return view('roles.profile', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        try {
            $title = 'Quản lý vai trò';
            $subTitle = 'Cập nhật vai trò: ' . $role->name;
            $permissions = Permission::all()->groupBy(function ($permission) {
                return Str::before($permission->name, '.');
            });

            return view('roles.edit', compact([
                'title',
                'subTitle',
                'role',
                'permissions'
            ]));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $role = Role::query()->findOrFail($id);

            if (!$role) {
                return redirect()->back()->with('error', 'Không tìm thấy vai trò');
            }

            $data = $request->validated();

            $role->update($data);

            $permissions = Permission::whereIn('id', $request->input('permissions', []))
                ->where('guard_name', $role->guard_name)
                ->pluck('id');

            $role->syncPermissions($permissions);

            DB::commit();

            return redirect()->back()->with('success', 'Cập nhật vai trò thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $role = Role::query()->findOrFail($id);

            if (!$role) {
                return redirect()->back()->with('error', 'Không tìm thấy vai trò');
            }

            $role->permissions()->detach();

            $role->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Xoá dữ liệu thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function import(Request $request) {
        try {
            
            $request->validate([
                'file' => 'required|mimes:xlsx,csv',
            ]);

            $file = $request->file('file');

            Excel::import(new RolesImport, $file);

            return redirect()->route('admin.roles.index')->with('success', 'Import dữ liệu thành công');

        }catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
}

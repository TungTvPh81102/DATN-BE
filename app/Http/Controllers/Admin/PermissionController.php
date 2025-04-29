<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PermissionExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Permissions\StorePermissionRequest;
use App\Http\Requests\Admin\Permissions\UpdatePermissionRequest;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class PermissionController extends Controller
{
    use LoggableTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $queryPermissions = Permission::query();

            if ($request->hasAny(['name', 'description', 'created_at', 'updated_at']))
                $queryPermissions = $this->filter($request, $queryPermissions);

            if ($request->has('search_full'))
                $queryPermissions = $this->search($request->search_full, $queryPermissions);

            $permissions = $queryPermissions->paginate(12);

            $groupedPermissions = $permissions->getCollection()->groupBy(function ($permission) {
                return Str::before($permission->name, '.');
            });

            $permissions->setCollection(collect($groupedPermissions));

            if ($request->ajax()) {
                $html = view('permissions.table', compact('permissions'))->render();
                return response()->json(['html' => $html]);
            }

            $title = 'Quản lý quyền';
            $subTitle = 'Danh sách quyền của hệ thống';

            return view('permissions.index', compact([
                'title',
                'subTitle',
                'permissions'
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request)
    {
        try {
            $data = $request->validated();

            Permission::query()->create($data);

            return redirect()->route('admin.permissions.index')->with('success', 'Thao tác thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->route('admin.permissions.index')->with('error', 'Lỗi: ' . $e->getMessage());
        }
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermissionRequest $request, $id)
    {
        $request->validated();

        $permission = Permission::findOrFail($id); // Thay WithdrawalRequest bằng model Permission nếu khác
        $permission->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true, 'message' => 'Cập nhật quyền thành công']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $permission = Permission::query()->findOrFail($id);

            if (!$permission) {
                return redirect()->back()->with('error', 'Không tìm thấy quyền');
            }

            $permission->delete();

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
    private function filter($request, $query)
    {
        $filters = [
            'created_at' => ['queryWhere' => '>='],
            'updated_at' => ['queryWhere' => '<='],
            'name' => ['queryWhere' => 'LIKE'],
            'description' => ['queryWhere' => 'LIKE'],
        ];

        foreach ($filters as $filter => $value) {
            if (!empty($value['queryWhere'])) {
                $filterValue = $request->input($filter);
                if (!empty($filterValue)) {
                    $filterValue = $value['queryWhere'] === 'LIKE' ? "%$filterValue%" : $filterValue;
                    $query->where($filter, $value['queryWhere'], $filterValue);
                }
            }
        }

        return $query;
    }

    private function search($searchTerm, $query)
    {
        if (!empty($searchTerm)) {
            $query->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%$searchTerm%")
                    ->orWhere('description', 'LIKE', "%$searchTerm%");
            });
        }

        return $query;
    }
    public function export()
    {
        try {

            return Excel::download(new PermissionExport, 'Permissions.xlsx');
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Users\StoreUserRequest;
use App\Http\Requests\Admin\Users\UpdateUserRequest;
use App\Models\User;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Notifications\CrudNotification;

class UserController extends Controller
{
    use LoggableTrait, UploadToCloudinaryTrait;

    const FOLDER = 'users';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $title = 'Quản lý thành viên';
            $subTitle = 'Danh sách người dùng';

            $users = User::query()
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'member');
                })
                ->latest('id')
                ->paginate(10);

            $userCounts = User::query()
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'member');
                })
                ->selectRaw('
                    count(id) as total_users,
                    sum(status = "active") as active_users,
                    sum(status = "inactive") as inactive_users,
                    sum(status = "blocked") as blocked_users
                ')
                ->first();

            return view('users.index', compact('users', 'userCounts', 'subTitle', 'title'));
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
            $title = 'Quản lý thành viên';
            $subTitle = 'Thêm mới người dùng';

            $roles = Role::query()->get()->pluck('name')->toArray();

            return view('users.create', compact([
                'title',
                'subTitle',
                'roles'
            ]));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->except('avatar');

            if ($request->hasFile('avatar')) {
                $data['avatar'] = $this->uploadImage($request->file('avatar'), self::FOLDER);
            }

            $data['code'] = str_replace('-', '', Str::uuid()->toString());

            $data['email_verified_at'] = now();

            $user =  User::query()->create($data);

            $user->assignRole($request->role);

            DB::commit();
            
            CrudNotification::sendToMany([],$user->id);

            return redirect()->route('admin.users.index')->with('success', 'Thêm mới thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['avatar']) && !empty($data['avatar']) && filter_var($data['avatar'], FILTER_VALIDATE_URL)) {
                $this->deleteImage($data['avatar'], self::FOLDER);
            }


            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {

            $title = 'Quản lý thành viên';
            $subTitle = 'Chi tiết người dùng';

            $user = User::query()->findOrFail($id);

            return view('users.show', compact([
                'user',
                'title',
                'subTitle'
            ]));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {

            $title = 'Quản lý thành viên';
            $subTitle = 'Cập nhật người dùng';

            $user = User::query()
                ->with('roles')
                ->findOrFail($id);

            // dd($user->roles->pluck('name')->toArray());

            $roles = Role::query()->get()->pluck('name')->toArray();

            return view('users.edit', compact([
                'user',
                'title',
                'subTitle',
                'roles'
            ]));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            $validator = $request->validated();

            $data = $request->except('avatar');

            DB::beginTransaction();

            $user = User::query()->findOrFail($id);

            $currencyAvatar = $user->avatar;

            if ($request->hasFile('avatar')) {
                $data['avatar'] = $this->uploadImage($request->file('avatar'), self::FOLDER);

                if (
                    isset($data['avatar']) && !empty($data['avatar'])
                    && filter_var($data['avatar'], FILTER_VALIDATE_URL)
                    && !empty($currencyAvatar)
                ) {
                    $this->deleteImage($currencyAvatar, self::FOLDER);
                }
            } else {
                $data['avatar'] = $currencyAvatar;
            }

            $user->update($data);

            if ($request->has('role')) {
                $user->syncRoles([]);

                $user->assignRole($request->input('role'));
            }

            DB::commit();
            
            CrudNotification::sendToMany([],$id);

            return redirect()->route('admin.users.edit', $id)->with('success', 'Cập nhật thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['avatar']) && !empty($data['avatar']) && filter_var($data['avatar'], FILTER_VALIDATE_URL)) {
                $this->deleteImage($data['avatar']);
            }

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

            if (str_contains($id, ',')) {

                $userID = explode(',', $id);

                $this->deleteUsers($userID);
            } else {
                $user = User::query()->findOrFail($id);

                $data['avatar'] = $user->avatar;

                $user->delete();

                if (isset($data['avatar']) && !empty($data['avatar']) && filter_var($data['avatar'], FILTER_VALIDATE_URL)) {
                    $this->deleteImage($data['avatar'], self::FOLDER);
                }
            }

            DB::commit();

            CrudNotification::sendToMany([],$id);

            return response()->json([
                'status' => 'success',
                'message' => 'Xóa thành công'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            $this->logError($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Xóa thất bại'
            ]);
        }
    }

    private function deleteUsers(array $userID)
    {

        $users = User::query()->whereIn('id', $userID)->get();

        foreach ($users as $user) {

            $avatar = $user->avatar;

            $user->delete();

            if (isset($avatar) && !empty($avatar) && filter_var($avatar, FILTER_VALIDATE_URL)) {
                $this->deleteImage($avatar, self::FOLDER);
            }
        }
    }
}

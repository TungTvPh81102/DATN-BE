<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\StoreSettingRequest;
use App\Http\Requests\Admin\Settings\UpdateSettingRequest;
use App\Models\Setting;
use App\Notifications\CrudNotification;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    use LoggableTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $title = 'Quản lý cài đặt';
            $subTitle = 'Danh sách các cài đặt';

            $settings = Setting::latest('id')->paginate(10);

            return view('settings.index', compact(['title', 'subTitle', 'settings']));
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
            $title = 'Quản lý cài đặt';
            $subTitle = 'Thêm mới cài đặt';

            return view('settings.create', compact([
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
    public function store(StoreSettingRequest $request)
    {
        try {

            $data = $request->validated();

            $setting = Setting::create($data);

            CrudNotification::sendToMany([],$setting->id);

            return redirect()->route('admin.settings.index')->with('success', true);
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
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
        try {

            $setting = Setting::query()->findOrFail($id);

            $title = 'Quản lý cài đặt';
            $subTitle = 'Cập nhật cài đặt';

            return view('settings.edit', compact([
                'setting',
                'title',
                'subTitle',
            ]));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSettingRequest $request, string $id)
    {
        try {

            $data = $request->validated();

            $setting = Setting::query()->findOrFail($id);

            $setting->update($data);

            CrudNotification::sendToMany([],$setting->id);

            return redirect()->route('admin.settings.edit', $id)->with('success', true);
        } catch (\Exception $e) {

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

                $settingID = explode(',', $id);

                $setting = Setting::query()->whereIn('id',$settingID)->delete();
            } else {
                $setting = Setting::query()->findOrFail($id);

                $setting->delete();
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
}

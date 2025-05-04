<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Banners\StoreBannerRequest;
use App\Http\Requests\Admin\Banners\UpdateBannerRequest;
use App\Models\Banner;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    use LoggableTrait, UploadToCloudinaryTrait, FilterTrait;

    const FOLDER = 'banners';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryBanners = Banner::orderBy('order', 'asc');

        // Kiểm tra nếu có từ khóa tìm kiếm
        if ($request->has('search_full') && $request->input('search_full')) {
            $search = $request->input('search_full');
            $queryBanners = $queryBanners->where('title', 'LIKE', "%$search%");
        }
        if ($request->hasAny(['title', 'id', 'status', 'created_at', 'updated_at'])) {
            $queryBanners = $this->filter($request, $queryBanners);
        }
        // Lấy dữ liệu và phân trang
        $banners = $queryBanners->paginate(10);
        if ($request->ajax()) {
            $html = view('banners.table', compact('banners'))->render();
            return response()->json(['html' => $html]);
        }
        return view('banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('banners.create');
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBannerRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->all();

            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadImage($request->file('image'), self::FOLDER);
            }
            $data['redirect_url'] = !empty($data['title']) ? Str::slug($data['title']) : null;
            $data['status'] ??= 0;
            $lastBanner = Banner::orderBy('order', 'desc')->first();
            $data['order'] = $lastBanner ? $lastBanner->order + 1 : 0;
            Banner::query()->create($data);

            DB::commit();

            return redirect()->route('admin.banners.index')->with('success', 'Thêm mới thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image']) && !empty($data['image']) && filter_var($data['image'], FILTER_VALIDATE_URL)) {
                $this->deleteImage($data['image'], self::FOLDER);
            }

            $this->logError($e);

            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $banner = Banner::findOrFail($id);
        return view('banners.show', compact('banner'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $banner = Banner::findOrFail($id);
        return view('banners.edit', compact('banner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBannerRequest $request, $id)
    {
        try {
            $data = $request->all();

            DB::beginTransaction();

            $banner = Banner::findOrFail($id);

            $imageOld = $banner->image;

            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadImage($request->file('image'), self::FOLDER);

                if (
                    isset($data['image']) && !empty($data['image'])
                    && filter_var($data['image'], FILTER_VALIDATE_URL)
                    && !empty($imageOld)
                ) {
                    $this->deleteImage($imageOld, self::FOLDER);
                }
            } else {
                $data['image'] = $imageOld;
            }

            $banner->update($data);

            DB::commit();

            return redirect()->route('admin.banners.edit', $banner->id)->with('success', 'Cập nhật thành công');
        } catch (\Exception $e) {

            DB::rollBack();

            if (isset($data['image']) && !empty($data['image']) && filter_var($data['image'], FILTER_VALIDATE_URL)) {
                $this->deleteImage($data['image'], self::FOLDER);
            }

            $this->logError($e);

            return back()->with('success', false)->with('error', $e->getMessage());
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

                $bannerID = explode(',', $id);

                $this->deleteBanners($bannerID);
            }else {
                $banner = Banner::query()->findOrFail($id);

                $banner->delete();
    
                if (!empty($banner->image) && filter_var($banner->image, FILTER_VALIDATE_URL)) {
                    $this->deleteImage($banner->image,  self::FOLDER);
                }
                Banner::where('order', '>', $banner->order)
                    ->decrement('order');
            }
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Xoá dữ liệu thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return back()
                ->with('success', false)
                ->with('error', 'Lỗi.');
        }
    }

    public function listDeleted(Request $request)
    {
        $queryBanners = Banner::latest('id')->onlyTrashed();
        $banner_deleted_at = true;

        if ($request->has('search_full') && $request->input('search_full')) {
            $search = $request->input('search_full');
            $queryBanners = $queryBanners->where('title', 'LIKE', "%$search%");
        }
        if ($request->hasAny(['title', 'id', 'status', 'start_deleted', 'end_deleted'])) {
            $queryBanners = $this->filter($request, $queryBanners);
        }

        $banners = $queryBanners->paginate(10);

        if ($request->ajax()) {
            $html = view('banners.table', compact(['banners', 'banner_deleted_at']))->render();
            return response()->json(['html' => $html]);
        }

        return view('banners.deleted', compact('banners'));
    }

    public function restoreDelete(string $id)
    {
        try {
            DB::beginTransaction();

            if (str_contains($id, ',')) {

                $bannerID = explode(',', $id);

                $this->restoreDeleteBanners($bannerID);
            } else {
                $banner = Banner::query()->onlyTrashed()->findOrFail($id);

                if ($banner->trashed()) {
                    $banner->restore();
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Khôi phục thành công'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            $this->logError($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Khôi phục thất bại'
            ]);
        }
    }


    public function forceDelete(string $id)
    {
        try {
            DB::beginTransaction();

            if (str_contains($id, ',')) {

                $bannerID = explode(',', $id);

                $this->deleteBanners($bannerID);
            } else {
                $banner = Banner::query()->onlyTrashed()->findOrFail($id);

                $banner->forceDelete();
            }

            DB::commit();

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


    private function deleteBanners(array $bannerID)
    {

        $banners = Banner::query()->whereIn('id', $bannerID)->withTrashed()->get();

        foreach ($banners as $banner) {

            if ($banner->trashed()) {
                $banner->forceDelete();
            } else {
                $banner->delete();
            }
        }
    }
    private function restoreDeleteBanners(array $bannerID)
    {

        $banners = Banner::query()->whereIn('id', $bannerID)->onlyTrashed()->get();

        foreach ($banners as $banner) {

            if ($banner->trashed()) {
                $banner->restore();
            }
        }
    }
    private function filter($request, $query)
    {
        $filters = [
            'created_at' => ['queryWhere' => '>='],
            'updated_at' => ['queryWhere' => '<='],
            'id' => ['queryWhere' => 'LIKE'],
            'title' => ['queryWhere' => 'LIKE'],
            'status' => ['queryWhere' => '='],
            'deleted_at' => ['attribute' => ['start_deleted' => '>=', 'end_deleted' => '<=',]],
        ];

        $query = $this->filterTrait($filters, $request, $query);

        return $query;
    }
    public function updateOrder(Request $request)
    {
        $data = $request->input('orderData'); // Nhận dữ liệu sắp xếp từ phía client

        foreach ($data as $order => $id) {
            Banner::where('id', $id)->update(['order' => $order]);
        }

        return response()->json(['status' => 'success']);
    }
}

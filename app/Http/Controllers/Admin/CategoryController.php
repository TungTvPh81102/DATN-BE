<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Categories\StoreCategoryRequest;
use App\Http\Requests\Admin\Categories\UpdateCategoryRequest;
use App\Models\Category;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use LoggableTrait, UploadToCloudinaryTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $categories = Category::query()->with('parent')->get();
        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        //

        // dd($request->all());

        try {
            //code...

            $data = $request->except('icon');

            $data['status'] ??= 0;

            if ($request->hasFile('icon')) {
                $data['icon'] = $this->uploadImage($request->file('icon'), 'categories');
            }

            Category::query()->create($data);

            return redirect()->route('admin.categories.index')->with('success', 'Thao tác thành công');

        } catch (\Exception $e) {
            //throw $th;

            if (
                !empty($data['icon']) 
                && filter_var($data['icon'], FILTER_VALIDATE_URL)
                ) 
            {
                $this->deleteImage($data['icon'], 'categories');
            }

            $this->logError($e);

            return redirect()
                ->back()
                ->with('fasle', 'Thao tác không thành công');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $category = Category::findOrFail($id);
        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $id)
    {
        //
        // dd($request->all());

        try {

            $category = Category::findOrFail($id);

            $data = $request->except('icon');

            $data['status'] ??= 0;

            if ($request->hasFile('icon')) {
                $data['icon'] = $this->uploadImage($request->file('icon'), 'categories');
            }

            $currencyIcon = $category->icon;

            $category->update($data);

            // kiem tra truong icon co tin tai hay khong , url co hop le hay khong va url cu co hay khong 

            if (
                !empty($data['icon'])
                && filter_var($data['icon'], FILTER_VALIDATE_URL)
                && !empty($currencyIcon)
            ) {
                $this->deleteImage($currencyIcon,'categories');
            }

            return back()->with('success', 'Thao tác thành công');
        } catch (\Exception $e) {
            //throw $th;
            if (
                !empty($data['icon']) 
                && filter_var($data['icon'], FILTER_VALIDATE_URL)
            ) {
                $this->deleteImage($data['icon'], 'categories');
            }

            $this->logError($e);
            return redirect()
                ->back()
                ->with('success', false);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //

        try {
            //code...
            $category->delete();

            if(isset($category->icon))
            {
                $this->deleteImage($category->icon, 'categories');
            }
            return response()->json($data = ['status' => 'success', 'message' => 'Mục đã được xóa.']);
        } catch (\Exception $e) {
            //throw $th;
            $this->logError($e);

            return response()->json($data = ['status' => 'error', 'message' => 'Lỗi thao tác.']);
        }
    }
}

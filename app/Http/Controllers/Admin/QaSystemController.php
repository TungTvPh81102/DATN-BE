<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QaSystems\StoreQaSystemRequest;
use App\Http\Requests\Admin\QaSystems\UpdateQaSystemRequest;
use App\Models\QaSystem;
use App\Traits\LoggableTrait;

class QaSystemController extends Controller
{
    use LoggableTrait;

    public function index()
    {
        try {
            $title = 'Hệ thống quản lý câu hỏi';
            $subTitle = 'Danh sách hệ thống quản lý câu hỏi';

            $qaSystems = QaSystem::all();

            return view('qa-systems.index', compact([
                'title',
                'subTitle',
                'qaSystems',
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function create()
    {
        try {
            $title = 'Hệ thống quản lý câu hỏi';
            $subTitle = 'Thêm câu hỏi ';

            return view('qa-systems.create', compact([
                'title',
                'subTitle',
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function store(StoreQaSystemRequest $request)
    {
        try {
            $data = $request->validated();
            $data['options'] = json_encode($data['options']);

            QaSystem::create($data);

            return redirect()->route('admin.qa-systems.index')
                ->with('success', 'Thêm câu hỏi thành công');
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function edit(string $id)
    {
        try {
            $qaSystem = QaSystem::query()->findOrFail($id);

            if (!$qaSystem) {
                return redirect()->back()->with('error', 'Không tìm thấy câu hỏi');
            }

            $title = 'Hệ thống quản lý câu hỏi';
            $subTitle = 'Chỉnh sửa câu hỏi: ' . $qaSystem->title;

            return view('qa-systems.edit', compact([
                'title',
                'subTitle',
                'qaSystem',
            ]));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function update(UpdateQaSystemRequest $request, string $id)
    {
        try {
            $qaSystem = QaSystem::query()->findOrFail($id);

            $data = $request->validated();

            if (isset($data['options'])) {
                $data['options'] = json_encode($data['options']);
            }

            $qaSystem->fill($data)->save();

            return redirect()->back()->with('success', 'Cập nhật câu hỏi thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại');
        }
    }

    public function destroy(string $id)
    {
        try {
            $qaSystem = QaSystem::query()->findOrFail($id);

            if (!$qaSystem) {
                return redirect()->back()->with('error', 'Không tìm thấy câu hỏi');
            }

            $qaSystem->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Xoá dữ liệu thành công'
            ]);
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại');
        }
    }
}

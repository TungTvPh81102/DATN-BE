<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QaSystems\ImportQaSystemRequest;
use App\Http\Requests\Admin\QaSystems\StoreQaSystemRequest;
use App\Http\Requests\Admin\QaSystems\UpdateQaSystemRequest;
use App\Imports\QaSystemImport;
use App\Models\QaSystem;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;

use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Http\Request;


class QaSystemController extends Controller
{
    use LoggableTrait, FilterTrait;

    public function index(Request $request)
    {
        try {
            $title = 'Hệ thống quản lý câu hỏi';
            $subTitle = 'Danh sách hệ thống quản lý câu hỏi';

            $queryQaSystems = QaSystem::query()->latest('id');

            if ($request->ajax()) {
                $queryQaSystems = $this->filter($request, $queryQaSystems);

                $queryQaSystems = $this->search($request, $queryQaSystems);
            }

            $qaSystems = $queryQaSystems->paginate(10);

            if ($request->ajax()) {
                $html = view('qa-systems.includes.table', compact('qaSystems'))->render();
                return response()->json(['html' => $html]);
            }

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


    public function importFile(ImportQaSystemRequest $request)
    {
        try {

        Excel::import(new QaSystemImport, $request->file('file'));

        return redirect()->route('admin.qa-systems.index')->with('success', 'Import dữ liệu thành công');

        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
    private function filter($request, $query)
    {
        $filters = [
            'status' => ['queryWhere' => '='],
            'title' => ['queryWhere' => 'LIKE'],
            'answer_type' => ['queryWhere' => '='],
            'created_at' => ['attribute' => ['startDate' => ">=", 'endDate' => "<="]],
        ];

        $query = $this->filterTrait($filters, $request, $query);

        return $query;
    }

    private function search($request, $query)
    {
        if (!empty($request->search_full)) {
            $searchTerm = $request->search_full;

            $query->where(function ($query) use ($searchTerm) {
                $query->where('title', 'LIKE', "%$searchTerm%");
            });
        }

        return $query;
    }
}

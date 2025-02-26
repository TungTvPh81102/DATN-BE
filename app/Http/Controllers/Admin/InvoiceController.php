<?php

namespace App\Http\Controllers\Admin;

use App\Exports\InvoicesExport;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class InvoiceController extends Controller
{
    use LoggableTrait, FilterTrait;

    public function index(Request $request)
    {
        try {
            $title = 'Khóa học đã bán';
            $subTitle = 'Khóa học đã bán';

            $queryInvoice = Invoice::query()
                ->with([
                    'course',
                    'user'
                ])
                ->latest('id')
                ->where('status', 'Đã thanh toán');

            $queryInvoice = $this->filterSearch($request, $queryInvoice);

            $invoices = $queryInvoice->paginate(10);

            if ($request->ajax()) {
                $html = view('invoices.table', compact('invoices'))->render();
                return response()->json(['html' => $html]);
            }

            return view('invoices.index', compact(['title', 'subTitle', 'invoices']));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    private function filterSearch(Request $request, $queryInvoice)
    {
        try {
            if ($request->hasAny(['course_user_name', 'user_name_invoice', 'course_name_invoice', 'amount_min', 'amount_max', 'startDate', 'endDate', 'code']))
                $queryInvoice = $this->filter($request, $queryInvoice);

            if ($request->has('search_full'))
                $queryInvoice = $this->search($request->search_full, $queryInvoice);

            return $queryInvoice;
        } catch (\Exception $e) {
            $this->logError($e, $request->all());
        }
    }

    private function filter($request, $query)
    {
        $filters = [
            'code' => ['queryWhere' => 'LIKE'],
            'created_at' => ['attribute' => ['startDate' => '>=', 'endDate' => '<=']],
            'final_total' => ['attribute' => ['amount_min' => '>=', 'amount_max' => '<=']],
            'user_name_invoice' => null,
            'course_name_invoice' => null,
        ];

        $query = $this->filterTrait($filters, $request, $query);

        if ($request->has('course_user_name') && !empty($request->course_user_name)) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->whereHas('user', function ($q1) use ($request) {
                    $q1->where('name', 'LIKE', "%$request->course_user_name%");
                });
            });
        }

        return $query;
    }

    public function export()
    {
        try {

            return Excel::download(new InvoicesExport, 'Invoices.xlsx');
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    private function search($searchTerm, $query)
    {
        if (!empty($searchTerm)) {
            $query->where(function ($query) use ($searchTerm) {
                $query->whereHas('user', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%$searchTerm%");
                })
                    ->orWhereHas('course', function ($q1) use ($searchTerm) {
                        $q1->where('name', 'LIKE', "%$searchTerm%")
                            ->orWhereHas('user', function ($q2) use ($searchTerm) {
                                $q2->where('name', 'LIKE', "%$searchTerm%");
                            });
                    });
            });
        }

        return $query;
    }
}

@extends('layouts.app')

@push('page-css')
    <link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <style>
        .no-data {
            text-align: center;
            font-size: 18px;
            color: #999;
            padding: 20px;
        }

        .highcharts-series rect {
            transition: all 0.3s ease-in-out;
        }

        .highcharts-series rect:hover {
            filter: brightness(1.2);
            transform: scale(1.05);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title ?? 'Dashboard' }}</h4>
                </div>
            </div>
        </div>

        <!-- Greeting -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="bg-white p-4 rounded shadow-sm">
                    <h4 class="fs-20 mb-1 text-primary" id="greeting">Xin chào, {{ Auth::user()->name ?? '' }}!</h4>
                    <p class="text-muted mb-0">Chúc bạn một ngày làm việc hiệu quả!</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 cursor-pointer">
            <!-- Tổng doanh thu -->
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body p-4"
                        style="background: linear-gradient(135deg, #e9f7ef, #d4efdf); border-radius: 12px; position: relative;">
                        <span class="percentage-change fw-bold"
                            style="position: absolute; top: 10px; right: 10px; color: {{ $revenueChange > 0 ? 'green' : ($revenueChange < 0 ? 'red' : 'gray') }};">
                            @if ($revenueChange > 0)
                                <i class="ri-arrow-right-up-line fs-13 align-middle"></i>
                            @else
                                <i class="ri-arrow-right-down-line fs-13 align-middle"></i>
                            @endif
                            {{ fmod($revenueChange, 1) == 0 ? number_format($revenueChange, 0) : number_format($revenueChange, 2) }}%
                        </span>
                        <p class="text-uppercase fw-semibold text-muted mb-3 fs-13">Tổng doanh thu</p>

                        <div class="d-flex align-items-center justify-content-between">
                            <div class="avatar-sm me-3 flex-shrink-0">
                                <span
                                    class="avatar-title bg-success-subtle rounded-circle fs-2 d-flex align-items-center justify-content-center shadow-sm">
                                    <i class="bx bx-dollar-circle text-success"></i>
                                </span>
                            </div>
                            <h4 class="fs-24 fw-bold text-dark mb-0 flex-grow-1 text-end">
                                <span class="counter-value"
                                    data-target="totalRevenue">{{ number_format($totalAmount->total_revenue ?? 0, 0, '.', '.') }}</span>
                                <span class="fs-14 text-muted"></span>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lợi nhuận đạt được -->
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body p-4"
                        style="background: linear-gradient(135deg, #e9f2ff, #d6eaff); border-radius: 12px;">
                        <span class="percentage-change fw-bold"
                            style="position: absolute; top: 10px; right: 10px; color: {{ $profitChange > 0 ? 'green' : ($profitChange < 0 ? 'red' : 'gray') }};">
                            @if ($profitChange > 0)
                                <i class="ri-arrow-right-up-line fs-13 align-middle"></i>
                            @else
                                <i class="ri-arrow-right-down-line fs-13 align-middle"></i>
                            @endif
                            {{ fmod($profitChange, 1) == 0 ? number_format($profitChange, 0) : number_format($profitChange, 2) }}%
                        </span>
                        <p class="text-uppercase fw-semibold text-muted mb-3 fs-13">Lợi nhuận đạt được</p>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="avatar-sm me-3 flex-shrink-0">
                                <span
                                    class="avatar-title bg-info-subtle rounded-circle fs-2 d-flex align-items-center justify-content-center shadow-sm">
                                    <i class="bx bx-dollar-circle text-info"></i>
                                </span>
                            </div>
                            <h4 class="fs-24 fw-bold text-dark mb-0 flex-grow-1 text-end">
                                <span class="counter-value"
                                    data-target="totalProfit">{{ number_format($totalAmount->total_profit ?? 0, 0, '.', '.') }}</span>
                                <span class="fs-14 text-muted"></span>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body p-4"
                        style="background: linear-gradient(135deg, #fff3e6, #ffeedb); border-radius: 12px;">
                        <span class="percentage-change fw-bold"
                            style="position: absolute; top: 10px; right: 10px; color: {{ $courseChange > 0 ? 'green' : ($courseChange < 0 ? 'red' : 'gray') }};">
                            @if ($courseChange > 0)
                                <i class="ri-arrow-right-up-line fs-13 align-middle"></i>
                            @else
                                <i class="ri-arrow-right-down-line fs-13 align-middle"></i>
                            @endif
                            {{ fmod($courseChange, 1) == 0 ? number_format($courseChange, 0) : number_format($courseChange, 2) }}%
                        </span>
                        <p class="text-uppercase fw-semibold text-muted mb-3 fs-13">Tổng khóa học</p>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="avatar-sm me-3 flex-shrink-0">
                                <span
                                    class="avatar-title bg-warning-subtle rounded-circle fs-2 d-flex align-items-center justify-content-center shadow-sm">
                                    <i class="las la-book-reader text-warning"></i>
                                </span>
                            </div>
                            <h4 class="fs-24 fw-bold text-dark mb-0 flex-grow-1 text-end">
                                <span class="counter-value" data-target="totalCourse">{{ $totalCourse ?? 0 }}</span>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body p-4"
                        style="background: linear-gradient(135deg, #e9e9ff, #dcdbff); border-radius: 12px;">
                        <span class="percentage-change fw-bold"
                            style="position: absolute; top: 10px; right: 10px; color: {{ $instructorChange > 0 ? 'green' : ($instructorChange < 0 ? 'red' : 'gray') }};">
                            @if ($instructorChange > 0)
                                <i class="ri-arrow-right-up-line fs-13 align-middle"></i>
                            @else
                                <i class="ri-arrow-right-down-line fs-13 align-middle"></i>
                            @endif
                            {{ fmod($instructorChange, 1) == 0 ? number_format($instructorChange, 0) : number_format($instructorChange, 2) }}%
                        </span>
                        <p class="text-uppercase fw-semibold text-muted mb-3 fs-13">Tổng số giảng viên </p>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="avatar-sm me-3 flex-shrink-0">
                                <span
                                    class="avatar-title bg-primary-subtle rounded-circle fs-2 d-flex align-items-center justify-content-center shadow-sm">
                                    <i class="ri-account-circle-line text-primary"></i>
                                </span>
                            </div>
                            <h4 class="fs-24 fw-bold text-dark mb-0 flex-grow-1 text-end">
                                <span class="counter-value"
                                    data-target="totalInstructor">{{ $totalInstructor ?? 0 }}</span>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Analysis -->
        <div class="row mt-2 g-4">
            <div class="col-xl-7">
                <div class="card">
                    <div class="card-header bg-primary bg-gradient bg-opacity-60 d-flex align-items-center">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Biểu đồ tổng quan top 10 danh mục</h4>
                    </div>
                    <div class="card-body">
                        <div id="category-revenue-chart" class="apex-charts"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card">
                    <div
                        class="card-header bg-primary bg-gradient bg-opacity-60 d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0 text-white">Tổng quan top 10 danh mục</h4>
                        <button class="badge bg-warning mx-2 rounded-5 dowloadExcel" data-type="top_category"><i
                                class='fs-9 bx bx-download'> Excel</i></button>
                    </div>
                    <div class="card-body" style="overflow-x: hidden; max-width: 100%;">
                        <div class="table-responsive table-card" style="overflow-x: hidden;">
                            <table id="table-categories" class="table table-centered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th
                                            style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            Danh mục
                                        </th>
                                        <th style="width: 100px;">Khóa học</th>
                                        <th style="width: 100px;">Học viên</th>
                                        <th style="width: 100px;">Giảng viên</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($categoryStats as $category)
                                        <tr>
                                            <td
                                                style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $category->category_name ?? 'Không xác định' }}
                                            </td>
                                            <td class="text-center">{{ $category->total_courses }}</td>
                                            <td class="text-center">{{ $category->total_enrolled_students }}</td>
                                            <td class="text-center">{{ $category->total_instructors }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Category Analysis -->

        <!-- Revenue Chart -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header bg-primary bg-gradient bg-opacity-60 border-0 align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Doanh thu 2025 CourseMeLy</h4>
                    </div>
                    <!-- end card header -->


                    <div>
                        <div class="row g-0 text-center">
                            <div class="col-6 col-sm-6">
                                <div class="p-3 border border-dashed border-start-0">
                                    <h5 class="mb-1"><span class="counter-value-revenue" data-target="228.89">
                                            {{ number_format($totalAmount->total_revenue ?? 0, 0, '.', '.') }}</span>
                                        VND</h5>
                                    <p class="text-muted mb-0">Doanh thu</p>
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-6 col-sm-6">
                                <div class="p-3 border border-dashed border-start-0 border-end-0">
                                    <h5 class="mb-1 text-success"><span class="counter-value-profit"
                                            data-target="10589">{{ number_format($totalAmount->total_profit ?? 0, 0, '.', '.') }}</span>
                                        VND</h5>
                                    <p class="text-muted mb-0">Lợi nhuận</p>
                                </div>
                            </div>
                            <!--end col-->
                        </div>
                    </div><!-- end card header -->
                    <div class="card-body p-0 pb-2">
                        <div>
                            <div id="projects-overview-chart"
                                data-colors='["--vz-primary", "--vz-warning", "--vz-danger"]' dir="ltr"
                                class="apex-charts"></div>
                        </div>
                    </div><!-- end card body -->
                </div><!-- end card -->
            </div><!-- end col -->
        </div><!-- end row -->

        <!-- Top Instructors & Courses -->
        <div class="row mt-2 g-4">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header bg-primary bg-gradient bg-opacity-60 d-flex align-items-center">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Giảng viên nổi bật</h4>
                        <button class="badge bg-warning mx-2 rounded-5 dowloadExcel" data-type="top_instructor"><i
                                class='fs-9 bx bx-download'> Excel</i></button>
                        <button class="fs-7 badge bg-primary mx-2" id="showTopInstructorButton">Xem biểu đồ</button>
                    </div>
                    <div class="card-body" id="showTopInstructorDiv">
                        <div class="table-responsive table-card">
                            <table id="table-instructors"
                                class="table table-centered table-hover align-middle table-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th>Giảng viên</th>
                                        <th>Khoá học</th>
                                        <th>Học viên</th>
                                        <th>Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topInstructors as $topInstructor)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $topInstructor->avatar ?? 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png' }}"
                                                        alt=""
                                                        class="avatar-sm p-2 rounded-circle object-fit-cover" />
                                                    <div class="ms-2">
                                                        <h5 class="fs-14 my-1 fw-medium">{{ $topInstructor->name ?? '' }}
                                                        </h5>
                                                        <span class="text-muted">Tham gia
                                                            {{ \Carbon\Carbon::parse($topInstructor->created_at)->format('d/m/Y') ?? '' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $topInstructor->total_courses ?? '' }}</td>
                                            <td>{{ $topInstructor->total_enrolled_students ?? '' }}</td>
                                            <td>{{ number_format($topInstructor->total_revenue) ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4 px-4 text-center">
                                <div id="pagination-links-instructors">
                                    {{ $topInstructors->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center bg-primary bg-gradient bg-opacity-60">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Top khoá học bán chạy</h4>
                        <button class="badge bg-warning mx-2 rounded-5 dowloadExcel" data-type="top_course"><i
                                class='fs-9 bx bx-download'> Excel</i></button>
                        <button class="fs-7 badge bg-primary mx-2" id="showBestSellingCoursesButton">Xem biểu đồ
                        </button>
                    </div>
                    <div class="card-body" id="showBestSellingCoursesDiv">
                        <div class="table-responsive table-card">
                            <table id="table-courses"
                                class="table table-hover table-centered align-middle table-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th>Khoá học</th>
                                        <th>Đã bán</th>
                                        <th>Người học</th>
                                        <th>Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topCourses as $topCourse)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img style="width:70px" src="{{ $topCourse->thumbnail }}"
                                                        alt="" class="img-fluid d-block" />
                                                    <div>
                                                        <h5 class="fs-14 my-1">
                                                            {{ \Illuminate\Support\Str::limit($topCourse->name, 20) }}</h5>
                                                        <span
                                                            class="text-muted">{{ \Carbon\Carbon::parse($topCourse->created_at)->format('d/m/Y') }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $topCourse->total_sales }}</td>
                                            <td>{{ $topCourse->total_enrolled_students }}</td>
                                            <td>{{ number_format($topCourse->total_revenue) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4 px-4 text-center">
                                <div id="pagination-links-courses">
                                    {{ $topCourses->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="row">
                <div class="col-xxl-7 d-flex">
                    <div class="card h-100 w-100">
                        <div class="card-header bg-primary bg-gradient bg-opacity-60 d-flex align-items-center p-3">
                            <img src="https://img.themesbrand.com/velzon/images/img-2.gif"
                                class="avatar-xs rounded-circle object-fit-cover" alt="">
                            <h4 class="card-title mb-0 mx-2 text-white">Tỷ trọng bán hàng: Khóa học & Gói thành viên</h4>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="row flex-grow-1">
                                <div class="col-6 text-center text-danger border-bottom border-3 fw-bold fs-15">
                                    <h6 class="text-danger">Khóa học bán ra</h6>
                                    {{ ($totalByPaymentMethodAndInvoiceType->total_invoice ?? 0) > 0
                                        ? (fmod(
                                            (($totalByPaymentMethodAndInvoiceType->total_course_sales ?? 0) /
                                                $totalByPaymentMethodAndInvoiceType->total_invoice) *
                                                100,
                                            1,
                                        ) == 0
                                            ? intval(
                                                (($totalByPaymentMethodAndInvoiceType->total_course_sales ?? 0) /
                                                    $totalByPaymentMethodAndInvoiceType->total_invoice) *
                                                    100,
                                            )
                                            : round(
                                                (($totalByPaymentMethodAndInvoiceType->total_course_sales ?? 0) /
                                                    $totalByPaymentMethodAndInvoiceType->total_invoice) *
                                                    100,
                                                2,
                                            ))
                                        : 0 }}%
                                </div>
                                <div
                                    class="col-6 text-center border-start border-bottom border-3 text-danger fw-bold fs-15">
                                    <h6 class="text-danger">Gói thành viên bán ra</h6>
                                    {{ ($totalByPaymentMethodAndInvoiceType->total_invoice ?? 0) > 0
                                        ? (fmod(
                                            (($totalByPaymentMethodAndInvoiceType->total_membership_sales ?? 0) /
                                                $totalByPaymentMethodAndInvoiceType->total_invoice) *
                                                100,
                                            1,
                                        ) == 0
                                            ? intval(
                                                (($totalByPaymentMethodAndInvoiceType->total_membership_sales ?? 0) /
                                                    $totalByPaymentMethodAndInvoiceType->total_invoice) *
                                                    100,
                                            )
                                            : round(
                                                (($totalByPaymentMethodAndInvoiceType->total_membership_sales ?? 0) /
                                                    $totalByPaymentMethodAndInvoiceType->total_invoice) *
                                                    100,
                                                2,
                                            ))
                                        : 0 }}%
                                </div>
                            </div>
                            <div id="render-membership-chart" class="w-100 flex-grow-1"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-5 d-flex">
                    <div class="card h-100 w-100">
                        <div class="card-header bg-primary bg-gradient bg-opacity-60 d-flex align-items-center p-4">
                            <h4 class="card-title mb-0 text-white">Tỷ trọng giao dịch qua từng phương thức thanh toán</h4>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="row flex-grow-1">
                                <div style="height: 60px !important"
                                    class="col-6 text-center text-danger border-bottom border-3 fw-bold fs-15">
                                    <h6 class="text-danger">Momo</h6>
                                    {{ ($totalByPaymentMethodAndInvoiceType->total_invoice ?? 0) > 0
                                        ? (fmod(
                                            (($totalByPaymentMethodAndInvoiceType->total_payment_method_momo ?? 0) /
                                                $totalByPaymentMethodAndInvoiceType->total_invoice) *
                                                100,
                                            1,
                                        ) == 0
                                            ? intval(
                                                (($totalByPaymentMethodAndInvoiceType->total_payment_method_momo ?? 0) /
                                                    $totalByPaymentMethodAndInvoiceType->total_invoice) *
                                                    100,
                                            )
                                            : round(
                                                (($totalByPaymentMethodAndInvoiceType->total_payment_method_momo ?? 0) /
                                                    $totalByPaymentMethodAndInvoiceType->total_invoice) *
                                                    100,
                                                2,
                                            ))
                                        : 0 }}%
                                </div>
                                <div style="height: 60px !important"
                                    class="col-6 text-center border-start border-bottom border-3 text-danger fw-bold fs-15">
                                    <h6 class="text-danger">Vnpay</h6>
                                    {{ ($totalByPaymentMethodAndInvoiceType->total_invoice ?? 0) > 0
                                        ? (fmod(
                                            (($totalByPaymentMethodAndInvoiceType->total_payment_method_vnpay ?? 0) /
                                                $totalByPaymentMethodAndInvoiceType->total_invoice) *
                                                100,
                                            1,
                                        ) == 0
                                            ? intval(
                                                (($totalByPaymentMethodAndInvoiceType->total_payment_method_vnpay ?? 0) /
                                                    $totalByPaymentMethodAndInvoiceType->total_invoice) *
                                                    100,
                                            )
                                            : round(
                                                (($totalByPaymentMethodAndInvoiceType->total_payment_method_vnpay ?? 0) /
                                                    $totalByPaymentMethodAndInvoiceType->total_invoice) *
                                                    100,
                                                2,
                                            ))
                                        : 0 }}%
                                </div>
                            </div>
                            <div id="render-payment-method-chart" class="w-100 flex-grow-1"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Ratings & Top Students -->
        <div class="row mt-5 d-flex">
            <div class="col-xl-5 d-flex">
                <div class="card w-100 h-100">
                    <div class="card-header bg-primary bg-gradient bg-opacity-60">
                        <h4 class="card-title mb-0 text-white">Đánh giá khoá học</h4>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div id="rating-pie-chart" class="w-100"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-7 d-flex">
                <div class="card w-100 h-100">
                    <div class="card-header bg-primary bg-gradient bg-opacity-60 d-flex align-items-center">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Top học viên</h4>
                        <button class="badge bg-warning mx-2 rounded-5 dowloadExcel" data-type="top_student">
                            <i class='fs-9 bx bx-download'> Excel</i>
                        </button>
                        <button class="fs-7 badge bg-primary mx-2" id="showRenderTopStudentsButton">Xem biểu đồ</button>
                    </div>
                    <div class="card-body" id="showRenderTopStudentsDiv">
                        <div class="table-responsive table-card">
                            <table id="table-students"
                                class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                <thead class="text-muted table-light">
                                    <tr>
                                        <th>STT</th>
                                        <th>Học viên</th>
                                        <th>Khoá học đã mua</th>
                                        <th>Tổng tiền đã chi</th>
                                        <th>Lần mua gần nhất</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topUsers as $topUser)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $topUser->avatar ?? 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png' }}"
                                                        alt=""
                                                        class="avatar-xs rounded-circle object-fit-cover" />
                                                    <div class="ms-2">{{ $topUser->name ?? '' }}</div>
                                                </div>
                                            </td>
                                            <td>{{ $topUser->total_courses_purchased }}</td>
                                            <td>{{ number_format($topUser->total_spent ?? 0) }}</td>
                                            <td>{{ $topUser->last_purchase_date }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4 px-4 text-center">
                                <div id="pagination-links-users">
                                    {{ $topUsers->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Completed Courses & Top Instructors -->
        <div class="row mt-2 ">
            <div class="col-xxl-7">
                <div class="card">
                    <div class="card-header d-flex align-items-center bg-primary bg-gradient bg-opacity-60">
                        <img src="https://img.themesbrand.com/velzon/images/img-2.gif"
                            class="avatar-xs rounded-circle object-fit-cover" alt="">
                        <h4 class="card-title mb-0 mx-2 text-white">Top 10 khóa học có tỉ lệ hoàn thành cao nhất</h4>
                    </div>
                    <div class="card-body">
                        <div id="topCompletedCourses" class="w-100"></div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-5">
                <div class="card">
                    <div class="card-header bg-primary bg-gradient bg-opacity-60">
                        <h4 class="card-title mb-0 text-white">Top 10 giảng viên được yêu thích nhất</h4>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div id="topInstructorsChart" class="w-100"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Courses Views -->
        <div class="row mt-2">
            <div class="col-xxl-12">
                <div class="card border-0 shadow">
                    <div
                        class="card-header bg-primary bg-gradient bg-opacity-60 d-flex align-items-center justify-content-between p-3">
                        <h4 class="card-title mb-0 fw-bold text-white">
                            <i class="ri-award-fill me-2"></i>Top 10 khóa học
                        </h4>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" type="button" id="dropdownMenuButton"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-filter-3-line"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item active course-filter" href="#"
                                        data-filter-course="views">Xem nhiều nhất</a></li>
                                <li><a class="dropdown-item course-filter" href="#"
                                        data-filter-course="created_at">Mới nhất</a></li>
                                <li><a class="dropdown-item course-filter" href="#"
                                        data-filter-course="price_asc">Giá thấp đến cao</a></li>
                                <li><a class="dropdown-item course-filter" href="#"
                                        data-filter-course="price_desc">Giá cao đến thấp</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <div class="swiper marketplace-swiper rounded gallery-light">
                            <div class="swiper-wrapper py-3" id="top-course-view">
                                @foreach ($getTopViewCourses as $getTopViewCourse)
                                    <div class="swiper-slide h-100">
                                        <div
                                            class="card explore-box card-animate rounded-lg overflow-hidden h-100 shadow-sm border-0 position-relative">
                                            @if ($getTopViewCourse->is_free)
                                                <div class="ribbon ribbon-primary ribbon-shape position-absolute">
                                                    <span>Miễn phí</span>
                                                </div>
                                            @elseif($getTopViewCourse->price_sale > 0)
                                                @php
                                                    $discount = round(
                                                        (1 - $getTopViewCourse->price_sale / $getTopViewCourse->price) *
                                                            100,
                                                    );
                                                @endphp
                                                <div class="ribbon ribbon-danger ribbon-shape position-absolute">
                                                    <span>-{{ $discount }}%</span>
                                                </div>
                                            @endif

                                            <div class="explore-place-bid-img position-relative">
                                                <img src="{{ $getTopViewCourse->thumbnail }}"
                                                    alt="{{ $getTopViewCourse->name }}"
                                                    class="img-fluid card-img-top explore-img"
                                                    style="max-height: 190px; width: 100%; object-fit: cover;">
                                                <div class="bg-overlay bg-dark opacity-25"></div>

                                                <div class="position-absolute bottom-0 start-0 w-100 p-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-xs me-2 bg-white rounded-circle p-1">
                                                                <img src="{{ $getTopViewCourse->instructor_avatar ?? '' }}"
                                                                    alt=""
                                                                    class="rounded-circle w-100 h-100 object-fit-cover">
                                                            </div>
                                                            <span
                                                                class="text-white fw-medium text-shadow">{{ $getTopViewCourse->instructor_name }}</span>
                                                        </div>
                                                        <span class="badge bg-primary rounded-pill fs-11 px-2 py-1">
                                                            <i
                                                                class="mdi mdi-eye align-middle me-1"></i>{{ number_format($getTopViewCourse->views) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card-body p-3">
                                                <h5 class="mb-3 fw-semibold text-truncate course-title"
                                                    title="{{ $getTopViewCourse->name }}">
                                                    {{ $getTopViewCourse->name }}
                                                </h5>

                                                <div class="d-flex align-items-center mb-2">
                                                    @if ($getTopViewCourse->is_free)
                                                        <span class="badge bg-success me-1">Miễn phí</span>
                                                    @elseif($getTopViewCourse->price_sale > 0)
                                                        <span
                                                            class="fs-15 fw-semibold text-success">{{ number_format($getTopViewCourse->price_sale) }}
                                                            VND</span>
                                                        <span
                                                            class="text-muted text-decoration-line-through ms-2 fs-13">{{ number_format($getTopViewCourse->price) }}
                                                            VND</span>
                                                        @php
                                                            $discount = round(
                                                                (1 -
                                                                    $getTopViewCourse->price_sale /
                                                                        $getTopViewCourse->price) *
                                                                    100,
                                                            );
                                                        @endphp
                                                        <span class="badge bg-danger ms-auto">-{{ $discount }}%</span>
                                                    @elseif($getTopViewCourse->price > 0)
                                                        <span
                                                            class="fs-15 fw-semibold">{{ number_format($getTopViewCourse->price) }}
                                                            VND</span>
                                                    @else
                                                        <span class="badge bg-success">Miễn phí</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="card-footer bg-light p-3 border-top">
                                                <div class="d-grid gap-2">
                                                    <a href="{{ config('app.fe_url') }}courses/{{ $getTopViewCourse->slug }}"
                                                        target="_blank" class="btn btn-primary btn-sm">
                                                        <i class="ri-eye-line align-bottom me-1"></i>
                                                        Xem chi tiết
                                                    </a>
                                                    <a href="{{ route('admin.courses.show', $getTopViewCourse->id) }}"
                                                        class="btn btn-outline-secondary btn-sm">
                                                        <i class="ri-settings-3-line align-bottom me-1"></i>
                                                        Quản lý
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="swiper-button-next swiper-nav-btn"></div>
                            <div class="swiper-button-prev swiper-nav-btn"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/moment.min.js') }}"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/modules/annotations.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script src="https://code.highcharts.com/modules/heatmap.js"></script>
    <script src="https://code.highcharts.com/modules/packed-bubble.js"></script>
    <script src="https://code.highcharts.com/modules/sankey.js"></script>

    <script>
        var topCourse = @json($topCourses);
        var topInstructor = @json($topInstructors);
        var system_Funds = @json($system_Funds);
        var ratingData = @json($courseRatings);
        var topStudent = @json($topUsers);
        var topCategory = @json($categoryStats);

        let chart, pieChart, chartBestSellingCourses, chartTopInstructors, chartTopInstructorFollows,
            chartTopCompletedCourses, chartTopStudents, categoryRevenueChart, chartMembership, chartPaymentMethod;

        var currentHour = new Date().getHours();
        var greetingText = "Xin chào, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        if (currentHour >= 5 && currentHour < 12) greetingText =
            "Chào buổi sáng, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        else if (currentHour >= 12 && currentHour < 18) greetingText =
            "Chào buổi chiều, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        else if (currentHour >= 18 && currentHour < 22) greetingText =
            "Chào buổi tối, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        else greetingText = "Chúc ngủ ngon, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        $("#greeting").text(greetingText);

        function updateChart(data) {
            let chartContainer = document.querySelector("#projects-overview-chart");
            chartContainer.innerHTML = "";

            if (!data || data.length === 0) {
                chartContainer.innerHTML = `<div class="no-data">Chưa có doanh thu</div>`;
                return;
            }

            let categories = data.map(item => {
                let date = new Date(item.date);
                return new Intl.DateTimeFormat('vi-VN', {
                    day: '2-digit',
                    month: '2-digit',
                }).format(date);
            });
            let profitData = data.map(item => parseFloat(item.total_profit));
            let renueveDate = data.map(item => parseFloat(item.total_revenue));

            Highcharts.chart(chartContainer, {
                chart: {
                    type: 'area',
                    zooming: {
                        type: 'x'
                    },
                    panning: true,
                    panKey: 'shift',
                    scrollablePlotArea: {
                        minWidth: 600
                    },
                    backgroundColor: null
                },
                title: {
                    text: 'Biểu đồ lợi nhuận Course MeLy'
                },
                credits: {
                    enabled: false
                },
                xAxis: {
                    categories: categories,
                    labels: {
                        format: '{value}'
                    },
                    title: {
                        text: 'Thời gian'
                    }
                },
                yAxis: {
                    startOnTick: true,
                    endOnTick: false,
                    title: {
                        text: null
                    },
                    labels: {
                        formatter: function() {
                            return this.value.toLocaleString() + " VND";
                        }
                    }
                },
                tooltip: {
                    shared: true,
                    formatter: function() {
                        let s = `<b>Ngày ${this.x}</b><br/>`;

                        let date = this.points[0].category;
                        s = `<b>Ngày ${date}</b><br/>`;

                        this.points.forEach(point => {
                            s += `${point.series.name}: <b>${point.y.toLocaleString()} VND</b><br/>`;
                        });
                        return s;
                    }
                },
                legend: {
                    enabled: false
                },
                series: [
                    {
                        name: 'Doanh thu',
                        data: renueveDate,
                        lineColor: Highcharts.getOptions().colors[3],
                        color: Highcharts.getOptions().colors[4],
                        fillOpacity: 0.3,
                        marker: {
                            enabled: false
                        },
                        threshold: null
                    },{
                        name: 'Lợi nhuận',
                        data: profitData,
                        lineColor: Highcharts.getOptions().colors[1],
                        color: Highcharts.getOptions().colors[2],
                        fillOpacity: 0.5,
                        marker: {
                            enabled: false
                        },
                        threshold: null
                    }
                ]
            });
        }

        function renderMembershipChart(data = []) {
            let chartContainer = document.querySelector("#render-membership-chart");
            chartContainer.innerHTML = "";

            if (!data || data.length === 0) {
                chartContainer.innerHTML = `<div class="no-data">Chưa có giao dịch</div>`;
                return;
            }

            let categories = data.map(item => {
                let date = new Date(item.date);
                return new Intl.DateTimeFormat('vi-VN', {
                    day: '2-digit',
                    month: '2-digit',
                }).format(date);
            });
            let courseSalesData = data.map(item => parseInt(item.total_course_sales));
            let membershipSalesData = data.map(item => parseInt(item.total_membership_sales));

            Highcharts.chart(chartContainer, {
                chart: {
                    type: 'area',
                    height: '50%',
                    backgroundColor: null,
                    spacing: [20, 20, 20, 20]
                },
                title: {
                    text: null
                },
                credits: {
                    enabled: false
                },
                xAxis: {
                    categories: categories
                },
                yAxis: {
                    title: {
                        text: 'Số lượng'
                    }
                },
                series: [{
                        name: 'Khóa học',
                        data: courseSalesData,
                        color: '#17a2b8'
                    },
                    {
                        name: 'Gói thành viên',
                        data: membershipSalesData,
                        color: '#ffc107'
                    }
                ]
            });
        }

        function renderPaymentMethodChart(data) {
            let chartContainer = document.querySelector("#render-payment-method-chart");
            chartContainer.innerHTML = "";

            if (!data || data.length === 0) {
                chartContainer.innerHTML = `<div class="no-data">Chưa có giao dịch</div>`;
                return;
            }

            let categories = data.map(item => {
                let date = new Date(item.date);
                return new Intl.DateTimeFormat('vi-VN', {
                    day: '2-digit',
                    month: '2-digit',
                }).format(date);
            });
            let momoData = data.map(item => parseInt(item.total_payment_method_momo) || 0);
            let vnpayData = data.map(item => parseInt(item.total_payment_method_vnpay) || 0);

            Highcharts.chart(chartContainer, {
                chart: {
                    type: 'bar',
                    height: "80%",
                    backgroundColor: null,
                    animation: {
                        duration: 1000,
                        easing: 'easeOutBounce'
                    }
                },
                credits: {
                    enabled: false
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: categories
                },
                yAxis: {
                    title: {
                        text: 'Số lượng giao dịch'
                    }
                },
                plotOptions: {
                    series: {
                        borderRadius: 5,
                        shadow: true
                    },
                    bar: {
                        horizontal: true,
                        barHeight: '150%'
                    }
                },
                series: [{
                        name: 'Momo',
                        data: momoData,
                        color: {
                            linearGradient: {
                                x1: 0,
                                x2: 1,
                                y1: 0,
                                y2: 1
                            },
                            stops: [
                                [0, '#ff4081'],
                                [1, '#ff80ab']
                            ]
                        }
                    },
                    {
                        name: 'VNPay',
                        data: vnpayData,
                        color: {
                            linearGradient: {
                                x1: 0,
                                x2: 1,
                                y1: 0,
                                y2: 1
                            },
                            stops: [
                                [0, '#2196f3'],
                                [1, '#6ec6ff']
                            ]
                        }
                    }
                ]
            });
        }

        function updatePieChart(ratingData) {
            let pieChartContainer = document.querySelector("#rating-pie-chart");
            pieChartContainer.innerHTML = "";

            if (!ratingData || !ratingData.length) {
                pieChartContainer.innerHTML = '<div class="no-data">Không có đánh giá</div>';
                return;
            }

            let series = ratingData.map(item => parseFloat(item.total_courses));
            let labels = ratingData.map(item => `${item.rating} sao`);

            Highcharts.chart(pieChartContainer, {
                chart: {
                    type: 'pie',
                    height: "80%",
                    backgroundColor: null,
                    animation: {
                        duration: 1500,
                        easing: 'easeOutBounce'
                    }
                },
                credits: {
                    enabled: false
                },
                title: {
                    text: 'Tỉ lệ đánh giá khóa học'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        borderRadius: 10,
                        shadow: true,
                        size: '100%',
                    }
                },
                series: [{
                    name: 'Khóa học',
                    data: labels.map((label, index) => ({
                        name: label,
                        y: series[index]
                    })),
                    colors: ['#FF9800', '#F44336', '#4CAF50', '#03A9F4', '#9C27B0']
                }]
            });
        }

        function updateCategoryRevenueChart(data = []) {
            let chartContainer = document.querySelector("#category-revenue-chart");
            chartContainer.innerHTML = "";

            if (!data || !data.length) {
                chartContainer.innerHTML = '<div class="text-center p-4 text-muted">Không có dữ liệu</div>';
                return;
            }

            let categories = data.map(item => item.category_name);
            let totalCoursesSeries = data.map(item => parseInt(item.total_courses));
            let totalEnrolledStudentsSeries = data.map(item => parseInt(item.total_enrolled_students));
            let totalInstructorsSeries = data.map(item => parseInt(item.total_instructors));

            Highcharts.chart(chartContainer, {
                chart: {
                    type: 'line',
                    height: '70%',
                    backgroundColor: null
                },
                title: {
                    text: 'Số học viên & giảng viên & khóa học theo danh mục'
                },
                xAxis: {
                    categories: categories,
                    labels: {
                        rotation: -45,
                        style: {
                            fontSize: '12px'
                        },
                        formatter: function() {
                            return this.value.length > 20 ? this.value.substring(0, 17) + "..." : this.value;
                        }
                    }
                },
                yAxis: {
                    title: {
                        text: 'Số lượng'
                    },
                    labels: {
                        formatter: function() {
                            return this.value.toLocaleString();
                        }
                    }
                },
                credits: {
                    enabled: false
                },
                legend: {
                    align: 'center',
                    verticalAlign: 'top'
                },
                tooltip: {
                    shared: true,
                    pointFormat: '{series.name}: <b>{point.y}</b><br>'
                },
                series: [{
                        name: 'Số khóa học',
                        data: totalCoursesSeries,
                        color: '#008FFB'
                    },
                    {
                        name: 'Số học viên',
                        data: totalEnrolledStudentsSeries,
                        color: '#00E396'
                    },
                    {
                        name: 'Số người hướng dẫn',
                        data: totalInstructorsSeries,
                        color: '#FEB019'
                    }
                ]
            });
        }

        function renderBestSellingCourses(data = []) {
            let chartContainer = document.querySelector("#bestSellingCourses");
            chartContainer.innerHTML = "";

            if (!data.data || !data.data.length) {
                chartContainer.innerHTML = `<p class="text-center p-4 text-muted">Không có dữ liệu</p>`;
                return;
            }

            let categories = data.data.map(item => item.name);
            let salesData = data.data.map(item => Number(item.total_sales) || 0);
            let revenueData = data.data.map(item => Number(item.total_revenue) || 0);

            Highcharts.chart(chartContainer, {
                chart: {
                    type: 'column',
                    height: "100%",
                    backgroundColor: null
                },
                title: {
                    text: 'Khóa học có doanh thu lớn nhất'
                },
                exporting: {
                    enabled: true,
                    buttons: {
                        contextButton: {
                            menuItems: ['downloadPNG', 'downloadJPEG', 'downloadPDF', 'downloadSVG']
                        }
                    }
                },
                xAxis: {
                    categories: categories.map(name => name.length > 20 ? name.substring(0, 17) + "..." : name),
                    crosshair: true
                },
                credits: {
                    enabled: false
                },
                yAxis: [{
                        title: {
                            text: 'Số lượng bán'
                        },
                        labels: {
                            formatter: function() {
                                return this.value.toLocaleString("vi-VN");
                            }
                        }
                    },
                    {
                        title: {
                            text: 'Doanh thu (VND)'
                        },
                        opposite: true,
                        labels: {
                            formatter: function() {
                                return this.value.toLocaleString("vi-VN", {
                                    style: "currency",
                                    currency: "VND"
                                });
                            }
                        }
                    }
                ],
                tooltip: {
                    shared: true,
                    formatter: function() {
                        let index = this.points[0].point.index;
                        return `<b>${categories[index]}</b><br>
                <span style="color:#008FFB">●</span> Số lượng bán: <b>${salesData[index].toLocaleString("vi-VN")}</b><br>
                <span style="color:#FF4560">●</span> Doanh thu: <b>${revenueData[index].toLocaleString("vi-VN", { 
                    style: "currency", 
                    currency: "VND" 
                })}</b>`;
                    }
                },
                plotOptions: {
                    column: {
                        borderRadius: 5,
                        grouping: true,
                        shadow: true
                    },
                    areaspline: {
                        fillOpacity: 0.2
                    }
                },
                series: [{
                        name: 'Số lượng bán',
                        type: 'column',
                        data: salesData,
                        color: '#008FFB'
                    },
                    {
                        name: 'Doanh thu (VND)',
                        type: 'areaspline',
                        yAxis: 1,
                        data: revenueData,
                        color: '#FF4560',
                        lineWidth: 3,
                        marker: {
                            radius: 5,
                            symbol: 'circle'
                        }
                    }
                ]
            });
        }

        function renderTopInstructorsChart(data = []) {
            let chartContainer = document.querySelector("#renderTopInstructorsChart");
            chartContainer.innerHTML = "";

            if (!data.data || !data.data.length) {
                chartContainer.innerHTML = `<p class="text-center p-4 text-muted">Không có dữ liệu</p>`;
                return;
            }

            let categories = data.data.map(item => item.name || "Không rõ");
            let revenueData = [],
                courseData = [],
                studentData = [];

            data.data.forEach((item, index) => {
                revenueData.push({
                    x: index,
                    y: 0,
                    value: Number(item.total_revenue) || 0
                });
                courseData.push({
                    x: index,
                    y: 1,
                    value: Number(item.total_courses) || 0
                });
                studentData.push({
                    x: index,
                    y: 2,
                    value: Number(item.total_enrolled_students) || 0
                });
            });

            Highcharts.chart(chartContainer, {
                chart: {
                    type: 'heatmap',
                    plotBorderWidth: 1,
                    height: "100%"
                },
                title: {
                    text: 'Giảng viên có doanh thu cao nhất'
                },
                credits: {
                    enabled: false
                },
                xAxis: {
                    categories: categories,
                    title: {
                        text: "Giảng viên"
                    },
                    labels: {
                        rotation: -45
                    }
                },
                yAxis: {
                    categories: ["Doanh thu (VND)", "Số khóa học", "Số học viên"],
                    title: null
                },
                colorAxis: [{
                    min: 0,
                    stops: [
                        [0, '#E3F2FD'],
                        [0.5, '#42A5F5'],
                        [1, '#0D47A1']
                    ]
                }, {
                    min: 0,
                    stops: [
                        [0, '#FFF3E0'],
                        [0.5, '#FFA726'],
                        [1, '#E65100']
                    ]
                }, {
                    min: 0,
                    stops: [
                        [0, '#FFEBEE'],
                        [0.5, '#EF5350'],
                        [1, '#B71C1C']
                    ]
                }],
                legend: {
                    align: 'right',
                    layout: 'vertical',
                    margin: 0,
                    verticalAlign: 'top',
                    y: 25,
                    symbolHeight: 280
                },
                tooltip: {
                    formatter: function() {
                        return `<b>${categories[this.point.x]}</b><br>${this.series.name}: ${this.point.value.toLocaleString("vi-VN")}`;
                    }
                },
                series: [{
                        name: "Doanh thu (VND)",
                        borderWidth: 1,
                        data: revenueData,
                        colorAxis: 0,
                        dataLabels: {
                            enabled: true,
                            color: '#000000'
                        }
                    },
                    {
                        name: "Số khóa học",
                        borderWidth: 1,
                        data: courseData,
                        colorAxis: 1,
                        dataLabels: {
                            enabled: true,
                            color: '#000000'
                        }
                    },
                    {
                        name: "Số học viên",
                        borderWidth: 1,
                        data: studentData,
                        colorAxis: 2,
                        dataLabels: {
                            enabled: true,
                            color: '#000000'
                        }
                    }
                ]
            });
        }

        function renderTopStudentsChart(data = []) {
            let chartContainer = document.querySelector("#renderTopStudentsChart");
            chartContainer.innerHTML = "";

            if (!data.data || !data.data.length) {
                chartContainer.innerHTML = `<p class="text-center p-4 text-muted">Không có dữ liệu</p>`;
                return;
            }

            let seriesData = [];
            data.data.forEach(item => {
                let student = item.name || "Không rõ";
                let totalSpent = Number(item.total_spent) || 0;
                let totalCourses = Number(item.total_courses_purchased) || 0;

                if (totalCourses > 0) {
                    seriesData.push([student, "Số khóa học đã mua", totalCourses]);
                }
                if (totalSpent > 0) {
                    seriesData.push([student, "Tổng số tiền chi tiêu", totalSpent]);
                }
            });

            Highcharts.chart(chartContainer, {
                chart: {
                    type: 'sankey',
                    height: "50%"
                },
                title: {
                    text: 'Chi tiêu của học viên'
                },
                credits: {
                    enabled: false
                },
                tooltip: {
                    pointFormatter: function() {
                        let unit = this.to === "Số khóa học đã mua" ? " khóa học" : " VND";
                        return `<b>${this.from}</b> → <b>${this.to}</b>: <b>${this.weight.toLocaleString("vi-VN")}${unit}</b>`;
                    }
                },
                series: [{
                    keys: ['from', 'to', 'weight'],
                    data: seriesData,
                    colors: ["#007BFF", "#28A745", "#FFC107"],
                    dataLabels: {
                        color: "#333",
                        style: {
                            fontWeight: "bold"
                        }
                    },
                    nodes: [{
                        id: "Số khóa học đã mua",
                        color: "#28A745"
                    }, {
                        id: "Tổng số tiền chi tiêu",
                        color: "#FFC107"
                    }]
                }]
            });
        }

        function renderTopInstructorsFollow(data = []) {
            let chartContainer = document.querySelector("#topInstructorsChart");
            chartContainer.innerHTML = "";

            if (!data.length) {
                chartContainer.innerHTML = `<p class="text-center p-4 text-muted">Không có dữ liệu</p>`;
                return;
            }

            let categories = data.map(item => item.name);
            let totalFollows = data.map(item => item.total_follow);

            Highcharts.chart(chartContainer, {
                chart: {
                    polar: true,
                    type: 'line',
                    height: 330
                },
                title: {
                    text: null
                },
                credits: {
                    enabled: false
                },
                pane: {
                    size: '80%'
                },
                xAxis: {
                    categories: categories,
                    tickmarkPlacement: 'on',
                    lineWidth: 0
                },
                yAxis: {
                    gridLineInterpolation: 'polygon',
                    min: 0,
                    title: {
                        text: 'Số lượng'
                    }
                },
                tooltip: {
                    shared: true,
                    pointFormat: `<b>{series.name}</b>: {point.y}`
                },
                series: [{
                    name: 'Lượt Follow',
                    data: totalFollows,
                    color: '#00E396',
                    pointPlacement: 'on'
                }]
            });
        }

        function renderTopCompletedCourses(data = []) {
            let chartContainer = document.querySelector("#topCompletedCourses");
            chartContainer.innerHTML = "";

            if (!data.length) {
                chartContainer.innerHTML = `<p class="text-center p-4 text-muted">Không có dữ liệu</p>`;
                return;
            }

            Highcharts.chart(chartContainer, {
                chart: {
                    type: 'bar',
                    height: 330
                },
                title: {
                    text: 'Tỷ lệ hoàn thành khóa học',
                    align: 'center'
                },
                credits: {
                    enabled: false
                },
                xAxis: {
                    categories: data.map(item => {
                        let name = item.course?.name || "N/A";
                        return name.length > 25 ? name.substring(0, 17) + "..." : name;
                    }),
                    title: {
                        text: 'Khoá học'
                    }
                },
                yAxis: {
                    min: 0,
                    max: 100,
                    title: {
                        text: 'Tỷ lệ hoàn thành (%)'
                    }
                },
                legend: {
                    enabled: false
                },
                tooltip: {
                    pointFormat: '<b>{point.category}</b>: {point.y}% hoàn thành'
                },
                series: [{
                    name: 'Tỷ lệ hoàn thành',
                    data: data.map(item => Number(item.avg_progress) || 0),
                    colorByPoint: true
                }],
                plotOptions: {
                    bar: {
                        dataLabels: {
                            enabled: true,
                            format: '{y}%'
                        }
                    }
                }
            });
        }

        $(document).ready(function() {
            $(document).on('click', '#pagination-links-courses a', function(e) {
                e.preventDefault();
                var page = $(this).attr('href').split('page=')[1];

                loadCoursesContent({
                    page: page
                });
            });

            $(document).on('click', '#pagination-links-instructors a', function(e) {
                e.preventDefault();
                var page = $(this).attr('href').split('page=')[1];

                loadInstructorsContent({
                    page: page
                });
            });

            $(document).on('click', '#pagination-links-users a', function(e) {
                e.preventDefault();
                var page = $(this).attr('href').split('page=')[1];

                loadUsersContent({
                    page: page
                });
            });

            function loadCoursesContent(dataFilter) {
                dataFilter.type = "courses";
                $.ajax({
                    url: "{{ route('admin.dashboard') }}",
                    type: "GET",
                    data: dataFilter,
                    dataType: "json",
                    success: function(data) {
                        $('#table-courses tbody').html(data.top_courses_table);
                        $('#pagination-links-courses').html(data.pagination_links_courses);
                        topCourse = data.topCourses;
                        if ($('#bestSellingCourses').is(':visible')) renderBestSellingCourses(
                            topCourse);
                    }
                });
            }

            function loadInstructorsContent(dataFilter) {
                dataFilter.type = 'instructors';
                $.ajax({
                    url: "{{ route('admin.dashboard') }}",
                    type: "GET",
                    data: dataFilter,
                    dataType: "json",
                    success: function(data) {
                        $('#table-instructors tbody').html(data.top_instructors_table);
                        $('#pagination-links-instructors').html(data.pagination_links_instructors);
                        topInstructor = data.topInstructors;
                        if ($('#renderTopInstructorsChart').is(':visible')) renderTopInstructorsChart(
                            topInstructor);
                    }
                });
            }

            function loadUsersContent(dataFilter) {
                dataFilter.type = 'user';
                $.ajax({
                    url: "{{ route('admin.dashboard') }}",
                    type: "GET",
                    data: dataFilter,
                    dataType: "json",
                    success: function(data) {
                        $('#table-students tbody').html(data.top_users_table);
                        $('#pagination-links-users').html(data.pagination_links_users);
                        topStudent = data.topUsers;
                        if ($('#renderTopStudentsChart').is(':visible')) renderTopStudentsChart(
                            topStudent);
                    }
                });
            }

            $(document).on('click', '#showBestSellingCoursesButton', function(e) {
                e.preventDefault();
                let tableDiv = $('#table-courses').closest('.table-responsive');
                let chartDiv = $('#bestSellingCourses');
                let button = $(this);

                if (tableDiv.is(':visible')) {
                    tableDiv.hide();
                    if (chartDiv.length === 0) {
                        $('#showBestSellingCoursesDiv').append(
                            '<div id="bestSellingCourses" class="apex-charts"></div>');
                        renderBestSellingCourses(topCourse);
                    } else chartDiv.show();
                    button.text('Xem bảng');
                } else {
                    if (chartBestSellingCourses) chartBestSellingCourses.destroy();
                    $('#bestSellingCourses').remove();
                    tableDiv.show();
                    button.text('Xem biểu đồ');
                }
            });

            $(document).on('click', '#showTopInstructorButton', function(e) {
                e.preventDefault();
                let tableDiv = $('#table-instructors').closest('.table-responsive');
                let chartDiv = $('#renderTopInstructorsChart');
                let button = $(this);

                if (tableDiv.is(':visible')) {
                    tableDiv.hide();
                    if (chartDiv.length === 0) {
                        $('#showTopInstructorDiv').append(
                            '<div id="renderTopInstructorsChart" class="apex-charts"></div>');
                        renderTopInstructorsChart(topInstructor);
                    } else chartDiv.show();
                    button.text('Xem bảng');
                } else {
                    if (chartTopInstructors) chartTopInstructors.destroy();
                    $('#renderTopInstructorsChart').remove();
                    tableDiv.show();
                    button.text('Xem biểu đồ');
                }
            });

            $(document).on('click', '#showRenderTopStudentsButton', function(e) {
                e.preventDefault();
                let tableDiv = $('#table-students').closest('.table-responsive');
                let chartDiv = $('#renderTopStudentsChart');
                let button = $(this);

                if (tableDiv.is(':visible')) {
                    tableDiv.hide();
                    if (chartDiv.length === 0) {
                        $('#showRenderTopStudentsDiv').append(
                            '<div id="renderTopStudentsChart" class="apex-charts"></div>');
                        renderTopStudentsChart(topStudent);
                    } else chartDiv.show();
                    button.text('Xem bảng');
                } else {
                    if (chartTopStudents) chartTopStudents.destroy();
                    $('#renderTopStudentsChart').remove();
                    tableDiv.show();
                    button.text('Xem biểu đồ');
                }
            });

            $(document).on('click', '.course-filter', function(e) {
                e.preventDefault();

                let data_type = $(this).data('filter-course');
                console.log(data_type);

                $.ajax({
                    url: "{{ route('admin.dashboard') }}",
                    method: 'GET',
                    data: {
                        orderby_course: data_type
                    },
                    dataType: 'json',
                    success: function(data) {
                        console.log(data);

                        $('#top-course-view').empty();
                        $('#top-course-view').html(data.getTopViewCourses);
                    }
                })
            })

            $(document).on('click', '.dowloadExcel', function() {
                let type_export = $(this).data('type');
                let data_export;

                if (type_export == 'top_instructor') {
                    data_export = topInstructor.data;
                } else if (type_export == 'top_course') {
                    data_export = topCourse.data;
                } else if (type_export == 'top_student') {
                    data_export = topStudent.data;
                } else if (type_export == 'top_category') {
                    data_export = topCategory;
                } else {
                    return;
                }

                if (!data_export || !Array.isArray(data_export)) {
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.dashboard.export') }}",
                    method: 'POST',
                    data: {
                        type: type_export,
                        data: data_export,
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(response, status, xhr) {
                        let filename = `${type_export}_export.xlsx`;
                        const disposition = xhr.getResponseHeader('Content-Disposition');

                        if (disposition && disposition.indexOf('attachment') !== -1) {
                            const matches = /filename="([^"]*)"/.exec(disposition);
                            if (matches != null && matches[1]) filename = matches[1];
                        }

                        const blob = new Blob([response]);
                        const url = window.URL.createObjectURL(blob);
                        const tempLink = document.createElement('a');
                        tempLink.style.display = 'none';
                        tempLink.href = url;
                        tempLink.setAttribute('download', filename);
                        document.body.appendChild(tempLink);
                        tempLink.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(tempLink);
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const filterItems = document.querySelectorAll('.course-filter');

            filterItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();

                    filterItems.forEach(i => i.classList.remove('active'));

                    this.classList.add('active');
                });
            });
        });

        updateChart(system_Funds);
        updatePieChart(ratingData);
        renderTopCompletedCourses(@json($topCoursesProgress));
        renderTopInstructorsFollow(@json($topInstructorsFollows));
        renderMembershipChart(system_Funds);
        renderPaymentMethodChart(system_Funds);
        updateCategoryRevenueChart(topCategory);

        var swiper = new Swiper('.marketplace-swiper', {
            slidesPerView: 4,
            spaceBetween: 20,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
            },
            breakpoints: {
                320: {
                    slidesPerView: 1
                },
                768: {
                    slidesPerView: 2
                },
                1024: {
                    slidesPerView: 4
                }
            }
        });

        const observer = new MutationObserver(() => {
            swiper.update();
        });

        observer.observe(document.querySelector('.marketplace-swiper .swiper-wrapper'), {
            childList: true,
            subtree: true
        });
    </script>
@endpush

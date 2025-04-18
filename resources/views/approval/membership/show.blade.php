@extends('layouts.app')

@push('page-css')
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/membership.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title ?? 'Chi tiết kiểm duyệt' }}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chủ</a></li>
                            <li class="breadcrumb-item"><a href="#">Kiểm duyệt</a></li>
                            <li class="breadcrumb-item active">{{ $subTitle ?? 'Chi tiết' }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ri-copper-diamond-line"></i> Thông tin kiểm duyệt #{{ $approval->id }}
                        </h5>

                        <div class="d-flex align-items-center gap-2">
                            @if ($approval->status == 'approved')
                                <span class="approval-status status-approved">Đã phê duyệt</span>
                            @elseif($approval->status == 'rejected')
                                <span class="approval-status status-rejected">Đã từ chối</span>
                            @else
                                <span class="approval-status status-pending">Đang chờ duyệt</span>
                            @endif

                            {{-- Nút mở modal --}}
                            <a href="javascript:void(0)" class="badge bg-primary text-white" data-bs-toggle="modal"
                                data-bs-target="#approvalLogsModal">
                                <i class="ri-time-line me-1"></i> Lịch sử kiểm duyệt
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th style="width: 20%">Kiểm duyệt</th>
                                        <td>{{ $approval->approvable_type == 'App\Models\MembershipPlan' ? 'Gói thành viên' : $approval->approvable_type }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Ngày yêu cầu</th>
                                        <td>{{ \Carbon\Carbon::parse($approval->request_date)->format('d/m/Y H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Thời gian xử lý</th>
                                        <td>
                                            @if ($approval->approved_at)
                                                {{ \Carbon\Carbon::parse($approval->approved_at)->format('d/m/Y H:i:s') }}
                                            @elseif($approval->rejected_at)
                                                {{ \Carbon\Carbon::parse($approval->rejected_at)->format('d/m/Y H:i:s') }}
                                            @else
                                                <span class="text-warning">Chưa xử lý</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if ($approval->note)
                                        <tr>
                                            <th>Ghi chú</th>
                                            <td>
                                                {{ $approval->note }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="p-3 border rounded bg-light-subtle">
                        <h6 class="mb-3">🧐 Tiêu chí đánh giá gói thành viên</h6>
                        <ul class="list-unstyled">
                            @foreach ($conditionalMembership['pass'] as $passed)
                                <li class="d-flex align-items-center mb-2 text-success">
                                    <i class='bx bx-check-circle me-2'></i> {{ $passed }}
                                </li>
                            @endforeach

                            @foreach ($conditionalMembership['errors'] as $error)
                                <li class="d-flex align-items-center mb-2 text-danger">
                                    <i class='bx bx-x-circle me-2'></i> {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="ri-file-list-3-line"></i>Thông tin gói thành viên</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th style="width: 20%">Tên gói</th>
                                        <td>{{ $approval->membershipPlan->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Mã gói</th>
                                        <td>{{ $approval->membershipPlan->code }}</td>
                                    </tr>
                                    <tr>
                                        <th>Thời hạn</th>
                                        <td>{{ $approval->membershipPlan->duration_months }} tháng</td>
                                    </tr>
                                    <tr>
                                        <th>Giá gói</th>
                                        <td>{{ number_format($approval->membershipPlan->price) }} VND</td>
                                    </tr>
                                    <tr>
                                        <th>Khoá học</th>
                                        <td>
                                            <button type="button" class="btn btn-link text-primary p-0"
                                                data-bs-toggle="modal" data-bs-target="#courseListModal">
                                                {{ $approval->membershipPlan->membershipCourseAccess->count() }}
                                                <span>khoá học</span>
                                                <i class="ri-external-link-line ms-1"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Mô tả</th>
                                        <td>{{ $approval->membershipPlan->description }}</td>
                                    </tr>
                                    <tr>
                                        <th>Trạng thái gói</th>
                                        <td>
                                            @if ($approval->membershipPlan->status === 'draft')
                                                <span class="badge bg-dark">Nháp</span>
                                            @elseif ($approval->membershipPlan->status === 'pending')
                                                <span class="badge bg-warning text-dark">Chờ xử lý</span>
                                            @elseif ($approval->membershipPlan->status === 'active')
                                                <span class="badge bg-success">Đang hoạt động</span>
                                            @elseif ($approval->membershipPlan->status === 'inactive')
                                                <span class="badge bg-danger">Không hoạt động</span>
                                            @else
                                                <span class="badge bg-dark">Không xác định</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Ngày tạo</th>
                                        <td>{{ \Carbon\Carbon::parse($approval->membershipPlan->created_at)->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Quyền lợi thành viên --}}
                        <div class="mt-4">
                            <h6 class="mb-3">Quyền lợi thành viên</h6>
                            @php
                                $benefits = $approval->membershipPlan->benefits ?? '';
                            @endphp

                            <ul class="benefits-list">
                                @if (is_array($benefits))
                                    @foreach ($benefits as $benefit)
                                        <li class="benefit-item">
                                            <div class="benefit-icon">
                                                <i class="ri-checkbox-circle-fill"></i>
                                            </div>
                                            <span>{{ $benefit }}</span>
                                        </li>
                                    @endforeach
                                @else
                                    <li class="benefit-item">
                                        <div class="benefit-icon">
                                            <i class="fas fa-info"></i>
                                        </div>
                                        <span>{{ $approval->membershipPlan->benefits }}</span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="ri-account-circle-line"></i>Thông tin giảng viên</h5>
                    </div>
                    <div class="card-body">
                        <div class="instructor-profile overflow-hidden">
                            <img src="{{ $approval->membershipPlan->instructor->avatar }}"
                                alt="{{ $approval->membershipPlan->instructor->name }}" class="instructor-avatar">
                            <div class="instructor-details">
                                <h5>{{ $approval->membershipPlan->instructor->name }}</h5>
                                <p>
                                    <i class="far fa-envelope me-1"></i>
                                    {{ $approval->membershipPlan->instructor->email }}
                                </p>
                                <p class="info-value">
                                    <i class="fas fa-id-badge me-2 text-primary"></i>
                                    {{ $approval->membershipPlan->instructor->code }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('admin.approvals.instructors.show', $approval->membershipPlan->instructor->id) }}"
                            class="btn btn-view action-btn">
                            <i class="fas fa-user-circle action-icon"></i>Xem hồ sơ giảng viên
                        </a>
                    </div>
                </div>

                @if ($approval->status == 'pending')
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><i class=" ri-settings-4-line"></i>Thao tác</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.approvals.memberships.approve', $approval->id) }}" method="POST"
                                id="approveForm">
                                @csrf
                                @method('PUT')
                                <button class="btn  btn-primary approve action-btn" type="button">Phê duyệt</button>
                            </form>
                            <button type="button" class="btn btn-reject action-btn" data-bs-toggle="modal"
                                data-bs-target="#rejectModal">
                                Từ chối
                            </button>
                        </div>
                    </div>

                    <div id="rejectModal" class="modal fade" tabindex="-1" aria-labelledby="rejectModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="rejectModalLabel">Từ chối</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="rejectForm"
                                    action="{{ route('admin.approvals.memberships.reject', $approval->id) }}"
                                    method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="rejectReason" class="form-label">Lý do từ
                                                chối</label>
                                            <textarea placeholder="Nhập lý do từ chối..." class="form-control" id="rejectNote" name="note" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                            Huỷ
                                        </button>
                                        <button type="button" class="btn btn-primary" id="submitRejectForm">
                                            Xác
                                            nhận
                                        </button>
                                    </div>
                                </form>
                            </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div id="courseListModal" class="modal fade" tabindex="-1" aria-labelledby="courseListModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="courseListModalLabel">Danh sách khóa học
                        ({{ $approval->membershipPlan->membershipCourseAccess->count() }} khóa học)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="courses-container">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="approvalLogsModal" tabindex="-1" aria-labelledby="approvalLogsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approvalLogsModalLabel">
                        <i class="ri-time-line me-1"></i> Lịch sử kiểm duyệt
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    @php
                        $approval_logs = collect(json_decode($approval->approval_logs, true))
                            ->sortByDesc('action_at')
                            ->values()
                            ->all();
                    @endphp

                    @if (!empty($approval_logs))
                        @foreach ($approval_logs as $log)
                            <div
                                class="card mb-3 shadow-sm border-start border-4 
                            @switch($log['status'])
                                @case('approved') border-success @break
                                @case('rejected') border-danger @break
                                @default border-secondary
                            @endswitch">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($log['action_at'])->format('d/m/Y H:i') }}
                                        </small>
                                        <h6 class="mb-0">{{ $log['name'] }}</h6>
                                    </div>

                                    <p class="mb-1">
                                        <strong>Trạng thái: </strong>
                                        @switch($log['status'])
                                            @case('approved')
                                                <span class="badge bg-success">Duyệt</span>
                                            @break

                                            @case('rejected')
                                                <span class="badge bg-danger">Từ chối</span>
                                            @break

                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($log['status']) }}</span>
                                        @endswitch
                                    </p>

                                    @if (!empty($log['note']))
                                        <p class="mb-1"><strong>Ghi chú:</strong> {{ $log['note'] }}</p>
                                    @endif

                                    @if (!empty($log['reason']))
                                        <p class="mb-0"><strong>Lý do:</strong> {{ $log['reason'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <h6 class="mb-0">Chưa có lịch sử kiểm duyệt.</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

@push('page-scripts')
    <script>
        $(document).ready(function() {
            $(".approve").click(function(event) {
                event.preventDefault();

                Swal.fire({
                    title: "Phê duyệt gói thành viên ?",
                    text: "Bạn có chắc chắn muốn phê duyệt gói thành viên này?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Phê duyệt",
                    cancelButtonText: "Huỷ"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#approveForm").submit();
                    }
                });
            });

            $('#submitRejectForm').on('click', function() {
                const note = $('#rejectNote').val();

                if (note.trim() === '') {
                    Swal.fire({
                        text: "Vui lòng nhập lý do từ chối.",
                        icon: 'warning'
                    });
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: $('#rejectForm').attr('action'),
                    data: {
                        _method: 'PUT',
                        note,
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Thao tác thành công!',
                            text: 'Lý do từ chối đã được ghi nhận.',
                            icon: 'success'
                        }).then(() => {
                            $('#rejectModal').modal('hide');
                            location.reload();
                        });
                    },
                    error: function(error) {
                        Swal.fire({
                            title: 'Thao tác thất bại!',
                            text: 'Đã có lỗi xảy ra. Vui lòng thử lại.',
                            icon: 'error'
                        });
                    }
                });
            });

            $('#courseListModal').on('show.bs.modal', function() {
                loadCourses(1);
            });

            function loadCourses(page) {
                let container = $('#courses-container');

                container.html(`
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);

                $.ajax({
                    url: `{{ route('admin.approvals.memberships.courses', $approval->id) }}?page=${page}`,
                    method: 'GET',
                    success: function(response) {
                        container.html(response);

                        container.find('.pagination a').on('click', function(e) {
                            e.preventDefault();
                            let page = new URL($(this).attr('href')).searchParams.get('page');
                            loadCourses(page);
                        });
                    },
                    error: function() {
                        container.html(`
                    <div class="alert alert-danger">
                        Có lỗi xảy ra khi tải danh sách khóa học. Vui lòng thử lại sau.
                    </div>
                `);
                    }
                });
            }
        });
    </script>
@endPush

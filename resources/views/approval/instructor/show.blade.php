@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="profile-foreground position-relative mx-n4 mt-n4">
            <div class="profile-wid-bg">
                <img src="{{ asset('assets/images/profile-bg.jpg') }}" alt="" class="profile-wid-img" />
            </div>
        </div>
        <div class="pt-4 mb-4 mb-lg-3 pb-lg-4 profile-wrapper">
            <div class="row g-4">
                <div class="col-md-auto">
                    <div style="width: 100px; height: 100px;" class="avatar-lg rounded-circle ">
                        <img src="{{ $approval->user->avatar }}" alt="" class="h-100 object-fit-cover rounded">
                    </div>
                </div>
                <!--end col-->
                <div class="col">
                    <div class="p-2">
                        <h3 class="text-white mb-1">{{ $approval->user->name ?? '' }}</h3>
                        <p class="text-white text-opacity-75">Owner & Founder</p>
                        <div class="hstack text-white-50 gap-1">
                            <div class="me-2"><i
                                    class="ri-map-pin-user-line me-1 text-white text-opacity-75 fs-16 align-middle"></i>
                                {{ $approval->user->profile->address ?? 'Chưa có thông tin' }}
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->
                <div class="col-12 col-lg-auto order-last order-lg-0">
                    <div class="row text text-white-50 text-center">
                        <div class="col-lg-6 col-4">
                            <div class="p-2">
                                <h4 class="text-white mb-1">{{ $approval->follower_count }}</h4>
                                <p class="fs-14 mb-0">Followers</p>
                            </div>
                        </div>
                        <div class="col-lg-6 col-4">
                            <div class="p-2">
                                <h4 class="text-white mb-1">{{ $approval->following_count }}</h4>
                                <p class="fs-14 mb-0">Following</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->

            </div>
            <!--end row-->
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div>
                    <div class="d-flex profile-wrapper">
                        <!-- Nav tabs -->
                        <ul class="nav nav-pills animation-nav profile-nav gap-2 gap-lg-3 flex-grow-1" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link fs-14 active" data-bs-toggle="tab" href="#overview-tab" role="tab">
                                    <i class="ri-airplay-fill d-inline-block d-md-none"></i> <span
                                        class="d-none d-md-inline-block">Tổng quan</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-14" data-bs-toggle="tab" href="#qa" role="tab">
                                    <i class="ri-price-tag-line d-inline-block d-md-none"></i> <span
                                        class="d-none d-md-inline-block">QA System</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-14" data-bs-toggle="tab" href="#certificates" role="tab">
                                    <i class="ri-folder-4-line d-inline-block d-md-none"></i> <span
                                        class="d-none d-md-inline-block">Chứng chỉ</span>
                                </a>
                            </li>
                            @if (!empty($approval->approval_logs))
                                <li class="nav-item">
                                    <a class="nav-link fs-14" data-bs-toggle="tab" href="#approval_logs" role="tab">
                                        <i class="ri-folder-4-line d-inline-block d-md-none"></i> <span
                                            class="d-none d-md-inline-block">Lịch sử kiểm duyệt</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                        <div class="flex-shrink-0">
                            @if ($approval->status === 'pending')
                                <div class="d-flex gap-2">
                                    <form action="{{ route('admin.approvals.instructors.approve', $approval->id) }}"
                                        method="POST" id="approveForm">
                                        @csrf
                                        @method('PUT')
                                        <button class="btn btn-primary approve " type="button">Phê duyệt</button>
                                    </form>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">
                                        Từ chối
                                    </button>

                                    <div id="rejectModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel"
                                        aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="myModalLabel">Từ chối người hướng
                                                        dẫn</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form id="rejectForm"
                                                    action="{{ route('admin.approvals.instructors.reject', $approval->id) }}"
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
                                                        <button type="button" class="btn btn-light"
                                                            data-bs-dismiss="modal">Huỷ
                                                        </button>
                                                        <button type="button" class="btn btn-primary"
                                                            id="submitRejectForm">Xác nhận
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($approval->status === 'rejected')
                                <button type="button" class="btn btn-danger ">
                                    Giảng viên không đủ điều kiện
                                </button>
                            @else
                                <button type="button" class="btn btn-success ">
                                    Giảng viên đã được phê duyệt
                                </button>
                            @endif

                        </div>
                    </div>
                    <div class="tab-content pt-4 text-muted">
                        <div class="tab-pane active" id="overview-tab" role="tabpanel">
                            <div class="row">
                                <div class="col-xxl-3">
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h5 class="card-title mb-0">Mức độ hoàn thiện hồ sơ</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="progress animated-progress custom-progress progress-label mb-2">
                                                <div class="progress-bar {{ $score < 50 ? 'bg-danger' : ($score < 80 ? 'bg-warning' : 'bg-success') }}"
                                                    role="progressbar" style="width: {{ $score }}%"
                                                    aria-valuenow="{{ $score }}" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <span class="fw-medium">{{ $score }}%</span>
                                                <small class="text-muted ms-1">
                                                    {{ $score < 50 ? 'Cần bổ sung thông tin' : ($score < 80 ? 'Khá đầy đủ' : 'Đã hoàn thiện') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h5 class="card-title m-0">Thông tin cá nhân</h5>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item px-0 d-flex justify-content-between">
                                                    <span class="text-muted">Họ tên</span>
                                                    <span
                                                        class="fw-medium">{{ $approval->user->name ?? 'No information' }}</span>
                                                </li>
                                                <li class="list-group-item px-0 d-flex justify-content-between">
                                                    <span class="text-muted">Số điện thoại</span>
                                                    <span
                                                        class="fw-medium">{{ $approval->user->profile->phone ?? 'No information' }}</span>
                                                </li>
                                                <li class="list-group-item px-0 d-flex justify-content-between">
                                                    <span class="text-muted">Email</span>
                                                    <span
                                                        class="fw-medium">{{ $approval->user->email ?? 'No information' }}</span>
                                                </li>
                                                <li class="list-group-item px-0 d-flex justify-content-between">
                                                    <span class="text-muted">Exp</span>
                                                    <span
                                                        class="fw-medium">{{ $approval->user->profile->experience ?? 'No information' }}
                                                        years</span>
                                                </li>
                                                <li class="list-group-item px-0 d-flex justify-content-between">
                                                    <span class="text-muted">Địa chỉ</span>
                                                    <span
                                                        class="fw-medium">{{ $approval->user->profile->address ?? 'No information' }}</span>
                                                </li>
                                                <li class="list-group-item px-0 d-flex justify-content-between">
                                                    <span class="text-muted">Tham gia</span>
                                                    <span
                                                        class="fw-medium">{{ $approval->created_at->format('d/m/Y') }}</span>
                                                </li>

                                                @if (!empty($approval->user->profile->identity_verification))
                                                    <li class="list-group-item px-0 d-flex justify-content-between">
                                                        <span class="text-muted">Xác minh danh tính</span>
                                                        <button type="button" class="badge bg-primary"
                                                            data-bs-toggle="modal" data-bs-target="#modalId">
                                                            Xác minh danh tính
                                                        </button>
                                                    </li>
                                                @endif

                                                <div class="modal fade" id="modalId" tabindex="-1"
                                                    data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
                                                    aria-labelledby="modalTitleId" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm"
                                                        role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="modalTitleId">
                                                                    Xác minh danh tính
                                                                </h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body"><img
                                                                    src="{{ Storage::url($approval->user->profile->identity_verification) }}"
                                                                    class="img-fluid rounded-top" alt="" />
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">
                                                                    Đóng
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                                <!--end col-->
                                <div class="col-xxl-9">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title mb-3">Giới thiệu bản thân</h5>
                                            <p>
                                                {{ $approval->user->profile->about_me ?? '' }}
                                            </p>
                                        </div>
                                        <!--end card-body-->
                                    </div><!-- end card -->

                                    <div class="card">
                                        <div
                                            class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <h5 class="card-title mb-0">Mạng xã hội</h5>
                                            @php
                                                $socials = json_decode($approval->user->profile->bio, true) ?? '';
                                                $socials = is_array($socials) ? $socials : [];
                                                $filledSocials = array_filter($socials);
                                            @endphp
                                            <span
                                                class="badge {{ empty($filledSocials) ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info' }}">
                                                {{ count($filledSocials) }} liên kết
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $icon = [
                                                    'facebook' => 'ri-facebook-fill',
                                                    'twitter' => 'ri-twitter-fill',
                                                    'instagram' => 'ri-instagram-fill',
                                                    'linkedin' => 'ri-linkedin-fill',
                                                    'github' => 'ri-github-fill',
                                                    'dribbble' => 'ri-dribbble-fill',
                                                    'youtube' => 'ri-youtube-fill',
                                                    'website' => 'ri-global-fill',
                                                ];
                                            @endphp

                                            @if (!empty($filledSocials))
                                                <div class="d-flex flex-wrap gap-3">
                                                    @foreach ($socials as $key => $url)
                                                        @if (array_key_exists($key, $icon) && $url)
                                                            <a href="{{ $url }}"
                                                                class="btn btn-soft-primary btn-sm" target="_blank">
                                                                <i class="{{ $icon[$key] }} me-1"></i>
                                                                {{ ucfirst($key) }}
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center py-3">
                                                    <div class="avatar-sm mx-auto mb-3">
                                                        <span class="avatar-title rounded-circle bg-light text-body fs-20">
                                                            <i class="ri-links-line"></i>
                                                        </span>
                                                    </div>
                                                    <p class="text-muted mb-0">Người dùng chưa thêm liên kết mạng xã hội
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <!--end col-->
                            </div>
                            <!--end row-->
                        </div>
                        <!--end tab-pane-->
                        <div class="tab-pane fade" id="qa" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    @php
                                        $qaSystems = json_decode($approval->user->profile->qa_systems ?? '[]', true);
                                        $qaSystems = is_array($qaSystems) ? $qaSystems : [];
                                    @endphp
                                    <div class="accordion" id="default-accordion-example">
                                        @if (!empty($qaSystems))
                                            @foreach ($qaSystems as $index => $qaSystem)
                                                <div class="accordion-item mb-3">
                                                    <h2 class="accordion-header" id="heading{{ $index }}">
                                                        <button class="accordion-button" type="button"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#collapse{{ $index }}"
                                                            aria-expanded="false"
                                                            aria-controls="collapse{{ $index }}">
                                                            {{ $qaSystem['question'] }}
                                                        </button>
                                                    </h2>
                                                    <div id="collapse{{ $index }}"
                                                        class="accordion-collapse collapse"
                                                        aria-labelledby="heading{{ $index }}"
                                                        data-bs-parent="#default-accordion-example">
                                                        <div class="accordion-body">
                                                            @if (count($qaSystem['selected_options']) > 1)
                                                                @foreach ($qaSystem['options'] as $optionIndex => $option)
                                                                    <div class="form-check  mb-3">
                                                                        <input type="checkbox"
                                                                            name="option[{{ $loop->parent->index }}][]"
                                                                            value="{{ $optionIndex }}"
                                                                            @if (in_array($optionIndex, $qaSystem['selected_options'])) checked @endif
                                                                            @if (!in_array($optionIndex, $qaSystem['selected_options'])) disabled @endif
                                                                            id="checkbox-{{ $loop->parent->index }}-{{ $loop->index }}"
                                                                            class="form-check-input">
                                                                        <label
                                                                            for="checkbox-{{ $loop->parent->index }}-{{ $loop->index }}"
                                                                            class="form-check-label">{{ $option }}</label>
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                @foreach ($qaSystem['options'] as $optionIndex => $option)
                                                                    <div class="form-check mb-3">
                                                                        <input type="radio"
                                                                            name="option[{{ $loop->parent->index }}]"
                                                                            value="{{ $optionIndex }}"
                                                                            @if (in_array($optionIndex, $qaSystem['selected_options'])) checked @endif
                                                                            @if (!in_array($optionIndex, $qaSystem['selected_options'])) disabled @endif
                                                                            id="radio-{{ $loop->parent->index }}-{{ $loop->index }}"
                                                                            class="form-check-input">
                                                                        <label
                                                                            for="radio-{{ $loop->parent->index }}-{{ $loop->index }}"
                                                                            class="form-check-label">{{ $option }}</label>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>

                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                </div>
                            </div>
                            <!--end card-->
                        </div>
                        <!--end tab-pane-->
                        <div class="tab-pane fade" id="certificates" role="tabpanel" aria-labelledby="certificates-tab">
                            <div class="card">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h5 class="card-title m-0">Chứng chỉ</h5>
                                    <span
                                        class="badge bg-info-subtle text-info">{{ count(json_decode($approval->user->profile->certificates ?? '[]', true)) }}
                                        chứng chỉ</span>
                                </div>
                                <div class="card-body">
                                    @php
                                        $certificates = json_decode(
                                            $approval->user->profile->certificates ?? '[]',
                                            true,
                                        );
                                        $certificates = is_array($certificates) ? $certificates : [];
                                    @endphp

                                    @if (!empty($certificates))
                                        <div class="row g-4">
                                            @foreach ($certificates as $certificate)
                                                @php
                                                    $fileExtension = pathinfo($certificate, PATHINFO_EXTENSION);
                                                    $fileName = pathinfo($certificate, PATHINFO_FILENAME);
                                                    $isPdf = strtolower($fileExtension) === 'pdf';
                                                @endphp

                                                <div class="col-md-6 col-lg-4">
                                                    <div class="card h-100 border shadow-sm">
                                                        <div class="position-relative certificate-preview">
                                                            @if ($isPdf)
                                                                <div class="text-center p-4 bg-light border-bottom"
                                                                    style="height: 200px;">
                                                                    <div
                                                                        class="d-flex justify-content-center align-items-center h-100">
                                                                        <div>
                                                                            <i
                                                                                class="ri-file-pdf-2-line text-danger fs-1 mb-2"></i>
                                                                            <p class="mb-0">
                                                                                {{ $fileName }}.{{ $fileExtension }}
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="certificate-img"
                                                                    style="height: 200px; overflow: hidden;">
                                                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($certificate) }}"
                                                                        class="card-img-top h-100 w-100 object-fit-cover"
                                                                        alt="Certificate Preview">
                                                                </div>
                                                            @endif

                                                            <div
                                                                class="certificate-actions position-absolute top-0 end-0 m-2">
                                                                <div class="dropdown">
                                                                    <button
                                                                        class="btn btn-sm btn-light rounded-circle shadow-sm"
                                                                        type="button" data-bs-toggle="dropdown"
                                                                        aria-expanded="false">
                                                                        <i class="ri-more-2-fill"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu">
                                                                        <li>
                                                                            <a class="dropdown-item"
                                                                                href="{{ \Illuminate\Support\Facades\Storage::url($certificate) }}"
                                                                                target="_blank">
                                                                                <i class="ri-eye-line me-2 text-muted"></i>
                                                                                View
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <a class="dropdown-item"
                                                                                href="{{ \Illuminate\Support\Facades\Storage::url($certificate) }}"
                                                                                download>
                                                                                <i
                                                                                    class="ri-download-2-line me-2 text-muted"></i>
                                                                                Download
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="card-body">
                                                            <h6 class="card-title text-truncate">{{ $fileName }}</h6>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="badge bg-light text-dark">
                                                                    <i
                                                                        class="ri-file-{{ $isPdf ? 'pdf' : 'image' }}-line me-1"></i>
                                                                    {{ strtoupper($fileExtension) }}
                                                                </span>
                                                                <div class="btn-group">
                                                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($certificate) }}"
                                                                        class="btn btn-sm btn-outline-primary"
                                                                        target="_blank">
                                                                        <i class="ri-eye-line"></i>
                                                                    </a>
                                                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($certificate) }}"
                                                                        class="btn btn-sm btn-outline-primary" download>
                                                                        <i class="ri-download-2-line"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <div class="avatar-md mx-auto mb-4">
                                                <div class="avatar-title bg-light text-secondary rounded-circle fs-24">
                                                    <i class="ri-award-line"></i>
                                                </div>
                                            </div>
                                            <h5>No certificates uploaded</h5>
                                            <p class="text-muted">The instructor hasn't uploaded any certificates or
                                                credentials yet.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="approval_logs" role="tabpanel">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">Lịch sử kiểm duyệt</h5>
                                        </div>
                                        <div class="card-body">
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
                                                @endswitch
                                            ">
                                                        <div class="card-body">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center mb-2">
                                                                <h6 class="mb-0">{{ $log['name'] }}</h6>
                                                                <small
                                                                    class="text-muted">{{ \Carbon\Carbon::parse($log['action_at'])->format('d/m/Y H:i') }}</small>
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
                                                                        <span
                                                                            class="badge bg-secondary">{{ ucfirst($log['status']) }}</span>
                                                                @endswitch
                                                            </p>

                                                            @if (!empty($log['note']))
                                                                <p class="mb-1"><strong>Ghi chú:</strong>
                                                                    {{ $log['note'] }}</p>
                                                            @endif

                                                            @if (!empty($log['reason']))
                                                                <p class="mb-0"><strong>Lý do:</strong>
                                                                    {{ $log['reason'] }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="d-flex justify-content-center align-items-center"
                                                    style="height: 150px;">
                                                    <p class="text-muted fs-5 mb-0">Chưa có lịch sử kiểm duyệt</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script>
        const myModal = new bootstrap.Modal(
            document.getElementById("modalId"),
            options,
        );

        $(document).ready(function() {
            $(".approve").click(function(event) {
                event.preventDefault();

                Swal.fire({
                    title: "Phê duyệt giảng viên ?",
                    text: "Bạn có chắc chắn muốn phê duyệt giảng viên này?",
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
        });
    </script>
@endpush

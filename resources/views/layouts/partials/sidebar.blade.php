<div class="container-fluid">

    <div id="two-column-menu">
    </div>
    <ul class="navbar-nav" id="navbar-nav">
        @canany(['analytic.read'])
            <li class="menu-title"><span data-key="t-menu">Bảng điều khiển</span></li>
        @endcanany
        <li class="nav-item cusor-pointer">
            @canany(['revenue.read'])
                <a class="nav-link menu-link" href="{{ route('admin.dashboard') }}">
                    <i class="ri-dashboard-2-line"></i>  <!-- Thay icon ở đây -->
                    <span data-key="t-dashboards">Thống kê tổng quan</span>
                </a>
            @endcanany
        </li>        
        
        <li class="nav-item cusor-pointer">
            @canany(['revenue.read'])
                <a class="nav-link menu-link" href="{{ route('admin.revenue-statistics.index') }}">
                    <i class="ri-money-dollar-circle-line"></i>
                    <span data-key="t-dashboards">Thống kê doanh thu</span>
                </a>
            @endcanany
        </li>
        
        <li class="nav-item cusor-pointer">
            @canany(['top-course.read'])
                <a class="nav-link menu-link" href="{{ route('admin.top-courses.index') }}">
                    <i class="ri-book-mark-line"></i> <!-- Thay icon ở đây -->
                    <span data-key="t-dashboards">Thống kê top khóa học</span>
                </a>
            @endcanany
        </li>
        
        <li class="nav-item cusor-pointer">
            @canany(['top-instructors.read'])
                <a class="nav-link menu-link" href="{{ route('admin.top-instructors.index') }}">
                    <i class="ri-graduation-cap-line"></i> <!-- Thay icon ở đây -->
                    <span data-key="t-dashboards">Thống kê top giảng viên</span>
                </a>
            @endcanany
        </li>
        
        <li class="nav-item cusor-pointer">
            @canany(['top-students.read'])
                <a class="nav-link menu-link" href="{{ route('admin.top-students.index') }}">
                    <i class="ri-user-star-line"></i> <!-- Thay icon ở đây -->
                    <span data-key="t-dashboards">Thống kê top học viên</span>
                </a>
            @endcanany
        </li>
        
        <li class="nav-item cusor-pointer">
            @canany(['analytic.read'])
                <a class="nav-link menu-link" href="{{ route('admin.analytics.index') }}">
                    <i class="ri-bar-chart-line"></i> <!-- Thay icon ở đây -->
                    <span data-key="t-dashboards">Thống kê truy cập</span>
                </a>
            @endcanany
        </li>        
        @canany(['transactions.read'])
            <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">Quản lý giao dịch</span>
            </li>
        @endcanany
        <li class="nav-item">
            @canany(['invoices.read'])
                <a class="nav-link menu-link" href="{{ route('admin.invoices.index') }}">
                    <i class="ri-book-open-line"></i> <span data-key="t-authentication">Khoá học đã bán</span>
                </a>
            @endcanany
            @canany(['invoices.memberships.read'])
                <a class="nav-link menu-link" href="{{ route('admin.invoices.memberships.index') }}">
                    <i class="ri-vip-crown-line"></i> <span data-key="t-authentication">Gói thành viên đã bán</span>
                </a>
            @endcanany
            @canany(['transactions.read'])
                <a class="nav-link menu-link" href="{{ route('admin.transactions.index') }}">
                    <i class="ri-exchange-dollar-line"></i> <span data-key="t-authentication">Giao dịch thanh toán</span>
                </a>
            @endcanany
            @canany(['withdrawals.read'])
                <a class="nav-link menu-link" href="{{ route('admin.withdrawals.index') }}">
                    <i class="ri-bank-card-line"></i> <span data-key="t-authentication">Yêu cầu rút tiền</span>
                </a>
            @endcanany
        </li>        

        <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">Kiểm duyệt hệ thống</span>
        </li>

        <li class="nav-item">
            <a class="nav-link menu-link" href="{{ route('admin.approvals.courses.index') }}">
                <i class="las la-book-reader"></i> <span data-key="t-authentication">Kiểm duyệt khoá học</span>
            </a>
            <a class="nav-link menu-link" href="{{ route('admin.approvals.instructors.index') }}">
                <i class="las la-chalkboard-teacher"></i> <span data-key="t-authentication">Kiểm duyệt giảng viên</span>
            </a>
            <a class="nav-link menu-link" href="{{ route('admin.approvals.posts.index') }}">
                <i class="las la-newspaper"></i> <span data-key="t-authentication">Kiểm duyệt bài viết</span>
            </a>
            <a class="nav-link menu-link" href="{{ route('admin.approvals.memberships.index') }}">
                <i class="ri-vip-crown-line"></i> <span data-key="t-authentication">Gói thành viên</span>
            </a>
        </li>        

        <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">Quản lý người dùng</span>
        </li>

        <li class="nav-item">
            <a class="nav-link menu-link" href="#sidebarAuth" data-bs-toggle="collapse" role="button"
                aria-expanded="false" aria-controls="sidebarAuth">
                <i class="ri-account-circle-line"></i> <span data-key="t-authentication">Quản lý thành viên</span>
            </a>
            <div class="collapse menu-dropdown" id="sidebarAuth">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('admin.clients.index') }}" class="nav-link">
                            Danh sách người dùng </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.instructors.index') }}" class="nav-link">
                            Giảng viên </a>
                    </li>
                    @can('super_admin.view')
                        <li class="nav-item">
                            <a href="{{ route('admin.employees.index') }}" class="nav-link">
                                Danh sách nhân viên </a>
                        </li>
                    @endcan
                    @can('user.create')
                        <li class="nav-item">
                            <a href="{{ route('admin.users.create') }}" class="nav-link">
                                Thêm mới người dùng
                            </a>
                        </li>
                    @endcan
                    @can('super_admin.view')
                        <li class="nav-item">
                            <a href="{{ route('admin.users.deleted.index') }}" class="nav-link">
                                Danh sách thành viên đã xóa </a>
                        </li>
                    @endcan
                    <li class="nav-item">
                        <a href="{{ route('admin.memberships.index') }}" class="nav-link">
                            Danh sách thành viên </a>
                    </li>
                </ul>
            </div>
            @canany(['permissions.create', 'permissions.edit', 'permissions.read', 'permissions.delete'])
                <a class="nav-link menu-link" href="#sidebarRole" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarRole">
                    <i class=" ri-shield-user-line"></i> <span data-key="t-authentication">Phân quyền</span>
                </a>
                <div class="collapse menu-dropdown" id="sidebarRole">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a href="{{ route('admin.permissions.index') }}" class="nav-link" data-key="t-chat">
                                Danh sách quyền </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.roles.index') }}" class="nav-link" data-key="t-chat">
                                Danh sách vai trò </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.roles.create') }}" class="nav-link" data-key="t-chat">
                                Thêm vai trò </a>
                        </li>
                    </ul>
                </div>
            @endcanany

        </li>

        <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">Quản lý hệ thống</span>
        </li>

        <li class="nav-item">
            @canany(['category.create', 'category.edit', 'category.read', 'category.delete'])
                <a class="nav-link menu-link" href="#sidebarCategory" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarCategory">
                    <i class="ri-apps-2-line"></i> <span data-key="t-authentication">Quản lý danh mục</span>
                </a>
                <div class="collapse menu-dropdown" id="sidebarCategory">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a href="{{ route('admin.categories.index') }}" class="nav-link" data-key="t-chat">
                                Danh sách danh mục </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.categories.create') }}" class="nav-link" data-key="t-chat">
                                Thêm mới danh mục </a>
                        </li>
                    </ul>
                </div>
            @endcanany
            @canany(['banner.create', 'banner.edit', 'banner.read', 'banner.delete'])
                <a class="nav-link menu-link" href="#sidebarBanner" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarBanner">
                    <i class=" las la-image"></i> <span data-key="t-authentication">Quản lý banners</span>
                </a>
                <div class="collapse menu-dropdown" id="sidebarBanner">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a href="{{ route('admin.banners.index') }}" class="nav-link" data-key="t-chat">
                                Danh sách banners </a>
                        </li>
                        {{-- @can('') --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.banners.create') }}" class="nav-link" data-key="t-chat">
                                Thêm mới banner </a>
                        </li>
                        {{-- @endcan --}}

                        <li class="nav-item">
                            <a href="{{ route('admin.banners.deleted') }}" class="nav-link" data-key="t-chat">
                                Danh sách banner đã xóa </a>
                        </li>
                    </ul>
                </div>
            @endcanany
            @canany(['post.create', 'post.edit', 'post.read', 'post.delete'])
                <a class="nav-link menu-link" href="#sidebarPost" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarPost">
                    <i class="las la-newspaper"></i> <span data-key="t-authentication">Quản lý bài viết</span>
                </a>
                <div class="collapse menu-dropdown" id="sidebarPost">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a href="{{ route('admin.posts.index') }}" class="nav-link" data-key="t-chat">
                                Danh sách bài viết </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.posts.create') }}" class="nav-link" data-key="t-chat">
                                Thêm mới bài viết </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.posts.list-post-delete') }}" class="nav-link" data-key="t-chat">
                                Danh sách bài viết đã xóa </a>
                        </li>
                    </ul>
                </div>
            @endcanany
            @canany(['coupon.create', 'coupon.edit', 'coupon.read', 'coupon.delete'])
                <a class="nav-link menu-link" href="#sidebarCoupon" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarCoupon">
                    <i class=" ri-coupon-line"></i> <span data-key="t-authentication">Quản lý mã giảm giá</span>
                </a>

                <div class="collapse menu-dropdown" id="sidebarCoupon">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a href="{{ route('admin.coupons.index') }}" class="nav-link" data-key="t-chat">
                                Danh sách mã giảm giá </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.coupons.create') }}" class="nav-link" data-key="t-chat">
                                Thêm mới mã giảm giá </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.coupons.deleted') }}" class="nav-link" data-key="t-chat">
                                Danh sách mã giảm giá đã xóa </a>
                        </li>
                    </ul>
                </div>
            @endcanany
            @canany(['comment.create', 'comment.edit', 'comment.read', 'comment.delete'])
                <a class="nav-link menu-link" href="#sidebarComment" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarComment">
                    <i class="las la-comment"></i> <span data-key="t-authentication">Quản lý bình luận</span>
                </a>
                <div class="collapse menu-dropdown" id="sidebarComment">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a href="{{ route('admin.comments.index') }}" class="nav-link" data-key="t-chat">
                                Danh sách Bình luận </a>
                        </li>


                    </ul>
                </div>
            @endcanany
            @canany(['course.create', 'course.edit', 'course.read', 'course.delete'])
                <a class="nav-link menu-link" href="#sidebarCourse" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarCourse">
                    <i class="ri-book-line"></i> <span data-key="t-authentication">Quản lý khóa học</span>
                </a>
                <div class="collapse menu-dropdown" id="sidebarCourse">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a href="{{ route('admin.courses.index') }}" class="nav-link" data-key="t-chat">
                                Danh sách khóa học </a>
                        </li>


                    </ul>
                </div>
            @endcanany


            <a class="nav-link menu-link" href="#sidebarInstructorCommission" data-bs-toggle="collapse"
                role="button" aria-expanded="false" aria-controls="sidebarInstructorCommission">
                <i class="ri-gift-line"></i> <span data-key="t-authentication">Quản lý hoa hồng</span>
            </a>
            <div class="collapse menu-dropdown" id="sidebarInstructorCommission">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('admin.instructor-commissions.index') }}" class="nav-link"
                            data-key="t-chat">
                            Danh sách hoa hồng của giảng viên </a>
                    </li>

                </ul>
            </div>


            @canany(['setting.create', 'setting.edit', 'setting.read', 'setting.delete'])
                <a class="nav-link menu-link" href="#sidebarSetting" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarSetting">
                    <i class=" ri-settings-3-line"></i> <span data-key="t-authentication">Quản lý settings</span>
                </a>
                <div class="collapse menu-dropdown" id="sidebarSetting">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a href="{{ route('admin.settings.index') }}" class="nav-link" data-key="t-chat">
                                Danh sách settings </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.settings.create') }}" class="nav-link" data-key="t-chat">
                                Thêm mới settings </a>
                        </li>
                    </ul>
                </div>
            @endcanany
            @canany(['spin.read'])
                <a class="nav-link menu-link" data-bs-toggle="collapse" data-bs-target="#sidebarSpin" role="button"
                    aria-expanded="false" aria-controls="sidebarSpin">
                    <i class="ri-refresh-line"></i> <span data-key="t-authentication">Quản lý vòng quay</span>
                </a>
                <div class="collapse menu-dropdown" id="sidebarSpin">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a href="{{ route('admin.spins.index') }}" class="nav-link" data-key="t-chat">Cấu hình vòng
                                quay</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.spin-types.index') }}" class="nav-link" data-key="t-chat">Danh sách
                                loại
                                quà</a>
                        </li>
                    </ul>
                </div>
            @endcanany

            <a class="nav-link menu-link" href="{{ route('admin.instructor-commissions.index') }}" >
                <i class="las la-coins"></i> <span data-key="t-authentication">Phân chia doanh thu</span>
            </a>

            @canany(['commissions.create', 'commissions.edit', 'commissions.read', 'commissions.delete'])
                <a class="nav-link menu-link" href="{{ route('admin.commissions.index') }}">
                    <i class="ri-money-dollar-circle-line"></i> <span data-key="t-authentication">Cấu hình thanh toán</span>
                </a>
            @endcanany
            <a class="nav-link menu-link" href="{{ route('admin.chats.index') }}">
                <i class="lab la-weixin"></i> <span data-key="t-authentication">Trò chuyện</span>
            </a>
            @canany(['qa_system.create', 'qa_system.edit', 'qa_system.read', 'qa_system.delete'])
                <a class="nav-link menu-link" href="{{ route('admin.qa-systems.index') }}">
                    <i class="ri-question-line"></i> <span data-key="t-authentication">QA System</span>
                </a>
            @endcanany
            <a class="nav-link menu-link" href="{{ route('admin.notifications.all-notifications') }}">
                <i class="bx bx-bell "></i> <span data-key="t-authentication">Thông báo</span>
            </a>

        </li>

    </ul>
</div>

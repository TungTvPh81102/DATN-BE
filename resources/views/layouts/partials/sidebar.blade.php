<div class="container-fluid">

    <div id="two-column-menu">
    </div>
    <ul class="navbar-nav" id="navbar-nav">
        <li class="menu-title"><span data-key="t-menu">Bảng điều khiển</span></li>
        <li class="nav-item cusor-pointer">
            <a class="nav-link menu-link">
                <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Thống kê doanh thu</span>
            </a>
        </li>
        <li class="nav-item cusor-pointer">
            <a class="nav-link menu-link">
                <i class=" ri-bar-chart-fill"></i> <span data-key="t-dashboards">Top khoá học bán chạy</span>
            </a>
        </li>
        <li class="nav-item cusor-pointer">
            <a class="nav-link menu-link">
                <i class=" ri-bar-chart-fill"></i> <span data-key="t-dashboards">Thống kê truy cập</span>
            </a>
        </li>

        <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">Quản lý thanh toán</span>
        </li>

        <li class="nav-item">
            <a class="nav-link menu-link" href="#transaction">
                <i class="ri-database-2-line"></i> <span data-key="t-authentication">Khoá học đã bán</span>
            </a>
            <a class="nav-link menu-link" href="#transaction">
                <i class="ri-database-2-line"></i> <span data-key="t-authentication">Giao dịch thanh toán</span>
            </a>
            <a class="nav-link menu-link" href="#transaction">
                <i class="ri-database-2-line"></i> <span data-key="t-authentication">Yêu cầu rút tiền</span>
            </a>
        </li>

        <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">Kiểm duyệt hệ thống</span>
        </li>

        <li class="nav-item">
            <a class="nav-link menu-link" href="#sidebarCheck">
                <i class="ri-database-2-line"></i> <span data-key="t-authentication">Kiểm duyệt giao dịch</span>
            </a>
            <a class="nav-link menu-link" href="#sidebarCheck">
                <i class="las la-book-reader"></i> <span data-key="t-authentication">Kiểm duyệt khoá học</span>
            </a>
            <a class="nav-link menu-link" href="#sidebarCheck">
                <i class="las la-chalkboard-teacher"></i> <span data-key="t-authentication">Kiểm duyệt giảng viên</span>
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
                        <a href="{{ route('admin.users.index') }}" class="nav-link">
                            Danh sách người dùng </a>
                    </li>
                    <li class="nav-item">
                        <a href="apps-chat.html" class="nav-link">
                            Người hướng dẫn </a>
                    </li>
                    @can('super_admin.view')
                        <li class="nav-item">
                            <a href="apps-chat.html" class="nav-link">
                                Danh sách quản trị viên </a>
                        </li>
                    @endcan
                    @can('user.create')
                        <li class="nav-item">
                            <a href="{{ route('admin.users.create') }}" class="nav-link">
                                Thêm mới người dùng
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
            @canany(['permission.create', 'permission.edit', 'permission.read', 'permission.delete'])
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
                </ul>
            </div>

            <a class="nav-link menu-link" href="#sidebarPost" data-bs-toggle="collapse" role="button"
                aria-expanded="false" aria-controls="sidebarPost">
                <i class="lab la-blogger"></i> <span data-key="t-authentication">Quản lý bài viết</span>
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
                </ul>
            </div>

            <a class="nav-link menu-link" href="#sidebarCoupon" data-bs-toggle="collapse" role="button"
                aria-expanded="false" aria-controls="sidebarCoupon">
                <i class=" ri-coupon-line"></i> <span data-key="t-authentication">Quản lý mã giảm giá</span>
            </a>
            <div class="collapse menu-dropdown" id="sidebarCoupon">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="apps-chat.html" class="nav-link" data-key="t-chat">
                            Danh sách mã giảm giá </a>
                    </li>
                    <li class="nav-item">
                        <a href="apps-chat.html" class="nav-link" data-key="t-chat">
                            Thêm mới mã giảm giá </a>
                    </li>
                </ul>
            </div>

            <a class="nav-link menu-link" href="#sidebarComment">
                <i class="las la-comment"></i> <span data-key="t-authentication">Quản lý bình luận</span>
            </a>

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

        </li>

    </ul>
</div>

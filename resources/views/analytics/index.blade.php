@extends('layouts.app')
@push('page-css')
    <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
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
                    <h4 class="mb-sm-0">Thống kê truy cập</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item active dateRangePicker"></li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-12">
                <div class="bg-white p-4 rounded shadow-sm">
                    <h4 class="fs-20 mb-1 text-primary" id="greeting">Xin chào, {{ Auth::user()->name ?? '' }}!</h4>
                    <p class="text-muted mb-0">Chúc bạn một ngày làm việc hiệu quả!</p>
                </div>
            </div>
        </div>
        <div class="row d-flex align-items-stretch">
            <div class="col-xxl-7 d-flex flex-column">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="fw-medium text-muted mb-0">Tổng người dùng duyệt web</p>
                                        <h2 class="mt-4 ff-secondary fw-semibold"><span class="counter-value"
                                                id="analytic-session-user"
                                                data-target="28.05">{{ number_format($analyticsUserSession[0]['totalUsers'] ?? 0) }}
                                                người</span>
                                        </h2>
                                    </div>
                                    <div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-info-subtle rounded-circle fs-2">
                                                <i class="bx bx-user text-info"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div> <!-- end card-->
                    </div> <!-- end col-->
        
                    <div class="col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="fw-medium text-muted mb-0">Số phiên duyệt web</p>
                                        <h2 class="mt-4 ff-secondary fw-semibold"><span class="counter-value"
                                                data-target="97.66"
                                                id="session-web">{{ number_format($analyticsUserSession[0]['sessions'] ?? 0) }}
                                                phiên</span>
                                        </h2>
                                    </div>
                                    <div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-info-subtle rounded-circle fs-2">
                                                <i class="bx bx-pulse text-info"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div> <!-- end card-->
                    </div> <!-- end col-->
                </div> <!-- end row-->
                <div class="flex-grow-1">
                    <div class="card" style="min-height: 330px">
                        <div class="card-body">
                            <div id="line_chart_basic" data-colors='["--vz-primary","--vz-success","--vz-danger"]'
                                class="apex-charts" dir="ltr"></div>
                        </div>
                    </div>
                </div>
            </div> <!-- end col-->
        
            <div class="col-xxl-5 d-flex flex-column">
                <div class="row flex-grow-1">
                    <div class="col-xl-12 d-flex flex-column">
                        <div class="card flex-grow-1">
                            <div class="card-header bg-primary bg-gradient bg-opacity-60 d-flex align-items-center">
                                <h4 class="card-title mb-0 flex-grow-1 text-white">Số lượt xem theo đất nước</h4>
                            </div>
                            <div class="card-body p-0" style="height: 100%;">
                                <div id="world-map" style="width: 100%; height: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                    
        </div> <!-- end row-->        

        <div class="row">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header bg-primary bg-gradient bg-opacity-60 d-flex align-items-center">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Thời gian truy cập</h4>
                    </div><!-- end card header -->
                    <div class="card-body p-0 pb-2">
                        <div>
                            <div id="hourlyTrafficChart" data-colors='["--vz-success", "--vz-light"]' class="apex-charts"
                                dir="ltr"></div>
                        </div>
                    </div><!-- end card body -->
                </div><!-- end card -->
            </div><!-- end col -->

            <div class="col-xl-6">
                <div class="card card-height-100">
                    <div class="card-header bg-primary bg-gradient bg-opacity-60 d-flex align-items-center">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Thống kê thiết bị sử dụng</h4>
                    </div><!-- end card header -->
                    <div class="card-body p-0">
                        <div>
                            <div id="user_device_chart"></div>
                        </div>
                    </div><!-- end cardbody -->
                </div><!-- end card -->
            </div><!-- end col -->
        </div><!-- end row -->

        <div class="row">
            <div class="col-xl-4">
                <div class="card card-height-100">
                    <div class="card-header bg-primary bg-gradient bg-opacity-60 d-flex align-items-center">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Top 4 trình duyệt sử dụng</h4>
                    </div><!-- end card header -->
                    <div class="card-body">
                        <div id="user_device_pie_charts" data-colors='["--vz-primary", "--vz-warning", "--vz-info"]'
                            class="apex-charts" dir="ltr"></div>

                        <div class="table-responsive mt-3">
                            <table class="table table-borderless table-sm table-centered align-middle table-nowrap mb-0">
                                <tbody class="border-0" id="list_browers">
                                </tbody>
                            </table>
                        </div>
                    </div><!-- end card body -->
                </div><!-- end card -->
            </div><!-- end col -->

            <div class="col-xl-4 col-md-6">
                <div class="card card-height-100">
                    <div class="card-header bg-primary bg-gradient bg-opacity-60 d-flex align-items-center">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Phân tích tỉ lệ thoát</h4>
                    </div>

                    <div id="bounceRateChart"></div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="card card-height-100">
                    <div class="card-header bg-primary bg-gradient bg-opacity-60 d-flex align-items-center">
                        <h4 class="card-title mb-0 flex-grow-1 text-white">Top 7 page có lượt truy cập nhiều nhất</h4>
                    </div><!-- end card header -->
                    <div class="card-body">
                        <div class="table-responsive table-card">
                            <table class="table align-middle table-borderless table-centered table-nowrap mb-0">
                                <thead class="text-muted table-light">
                                    <tr>
                                        <th scope="col" class="col-6" style="width: 62;">Tiêu đề trang</th>
                                        <th scope="col" class="col-3">Người dùng</th>
                                        <th scope="col" class="col-3">Lượt xem</th>
                                    </tr>
                                </thead>
                                <tbody id="most-visited-page">
                                    @foreach ($fetchMostVisitedPages as $fetchMostVisitedPage)
                                        <tr>
                                            <td class="col-6">
                                                <a
                                                    href="#">{{ Str::limit($fetchMostVisitedPage['pageTitle'], 25) }}</a>
                                            </td>
                                            <td class="col-3">{{ number_format($fetchMostVisitedPage['activeUsers']) }}
                                                người</td>
                                            <td class="col-3">
                                                {{ number_format($fetchMostVisitedPage['screenPageViews']) }} lượt</td>
                                        </tr>
                                    @endforeach
                                </tbody><!-- end tbody -->
                            </table><!-- end table -->
                        </div><!-- end -->
                    </div><!-- end cardbody -->
                </div><!-- end card -->
            </div><!-- end col -->
        </div><!-- end row -->
    </div>
@endsection

@push('page-scripts')
    <!-- apexcharts -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Vector map-->
    <script src="{{ asset('assets/libs/jsvectormap/js/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jsvectormap/maps/world-merc.js') }}"></script>
    <script src="{{ asset('assets/libs/jsvectormap/maps/world.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}" />
    <script src="{{ asset('assets/js/pages/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/daterangepicker.min.js') }}"></script>

    <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>
    <!-- 1. Highmaps = Highcharts + Maps -->
    <script src="https://code.highcharts.com/maps/highmaps.js"></script>

    <!-- 2. Các module mở rộng khác -->
    <script src="https://code.highcharts.com/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/modules/annotations.js"></script>
    <script src="https://code.highcharts.com/modules/heatmap.js"></script>
    <script src="https://code.highcharts.com/modules/packed-bubble.js"></script>
    <script src="https://code.highcharts.com/modules/sankey.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

    <!-- 3. Module export (chỉ cần 1 lần) -->
    <script src="https://code.highcharts.com/modules/exporting.js"></script>

    <!-- 4. Map data -->
    <script src="https://code.highcharts.com/mapdata/custom/world.js"></script>

    <script>
        var currentHour = new Date().getHours();
        var greetingText = "Xin chào, {{ Auth::user()->name ?? 'Quản trị viên' }}!";

        if (currentHour >= 5 && currentHour < 12) {
            greetingText = "Chào buổi sáng, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        } else if (currentHour >= 12 && currentHour < 18) {
            greetingText = "Chào buổi chiều, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        } else if (currentHour >= 18 && currentHour < 22) {
            greetingText = "Chào buổi tối, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        } else {
            greetingText = "Chúc ngủ ngon, {{ Auth::user()->name ?? 'Quản trị viên' }}!";
        }

        $("#greeting").text(greetingText);

        $(".dateRangePicker").each(function() {
            let button = $(this);

            function updateDateRangeText(start, end) {
                button.html("📅 " + start.format("DD/MM/YYYY") + " - " + end.format("DD/MM/YYYY"));

                button.attr("data-start", start.format("YYYY-MM-DD"));
                button.attr("data-end", end.format("YYYY-MM-DD"));
            }

            let defaultStart = moment().startOf("month");
            let defaultEnd = moment();

            button.daterangepicker({
                    autoUpdateInput: false,
                    showDropdowns: true,
                    linkedCalendars: false,
                    minDate: moment("2000-01-01"),
                    maxDate: moment(),
                    startDate: defaultStart,
                    endDate: defaultEnd,
                    ranges: {
                        "Hôm nay": [moment(), moment()],
                        "Hôm qua": [moment().subtract(1, "days"), moment().subtract(1, "days")],
                        "7 ngày trước": [moment().subtract(6, "days"), moment()],
                        "Tháng này": [moment().startOf("month"), moment().endOf("month")],
                        "Tháng trước": [
                            moment().subtract(1, "month").startOf("month"),
                            moment().subtract(1, "month").endOf("month"),
                        ],
                        "1 năm trước": [
                            moment().subtract(1, "year").startOf("year"),
                            moment().subtract(1, "year").endOf("year")
                        ],

                    },
                    locale: {
                        format: "DD/MM/YYYY",
                        applyLabel: "Áp dụng",
                        cancelLabel: "Hủy",
                        customRangeLabel: "Tùy chỉnh",
                    },
                },
                function(start, end) {
                    updateDateRangeText(start, end);
                    $.ajax({
                        url: "{{ route('admin.analytics.index') }}",
                        type: 'GET',
                        data: {
                            startDate: start.format("YYYY-MM-DD"),
                            endDate: end.format("YYYY-MM-DD")
                        },
                        beforeSend: function() {
                            $('#list_browers').empty();
                        },
                        success: function(response) {
                            console.log(response);
                            let tbody = $("#most-visited-page");
                            tbody.empty();
                            if (response.fetchMostVisitedPages.length > 0) {
                                response.fetchMostVisitedPages.forEach(function(item) {
                                    let row = `<tr>
                            <td class="col-6">
                                <a href="#">${item.pageTitle.length > 25 ? item.pageTitle.substring(0, 25) + "..." : item.pageTitle}</a>
                            </td>
                            <td class="col-3">${new Intl.NumberFormat().format(item.activeUsers)} người</td>
                            <td class="col-3">${new Intl.NumberFormat().format(item.screenPageViews)} lượt</td>
                        </tr>`;
                                    tbody.append(row);
                                });
                            } else {
                                tbody.html(`<div style="text-align: center; padding: 20px; color: #999;">
                            <p><i class="fas fa-exclamation-circle"></i> Không có dữ liệu</p>
                        </div>`);
                            }

                            let analytic_session_user = 0;
                            let analytic_session = 0;
                            if (response.analyticsUserSession.length > 0) {
                                analytic_session_user = response.analyticsUserSession[0].sessions;
                                analytic_session = response.analyticsUserSession[0].totalUsers;
                            }

                            $("#session-web").text(new Intl.NumberFormat().format(
                                analytic_session_user) + " phiên");

                            $('#analytic-session-user').text(new Intl.NumberFormat().format(
                                analytic_session) + " người");

                            updateDeviceUsersChart(response.userDevices);
                            updateBounceRateChart(response.analyticsEngagement);
                            updateBrowerUsersChart(response.topBrowsers);
                            updateWorldMap(response.analyticsCountriesSession);
                            line_chart_basic(response.analyticsData);
                        }
                    });
                }
            );

            updateDateRangeText(defaultStart, defaultEnd);
        });

        function getFirstDayOfWeek(year, weekNumber) {
            let firstDayOfYear = new Date(year, 0, 1);
            let dayOffset = firstDayOfYear.getDay() === 0 ? 1 : (8 - firstDayOfYear.getDay());
            let firstWeekDate = new Date(year, 0, 1 + (weekNumber - 1) * 7 + dayOffset);
            return firstWeekDate.toISOString().split("T")[0]; // YYYY-MM-DD
        }

        function extractSeries(data, groupBy, metric) {
            let seriesData = {};

            data.forEach(item => {
                let formattedDate = "";

                if (groupBy === "yearWeek" && item.yearWeek) {
                    const year = parseInt(item.yearWeek.substring(0, 4), 10);
                    const weekNumber = parseInt(item.yearWeek.substring(4), 10);
                    formattedDate = getFirstDayOfWeek(year, weekNumber);
                } else if (groupBy === "yearMonth" && item.yearMonth) {
                    const year = parseInt(item.yearMonth.substring(0, 4), 10);
                    const month = parseInt(item.yearMonth.substring(4), 10);
                    formattedDate = `${year}-${String(month).padStart(2, "0")}-01`;
                } else if (groupBy === "date" && item.date) {
                    formattedDate = new Date(item.date).toISOString().split("T")[0];
                }

                if (formattedDate) {
                    seriesData[formattedDate] = (seriesData[formattedDate] || 0) + (parseInt(item[metric]) || 0);
                }
            });

            return Object.keys(seriesData).map(date => ({
                date: date,
                value: seriesData[date]
            }));
        }

        let chart;

        function line_chart_basic(data = []) {
            let chartContainer = document.querySelector("#line_chart_basic");

            if (typeof chart !== "undefined" && chart) {
                chart.destroy();
                chart = undefined;
            }

            // Xóa nội dung container
            chartContainer.innerHTML = "";

            if (!Array.isArray(data) || data.length === 0) {
                chartContainer.innerHTML = `<div style="text-align: center; padding: 20px; color: #999;">
                    <p><i class="fas fa-exclamation-circle"></i> Không có dữ liệu</p>
                </div>`;
                return;
            }

            let type = 'date';
            if (data[0].yearWeek) {
                type = 'yearWeek';
            } else if (data[0].yearMonth) {
                type = 'yearMonth';
            }

            let categories = extractSeries(data, type, "date").map(item => item.date);
            let newUsersData = extractSeries(data, type, "newUsers").map((item, index) => [categories[index], item.value]);
            let totalUsersData = extractSeries(data, type, "totalUsers").map((item, index) => [categories[index], item
                .value
            ]);
            let sessionsData = extractSeries(data, type, "sessions").map((item, index) => [categories[index], item.value]);

            // Highcharts configuration
            chart = Highcharts.chart('line_chart_basic', {
                credits: {
                    enabled: false
                },
                title: {
                    text: null
                },
                xAxis: {
                    type: 'category',
                    categories: categories
                },
                yAxis: {
                    title: {
                        text: 'Lượt'
                    }
                },
                legend: {
                    enabled: true
                },
                plotOptions: {
                    series: {
                        marker: {
                            enabled: true,
                            radius: 3
                        }
                    }
                },
                series: [{
                    name: 'Người dùng mới',
                    data: newUsersData.map(item => item[1])
                }, {
                    name: 'Tổng người dùng',
                    data: totalUsersData.map(item => item[1])
                }, {
                    name: 'Số phiên duyệt web',
                    data: sessionsData.map(item => item[1])
                }]
            });
        }

        let chartBrowerUsers;

        function updateBrowerUsersChart(data = []) {
            let chartContainer = document.querySelector("#user_device_pie_charts");

            if (typeof chartBrowerUsers !== "undefined" && chartBrowerUsers) {
                chartBrowerUsers.destroy();
                chartBrowerUsers = undefined;
            }

            chartContainer.innerHTML = "";

            if (!data || data.length == 0) {
                chartContainer.innerHTML = `<div style="text-align: center; padding: 20px; color: #999;">
                    <p><i class="fas fa-exclamation-circle"></i> Không có dữ liệu</p>
                </div>`;
                return;
            }

            let browsers = data.map(item => item.browser);
            let screenPageViews = data.map(item => item.screenPageViews);
            let colors = ["#4B0082", "#FFD700", "#007bff", "#6c757d", "#343a40", "#ff00ff", "#00ff00", "#00ffff",
                "#ff5733"
            ];

            // Tạo series data cho Highcharts
            let seriesData = browsers.map((browser, index) => ({
                name: browser,
                y: screenPageViews[index],
                color: colors[index % colors.length]
            }));

            // Highcharts configuration
            chartBrowerUsers = Highcharts.chart('user_device_pie_charts', {
                credits: {
                    enabled: false
                },
                chart: {
                    type: 'pie',
                    height: 219
                },
                title: {
                    text: null
                },
                plotOptions: {
                    pie: {
                        innerSize: '76%',
                        depth: 45,
                        dataLabels: {
                            enabled: false
                        }
                    }
                },
                tooltip: {
                    pointFormat: '{point.name}: <b>{point.y}</b> lượt'
                },
                series: [{
                    name: 'Lượt xem',
                    data: seriesData
                }]
            });

            $('#list_browers').empty();
            if (data && data.length > 0) {
                $.each(data, function(index, item) {
                    let iconColor = colors[index % colors.length];
                    let formattedValue = formatNumber(item.screenPageViews);

                    let row = `
                    <tr>
                        <td>
                            <h4 class="text-truncate fs-14 fs-medium mb-0">
                                <i class="ri-stop-fill align-middle fs-18" style="color:${iconColor};"></i>
                                ${item.browser}
                            </h4>
                        </td>
                        <td class="text-end">
                            <p class="text-muted mb-0"><i data-feather="users" class="me-2 icon-sm"></i>${formattedValue}</p>
                        </td>
                    </tr>`;

                    $('#list_browers').append(row);
                });

                if (typeof feather !== "undefined") {
                    feather.replace();
                }
            }
        }

        let chartBounceRate;

        function updateBounceRateChart(data = []) {
            let chartContainer = document.querySelector("#bounceRateChart");

            if (typeof chartBounceRate !== "undefined" && chartBounceRate) {
                chartBounceRate.destroy();
                chartBounceRate = undefined;
            }

            chartContainer.innerHTML = "";

            if (!Array.isArray(data) || data.length === 0) {
                chartContainer.innerHTML = `<div style="text-align: center; padding: 20px; color: #999;">
                    <p><i class="fas fa-exclamation-circle"></i> Không có dữ liệu</p>
                </div>`;
                return;
            }

            let cleanedData = data.map(item => {
                let bounceRateValue = parseFloat(item.bounceRate) * 100 || 0;
                return {
                    sessionSource: item.sessionSource ? item.sessionSource.replace(/[()]/g, '') : "Unknown",
                    bounceRate: bounceRateValue.toFixed(2)
                };
            });

            let categories = cleanedData.map(item => item.sessionSource);
            let bounceRates = cleanedData.map(item => parseFloat(item.bounceRate));
            let colors = ["#ff4d4f", "#faad14", "#1890ff", "#52c41a", "#9254de"];

            // Highcharts configuration
            chartBounceRate = Highcharts.chart('bounceRateChart', {
                credits: {
                    enabled: false
                },
                chart: {
                    type: 'bar',
                    height: 300
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: categories
                },
                yAxis: {
                    title: {
                        text: 'Tỷ lệ thoát (%)'
                    }
                },
                legend: {
                    enabled: false
                },
                plotOptions: {
                    bar: {
                        colorByPoint: true,
                        colors: colors
                    }
                },
                series: [{
                    name: 'Tỷ lệ thoát',
                    data: bounceRates
                }]
            });
        }

        let worldMap;

        const countryNameToCode = @json(config('analytics.code_country'));

        function updateWorldMap(data = []) {
            let chartContainer = document.querySelector("#world-map");

            if (worldMap) {
                // Nếu đang sử dụng jsVectorMap, ta cần giữ nguyên phần này
                // hoặc chuyển sang sử dụng Highcharts maps thay thế
                worldMap.destroy();
                worldMap = null;
            }

            chartContainer.innerHTML = "";

            if (!Array.isArray(data) || data.length == 0) {
                chartContainer.innerHTML = `<div style="text-align: center; padding: 20px; color: #999;">
                    <p><i class="fas fa-exclamation-circle"></i> Không có dữ liệu</p>
                </div>`;
                return;
            }

            // Chuẩn bị dữ liệu cho Highcharts
            let mapData = [];
            data.forEach(item => {
                let countryCode = countryNameToCode[item.country];
                if (countryCode) {
                    mapData.push({
                        'hc-key': countryCode.toLowerCase(), // Highcharts sử dụng mã nước viết thường
                        value: parseInt(item.screenPageViews),
                        name: item.country
                    });
                }
            });

            // Sử dụng Highcharts Map
            // Lưu ý: Cần tải thêm module highcharts-maps và bản đồ thế giới
            worldMap = Highcharts.mapChart('world-map', {
                credits: {
                    enabled: false
                },
                chart: {
                    map: 'custom/world', // Yêu cầu tải bản đồ thế giới
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                mapNavigation: {
                    enabled: true,
                    buttonOptions: {
                        verticalAlign: 'bottom'
                    }
                },
                colorAxis: {
                    min: 0,
                    minColor: '#E6E7E8',
                    maxColor: '#005645'
                },
                series: [{
                    data: mapData,
                    name: 'Lượt xem',
                    states: {
                        hover: {
                            color: '#007bff'
                        }
                    },
                    dataLabels: {
                        enabled: false,
                        format: '{point.name}'
                    },
                    tooltip: {
                        pointFormat: '{point.name}: {point.value} lượt xem'
                    }
                }]
            });
        }

        function formatNumber(value) {
            return value.toLocaleString("vi-VN").replace(/\./g, ".") + ' lượt';
        }

        let chartDeviceUsers;

        function updateDeviceUsersChart(data = []) {
            let chartContainer = document.querySelector("#user_device_chart");

            if (typeof chartDeviceUsers !== "undefined" && chartDeviceUsers) {
                chartDeviceUsers.destroy();
                chartDeviceUsers = undefined;
            }

            chartContainer.innerHTML = "";

            if (!data || data.length === 0) {
                chartContainer.innerHTML = `<div style="text-align: center; padding: 20px; color: #999;">
                    <p><i class="fas fa-exclamation-circle"></i> Không có dữ liệu</p>
                </div>`;
                return;
            }

            let deviceNames = data.map(item => item.deviceCategory);
            let sessionCounts = data.map(item => item.sessions);
            let colors = [
                "#FF5733", "#33FF57", "#3357FF", "#FF33A8", "#FFD700", "#8A2BE2", "#00CED1", "#FF4500", "#2E8B57",
                "#C71585"
            ];

            // Tạo dữ liệu cho Highcharts
            let seriesData = deviceNames.map((device, index) => ({
                name: device,
                y: sessionCounts[index],
                color: colors[index % colors.length]
            }));

            // Highcharts configuration - chuyển radialBar sang column chart hoặc pie chart
            chartDeviceUsers = Highcharts.chart('user_device_chart', {
                credits: {
                    enabled: false
                },
                chart: {
                    type: 'column',
                    height: 380
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: deviceNames
                },
                yAxis: {
                    title: {
                        text: 'Số phiên'
                    }
                },
                legend: {
                    enabled: false
                },
                tooltip: {
                    pointFormat: '{point.name}: <b>{point.y}</b> phiên'
                },
                plotOptions: {
                    column: {
                        colorByPoint: true,
                        colors: colors
                    }
                },
                series: [{
                    name: 'Thiết bị',
                    data: seriesData
                }]
            });
        }

        let chartHourTraffic;

        function updateHourlyTrafficChart(data = []) {
            let chartContainer = document.querySelector("#hourlyTrafficChart");

            if (typeof chartHourTraffic !== "undefined" && chartHourTraffic) {
                chartHourTraffic.destroy();
                chartHourTraffic = undefined;
            }

            chartContainer.innerHTML = "";

            if (!data || data.length === 0) {
                chartContainer.innerHTML = `<div style="text-align: center; padding: 20px; color: #999;">
                    <p><i class="fas fa-exclamation-circle"></i> Không có dữ liệu</p>
                </div>`;
                return;
            }

            let days = ["Chủ Nhật", "Thứ Hai", "Thứ Ba", "Thứ Tư", "Thứ Năm", "Thứ Sáu", "Thứ Bảy"];
            let uniqueDays = [...new Set(data.map(item => item.dayOfWeek))];

            // Chuẩn bị dữ liệu cho Highcharts heat map
            let transformedData = [];

            data.forEach(item => {
                transformedData.push([
                    parseInt(item.hour), // x: hour
                    parseInt(item.dayOfWeek), // y: day of week
                    parseInt(item.sessions) // z: value (sessions)
                ]);
            });

            // Highcharts configuration
            chartHourTraffic = Highcharts.chart('hourlyTrafficChart', {
                credits: {
                    enabled: false
                },
                chart: {
                    type: 'heatmap',
                    height: 350
                },
                title: {
                    text: null
                },
                xAxis: {
                    title: {
                        text: 'Giờ trong ngày'
                    },
                    categories: Array.from({
                        length: 24
                    }, (_, i) => `${i}:00`)
                },
                yAxis: {
                    title: {
                        text: null
                    },
                    categories: days,
                    reversed: true
                },
                colorAxis: {
                    min: 0,
                    minColor: '#FFFFFF',
                    maxColor: Highcharts.getOptions().colors[0]
                },
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
                        return '<b>' + days[this.point.y] + ', ' + this.series.xAxis.categories[this.point.x] +
                            '</b><br/>' +
                            'Số phiên: <b>' + this.point.value + '</b>';
                    }
                },
                series: [{
                    name: 'Lưu lượng theo giờ',
                    data: transformedData,
                    dataLabels: {
                        enabled: true,
                        color: '#000000'
                    }
                }]
            });
        }

        function formatNumber(value) {
            return new Intl.NumberFormat().format(value);
        }

        let sampleData = [{
                deviceCategory: "Desktop",
                sessions: 500
            },
            {
                deviceCategory: "Mobile",
                sessions: 300
            },
            {
                deviceCategory: "Tablet",
                sessions: 200
            },
            {
                deviceCategory: "Smart TV",
                sessions: 150
            },
            {
                deviceCategory: "Game Console",
                sessions: 90
            },
            {
                deviceCategory: "Wearable",
                sessions: 70
            },
            {
                deviceCategory: "Other",
                sessions: 30
            }
        ];

        updateHourlyTrafficChart(@json($analyticsHourlyTraffic));
        updateDeviceUsersChart(sampleData);
        // updateDeviceUsersChart(@json($userDevices));
        updateBounceRateChart(@json($analyticsEngagement));
        updateBrowerUsersChart(@json($topBrowsers));
        line_chart_basic(@json($analyticsData));
        updateWorldMap(@json($analyticsCountriesSession));
    </script>
@endpush
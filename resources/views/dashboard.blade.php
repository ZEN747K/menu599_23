@extends('admin.layout')
@section('style')
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    svg {
        width: 100%;
    }
</style>
@endsection
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('user')->is_rider != 1)
        <div class="row">
            <div class="col-lg-12 col-md-12 order-1">
                <div class="row">
                    <div class="col-lg-3 col-md-12 col-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <img src="{{asset('assets/img/icons/unicons/chart.png')}}" alt="chart success" class="rounded" />
                                    </div>
                                </div>
                                <span class="fw-semibold d-block mb-1">ยอดขายวันนี้</span>
                                <h3 class="card-title mb-2">{{$orderday->total}} บาท</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-12 col-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <img src="{{asset('assets/img/icons/unicons/chart.png')}}" alt="Credit Card" class="rounded" />
                                    </div>
                                </div>
                                <span class="fw-semibold d-block mb-1">ยอดขายเดือนนี้</span>
                                <h3 class="card-title mb-2">{{$ordermouth->total}} บาท</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-12 col-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <img src="{{asset('assets/img/icons/unicons/chart.png')}}" alt="Credit Card" class="rounded" />
                                    </div>
                                </div>
                                <span class="fw-semibold d-block mb-1">ยอดขายปีนี้</span>
                                <h3 class="card-title mb-2">{{$orderyear->total}} บาท</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-12 col-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <img src="{{asset('assets/img/icons/unicons/chart.png')}}" alt="chart success" class="rounded" />
                                    </div>
                                </div>
                                <span class="fw-semibold d-block mb-1">ออเดอร์ทั้งหมด</span>
                                <h3 class="card-title mb-2">{{$ordertotal}} ออเดอร์</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 col-md-12 order-2">
                <div class="row">
                    <div class="col-lg-4 col-md-12 col-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <img src="{{asset('assets/img/icons/unicons/chart-success.png')}}" alt="chart success" class="rounded" />
                                    </div>
                                </div>
                                <span class="fw-semibold d-block mb-1">ยอดเงินสดวันนี้</span>
                                <h3 class="card-title mb-2">{{$moneyDay->total}} บาท</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 col-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <img src="{{asset('assets/img/icons/unicons/chart-success.png')}}" alt="Credit Card" class="rounded" />
                                    </div>
                                </div>
                                <span class="fw-semibold d-block mb-1">ยอดเงินโอนวันนี้</span>
                                <h3 class="card-title mb-2">{{$transferDay->total}} บาท</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 col-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <img src="{{asset('assets/img/icons/unicons/chart-success.png')}}" alt="Credit Card" class="rounded" />
                                    </div>
                                </div>
                                <span class="fw-semibold d-block mb-1">จำนวนจัดส่งวันนี้</span>
                                <h3 class="card-title mb-2">{{$delivery}} ออเดอร์</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 order-3 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6>สถิติเมนูขายดี</h6>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div style="width:100%;">
                            <canvas id="myChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 order-3 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6>ยอดขายแต่ละเดือน</h6>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div style="width:100%;">
                            <canvas id="myChart2"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 col-md-12 order-3 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6>ยอดรายการสั่งของลูกค้ารายบุคคล</h6>
                        <hr>
                    </div>
                    <div class="card-body">
                        <table id="myTable" class="display table-responsive">
                            <thead>
                                <tr>
                                    <th class="text-left">ชื่อลูกค้า</th>
                                    <th class="text-end">ยอดรวม</th>
                                    <th class="text-end">ยอดเงินสด</th>
                                    <th class="text-end">ยอดเงินโอน</th>
                                    <th class="text-end">ยอดจำนวนสั่งอาหาร</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if(session('user')->is_rider == 1)
        <div class="row">
            <div class="col-lg-12 col-md-12 order-1">
                <div class="row">
                    <div class="col-lg-6 col-md-12 col-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <img src="{{asset('assets/img/icons/unicons/chart-success.png')}}" alt="chart success" class="rounded" />
                                    </div>
                                </div>
                                <span class="fw-semibold d-block mb-1">จำนวนจัดส่งวันนี้</span>
                                <h3 class="card-title mb-2">{{$delivery_day}} ออเดอร์</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 col-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                    <div class="avatar flex-shrink-0">
                                        <img src="{{asset('assets/img/icons/unicons/chart-success.png')}}" alt="Credit Card" class="rounded" />
                                    </div>
                                </div>
                                <span class="fw-semibold d-block mb-1">จำนวนจัดส่งเดือนนี้</span>
                                <h3 class="card-title mb-2">{{$delivery_mouth}} ออเดอร์</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script>
    function getRandomColor(opacity = 1) {
        const r = Math.floor(Math.random() * 255);
        const g = Math.floor(Math.random() * 255);
        const b = Math.floor(Math.random() * 255);
        return `rgba(${r}, ${g}, ${b}, ${opacity})`;
    }
    var ctx = document.getElementById('myChart').getContext('2d');

    var labels = <?= json_encode($item_menu) ?>;
    var backgroundColors = labels.map(() => getRandomColor(0.5));
    var borderColors = labels.map(() => getRandomColor(1));
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: '# จำนวนออเดอร์ (จำนวน)',
                data: <?= json_encode($item_order) ?>,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    var ctx = document.getElementById('myChart2').getContext('2d');

    var labels = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    var backgroundColors = labels.map(() => getRandomColor(0.5));
    var borderColors = labels.map(() => getRandomColor(1));
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: '# ยอดขาย (บาท)',
                data: <?= json_encode($item_mouth) ?>,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
<script>
    var language = '{{asset("assets/js/datatable-language.js")}}';
    $("#myTable").DataTable({
        language: {
            url: language,
        },
        processing: true,
        scrollX: true,
        order: [
            [4, 'desc']
        ],
        ajax: {
            url: "{{route('ListOrderPeople')}}",
            type: "post",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
        },

        columns: [{
                data: 'name',
                class: 'text-left',
                width: '20%'
            },
            {
                data: 'total',
                class: 'text-end',
                width: '20%'
            },
            {
                data: 'moneyDay',
                class: 'text-end',
                width: '20%'
            },
            {
                data: 'transferDay',
                class: 'text-end',
                width: '20%'
            },
            {
                data: 'delivery',
                class: 'text-end',
                width: '20%'
            },
        ]
    });
</script>
@endsection
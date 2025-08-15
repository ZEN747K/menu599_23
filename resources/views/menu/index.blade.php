@extends('admin.layout')
@section('style')
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
<style>
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    .toggle-btn {
        transition: all 0.2s ease;
    }
    .toggle-btn:hover {
        transform: scale(1.05);
    }
    .menu-controls {
        display: flex;
        gap: 0.25rem;
        justify-content: center;
    }
    .availability-text {
        font-size: 0.85rem;
        line-height: 1.2;
    }
    .current-time-display {
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .pulse {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
</style>
@endsection
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- แสดงเวลาปัจจุบัน -->
        <div class="current-time-display">
            <i class="bx bx-time-five me-2"></i>
            เวลาปัจจุบัน: <span id="currentTime" class="fw-bold"></span>
            <span class="badge bg-light text-dark ms-2 pulse" id="autoRefreshIndicator">
                <i class="bx bx-refresh"></i> อัปเดตอัตโนมัติ
            </span>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">จัดการเมนูอาหาร</h5>
                            <small class="text-muted">สถานะจะเปลี่ยนตามเวลาขายที่กำหนด</small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary me-2" id="toggleAutoRefresh">
                                <i class="bx bx-pause"></i> หยุดอัปเดต
                            </button>
                            <button class="btn btn-sm btn-outline-info me-2" id="refreshTable">
                                <i class="bx bx-refresh"></i> รีเฟรช
                            </button>
                            <a href="{{route('MenuCreate')}}" class="btn btn-sm btn-success">
                                <i class="bx bxs-plus-circle me-1"></i> เพิ่มเมนู
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="myTable" class="display table-responsive" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-left">ชื่อเมนู</th>
                                    <th class="text-center">หมวดหมู่</th>
                                    <th class="text-center">ราคา</th>
                                    <th class="text-center">สถานะ</th>
                                    <th class="text-center">จำนวนคงเหลือ</th>
                                    <th class="text-center">สถานะการขาย</th>
                                    <th class="text-center">เปิดปิดเมนู</th>
                                    <th class="text-center">ตัวเลือก</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">แก้ไขจำนวนสต็อก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">จำนวนคงเหลือ</label>
                    <input type="number" class="form-control" id="stockQuantity" min="0" placeholder="ไม่จำกัด">
                    <small class="text-muted">เว้นว่าง = ไม่จำกัดจำนวน</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="saveStock">บันทึก</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>

<script>
    var language = '{{asset("assets/js/datatable-language.js")}}';
    var table;
    var autoRefreshInterval;
    var isAutoRefreshEnabled = true;
    
    $(document).ready(function() {
        // อัปเดตเวลาปัจจุบัน
        updateCurrentTime();
        setInterval(updateCurrentTime, 1000);

        table = $("#myTable").DataTable({
            language: {
                url: language,
            },
            processing: true,
            scrollX: true,
            order: [[0, 'asc']], 
            ajax: {
                url: "{{route('menulistData')}}",
                type: "post",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            },
            columns: [
                {
                    data: 'name',
                    class: 'text-left',
                    width: '20%'
                },
                {
                    data: 'category',
                    class: 'text-center',
                    width: '12%'
                },
                {
                    data: 'price',
                    class: 'text-center',
                    width: '10%'
                },
                {
                    data: 'status',
                    class: 'text-center',
                    width: '15%',
                    orderable: false
                },
                {
                    data: 'stock_quantity',
                    class: 'text-center',
                    width: '10%'
                },
                {
                    data: 'availability',
                    class: 'text-center availability-text',
                    width: '13%'
                },
                {
                    data: 'controls',
                    class: 'text-center',
                    width: '10%',
                    orderable: false
                },
                {
                    data: 'option',
                    class: 'text-center',
                    width: '5%',
                    orderable: false
                },
                {
                    data: 'action',
                    class: 'text-center',
                    width: '10%',
                    orderable: false
                }
            ]
        });

        // เริ่มการอัปเดตอัตโนมัติทุก 30 วินาที
        startAutoRefresh();

        // ปุ่ม Refresh
        $('#refreshTable').click(function() {
            table.ajax.reload(null, false);
            showNotification('รีเฟรชเรียบร้อย', 'success');
        });

        // ปุ่มเปิด/ปิดการอัปเดตอัตโนมัติ
        $('#toggleAutoRefresh').click(function() {
            if (isAutoRefreshEnabled) {
                stopAutoRefresh();
                $(this).html('<i class="bx bx-play"></i> เริ่มอัปเดต');
                $(this).removeClass('btn-outline-secondary').addClass('btn-outline-success');
            } else {
                startAutoRefresh();
                $(this).html('<i class="bx bx-pause"></i> หยุดอัปเดต');
                $(this).removeClass('btn-outline-success').addClass('btn-outline-secondary');
            }
        });
    });

    function updateCurrentTime() {
        const now = new Date();
        const options = {
            weekday: 'long',
            year: 'numeric',
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            timeZone: 'Asia/Bangkok'
        };
        
        $('#currentTime').text(now.toLocaleDateString('th-TH', options));
    }

    function startAutoRefresh() {
        isAutoRefreshEnabled = true;
        $('#autoRefreshIndicator').show();
        
        autoRefreshInterval = setInterval(function() {
            table.ajax.reload(null, false);
        }, 30000); // 30 วินาที
    }

    function stopAutoRefresh() {
        isAutoRefreshEnabled = false;
        $('#autoRefreshIndicator').hide();
        
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
    }

    function showNotification(message, type = 'info') {
        Swal.fire({
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 1500,
            position: 'top-end',
            toast: true
        });
    }

    // จัดการปุ่มสลับสถานะ
    $(document).on('click', '.toggle-status', function(e) {
        e.preventDefault();
        var button = $(this);
        var id = button.data('id');
        var field = button.data('field');
        var value = button.data('value');
        
        var fieldName = field === 'is_active' ? 'การขาย' : 'สต็อกสินค้า';
        var statusName = '';
        
        if (field === 'is_active') {
            statusName = value == 1 ? 'เปิดขาย' : 'ปิดขาย';
        } else {
            statusName = value == 1 ? 'สินค้าหมด' : 'มีสินค้า';
        }
        
        Swal.fire({
            title: 'ยืนยันการเปลี่ยนสถานะ',
            text: 'ต้องการเปลี่ยนสถานะ' + fieldName + 'เป็น "' + statusName + '" ใช่หรือไม่?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                button.prop('disabled', true);
                
                $.ajax({
                    url: "{{route('toggleMenuStatus')}}",
                    type: "post",
                    data: {
                        id: id,
                        field: field,
                        value: value
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status) {
                            table.ajax.reload(null, false);
                            showNotification(response.message, 'success');
                        } else {
                            showNotification(response.message, 'error');
                        }
                    },
                    error: function() {
                        showNotification('เกิดข้อผิดพลาดในการอัปเดตสถานะ', 'error');
                    },
                    complete: function() {
                        button.prop('disabled', false);
                    }
                });
            }
        });
    });

    // จัดการปุ่มแก้ไขสต็อก
    var currentMenuId = null;
    $(document).on('click', '.edit-stock', function(e) {
        e.preventDefault();
        currentMenuId = $(this).data('id');
        var currentStock = $(this).data('stock') || '';
        
        $('#stockQuantity').val(currentStock);
        $('#stockModal').modal('show');
    });

    // บันทึกการแก้ไขสต็อก
    $('#saveStock').click(function() {
        var quantity = $('#stockQuantity').val();
        
        $.ajax({
            url: "{{route('updateMenuStock')}}",
            type: "post",
            data: {
                id: currentMenuId,
                stock_quantity: quantity
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status) {
                    $('#stockModal').modal('hide');
                    table.ajax.reload(null, false);
                    showNotification(response.message, 'success');
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('เกิดข้อผิดพลาดในการอัปเดตสต็อก', 'error');
            }
        });
    });

    // จัดการปุ่มลบเมนู
    $(document).on('click', '.deleteMenu', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'ยืนยันการลบ',
            text: 'ต้องการลบเมนูนี้ใช่หรือไม่? (จะย้ายไปถังขยะ)',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{route('menuDelete')}}",
                    type: "post",
                    data: {
                        id: id
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status) {
                            table.ajax.reload(null, false);
                            showNotification(response.message, 'success');
                        } else {
                            showNotification(response.message, 'error');
                        }
                    },
                    error: function() {
                        showNotification('เกิดข้อผิดพลาดในการลบเมนู', 'error');
                    }
                });
            }
        });
    });

    $(document).ready(function() {
        $('[title]').tooltip();
    });
</script>
@endsection
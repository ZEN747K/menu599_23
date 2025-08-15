@extends('admin.layout')
@section('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        svg {
            width: 100%;
        }

        /* CSS สำหรับ Modal Preview */
        #preview-frame {
            width: 100%;
            height: 500px;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            background-color: #f8f9fa;
        }

        .modal-lg {
            max-width: 900px;
        }

        #modal-preview .modal-body {
            padding: 0;
        }

        #modal-preview .modal-header {
            border-bottom: 1px solid #dee2e6;
        }

        #modal-preview .modal-footer {
            border-top: 1px solid #dee2e6;
        }

        /* CSS สำหรับแสดงสลิป */
        #slip-image {
            transition: transform 0.3s ease;
            cursor: zoom-in;
            max-width: 100%;
            height: auto;
        }

        #slip-image:hover {
            transform: scale(1.02);
        }

        .badge {
            font-size: 0.875em;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .slip-container {
            max-height: 600px;
            overflow: auto;
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
    </style>
@endsection
@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-lg-12 col-md-12 order-1 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h6>รายการออเดอร์ทั้งหมด</h6>
                            <hr>
                        </div>
                        <div class="card-body">
                            <table id="myTable" class="display table-responsive">
                                <thead>
                                    <tr>
                                        <th class="text-center">สั่งหน้าร้าน</th>
                                        <th class="text-center">เลขโต้ะ</th>
                                        <th class="text-center">ยอดราคา</th>
                                        <th class="text-left">หมายเหตุ</th>
                                        <th class="text-left">วันที่สั่ง</th>
                                        <th class="text-center">สถานะ</th>
                                        <th class="text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 col-md-12 order-2">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h6>รายการชำระเงินแล้ว</h6>
                                </div>
                                <div class="col-6 text-end">
                                    <a href="{{route('exportExcel')}}" target="_blank" class="btn btn-sm btn-success">Export
                                        Excel</a>
                                </div>
                            </div>
                            <hr>
                        </div>
                        <div class="card-body">
                            <table id="myTable2" class="display table-responsive">
                                <thead>
                                    <tr>
                                        <th class="text-center">เลขที่ใบเสร็จ</th>
                                        <th class="text-center">รูปแบบการชำระ</th>
                                        <th class="text-center">โต้ะ</th>
                                        <th class="text-center">ยอดรวม</th>
                                        <th class="text-center">วันที่ชำระ</th>
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

    <!-- Modal รายละเอียดออเดอร์  -->
    <div class="modal fade" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" id="modal-detail">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">รายละเอียดออเดอร์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="body-html">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal รายละเอียดออเดอร์ที่ชำระแล้ว -->
    <div class="modal fade" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" id="modal-detail-pay">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">รายละเอียดออเดอร์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="body-html-pay">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับดูรูปสลิป -->
    <div class="modal fade" tabindex="-1" aria-labelledby="slipModalLabel" aria-hidden="true" id="modal-slip">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bx bx-image me-2"></i>สลิปการโอนเงิน
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="slip-container">
                        <img id="slip-image" src="" alt="สลิปการโอนเงิน"
                            style="border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <a id="download-slip" href="" download class="btn btn-primary">
                        <i class="bx bx-download me-1"></i>ดาวน์โหลด
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับปฏิเสธการชำระเงิน -->
    <div class="modal fade" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true" id="modal-reject">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bx bx-x-circle me-2 text-danger"></i>ปฏิเสธการชำระเงิน
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bx bx-info-circle me-2"></i>
                        การปฏิเสธจะทำให้ออเดอร์กลับไปสถานะ "กำลังทำอาหาร" และลบรูปสลิปออก
                    </div>
                    <div class="mb-3">
                        <label for="reject-reason" class="form-label">เหตุผลในการปฏิเสธ <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" id="reject-reason" rows="3"
                            placeholder="กรุณาระบุเหตุผลในการปฏิเสธ เช่น สลิปไม่ชัดเจน, จำนวนเงินไม่ถูกต้อง"
                            required></textarea>
                    </div>
                    <input type="hidden" id="reject-order-id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-danger" id="confirm-reject">
                        <i class="bx bx-x me-1"></i>ยืนยันการปฏิเสธ
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal ชำระเงิน -->
    <div class="modal fade" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" id="modal-pay">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ชำระเงิน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body d-flex justify-content-center">
                            <div class="row">
                                <div class="col-12 text-center">
                                    <h5>ยอดชำระ</h5>
                                    <h1 class="text-success" id="totalPay"></h1>
                                </div>
                                <div class="col-12 d-flex justify-content-center mb-3" id="qr_code">
                                </div>
                            </div>
                            <input type="hidden" id="table_id">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-primary confirm_pay" data-id="0">ชำระเงินสด</button>
                    <button type="button" class="btn btn-sm btn-primary confirm_pay" data-id="1">ชำระโอนเงิน</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal เลือกไรเดอร์ -->
    <div class="modal fade" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" id="modal-rider">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">เลือกไรเดอร์ที่ต้องการจัดส่ง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label for="name" class="form-label">ไรเดอร์ : </label>
                                <select class="form-control" name="rider_id" id="rider_id">
                                    <option value="" disabled selected>กรุณาเลือกไรเดอร์</option>
                                    @foreach($rider as $rs)
                                        <option value="{{$rs->id}}">{{$rs->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="order_id_rider">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="confirm_rider">ยืนยันการจัดส่ง</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal ออกใบกำกับภาษี -->
    <div class="modal fade" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" id="modal-tax-full">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ออกใบกำกับภาษี</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="tax-full">
                    <div class="modal-body">
                        <div class="card-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-12">
                                    <label for="name" class="form-label">ชื่อลูกค้า : </label>
                                    <input type="text" name="name" id="name" class="form-control" required>
                                </div>
                                <div class="col-md-12">
                                    <label for="tel" class="form-label">เบอร์โทรศัพท์ : </label>
                                    <input type="text" name="tel" id="tel" class="form-control" required
                                        onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="10">
                                </div>
                                <div class="col-md-12">
                                    <label for="tax_id" class="form-label">เลขประจำตัวผู้เสียภาษี : </label>
                                    <input type="text" name="tax_id" id="tax_id" class="form-control" required
                                        onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                </div>
                                <div class="col-md-12">
                                    <label for="address" class="form-label">ที่อยู่ : </label>
                                    <textarea rows="4" class="form-control" name="address" id="address" required></textarea>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="pay_id">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="open-tex-full">ออกใบเสร็จ</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal คำนวณเงิน -->
    <div class="modal fade" tabindex="-1" aria-labelledby="cashPaymentLabel" aria-hidden="true" id="modal-cash-payment">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bx bx-money me-2"></i>ชำระเงินสด
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h3 class="text-primary">ยอดที่ต้องชำระ</h3>
                        <h1 class="text-success" id="cash-total-amount">0.00 ฿</h1>
                    </div>

                    <div class="mb-3">
                        <label for="cash-received" class="form-label">
                            <i class="bx bx-wallet me-1"></i>จำนวนเงินที่รับมา <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">฿</span>
                            <input type="number" class="form-control form-control-lg text-end" id="cash-received"
                                placeholder="0.00" step="0.01" min="0" style="font-size: 1.2rem;">
                        </div>
                        <div class="form-text">กรุณาใส่จำนวนเงินที่ลูกค้าจ่าย</div>
                    </div>

                    <!-- แสดงเงินทอน -->
                    <div class="card" id="change-card" style="display: none;">
                        <div class="card-body text-center">
                            <h5 class="card-title text-warning">
                                <i class="bx bx-transfer me-2"></i>เงินทอน
                            </h5>
                            <h2 class="text-warning mb-0" id="change-amount">0.00 ฿</h2>
                        </div>
                    </div>

                    <div class="alert alert-danger" id="insufficient-alert" style="display: none;">
                        <i class="bx bx-error-circle me-2"></i>
                        <strong>เงินไม่เพียงพอ!</strong> กรุณาใส่จำนวนเงินที่มากกว่าหรือเท่ากับยอดที่ต้องชำระ
                    </div>

                    <input type="hidden" id="cash-table-id">
                    <input type="hidden" id="cash-bill-total">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>ยกเลิก
                    </button>
                    <button type="button" class="btn btn-success" id="confirm-cash-payment" disabled>
                        <i class="bx bx-check me-1"></i>ยืนยันการชำระเงิน
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal พรีวิวใบเสร็จ -->
    <div class="modal fade" tabindex="-1" aria-labelledby="previewLabel" aria-hidden="true" id="modal-preview">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">พรีวิวใบเสร็จ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe src="" id="preview-frame" style="width:100%;height:500px;border:0;"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="confirm-print">ปริ้นใบเสร็จ</button>
                    <button type="button" class="btn btn-warning" id="print-browser">ปริ้นแบบธรรมดา</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>

    <script>
        var language = '{{asset("assets/js/datatable-language.js")}}';

        $(document).ready(function () {
            // DataTable สำหรับรายการออเดอร์
            var table1 = $("#myTable").DataTable({
                language: {
                    url: language,
                },
                processing: true,
                scrollX: true,
                order: [
                    [4, 'desc']
                ],
                ajax: {
                    url: "{{route('ListOrder')}}",
                    type: "post",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                },
                columns: [{
                    data: 'flag_order',
                    class: 'text-center',
                    width: '15%'
                },
                {
                    data: 'table_id',
                    class: 'text-center',
                    width: '15%'
                },
                {
                    data: 'total',
                    class: 'text-center',
                    width: '10%'
                },
                {
                    data: 'remark',
                    class: 'text-left',
                    width: '15%'
                },
                {
                    data: 'created',
                    class: 'text-center',
                    width: '15%'
                },
                {
                    data: 'status',
                    class: 'text-center',
                    width: '15%'
                },
                {
                    data: 'action',
                    class: 'text-center',
                    width: '15%',
                    orderable: false
                },
                ]
            });
            var highlight = new URLSearchParams(window.location.search).get('highlight');
            if (highlight) {
                table1.on('draw', function () {
                    table1.rows().every(function () {
                        var row = this.node();
                        var text = $(row).find('td:eq(1)').text().trim();
                        if (text === highlight) {
                            $(row).addClass('highlight-row');
                            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    });
                });
            }

            // DataTable สำหรับรายการชำระเงินแล้ว
            $("#myTable2").DataTable({
                language: {
                    url: language,
                },
                processing: true,
                scrollX: true,
                order: [
                    [0, 'desc']
                ],
                ajax: {
                    url: "{{route('ListOrderPay')}}",
                    type: "post",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                },
                columns: [{
                    data: 'payment_number',
                    class: 'text-center',
                    width: '15%'
                },
                {
                    data: 'type',
                    class: 'text-center',
                    width: '15%'
                },
                {
                    data: 'table_id',
                    class: 'text-center',
                    width: '10%'
                },
                {
                    data: 'total',
                    class: 'text-center',
                    width: '10%'
                },
                {
                    data: 'created',
                    class: 'text-center',
                    width: '20%'
                },
                {
                    data: 'action',
                    class: 'text-center',
                    width: '30%',
                    orderable: false
                },
                ]
            });
        });

        // ฟังก์ชันตรวจสอบว่ามาจาก mobile app หรือไม่
        function isMobileApp() {
            // ตรวจสอบจาก HTTP headers ที่ส่งมาใน meta tag
            const channel = document.querySelector('meta[name="app-channel"]')?.getAttribute('content');
            const device = document.querySelector('meta[name="app-device"]')?.getAttribute('content');

            return channel === 'pos-app' && (device === 'android' || device === 'ios');
        }

        // ดูรูปสลิป
        $(document).on('click', '.viewSlip', function (e) {
            e.preventDefault();
            var imageUrl = $(this).data('image');

            if (imageUrl) {
                $('#slip-image').attr('src', imageUrl);
                $('#download-slip').attr('href', imageUrl);
                $('#modal-slip').modal('show');
            } else {
                Swal.fire('ไม่พบรูปภาพ', 'ไม่มีรูปสลิปในระบบ', 'warning');
            }
        });

        // ยืนยันการชำระเงิน
        $(document).on('click', '.confirmPayment', function (e) {
            e.preventDefault();
            var orderId = $(this).data('id');

            Swal.fire({
                title: 'ยืนยันการชำระเงิน?',
                text: 'คุณแน่ใจหรือไม่ว่าต้องการยืนยันการชำระเงินนี้',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังประมวลผล...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('confirmSlipPayment') }}",
                        type: "post",
                        data: {
                            order_id: orderId
                        },
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (response.status) {
                                Swal.fire({
                                    title: 'สำเร็จ!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'ตกลง'
                                });
                                $('#myTable').DataTable().ajax.reload(null, false);
                                $('#myTable2').DataTable().ajax.reload(null, false);
                            } else {
                                Swal.fire('เกิดข้อผิดพลาด!', response.message, 'error');
                            }
                        },
                        error: function (xhr) {
                            var errorMessage = 'ไม่สามารถยืนยันการชำระเงินได้';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire('เกิดข้อผิดพลาด!', errorMessage, 'error');
                        }
                    });
                }
            });
        });

        // ปฏิเสธการชำระเงิน
        $(document).on('click', '.rejectPayment', function (e) {
            e.preventDefault();
            var orderId = $(this).data('id');

            $('#reject-order-id').val(orderId);
            $('#reject-reason').val('');
            $('#modal-reject').modal('show');
        });

        // ยืนยันการปฏิเสธ
        $('#confirm-reject').click(function (e) {
            e.preventDefault();
            var orderId = $('#reject-order-id').val();
            var reason = $('#reject-reason').val().trim();

            if (!reason) {
                Swal.fire('กรุณาระบุเหตุผล', 'กรุณาระบุเหตุผลในการปฏิเสธการชำระเงิน', 'warning');
                $('#reject-reason').focus();
                return;
            }

            Swal.fire({
                title: 'ปฏิเสธการชำระเงิน?',
                text: 'คุณแน่ใจหรือไม่ว่าต้องการปฏิเสธการชำระเงินนี้',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ปฏิเสธ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#modal-reject').modal('hide');

                    Swal.fire({
                        title: 'กำลังประมวลผล...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('rejectSlipPayment') }}",
                        type: "post",
                        data: {
                            order_id: orderId,
                            reason: reason
                        },
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (response.status) {
                                Swal.fire({
                                    title: 'สำเร็จ!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'ตกลง'
                                });
                                $('#myTable').DataTable().ajax.reload(null, false);
                                $('#myTable2').DataTable().ajax.reload(null, false);
                            } else {
                                Swal.fire('เกิดข้อผิดพลาด!', response.message, 'error');
                            }
                        },
                        error: function (xhr) {
                            var errorMessage = 'ไม่สามารถปฏิเสธการชำระเงินได้';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire('เกิดข้อผิดพลาด!', errorMessage, 'error');
                        }
                    });
                }
            });
        });

        // รายละเอียดออเดอร์
        $(document).on('click', '.modalShow', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                type: "post",
                url: "{{ route('listOrderDetail') }}",
                data: {
                    id: id
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('#modal-detail').modal('show');
                    $('#body-html').html(response);
                }
            });
        });

        // รายละเอียดออเดอร์ที่ชำระแล้ว
        $(document).on('click', '.modalShowPay', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                type: "post",
                url: "{{ route('listOrderDetailPay') }}",
                data: {
                    id: id
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('#modal-detail-pay').modal('show');
                    $('#body-html-pay').html(response);
                }
            });
        });

        // พรีวิวใบเสร็จ
        $(document).on('click', '.preview-short', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            console.log('Preview button clicked, ID:', id);

            // แสดง loading
            Swal.showLoading();

            // สร้าง URL สำหรับ preview
            var previewUrl = '{{ route("printReceipt", ":id") }}'.replace(':id', id);
            console.log('Preview URL:', previewUrl);

            // โหลด preview ใน iframe
            $('#preview-frame').attr('src', previewUrl);

            // เก็บ ID สำหรับใช้ในการ print
            $('#modal-preview').data('receipt-id', id);

            // ซ่อน loading และแสดง modal
            Swal.close();
            $('#modal-preview').modal('show');
        });

        // ปริ้นใบเสร็จ (รองรับ JSBridge)
        $(document).on('click', '#confirm-print', function (e) {
            e.preventDefault();
            var id = $('#modal-preview').data('receipt-id');

            if (id) {
                // สร้าง URL พร้อม parameters
                var printUrl = '{{ route("printReceipt", ":id") }}'.replace(':id', id);

                // เพิ่ม parameters ถ้ามาจาก mobile app
                if (isMobileApp()) {
                    const channel = document.querySelector('meta[name="app-channel"]')?.getAttribute('content');
                    const device = document.querySelector('meta[name="app-device"]')?.getAttribute('content');
                    printUrl += `?channel=${channel}&device=${device}`;
                }

                // เปิดหน้าต่างใหม่เพื่อ print
                var printWindow = window.open(printUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');

                // รอให้หน้าโหลดเสร็จแล้วปิด modal
                if (printWindow) {
                    $('#modal-preview').modal('hide');
                }
            }
        });

        // ปริ้นแบบธรรมดา (ไม่เปลี่ยนแปลง)
        $(document).on('click', '#print-browser', function (e) {
            e.preventDefault();
            var frame = document.getElementById('preview-frame');
            if (frame && frame.contentWindow) {
                frame.contentWindow.print();
            }
        });

        // ชำระเงิน
        $(document).on('click', '.modalPay', function (e) {
            var total = $(this).data('total');
            var id = $(this).data('id');
            Swal.showLoading();
            $.ajax({
                type: "post",
                url: "{{ route('generateQr') }}",
                data: {
                    total: total
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function (response) {
                    Swal.close();
                    $('#modal-pay').modal('show');
                    $('#totalPay').html(total + ' บาท');
                    $('#qr_code').html(response);
                    $('#table_id').val(id);
                }
            });
        });

        // เลือกไรเดอร์
        $(document).on('click', '.modalRider', function (e) {
            var total = $(this).data('total');
            var id = $(this).data('id');
            Swal.showLoading();
            $('#order_id_rider').val(id);
            $('#modal-rider').modal('show');
            Swal.close();
        });

        $(document).off('click', '.confirm_pay');
        $(document).on('click', '.confirm_pay', function (e) {
            e.preventDefault();
            var tableId = $('#table_id').val();
            var paymentType = $(this).data('id');
            var totalAmount = parseFloat($('#totalPay').text().replace(' บาท', '').replace(/,/g, ''));

            $('#modal-pay').modal('hide');

            if (paymentType == 0) {
                // ชำระเงินสด - แสดง Modal ใส่จำนวนเงิน
                showCashPaymentModal(tableId, totalAmount);
            } else {
                // ชำระโอนเงิน - ดำเนินการตามปกติ
                processCashlessPayment(tableId, paymentType);
            }
        });

        // ยืนยันการจัดส่งไรเดอร์
        $('#confirm_rider').click(function (e) {
            e.preventDefault();
            var id = $('#order_id_rider').val();
            var rider_id = $('#rider_id').val();
            $.ajax({
                url: "{{route('confirm_rider')}}",
                type: "post",
                data: {
                    id: id,
                    rider_id: rider_id
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('#modal-rider').modal('hide')
                    if (response.status == true) {
                        Swal.fire(response.message, "", "success");
                        $('#myTable').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire(response.message, "", "error");
                    }
                }
            });
        });

        // ออกใบกำกับภาษี
        $(document).on('click', '.modalTax', function (e) {
            var id = $(this).data('id');
            $('#modal-tax-full').modal('show');
            $('#pay_id').val(id);
        });

        $('#modal-tax-full').on('hidden.bs.modal', function () {
            $('#pay_id').val('');
            $('input').val('');
            $('textarea').val('');
        });

        $('#modal-pay').on('hidden.bs.modal', function () {
            $('#table_id').val('');
        });

        $('#modal-reject').on('hidden.bs.modal', function () {
            $('#reject-order-id').val('');
            $('#reject-reason').val('');
        });

        $('#modal-slip').on('hidden.bs.modal', function () {
            $('#slip-image').attr('src', '');
            $('#download-slip').attr('href', '');
        });

        // ส่งข้อมูลใบกำกับภาษี
        $(document).on('submit', '#tax-full', function (e) {
            e.preventDefault();
            var pay_id = $('#pay_id').val();
            var name = $('#name').val();
            var tel = $('#tel').val();
            var tax_id = $('#tax_id').val();
            var address = $('#address').val();
            window.open('<?= url('admin/order/printReceiptfull') ?>/' + pay_id + '?name=' + name + '&tel=' + tel + '&tax_id=' + tax_id + '&address=' + address, '_blank');
        });

        // ยกเลิกออเดอร์
        $(document).on('click', '.cancelOrderSwal', function (e) {
            var id = $(this).data('id');
            $('#modal-detail').modal('hide');
            Swal.fire({
                title: "ต้องการยกเลิกออเดอร์นี้ใช่หรือไม่",
                showCancelButton: true,
                confirmButtonText: "ยืนยัน",
                denyButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    $.ajax({
                        type: "post",
                        url: "{{ route('cancelOrder') }}",
                        data: {
                            id: id
                        },
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            Swal.close();
                            if (response.status == true) {
                                $('#myTable').DataTable().ajax.reload(null, false);
                                Swal.fire(response.message, "", "success");
                            } else {
                                Swal.fire(response.message, "", "error");
                            }
                        }
                    });
                }
            });
        });

        // ยกเลิกเมนู
        $(document).on('click', '.cancelMenuSwal', function (e) {
            var id = $(this).data('id');
            $('#modal-detail').modal('hide');
            Swal.fire({
                title: "ต้องการยกเลิกเมนูนี้ใช่หรือไม่",
                showCancelButton: true,
                confirmButtonText: "ยืนยัน",
                denyButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    $.ajax({
                        type: "post",
                        url: "{{ route('cancelMenu') }}",
                        data: {
                            id: id
                        },
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            Swal.close();
                            if (response.status == true) {
                                $('#myTable').DataTable().ajax.reload(null, false);
                                Swal.fire(response.message, "", "success");
                            } else {
                                Swal.fire(response.message, "", "error");
                            }
                        }
                    });
                }
            });
        });

        // อัพเดทสถานะ
        $(document).on('click', '.update-status', function (e) {
            var id = $(this).data('id');
            $('#modal-detail').modal('hide');
            Swal.fire({
                title: "<h5>ท่านต้องการอัพเดทสถานะรายการนี้ใช่หรือไม่</h5>",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "ยืนยัน",
                denyButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    $.ajax({
                        type: "post",
                        url: "{{ route('updatestatus') }}",
                        data: {
                            id: id
                        },
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            Swal.close();
                            if (response.status == true) {
                                $('#myTable').DataTable().ajax.reload(null, false);
                                Swal.fire(response.message, "", "success");
                            } else {
                                Swal.fire(response.message, "", "error");
                            }
                        }
                    });
                }
            });
        });

        // อัพเดทสถานะออเดอร์
        $(document).on('click', '.updatestatusOrder', function (e) {
            var id = $(this).data('id');
            $('#modal-detail').modal('hide');
            Swal.fire({
                title: "<h5>ท่านต้องการอัพเดทสถานะรายการนี้ใช่หรือไม่</h5>",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "ยืนยัน",
                denyButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    $.ajax({
                        type: "post",
                        url: "{{ route('updatestatusOrder') }}",
                        data: {
                            id: id
                        },
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            Swal.close();
                            if (response.status == true) {
                                $('#myTable').DataTable().ajax.reload(null, false);
                                Swal.fire(response.message, "", "success");
                            } else {
                                Swal.fire(response.message, "", "error");
                            }
                        }
                    });
                }
            });
        });
        // ปุ่มพรีวิวใบเสร็จสำหรับสลิป
        $(document).on('click', '.preview-short-order', function (e) {
            e.preventDefault();
            var orderId = $(this).data('id');
            console.log('Preview order button clicked, ID:', orderId);

            Swal.showLoading();

            var previewUrl = '{{ route("printReceiptFromOrder", ":id") }}'.replace(':id', orderId);
            console.log('Preview Order URL:', previewUrl);

            $('#preview-frame').attr('src', previewUrl);

            $('#modal-preview').data('receipt-id', orderId);
            $('#modal-preview').data('receipt-type', 'order');

            Swal.close();
            $('#modal-preview').modal('show');
        });

        $(document).off('click', '#confirm-print');
        $(document).on('click', '#confirm-print', function (e) {
            e.preventDefault();
            var id = $('#modal-preview').data('receipt-id');
            var type = $('#modal-preview').data('receipt-type') || 'pay';

            if (id) {
                var printUrl;

                if (type === 'order') {
                    printUrl = '{{ route("printReceiptFromOrder", ":id") }}'.replace(':id', id);
                } else {
                    printUrl = '{{ route("printReceipt", ":id") }}'.replace(':id', id);
                }

                if (isMobileApp()) {
                    const channel = document.querySelector('meta[name="app-channel"]')?.getAttribute('content');
                    const device = document.querySelector('meta[name="app-device"]')?.getAttribute('content');
                    printUrl += `?channel=${channel}&device=${device}`;
                }

                var printWindow = window.open(printUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');

                if (printWindow) {
                    $('#modal-preview').modal('hide');
                }
            }
        });

        $(document).off('click', '.modalShowPay');
        $(document).on('click', '.modalShowPay', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var type = $(this).data('type') || 'pay';

            $.ajax({
                type: "post",
                url: "{{ route('listOrderDetailPay') }}",
                data: {
                    id: id,
                    type: type
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('#modal-detail-pay').modal('show');
                    $('#body-html-pay').html(response);
                },
                error: function (xhr) {
                    Swal.fire('เกิดข้อผิดพลาด!', 'ไม่สามารถดึงข้อมูลได้', 'error');
                }
            });
        });

        $(document).on('click', '.modalTaxOrder', function (e) {
            e.preventDefault();
            var orderId = $(this).data('id');

            Swal.fire({
                title: 'ออกใบกำกับภาษี',
                text: 'ต้องการออกใบกำกับภาษีสำหรับออเดอร์นี้หรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ออกใบกำกับภาษี',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    var taxUrl = '{{ route("printReceiptFromOrder", ":id") }}'.replace(':id', orderId) + '?type=tax';
                    window.open(taxUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
                }
            });
        });

        $('#modal-preview').on('hidden.bs.modal', function () {
            $(this).removeData('receipt-id').removeData('receipt-type');
            $('#preview-frame').attr('src', 'about:blank');
        });

        $('#preview-frame').on('load', function () {
            var iframe = this;
            try {
                var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                if (iframeDoc.title.includes('404') || iframeDoc.title.includes('Error')) {
                    throw new Error('Page not found');
                }
            } catch (e) {
                console.error('Preview loading error:', e);
                Swal.fire('ไม่สามารถโหลดพรีวิวได้', 'กรุณาลองใหม่อีกครั้ง', 'error');
                $('#modal-preview').modal('hide');
            }
        });

        function reloadDataTables() {
            $('#myTable').DataTable().ajax.reload(null, false);
            $('#myTable2').DataTable().ajax.reload(null, false);
        }

        console.log('Order management JavaScript loaded successfully');
        console.log('Available routes:', {
            printReceipt: '{{ route("printReceipt", "ID") }}',
            printReceiptFromOrder: '{{ route("printReceiptFromOrder", "ID") }}',
            confirmSlipPayment: '{{ route("confirmSlipPayment") }}',
            rejectSlipPayment: '{{ route("rejectSlipPayment") }}'
        });

        //ฟังก์ชันสำหรับยืนยันการชำระเงินสด
        $(document).on('click', '#confirm-cash-payment', function (e) {
            e.preventDefault();

            var tableId = $('#cash-table-id').val();
            var billTotal = parseFloat($('#cash-bill-total').val());
            var receivedAmountInput = $('#cash-received').val().trim();
            var receivedAmount = parseFloat(receivedAmountInput) || 0;
            var changeAmount = receivedAmount - billTotal;

            if (!receivedAmountInput || receivedAmountInput.trim() === '' || receivedAmount <= 0) {
                Swal.fire({
                    title: 'กรุณาใส่จำนวนเงิน!',
                    text: 'กรุณาใส่จำนวนเงินที่ลูกค้าจ่าย',
                    icon: 'warning',
                    confirmButtonText: 'ตกลง'
                });
                $('#cash-received').focus();
                return;
            }

            // ตรวจสอบว่าเงินเพียงพอหรือไม่ 
            if (receivedAmount < billTotal) {
                Swal.fire({
                    title: 'เงินไม่เพียงพอ!',
                    text: `จำนวนเงินที่ใส่ (${formatCurrency(receivedAmount)} ฿) น้อยกว่ายอดที่ต้องชำระ (${formatCurrency(billTotal)} ฿)`,
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
                $('#cash-received').focus().select();
                return;
            }

            var summaryHtml = `
            <div class="text-start">
                <p><strong>ยอดที่ต้องชำระ:</strong> <span class="text-primary">${formatCurrency(billTotal)} ฿</span></p>
                <p><strong>เงินที่รับมา:</strong> <span class="text-success">${formatCurrency(receivedAmount)} ฿</span></p>
                <p><strong>เงินทอน:</strong> <span class="text-warning">${formatCurrency(changeAmount)} ฿</span></p>
            </div>
        `;

            $('#modal-cash-payment').modal('hide');

            setTimeout(function () {
                Swal.fire({
                    title: 'ยืนยันการชำระเงินสด',
                    html: summaryHtml,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'ยืนยันการชำระ',
                    cancelButtonText: 'ยกเลิก',
                    customClass: {
                        popup: 'swal-wide'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'กำลังประมวลผล...',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{route('confirm_pay')}}",
                            type: "post",
                            data: {
                                id: tableId,
                                value: 0,
                                received_amount: receivedAmount,
                                change_amount: changeAmount
                            },
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                if (response.status) {
                                    var successHtml = response.message;
                                    if (changeAmount > 0) {
                                        successHtml += `<br><br><div class="alert alert-warning mt-2">
                                        <strong><i class="bx bx-transfer me-2"></i>เงินทอน: ${formatCurrency(changeAmount)} ฿</strong>
                                    </div>`;
                                    }

                                    Swal.fire({
                                        title: 'สำเร็จ!',
                                        html: successHtml,
                                        icon: 'success',
                                        confirmButtonText: 'ตกลง',
                                        customClass: {
                                            popup: 'swal-wide'
                                        }
                                    });

                                    $('#myTable').DataTable().ajax.reload(null, false);
                                    $('#myTable2').DataTable().ajax.reload(null, false);
                                } else {
                                    Swal.fire('เกิดข้อผิดพลาด!', response.message, 'error');
                                }
                            },
                            error: function (xhr) {
                                var errorMessage = 'ไม่สามารถชำระเงินได้';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                Swal.fire('เกิดข้อผิดพลาด!', errorMessage, 'error');
                            }
                        });
                    } else if (result.isDismissed) {
                        $('#modal-cash-payment').modal('show');
                    }
                });
            }, 300);
        });

        $(document).on('input', '#cash-received', function () {
            var receivedAmountInput = $(this).val().trim();
            var receivedAmount = parseFloat(receivedAmountInput) || 0;
            var billTotal = parseFloat($('#cash-bill-total').val()) || 0;
            var change = receivedAmount - billTotal;

            $('#change-card').hide();
            $('#insufficient-alert').hide();
            $('#confirm-cash-payment').prop('disabled', true);

            if (!receivedAmountInput || receivedAmountInput === '' || isNaN(receivedAmount) || receivedAmount <= 0) {
                $('#confirm-cash-payment')
                    .prop('disabled', true)
                    .removeClass('btn-success btn-warning')
                    .addClass('btn-secondary')
                    .html('<i class="bx bx-check me-1"></i>ยืนยันการชำระเงิน');
                return;
            }

            if (change >= 0) {
                // แสดงเงินทอน
                $('#change-amount').text(formatCurrency(change) + ' ฿');
                $('#change-card').show();
                $('#confirm-cash-payment').prop('disabled', false);

                if (change === 0) {
                    $('#confirm-cash-payment')
                        .removeClass('btn-warning btn-secondary')
                        .addClass('btn-success')
                        .html('<i class="bx bx-check me-1"></i>ยืนยันการชำระเงิน (พอดี)');
                } else {
                    $('#confirm-cash-payment')
                        .removeClass('btn-success btn-secondary')
                        .addClass('btn-warning')
                        .html('<i class="bx bx-check me-1"></i>ยืนยันการชำระเงิน (ทอน ' + formatCurrency(change) + ' ฿)');
                }
            } else {
                // เงินไม่พอ - แสดงข้อความเตือน
                $('#insufficient-alert').show();
                $('#confirm-cash-payment')
                    .prop('disabled', true)
                    .removeClass('btn-success btn-warning')
                    .addClass('btn-secondary')
                    .html('<i class="bx bx-x me-1"></i>เงินไม่เพียงพอ');
            }
        });

        $(document).on('blur', '#cash-received', function () {
            var receivedAmountInput = $(this).val().trim();

            if (receivedAmountInput && isNaN(parseFloat(receivedAmountInput))) {
                $(this).val('');
                $('#change-card').hide();
                $('#insufficient-alert').hide();
                $('#confirm-cash-payment')
                    .prop('disabled', true)
                    .removeClass('btn-success btn-warning')
                    .addClass('btn-secondary')
                    .html('<i class="bx bx-check me-1"></i>ยืนยันการชำระเงิน');
            }
        });


        function showCashPaymentModal(tableId, totalAmount) {
            $('#cash-table-id').val(tableId);
            $('#cash-bill-total').val(totalAmount);
            $('#cash-total-amount').text(formatCurrency(totalAmount) + ' ฿');
            $('#cash-received').val('');
            $('#change-card').hide();
            $('#insufficient-alert').hide();
            $('#confirm-cash-payment').prop('disabled', true);
            $('#modal-cash-payment').modal('show');

            setTimeout(() => {
                $('#cash-received').focus();
            }, 500);
        }

        function processCashlessPayment(tableId, paymentType) {
            $.ajax({
                url: "{{route('confirm_pay')}}",
                type: "post",
                data: {
                    id: tableId,
                    value: paymentType
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.status == true) {
                        Swal.fire(response.message, "", "success");
                        $('#myTable').DataTable().ajax.reload(null, false);
                        $('#myTable2').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire(response.message, "", "error");
                    }
                },
                error: function (xhr) {
                    var errorMessage = 'ไม่สามารถชำระเงินได้';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire('เกิดข้อผิดพลาด!', errorMessage, 'error');
                }
            });
        }

        function formatCurrency(amount) {
            return parseFloat(amount).toLocaleString('th-TH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        $(document).on('keypress', '#cash-received', function (e) {
            if (e.which === 13 && !$('#confirm-cash-payment').prop('disabled')) {
                $('#confirm-cash-payment').click();
            }
        });

        $('#modal-cash-payment').on('hidden.bs.modal', function () {
            $('#cash-received').val('');
            $('#change-card').hide();
            $('#insufficient-alert').hide();
            $('#confirm-cash-payment').prop('disabled', true);
            $('#cash-table-id').val('');
            $('#cash-bill-total').val('');
        });

    </script>
@endsection
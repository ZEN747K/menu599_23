@extends('admin.layout')
@section('style')
<style>
    .time-restriction-fields {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-top: 0.5rem;
    }
</style>
@endsection
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 col-md-12 order-1">
                <div class="row d-flex justify-content-center">
                    <div class="col-12">
                        <form action="{{route('menuSave')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="card">
                                <div class="card-header">
                                    เพิ่มเมนู
                                    <hr>
                                </div>
                                <div class="card-body">
                                    {{-- ข้อมูลพื้นฐาน --}}
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="name" class="form-label">ชื่อเมนู : </label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="base_price" class="form-label">ราคา : </label>
                                            <input type="text" class="form-control" id="base_price" name="base_price" onkeypress="return event.charCode >= 48 && event.charCode <= 57" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="categories_id" class="form-label">หมวดหมู่อาหาร : </label>
                                            <select class="form-control" name="categories_id" id="categories_id" required>
                                                <option value="" disabled selected>เลือกหมวดหมู่</option>
                                                @foreach($category as $categories)
                                                <option value="{{$categories->id}}">{{$categories->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="detail" class="form-label">รายละเอียด : </label>
                                            <textarea class="form-control" rows="4" name="detail" id="detail"></textarea>
                                        </div>
                                    </div>

                                    {{-- การจัดการสถานะเมนู --}}
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">การจัดการสถานะเมนู</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">สถานะเมนู</label>
                                                    <select class="form-control" name="is_active" required>
                                                        <option value="1" selected>เปิดขาย</option>
                                                        <option value="0">ปิดขาย</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">สถานะสต็อก</label>
                                                    <select class="form-control" name="is_out_of_stock">
                                                        <option value="0" selected>มีสินค้า</option>
                                                        <option value="1">สินค้าหมด</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">จำนวนคงเหลือ</label>
                                                    <input type="number" class="form-control" name="stock_quantity" 
                                                           placeholder="ไม่จำกัด" min="0">
                                                    <small class="text-muted">เว้นว่างไว้ = ไม่จำกัดจำนวน</small>
                                                </div>
                                                <div class="col-md-8">
                                                    <label class="form-label">ข้อความเมื่อไม่สามารถสั่งได้</label>
                                                    <input type="text" class="form-control" name="unavailable_message" 
                                                           placeholder="เช่น สินค้าหมด หรือ ไม่ได้อยู่ในช่วงเวลาขาย">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- การตั้งเวลาขาย --}}
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">การตั้งเวลาและวันขาย</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-12">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="has_time_restriction" value="1" 
                                                               id="hasTimeRestriction">
                                                        <label class="form-check-label" for="hasTimeRestriction">
                                                            <strong>จำกัดเวลาและวันขาย</strong>
                                                        </label>
                                                        <div class="form-text">เลือกหากต้องการกำหนดเวลาหรือวันที่เฉพาะในการขาย</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="timeRestrictionFields" class="time-restriction-fields" style="display: none;">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">เวลาเริ่มขาย</label>
                                                        <input type="time" class="form-control" name="available_from">
                                                        <small class="text-muted">เว้นว่าง = ไม่จำกัดเวลาเริ่ม</small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">เวลาสิ้นสุดการขาย</label>
                                                        <input type="time" class="form-control" name="available_until">
                                                        <small class="text-muted">เว้นว่าง = ไม่จำกัดเวลาสิ้นสุด</small>
                                                    </div>
                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <label class="form-label">วันที่ขาย</label>
                                                        <div class="row">
                                                            @php
                                                                $days = [
                                                                    1 => 'จันทร์',
                                                                    2 => 'อังคาร', 
                                                                    3 => 'พุธ',
                                                                    4 => 'พฤหัสบดี',
                                                                    5 => 'ศุกร์',
                                                                    6 => 'เสาร์',
                                                                    0 => 'อาทิตย์'
                                                                ];
                                                            @endphp
                                                            
                                                            @foreach($days as $dayValue => $dayName)
                                                            <div class="col-md-3 mb-2">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" name="available_days[]" 
                                                                           value="{{ $dayValue }}" id="day{{ $dayValue }}">
                                                                    <label class="form-check-label" for="day{{ $dayValue }}">
                                                                        {{ $dayName }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                        <small class="text-muted">ไม่เลือกวันไหน = ขายทุกวัน</small>
                                                    </div>
                                                </div>

                                                <div class="alert alert-info">
                                                    <i class="bx bx-info-circle me-2"></i>
                                                    <strong>ตัวอย่าง:</strong> หากต้องการขายเฉพาะวันจันทร์-ศุกร์ เวลา 09:00-17:00 
                                                    ให้เลือกวันจันทร์ถึงศุกร์ และกำหนดเวลา 09:00-17:00
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="file" class="form-label">รูปภาพเมนู : </label>
                                            <input class="form-control" type="file" id="file" name="file" accept="image/*">
                                            <small class="text-muted">รองรับไฟล์ JPG, PNG, GIF ขนาดไม่เกิน 2MB</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-between">
                                    <a href="{{route('menu')}}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> กลับ
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save me-1"></i> บันทึก
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#hasTimeRestriction').change(function() {
            const fields = $('#timeRestrictionFields');
            if (this.checked) {
                fields.slideDown(300);
            } else {
                fields.slideUp(300);
                fields.find('input[type="time"]').val('');
                fields.find('input[type="checkbox"]').prop('checked', false);
            }
        });

        // ตรวจสอบการเลือกวัน
        $('input[name="available_days[]"]').change(function() {
            const checkedDays = $('input[name="available_days[]"]:checked').length;
            const dayHint = checkedDays === 0 ? 'ขายทุกวัน' : `ขาย ${checkedDays} วัน`;
            
            let hintElement = $('#day-hint');
            if (hintElement.length === 0) {
                hintElement = $('<small id="day-hint" class="text-info d-block mt-1"></small>');
                $(this).closest('.row').after(hintElement);
            }
            hintElement.text(dayHint);
        });

        // ตรวจสอบเวลา
        $('input[name="available_from"], input[name="available_until"]').change(function() {
            const fromTime = $('input[name="available_from"]').val();
            const untilTime = $('input[name="available_until"]').val();
            
            if (fromTime && untilTime && fromTime >= untilTime) {
                Swal.fire({
                    icon: 'warning',
                    title: 'เวลาไม่ถูกต้อง',
                    text: 'เวลาเริ่มต้องน้อยกว่าเวลาสิ้นสุด'
                });
                $(this).val('');
            }
        });

        $('form').submit(function(e) {
            const hasTimeRestriction = $('#hasTimeRestriction').is(':checked');
            
            if (hasTimeRestriction) {
                const fromTime = $('input[name="available_from"]').val();
                const untilTime = $('input[name="available_until"]').val();
                const selectedDays = $('input[name="available_days[]"]:checked').length;
                
                
                if (!fromTime && !untilTime && selectedDays === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'ข้อมูลไม่ครบถ้วน',
                        text: 'กรุณากำหนดเวลาหรือวันที่ขาย หรือยกเลิกการจำกัดเวลา'
                    });
                    return false;
                }
            }
        });
    });
</script>
@endsection
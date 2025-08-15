@extends('layouts.delivery')

@section('title', 'หน้ารายละเอียด')

@section('content')
<?php
use App\Models\Config;
$config = Config::first();
?>
<style>
    /* --- CSS ทั่วไป (ปรับปรุงจากของเดิม) --- */
    .title-buy {
        font-size: 28px;
        font-weight: 600;
        color: <?= $config->color_font != '' ? $config->color_font : '#ffffff' ?>;
    }
    .title-list-buy {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    .btn-edit, .btn-delete {
        background: none; border: none; cursor: pointer; padding: 0 5px;
        font-size: 13px; font-weight: bold; transition: all 0.3s ease;
    }
    .btn-edit { color: #007bff; text-decoration: none; }
    .btn-edit:hover { color: #0056b3; }
    .btn-delete { color: rgb(192, 0, 0); }
    .btn-delete:hover { color: rgb(255, 80, 80); }
    .btn-aprove {
        background: linear-gradient(360deg, var(--primary-color), var(--sub-color));
        border-radius: 50px; border: none; padding: 10px 0px;
        font-weight: bold; text-decoration: none; color: white;
        transition: all 0.3s ease; text-align: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .btn-aprove:hover {
        background: linear-gradient(360deg, var(--sub-color), var(--primary-color));
        cursor: pointer; transform: translateY(-2px);
    }
    .checkbox-delete {
        transform: scale(1.4); margin-right: 15px;
        cursor: pointer; vertical-align: middle;
    }

    /* --- CSS ที่เพิ่มเข้ามาใหม่สำหรับ UI ที่สวยขึ้น --- */
    .modern-card {
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid #f0f0f0;
    }
    .order-item {
        padding: 1rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .order-item:last-child { border-bottom: none; padding-bottom: 0; }
    .total-section {
        border-top: 1px solid #e9ecef;
        padding-top: 1.5rem; margin-top: 1.5rem;
    }
    /* สไตล์สำหรับตัวเลือกที่อยู่ */
    .address-option-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .address-option-card:hover {
        border-color: var(--sub-color);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    /* เมื่อ radio ถูกเลือก ให้ card ของมันมีขอบสี */
    .address-option-card input[type="radio"]:checked {
        border-color: var(--primary-color);
    }
    label:has(input[type="radio"]:checked) {
        border-color: var(--primary-color);
        background-color: #f8f9fa;
    }
</style>

<div class="container">
    <div class="d-flex flex-column justify-content-center gap-4">
        <div class="title-buy">คำสั่งซื้อและจัดส่ง</div>

        @if(Session::get('user'))
        <div class="modern-card p-0 overflow-hidden">
            <div class="card-header bg-transparent py-3 px-4">
                <h5 class="mb-0 fw-bold">ข้อมูลที่อยู่สำหรับจัดส่ง</h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    @forelse($address as $rs)
                    <div class="col-md-6 mb-3">
                        <label class="address-option-card p-3 d-flex w-100">
                            <input type="radio" class="form-check-input mt-1 me-3" name="address" onclick="change_is_use(this)" value="{{$rs->id}}" {{ $rs->is_use ? 'checked' : '' }}>
                            <div class="flex-grow-1">
                                <span class="fw-bold">{{$rs->name}}</span>
                                <small class="text-muted d-block">{{$rs->detail}}</small>
                            </div>
                        </label>
                    </div>
                    @empty
                    @endforelse
                    <div class="col-md-6 mb-3">
                        <a href="{{route('delivery.createaddress')}}" class="text-decoration-none">
                             <div class="address-option-card p-3 d-flex align-items-center justify-content-center w-100 h-100" style="border-style: dashed;">
                                <span class="fw-bold text-success"><i class="fa fa-plus me-2"></i>เพิ่มที่อยู่ใหม่</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="modern-card p-4">
            <div class="title-list-buy">รายการอาหารที่สั่ง</div>
            <div id="order-summary" class="mt-2">
                </div>
            <div id="action-buttons" class="d-flex flex-column gap-2 mt-3">
                <a href="javascript:void(0);" class="btn btn-warning d-none" id="delete-selected-btn" style="border-radius:20px;">ลบรายการที่เลือก</a>
                <a href="javascript:void(0);" class="btn btn-danger d-none" id="clear-order-btn" style="border-radius:20px;">ลบทั้งหมด</a>
            </div>
            <div class="total-section">
                <div class="fw-bold fs-5 mb-2">ยอดชำระทั้งหมด</div>
                <div class="fw-bold text-center" style="font-size: 45px;">
                    <span id="total-price" style="color: #0d9700"></span>
                    <span class="text-dark ms-2" style="font-size: 2rem;">บาท</span>
                </div>
            </div>
        </div>

        <div class="modern-card p-3">
            <textarea class="form-control fw-bold text-center border-0 shadow-none bg-transparent" rows="3" id="remark" placeholder="หมายเหตุ (ความต้องการเพิ่มเติม)"></textarea>
        </div>

        @if(Session::get('user'))
            <a href="javascript:void(0);" class="btn-aprove d-none" id="confirm-order-btn">ยืนยันคำสั่งซื้อ</a>
        @endif
    </div>
</div>

<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const container = document.getElementById('order-summary');
    const totalPriceEl = document.getElementById('total-price');
    const confirmButton = document.getElementById('confirm-order-btn');
    const clearButton = document.getElementById('clear-order-btn');
    const deleteSelectedBtn = document.getElementById('delete-selected-btn');

    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    function renderOrderList() {
        container.innerHTML = '';
        let total = 0;

        if (cart.length === 0) {
            container.innerHTML = `<div class="text-center text-muted p-4">ไม่มีสินค้าในตะกร้า 🛒</div>`;
        } else {
            cart.forEach(item => {
                const optionsText = (item.options && item.options.length)
                    ? item.options.map(opt => opt.label).join(', ')
                    : 'ไม่มีตัวเลือกเพิ่มเติม';

                const itemHTML = `
                    <div class="order-item d-flex align-items-center" data-uuid="${item.uuid}">
                        <div class="flex-shrink-0">
                            <input type="checkbox" class="checkbox-delete" data-uuid="${item.uuid}">
                        </div>
                        <div class="flex-grow-1 ms-2 lh-sm">
                            <div class="fw-bold">${item.name} x ${item.amount}</div>
                            <div class="text-muted" style="font-size: 12px;">${optionsText}</div>
                        </div>
                        <div class="flex-shrink-0 text-end">
                            <div class="fw-bold fs-6">${item.total_price.toLocaleString()}</div>
                            <div>
                                <a href="/detail/${item.category_id}#select-${item.id}&uuid=${item.uuid}" class="btn-edit">แก้ไข</a>
                                <a href="javascript:void(0);" class="btn-delete" data-uuid="${item.uuid}">ลบ</a>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += itemHTML;
                total += item.total_price;
            });
        }
        totalPriceEl.textContent = total.toLocaleString();
        toggleButtons();
    }

    container.addEventListener('click', function(event) {
        if (event.target.classList.contains('btn-delete')) {
            const uuidToDelete = event.target.dataset.uuid;
            cart = cart.filter(cartItem => cartItem.uuid !== uuidToDelete);
            updateCartAndRender();
        }
    });

    function updateCartAndRender() {
        if (cart.length > 0) {
            localStorage.setItem('cart', JSON.stringify(cart));
        } else {
            localStorage.removeItem('cart');
        }
        renderOrderList();
    }

    function toggleButtons() {
        const hasItems = cart.length > 0;
        if(confirmButton) confirmButton.classList.toggle('d-none', !hasItems);
        clearButton.classList.toggle('d-none', !hasItems);
        deleteSelectedBtn.classList.toggle('d-none', !hasItems);
    }

    if(confirmButton) {
        confirmButton.addEventListener('click', function(event) {
            event.preventDefault();
            if (cart.length > 0) {
                $.ajax({
                    type: "post",
                    url: "{{ route('delivery.SendOrder') }}", // <-- ใช้ route เดิม
                    data: { cart: cart, remark: $('#remark').val() },
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    dataType: "json",
                    success: function(response) {
                        if (response.status == true) {
                            Swal.fire(response.message, "", "success");
                            cart = [];
                            updateCartAndRender();
                            setTimeout(() => { location.reload(); }, 2000);
                        } else {
                            Swal.fire(response.message, "", "error");
                        }
                    }
                });
            }
        });
    }

    clearButton.addEventListener('click', function() {
        Swal.fire({
            title: 'ต้องการลบรายการทั้งหมด?',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
            confirmButtonText: 'ใช่, ลบทั้งหมด', cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                cart = [];
                updateCartAndRender();
            }
        });
    });

    deleteSelectedBtn.addEventListener('click', function() {
        const selected = document.querySelectorAll('.checkbox-delete:checked');
        if (selected.length > 0) {
            const uuidsToDelete = Array.from(selected).map(chk => chk.dataset.uuid);
            cart = cart.filter(item => !uuidsToDelete.includes(item.uuid));
            updateCartAndRender();
        } else {
            Swal.fire('โปรดเลือกรายการ', 'กรุณาเลือกรายการที่ต้องการลบก่อน', 'warning');
        }
    });

    renderOrderList();
});
</script>

<script>
function change_is_use(input) {
    var id = $(input).val();
    $.ajax({
        type: "post",
        url: "{{route('delivery.change')}}",
        data: { id: id },
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function(response) {
            //$('#modal-qr').modal('show')
            //$('#body-html').html(response);
            // ไม่ต้องทำอะไรหลังจากเปลี่ยนสำเร็จ เพราะหน้าเว็บจะโหลดใหม่เมื่อกดสั่งซื้อ
            // หรือถ้าต้องการให้มี feedback ก็สามารถใส่ Swal.fire() ที่นี่ได้
        }
    });
}
</script>
@endsection
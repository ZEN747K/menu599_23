@extends('layouts.luxury-nav')

@section('title', 'หน้ารายละเอียด')

@section('content')
<?php
use App\Models\Config;
$config = Config::first();
?>
<style>
    /* --- CSS เดิม (ปรับปรุงเล็กน้อย) --- */
    .title-buy {
        font-size: 28px; /* ปรับขนาดให้พอดีขึ้น */
        font-weight: 600; /* ใช้ font-weight เป็นตัวเลข */
        color: <?= $config->color_font != '' ? $config->color_font : '#ffffff' ?>;
    }
    .title-list-buy {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 1rem; /* เพิ่มระยะห่างด้านล่าง */
    }
    .btn-edit, .btn-delete {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0 5px;
        font-size: 13px;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    .btn-edit {
        color: #007bff; /* สีที่ดูสากลขึ้น */
        text-decoration: none;
    }
    .btn-edit:hover {
        color: #0056b3;
    }
    .btn-delete {
        color: rgb(192, 0, 0);
    }
    .btn-delete:hover {
        color: rgb(255, 80, 80);
    }
    .btn-aprove {
        background: linear-gradient(360deg, var(--primary-color), var(--sub-color));
        border-radius: 50px; /* ทำให้โค้งมนสวยงาม */
        border: none;
        padding: 10px 0px; /* เพิ่มความสูงของปุ่ม */
        font-weight: bold;
        text-decoration: none;
        color: rgb(255, 255, 255);
        transition: background 0.3s ease;
        text-align: center; /* จัดกลางเสมอ */
        box-shadow: 0 4px 10px rgba(0,0,0,0.1); /* เพิ่มเงาเล็กน้อย */
    }
    .btn-aprove:hover {
        background: linear-gradient(360deg, var(--sub-color), var(--primary-color));
        cursor: pointer;
        transform: translateY(-2px); /* เพิ่ม animation ตอน hover */
    }
    .checkbox-delete {
        transform: scale(1.4);
        margin-right: 15px;
        cursor: pointer;
        vertical-align: middle;
    }

    /* --- CSS ที่เพิ่มเข้ามาใหม่ --- */
    .order-summary-card {
        background-color: #fff;
        border-radius: 15px; /* เพิ่มความโค้งมน */
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); /* เงาที่นุ่มนวลขึ้น */
    }
    .order-item {
        padding: 1rem 0;
        border-bottom: 1px solid #f0f0f0; /* เส้นคั่นที่ดูสบายตา */
    }
    .order-item:last-child {
        border-bottom: none; /* รายการสุดท้ายไม่ต้องมีเส้นคั่น */
        padding-bottom: 0;
    }
    .total-section {
        border-top: 1px solid #e9ecef; /* เส้นคั่นรวมยอดแบบนุ่มนวล */
        padding-top: 1.5rem;
        margin-top: 1.5rem;
    }
</style>

<div class="container">
    <div class="d-flex flex-column justify-content-center gap-4">
        <div class="title-buy">สรุปคำสั่งซื้อ</div>

        <div class="order-summary-card p-4">
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

        <a href="javascript:void(0);" class="btn-aprove d-none" id="confirm-order-btn">
            ยืนยันคำสั่งซื้อ
        </a>
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
            // ใช้ Template Literals (``) ในการสร้าง HTML ทำให้โค้ดอ่านง่ายขึ้นมาก
            cart.forEach(item => {
                const optionsText = (item.options && item.options.length)
                    ? item.options.map(opt => opt.label).join(', ')
                    : 'ไม่มีตัวเลือกเพิ่มเติม';

                // สร้าง HTML สำหรับแต่ละรายการ
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

    // ใช้ Event Delegation ในการจัดการ event การลบ ซึ่งมีประสิทธิภาพกว่า
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
        // ใช้ classList.toggle หรือ add/remove แทนการแก้ style ตรงๆ
        confirmButton.classList.toggle('d-none', !hasItems);
        clearButton.classList.toggle('d-none', !hasItems);
        deleteSelectedBtn.classList.toggle('d-none', !hasItems);
    }

    // ปุ่มยืนยันคำสั่งซื้อ
    confirmButton.addEventListener('click', function(event) {
        event.preventDefault();
        if (cart.length > 0) {
            // โค้ด Ajax เดิมของคุณ
            $.ajax({
                type: "post",
                url: "{{ route('SendOrder') }}",
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

    // ปุ่มลบทั้งหมด
    clearButton.addEventListener('click', function() {
        Swal.fire({
            title: 'ต้องการลบรายการทั้งหมด?',
            text: "การกระทำนี้ไม่สามารถย้อนกลับได้",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ใช่, ลบทั้งหมด',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                cart = [];
                updateCartAndRender();
                Swal.fire('สำเร็จ!', 'ลบรายการทั้งหมดเรียบร้อยแล้ว', 'success');
            }
        });
    });

    // ปุ่มลบที่เลือก
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

    // เริ่มต้นแสดงผล
    renderOrderList();
});
</script>
@endsection
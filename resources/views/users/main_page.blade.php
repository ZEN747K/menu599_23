@extends('layouts.luxury-nav')

@section('title', 'หน้าหลัก')

@section('content')
<?php

use App\Models\Config;

$config = Config::first();
?>
<style>
    .carousel-item img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 10px;
    }

    .icon-have {
        padding: 5px;
        background: linear-gradient(360deg, var(--primary-color), var(--sub-color));
        object-fit: cover;
        border-radius: 100%;
    }

    .icon-have img {
        width: 50px;
        height: 50px;
    }

    .title-food {
        font-size: 30px;
        font-weight: bold;
        color: <?= ($config->color_font != '')  ? $config->color_font :  '#ffffff' ?>;
    }

    .food-box {
        position: relative;
        transition: transform 0.2s ease-in-out;
    }

    .food-box:hover {
        transform: translateY(-2px);
    }

    .food-box img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 0.5rem;
    }

   .food-label {
    font-size: 18px;
    color: #000000;
    font-weight: bold;
    text-align: center;
    word-wrap: break-word;
    overflow-wrap: break-word;
    width: 100%;
    line-height: 0.9;
}


    .category-status {
        position: absolute;
        top: 8px;
        right: 8px;
        z-index: 10;
    }

    .status-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
        border-radius: 10px;
        font-weight: bold;
    }

    .menu-count {
        position: absolute;
        bottom: 8px;
        left: 8px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 0.2rem 0.5rem;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: bold;
    }

    .availability-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 4px;
    }

    .available {
        background-color: #28a745;
    }

    .limited {
        background-color: #ffc107;
    }

    .unavailable {
        background-color: #dc3545;
    }

    .category-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.3);
        display: none;
        border-radius: 0.5rem;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
    }

    .food-box.unavailable-category .category-overlay {
        display: flex;
    }

    .food-box.unavailable-category img {
        filter: grayscale(100%);
        opacity: 0.6;
    }

    .current-time {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 0.5rem;
        margin-bottom: 1rem;
        text-align: center;
        backdrop-filter: blur(10px);
    }

    .current-time-text {
        color: <?= ($config->color_font != '')  ? $config->color_font :  '#ffffff' ?>;
        font-size: 0.9rem;
        margin: 0;
    }
</style>

@if(count($promotion) > 0)
<div id="carouselCaptions" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner" style="border-radius: 10px;">
        @foreach($promotion as $key => $rs)
        <div class="carousel-item <?= ($key == 0) ? 'active' : '' ?>">
            <img src="{{ url('storage/'.$rs->image) }}" class="d-block w-100" alt="slide">
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="container mt-1">
    <div class="d-flex flex-column justify-content-center">
        <!-- แสดงเวลาปัจจุบัน -->
        <!-- <div class="current-time">
            <p class="current-time-text">
                <i class="fas fa-clock me-1"></i>
                เวลาปัจจุบัน: <span id="current-time"></span>
            </p>
        </div> -->

        <div class="title-food" style="color: black;">
    หมวดอาหาร
    <small class="text-muted" style="font-size: 0.6em; display: block;">
        แสดงเฉพาะหมวดที่มีเมนูพร้อมขาย
    </small>
</div>

        
        <div class="gap-2 py-2">
            <div class="row py-2">
                @forelse($category as $rs)
                    @php
                        // นับเมนูที่พร้อมขายในหมวดนี้
                        $availableMenus = \App\Models\Menu::where('categories_id', $rs->id)->availableNow()->count();
                        $totalMenus = \App\Models\Menu::where('categories_id', $rs->id)->count();
                        
                        // กำหนดสถานะหมวดหมู่
                        $categoryStatus = 'available';
                        $statusText = 'พร้อมขาย';
                        $statusClass = 'bg-success';
                        
                        if ($availableMenus == 0) {
                            $categoryStatus = 'unavailable';
                            $statusText = 'ปิดขาย';
                            $statusClass = 'bg-danger';
                        } elseif ($availableMenus < $totalMenus) {
                            $categoryStatus = 'limited';
                            $statusText = 'บางรายการ';
                            $statusClass = 'bg-warning text-dark';
                        }
                    @endphp
                    
                    <div class="col-6 mb-2 category-item" data-category-id="{{ $rs->id }}">
                        <div class="food-box {{ $availableMenus == 0 ? 'unavailable-category' : '' }}">
                            <a href="{{ $availableMenus > 0 ? route('detail', $rs->id) : 'javascript:void(0);' }}" 
                               style="text-decoration: none;" 
                               class="d-flex flex-column justify-content-center align-items-center {{ $availableMenus == 0 ? 'pe-none' : '' }}">
                                
                                <!-- สถานะหมวดหมู่ -->
                                <div class="category-status">
                                    <span class="badge status-badge {{ $statusClass }}">
                                        <span class="availability-indicator {{ $categoryStatus }}"></span>
                                        {{ $statusText }}
                                    </span>
                                </div>

                                <!-- จำนวนเมนู -->
                                <div class="menu-count">
                                    {{ $availableMenus }}/{{ $totalMenus }} เมนู
                                </div>

                                <!-- รูปภาพ -->
                                @if($rs['files'])
                                    <img src="{{ url('storage/'.$rs['files']->file) }}" alt="{{ $rs->name }}">
                                @else
                                    <img src="{{ asset('foods/default-photo.png') }}" alt="{{ $rs->name }}">
                                @endif

                                <!-- ชื่อหมวดหมู่ -->
                                <div class="food-label mt-2">{{ $rs->name }}</div>

                                <div class="category-overlay">
                                    <div class="text-center">
                                        <i class="fas fa-clock fa-2x mb-2"></i>
                                        <div>ปิดขาย</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-clock fa-3x text-muted"></i>
                        </div>
                        <h5 class="text-muted">ไม่มีเมนูพร้อมขายในขณะนี้</h5>
                        <p class="text-muted">กรุณาลองใหม่</p>
                        <button class="btn btn-outline-primary" onclick="location.reload()">
                            <i class="fas fa-refresh me-1"></i>
                            รีเฟรชหน้า
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- สถิติสรุป -->
    @if(count($category) > 0)
<div class="mt-3 p-3" style="background: rgba(255, 255, 255, 0.3); border-radius: 8px; backdrop-filter: blur(5px); border: 1px solid rgba(255, 255, 255, 0.2);">
    <div class="row text-center">
        <div class="col-4">
            <div class="fw-bold" style="color: #000000 !important;">
                {{ count($category) }}
            </div>
            <small style="color: #333333 !important;">หมวดพร้อมขาย</small>
        </div>
        <div class="col-4">
            <div class="fw-bold" style="color: #000000 !important;">
                {{ \App\Models\Menu::availableNow()->count() }}
            </div>
            <small style="color: #333333 !important;">เมนูพร้อมขาย</small>
        </div>
        <div class="col-4">
            <div class="fw-bold" style="color: #000000 !important;">
                {{ \App\Models\Menu::count() }}
            </div>
            <small style="color: #333333 !important;">เมนูทั้งหมด</small>
        </div>
    </div>
</div>
@endif
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
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
        
        const timeString = now.toLocaleDateString('th-TH', options);
        $('#current-time').text(timeString);
    }

    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);

    setInterval(checkCategoryAvailability, 30000);

    function checkCategoryAvailability() {
        var categoryIds = [];
        $('.category-item').each(function() {
            var categoryId = $(this).data('category-id');
            if (categoryId) {
                categoryIds.push(categoryId);
            }
        });

        if (categoryIds.length === 0) return;

        $.ajax({
            url: '{{ route("checkCategoryAvailability") }}',
            method: 'POST',
            data: {
                category_ids: categoryIds,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                updateCategoryStatus(response);
            },
            error: function() {
                console.log('ไม่สามารถตรวจสอบสถานะหมวดหมู่ได้');
            }
        });
    }

    function updateCategoryStatus(statusData) {
        var hasChanges = false;
        
        $.each(statusData, function(categoryId, status) {
            var categoryElement = $('.category-item[data-category-id="' + categoryId + '"]');
            var currentAvailable = !categoryElement.find('.food-box').hasClass('unavailable-category');
            
            if (currentAvailable !== status.has_available_menus) {
                hasChanges = true;
            }
            
            var statusBadge = categoryElement.find('.status-badge');
            var menuCount = categoryElement.find('.menu-count');
            var foodBox = categoryElement.find('.food-box');
            var link = categoryElement.find('a');
            
            statusBadge.removeClass('bg-success bg-warning bg-danger text-dark')
                      .addClass(status.status_class)
                      .find('.availability-indicator')
                      .removeClass('available limited unavailable')
                      .addClass(status.indicator_class);
            
            statusBadge.contents().filter(function() {
                return this.nodeType === 3;
            }).remove();
            statusBadge.append(' ' + status.status_text);
            
            menuCount.text(status.available_count + '/' + status.total_count + ' เมนู');
            
            if (status.has_available_menus) {
                foodBox.removeClass('unavailable-category');
                link.removeClass('pe-none').attr('href', '{{ url("detail") }}/' + categoryId);
            } else {
                foodBox.addClass('unavailable-category');
                link.addClass('pe-none').attr('href', 'javascript:void(0);');
            }
        });
        
        if (hasChanges) {
            // 
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'อัปเดตเมนู',
                    text: 'มีการเปลี่ยนแปลงสถานะเมนู',
                    showConfirmButton: false,
                    timer: 2000,
                    position: 'top-end',
                    toast: true
                });
            }
        }
    }

    $('.unavailable-category a').click(function(e) {
        e.preventDefault();
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'หมวดนี้ปิดขาย',
                text: 'ขณะนี้ไม่มีเมนูพร้อมขายในหมวดนี้',
                confirmButtonText: 'ตกลง'
            });
        } else {
            alert('ขณะนี้ไม่มีเมนูพร้อมขายในหมวดนี้');
        }
    });
});
</script>
@endsection
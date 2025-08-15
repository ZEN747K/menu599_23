<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            // สถานะเปิดปิดเมนู
            $table->tinyInteger('is_active')->default(1)->after('detail'); 
            
            // การควบคุมเวลาขาย
            $table->tinyInteger('has_time_restriction')->default(0)->after('is_active'); // มีจำกัดเวลาหรือไม่
            $table->time('available_from')->nullable()->after('has_time_restriction'); // เวลาเริ่มขาย
            $table->time('available_until')->nullable()->after('available_from'); // เวลาสิ้นสุดการขาย
            
            // การควบคุมวันขาย (JSON format)
            $table->json('available_days')->nullable()->after('available_until'); // วันที่ขาย 
            
            // สถานะ stock หมด
            $table->tinyInteger('is_out_of_stock')->default(0)->after('available_days'); // 0=มีของ, 1=หมด
            
            // จำนวนคงเหลือ (ถ้าต้องการ)
            $table->integer('stock_quantity')->nullable()->after('is_out_of_stock'); // จำนวนคงเหลือ
            
            // ข้อความแจ้งเตือน
            $table->text('unavailable_message')->nullable()->after('stock_quantity'); // ข้อความเมื่อไม่สามารถสั่งได้
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn([
                'is_active',
                'has_time_restriction',
                'available_from',
                'available_until',
                'available_days',
                'is_out_of_stock',
                'stock_quantity',
                'unavailable_message'
            ]);
        });
    }
};
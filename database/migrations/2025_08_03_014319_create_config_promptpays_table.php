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
        // ตรวจสอบว่าตารางมีอยู่แล้วหรือไม่
        if (!Schema::hasTable('config_promptpays')) {
            Schema::create('config_promptpays', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('config_id')->nullable();
                $table->text('promptpay')->nullable();
                $table->text('bank_name')->nullable();
                $table->text('account_name')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_promptpays');
    }
};
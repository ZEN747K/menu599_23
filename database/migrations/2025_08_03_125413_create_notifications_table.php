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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedBigInteger('table_id')->nullable();
            $table->string('table_number')->nullable();
            $table->string('message');
            $table->string('sub_message')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->integer('order_count')->default(0);
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            $table->index(['type', 'is_read']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
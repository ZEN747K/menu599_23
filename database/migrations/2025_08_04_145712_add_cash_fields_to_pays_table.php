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
        Schema::table('pays', function (Blueprint $table) {
            $table->decimal('received_amount', 10, 2)->nullable()->after('total')->comment('จำนวนเงินที่รับมา');
            $table->decimal('change_amount', 10, 2)->nullable()->after('received_amount')->comment('เงินทอน');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pays', function (Blueprint $table) {
            $table->dropColumn(['received_amount', 'change_amount']);
        });
    }
};
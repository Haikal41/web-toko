<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('fulfillment', 10)->default('delivery');
            $table->decimal('cash_received', 12, 2)->nullable();
            $table->decimal('change_amount', 12, 2)->nullable();
        });

        // Pesanan POS lama yang diambil di toko tidak memerlukan label kirim.
        DB::table('orders')
            ->where('channel', 'pos')
            ->where('customer_address', 'Ambil di toko')
            ->update(['fulfillment' => 'pickup']);

        Schema::table('whatsapp_logs', function (Blueprint $table) {
            $table->string('message_type', 30)->default('payment_receipt');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_logs', function (Blueprint $table) {
            $table->dropColumn('message_type');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['fulfillment', 'cash_received', 'change_amount']);
        });
    }
};

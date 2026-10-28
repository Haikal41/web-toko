<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Role baru: owner (pemilik), kasir (staf toko), customer (akun lama).
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('customer')->change();
        });

        DB::table('users')->where('role', 'admin')->update(['role' => 'owner']);
        DB::table('users')->where('role', 'karyawan')->update(['role' => 'kasir']);

        // Pesanan tamu tidak terhubung ke akun. Pesanan walk-in dari kasir
        // boleh tanpa nomor WhatsApp.
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('customer_phone', 20)->nullable()->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('channel', 10)->default('online');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->index('customer_phone');
        });

        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
        });

        // Modul absensi dihapus total.
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('attendance_tokens');
        Schema::dropIfExists('attendance_settings');
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_logs');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['customer_phone']);
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn(['channel', 'cancelled_at', 'cancel_reason']);
        });

        DB::table('users')->where('role', 'owner')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'kasir')->update(['role' => 'karyawan']);
    }
};

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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 100)->unique()->nullable()->after('id')->comment('Tài khoản đăng nhập');
            $table->timestamp('last_login_at')->nullable()->after('password')->comment('Ngày đăng nhập cuối cùng');
            $table->string('role', 50)->default('user')->after('last_login_at')->comment('Vai trò người dùng');
            $table->boolean('status')->default(true)->after('role')->comment('Trạng thái (1: hoạt động, 0: vô hiệu hóa)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'last_login_at', 'role', 'status']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('gioi_tinh', 20)->nullable()->after('ho_ten');
            $table->string('trang_thai', 50)->nullable()->after('avatar');
            $table->string('ma_ho_so', 100)->nullable()->after('trang_thai');
            $table->date('ngay_vao_truong')->nullable()->after('ma_ho_so');
            $table->string('co_so', 255)->nullable()->after('lop');
            $table->string('bac_dao_tao', 100)->nullable()->after('co_so');
            $table->string('loai_hinh_dao_tao', 100)->nullable()->after('bac_dao_tao');
            $table->string('khoa', 255)->nullable()->after('loai_hinh_dao_tao');
            $table->string('nganh', 255)->nullable()->after('khoa');
            $table->string('chuyen_nganh', 255)->nullable()->after('nganh');
            $table->string('khoa_hoc', 50)->nullable()->after('chuyen_nganh');
            $table->string('dan_toc', 50)->nullable()->after('ngay_sinh');
            $table->string('ton_giao', 100)->nullable()->after('dan_toc');
            $table->string('quoc_tich', 100)->nullable()->after('ton_giao');
            $table->string('khu_vuc', 255)->nullable()->after('quoc_tich');
            $table->string('so_cccd', 50)->nullable()->after('khu_vuc');
            $table->date('ngay_cap_cccd')->nullable()->after('so_cccd');
            $table->string('noi_cap_cccd', 255)->nullable()->after('ngay_cap_cccd');
            $table->string('doi_tuong', 100)->nullable()->after('noi_cap_cccd');
            $table->string('dien_chinh_sach', 255)->nullable()->after('doi_tuong');
            $table->string('ngay_vao_doan', 50)->nullable()->after('dien_chinh_sach');
            $table->string('ngay_vao_dang', 50)->nullable()->after('ngay_vao_doan');
            $table->text('dia_chi_lien_he')->nullable()->after('email');
            $table->string('noi_sinh', 255)->nullable()->after('dia_chi_lien_he');
            $table->text('ho_khau_thuong_tru')->nullable()->after('noi_sinh');
            $table->string('nam_sinh_cha', 50)->nullable()->after('ho_ten_cha');
            $table->string('nghe_nghiep_cha', 255)->nullable()->after('nam_sinh_cha');
            $table->string('quoc_tich_cha', 100)->nullable()->after('nghe_nghiep_cha');
            $table->string('dan_toc_cha', 50)->nullable()->after('quoc_tich_cha');
            $table->string('ton_giao_cha', 100)->nullable()->after('dan_toc_cha');
            $table->string('co_quan_cha', 255)->nullable()->after('ton_giao_cha');
            $table->string('chuc_vu_cha', 255)->nullable()->after('co_quan_cha');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'gioi_tinh', 'trang_thai', 'ma_ho_so', 'ngay_vao_truong', 'co_so', 'bac_dao_tao',
                'loai_hinh_dao_tao', 'khoa', 'nganh', 'chuyen_nganh', 'khoa_hoc',
                'dan_toc', 'ton_giao', 'quoc_tich', 'khu_vuc', 'so_cccd', 'ngay_cap_cccd', 'noi_cap_cccd',
                'doi_tuong', 'dien_chinh_sach', 'ngay_vao_doan', 'ngay_vao_dang',
                'dia_chi_lien_he', 'noi_sinh', 'ho_khau_thuong_tru',
                'nam_sinh_cha', 'nghe_nghiep_cha', 'quoc_tich_cha', 'dan_toc_cha', 'ton_giao_cha',
                'co_quan_cha', 'chuc_vu_cha',
            ]);
        });
    }
};

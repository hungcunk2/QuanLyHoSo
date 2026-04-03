@extends('layouts.admin')

@section('title', 'Quản lý đăng ký học phần')
@section('page-title', 'Quản lý đăng ký học phần')

@push('styles')
<style>
    #createCourseOfferingModal .modal-dialog {
        max-height: calc(100vh - 2rem);
        margin: 1rem auto;
        height: auto;
    }
    #createCourseOfferingModal .modal-content {
        max-height: calc(100vh - 2rem);
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    #createCourseOfferingModal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        max-height: min(65vh, 600px);
        -webkit-overflow-scrolling: touch;
    }
    #createCourseOfferingModal.modal-edit .modal-dialog {
        max-width: 95vw;
        width: 95vw;
    }
    #createCourseOfferingModal.modal-edit .modal-body {
        max-height: 80vh;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                        <h5 class="fw-bold">Danh sách đăng ký học phần</h5>
                        <button type="button" class="btn btn-primary" id="btnOpenCreateOffering" data-bs-toggle="modal" data-bs-target="#createCourseOfferingModal">
                            <i class="fas fa-plus"></i> Tạo học phần mới
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row justify-content-between gy-3">
            <div class="col-md-6 col-lg-4 col-xl-3">
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="d-flex align-items-center gap-3 justify-content-end">
                    <div class="input-group input-group-search ms-2">
                        <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control dt-search" placeholder="Tìm kiếm..."
                            aria-label="Search" aria-describedby="addon-wrapping" aria-controls="registrationsTable">
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive mt-3">
            <table id="registrationsTable" class="table table-striped border">
                <thead>
                    <tr>
                        <th>Thời gian tạo</th>
                        <th>Tên học phần</th>
                        <th>Môn học</th>
                        <th>Lớp</th>
                        <th>Giáo viên</th>
                        <th>Trạng thái</th>
                        <th>Thời gian học</th>
                        <th>Lịch học</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tạo học phần mới -->
<div class="modal fade" id="createCourseOfferingModal" tabindex="-1" aria-labelledby="createCourseOfferingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" id="createCourseOfferingModalDialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCourseOfferingModalLabel">Tạo học phần mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createCourseOfferingForm">
                @csrf
                <input type="hidden" name="offering_id" id="offering_id" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="ten_hoc_phan" class="form-label">Tên học phần <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ten_hoc_phan" name="ten_hoc_phan" required placeholder="VD: Toán cao cấp 1 - HK1 2024">
                        </div>
                        <div class="col-md-6">
                            <label for="class_room_id" class="form-label">Phòng học <span class="text-danger">*</span></label>
                            <select class="form-select" id="class_room_id" name="class_room_id" required>
                                <option value="">-- Chọn lớp --</option>
                                @foreach($classes ?? [] as $c)
                                    <option value="{{ $c->id }}">{{ $c->ma_lop }} - {{ $c->ten_lop }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="subject_id" class="form-label">Môn học <span class="text-danger">*</span></label>
                            <select class="form-select" id="subject_id" name="subject_id" required>
                                <option value="">-- Chọn môn học --</option>
                                @foreach($subjects ?? [] as $s)
                                    <option value="{{ $s->id }}">{{ $s->ma_mon_hoc }} - {{ $s->ten_mon_hoc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="teacher_id" class="form-label">Giáo viên phụ trách <span class="text-danger">*</span></label>
                            <select class="form-select" id="teacher_id" name="teacher_id" required>
                                <option value="">-- Chọn giáo viên --</option>
                                @foreach($teachers ?? [] as $t)
                                    <option value="{{ $t->id }}">{{ $t->msgv }} - {{ $t->ho_ten }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="si_so_lop" class="form-label">Sĩ số lớp <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="si_so_lop" name="si_so_lop" min="1" required placeholder="VD: 50">
                        </div>
                        <div class="col-md-6">
                            <label for="ngay_mo_dang_ky" class="form-label">Ngày bắt đầu mở đăng ký <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="ngay_mo_dang_ky" name="ngay_mo_dang_ky" required>
                        </div>
                        <div class="col-md-6">
                            <label for="ngay_ket_thuc_dang_ky" class="form-label">Ngày kết thúc đăng ký <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="ngay_ket_thuc_dang_ky" name="ngay_ket_thuc_dang_ky" required>
                        </div>
                        <div class="col-md-6">
                            <label for="ngay_bat_dau_hoc" class="form-label">Ngày bắt đầu học <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="ngay_bat_dau_hoc" name="ngay_bat_dau_hoc" required>
                        </div>
                        <div class="col-md-6">
                            <label for="ngay_ket_thuc_hoc" class="form-label">Ngày kết thúc học <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="ngay_ket_thuc_hoc" name="ngay_ket_thuc_hoc" required>
                        </div>
                        <div class="col-12" id="preview-ngay-hoc-wrap" style="display: none;">
                            <div class="card border-primary">
                                <div class="card-header py-2 bg-light small fw-bold">Xem trước các ngày học</div>
                                <div class="card-body py-2 small" id="preview-ngay-hoc"></div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-primary mb-3"><i class="fas fa-book-reader me-1"></i> Buổi học lý thuyết</h6>
                    <p class="small text-muted mb-2">Thông thường 1 buổi học = 3 tiết liên tiếp. Dùng nút "Thêm buổi lý thuyết" để thêm nhiều buổi.</p>
                    <div id="buoi-ly-thuyet-list">
                        <div class="buoi-ly-thuyet-row border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="small fw-bold text-secondary">Buổi 1</span>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-buoi-lt d-none" title="Xóa buổi này"><i class="fas fa-times"></i></button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Thứ trong tuần <span class="text-danger">*</span></label>
                                    <select class="form-select" name="thu_ly_thuyet[]" required>
                                        @foreach($weekdays ?? [] as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tiết học (3 tiết liên tiếp) <span class="text-danger">*</span></label>
                                    <div class="mb-2">
                                        <div class="btn-group btn-group-sm flex-wrap" role="group">
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="1">1-3</button>
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="4">4-6</button>
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="7">7-9</button>
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="10">10-12</button>
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="13">13-15</button>
                                        </div>
                                    </div>
                                    <div class="border rounded p-2 bg-white">
                                        @foreach([1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16] as $i)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input tiet-ly-thuyet" type="checkbox" value="{{ $i }}" id="lt_0_{{ $i }}">
                                                <label class="form-check-label small" for="lt_0_{{ $i }}">T{{ $i }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="tiet_ly_thuyet[]" class="tiet-lt-hidden" value="">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Thi lý thuyết vào buổi thứ mấy</label>
                                    <input type="number" class="form-control" name="ngay_thi_ly_thuyet_buoi_thu[]" min="1" placeholder="VD: 5">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-them-buoi-ly-thuyet"><i class="fas fa-plus me-1"></i> Thêm buổi lý thuyết</button>

                    <!-- Template clone cho buổi lý thuyết (ẩn) -->
                    <template id="tpl-buoi-ly-thuyet">
                        <div class="buoi-ly-thuyet-row border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="small fw-bold text-secondary buoi-lt-label">Buổi 2</span>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-buoi-lt" title="Xóa buổi này"><i class="fas fa-times"></i></button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Thứ trong tuần <span class="text-danger">*</span></label>
                                    <select class="form-select" name="thu_ly_thuyet[]" required>
                                        @foreach($weekdays ?? [] as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tiết học (3 tiết liên tiếp) <span class="text-danger">*</span></label>
                                    <div class="mb-2">
                                        <div class="btn-group btn-group-sm flex-wrap" role="group">
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="1">1-3</button>
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="4">4-6</button>
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="7">7-9</button>
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="10">10-12</button>
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="13">13-15</button>
                                        </div>
                                    </div>
                                    <div class="border rounded p-2 bg-white tiet-lt-checkboxes"></div>
                                    <input type="hidden" name="tiet_ly_thuyet[]" class="tiet-lt-hidden" value="">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Thi lý thuyết vào buổi thứ mấy</label>
                                    <input type="number" class="form-control" name="ngay_thi_ly_thuyet_buoi_thu[]" min="1" placeholder="VD: 5">
                                </div>
                            </div>
                        </div>
                    </template>

                    <hr class="my-4">
                    <h6 class="text-primary mb-2"><i class="fas fa-flask me-1"></i> Buổi học thực hành</h6>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-them-buoi-thuc-hanh"><i class="fas fa-plus me-1"></i> Thêm buổi thực hành</button>
                        <span class="small text-muted">Môn không có thực hành có thể bỏ trống.</span>
                    </div>
                    <div id="blockThucHanh">
                        <div id="buoi-thuc-hanh-list">
                            <div class="buoi-thuc-hanh-row border rounded p-3 mb-3 bg-light">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="small fw-bold text-secondary">Buổi TH 1</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-buoi-th d-none" title="Xóa buổi này"><i class="fas fa-times"></i></button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Thứ trong tuần</label>
                                        <select class="form-select" name="thu_thuc_hanh[]">
                                            <option value="">-- Chọn thứ --</option>
                                            @foreach($weekdays ?? [] as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tiết (3 tiết liên tiếp)</label>
                                        <div class="mb-2">
                                            <div class="btn-group btn-group-sm flex-wrap" role="group">
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="1">1-3</button>
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="4">4-6</button>
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="7">7-9</button>
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="10">10-12</button>
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="13">13-15</button>
                                            </div>
                                        </div>
                                        <div class="border rounded p-2 bg-white">
                                            @foreach([1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16] as $i)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input tiet-thuc-hanh" type="checkbox" value="{{ $i }}" id="th_0_{{ $i }}">
                                                    <label class="form-check-label small" for="th_0_{{ $i }}">T{{ $i }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <input type="hidden" name="tiet_thuc_hanh[]" class="tiet-th-hidden" value="">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Thi TH vào buổi thứ mấy</label>
                                        <input type="number" class="form-control" name="ngay_thi_thuc_hanh_buoi_thu[]" min="1" placeholder="VD: 3">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <template id="tpl-buoi-thuc-hanh">
                            <div class="buoi-thuc-hanh-row border rounded p-3 mb-3 bg-light">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="small fw-bold text-secondary buoi-th-label">Buổi TH 2</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-buoi-th" title="Xóa buổi này"><i class="fas fa-times"></i></button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Thứ trong tuần</label>
                                        <select class="form-select" name="thu_thuc_hanh[]">
                                            <option value="">-- Chọn thứ --</option>
                                            @foreach($weekdays ?? [] as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tiết (3 tiết liên tiếp)</label>
                                        <div class="mb-2">
                                            <div class="btn-group btn-group-sm flex-wrap" role="group">
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="1">1-3</button>
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="4">4-6</button>
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="7">7-9</button>
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="10">10-12</button>
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="13">13-15</button>
                                            </div>
                                        </div>
                                        <div class="border rounded p-2 bg-white tiet-th-checkboxes"></div>
                                        <input type="hidden" name="tiet_thuc_hanh[]" class="tiet-th-hidden" value="">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Thi TH vào buổi thứ mấy</label>
                                        <input type="number" class="form-control" name="ngay_thi_thuc_hanh_buoi_thu[]" min="1" placeholder="VD: 3">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="submitCourseOfferingBtn">Tạo học phần</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#registrationsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.subject-registrations.data") }}',
                type: 'GET',
                data: function(d) {
                    d.search = $('.dt-search').val();
                }
            },
            columns: [
                {
                    data: 'created_at_formatted',
                    name: 'created_at',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'ten_hoc_phan',
                    name: 'ten_hoc_phan',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'subject_info',
                    name: 'subject_info',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'class_info',
                    name: 'class_info',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'teacher_info',
                    name: 'teacher_info',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'offering_status',
                    name: 'offering_status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'date_range',
                    name: 'date_range',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'schedule_summary',
                    name: 'schedule_summary',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[0, 'desc']],
            pageLength: 10,
            language: {
                processing: "Đang xử lý...",
                search: "Tìm kiếm:",
                lengthMenu: "Hiển thị _MENU_ bản ghi",
                info: "Hiển thị _START_ đến _END_ trong tổng số _TOTAL_ bản ghi",
                infoEmpty: "Hiển thị 0 đến 0 trong tổng số 0 bản ghi",
                infoFiltered: "(lọc từ _MAX_ tổng số bản ghi)",
                paginate: {
                    first: "Đầu",
                    last: "Cuối",
                    next: "Sau",
                    previous: "Trước"
                },
                emptyTable: "Chưa có học phần nào. Bấm \"Tạo học phần mới\" để thêm.",
                zeroRecords: "Không tìm thấy kết quả"
            },
            dom: '<"row align-items-center"><"table-responsive my-3 mt-3 mb-2 pb-1" rt><"row align-items-center data_table_widgets" <"col-md-6" <"d-flex align-items-center flex-wrap gap-3" l i>><"col-md-6" p>><"clear">'
        });

        $('.dt-search').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Xem trước các ngày học: thứ (2-8 VN) → getDay() (0=CN, 1=T2, ..., 6=T7)
        var thuVNToJS = { 2: 1, 3: 2, 4: 3, 5: 4, 6: 5, 7: 6, 8: 0 };
        function getDatesForWeekday(weekdayJS, startStr, endStr) {
            if (!startStr || !endStr) return [];
            var start = new Date(startStr), end = new Date(endStr);
            if (isNaN(start.getTime()) || isNaN(end.getTime()) || start > end) return [];
            var d = new Date(start);
            if (d.getDay() !== weekdayJS) {
                var diff = (weekdayJS - d.getDay() + 7) % 7;
                d.setDate(d.getDate() + diff);
            }
            var list = [];
            while (d <= end) {
                list.push(new Date(d));
                d.setDate(d.getDate() + 7);
            }
            return list;
        }
        function fmt(d) { return ('0' + d.getDate()).slice(-2) + '/' + ('0' + (d.getMonth()+1)).slice(-2) + '/' + d.getFullYear(); }
        function updatePreviewNgayHoc() {
            var startStr = $('#ngay_bat_dau_hoc').val(), endStr = $('#ngay_ket_thuc_hoc').val();
            var html = [];
            var firstLt = $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').first();
            var thuLt = firstLt.find('select[name="thu_ly_thuyet[]"]').val();
            var tietLt = firstLt.find('.tiet-lt-hidden').val();
            if (thuLt && tietLt && startStr && endStr) {
                var jsDay = thuVNToJS[parseInt(thuLt, 10)];
                var dates = getDatesForWeekday(jsDay, startStr, endStr);
                var label = firstLt.find('select[name="thu_ly_thuyet[]"] option:selected').text();
                html.push('<strong>Lý thuyết</strong> (' + label + ', tiết ' + tietLt + '): ' + (dates.length ? dates.slice(0, 10).map(fmt).join(', ') + (dates.length > 10 ? ' … (tổng ' + dates.length + ' buổi)' : '') : '—'));
            }
            var firstTh = $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').first();
            var thuTh = firstTh.find('select[name="thu_thuc_hanh[]"]').val();
            var tietTh = firstTh.find('.tiet-th-hidden').val();
            if (thuTh && tietTh && startStr && endStr) {
                var jsDay = thuVNToJS[parseInt(thuTh, 10)];
                var dates = getDatesForWeekday(jsDay, startStr, endStr);
                var label = firstTh.find('select[name="thu_thuc_hanh[]"] option:selected').text();
                html.push('<strong>Thực hành</strong> (' + label + ', tiết ' + tietTh + '): ' + (dates.length ? dates.slice(0, 10).map(fmt).join(', ') + (dates.length > 10 ? ' … (tổng ' + dates.length + ' buổi)' : '') : '—'));
            }
            if (html.length) {
                $('#preview-ngay-hoc').html(html.join('<br>'));
                $('#preview-ngay-hoc-wrap').show();
            } else {
                $('#preview-ngay-hoc-wrap').hide();
            }
        }
        $('#ngay_bat_dau_hoc, #ngay_ket_thuc_hoc').on('change', updatePreviewNgayHoc);
        $(document).on('change', '#buoi-ly-thuyet-list select[name="thu_ly_thuyet[]"], #buoi-thuc-hanh-list select[name="thu_thuc_hanh[]"]', updatePreviewNgayHoc);
        $(document).on('change', '.tiet-ly-thuyet, .tiet-thuc-hanh', function() {
            setTimeout(updatePreviewNgayHoc, 50);
        });
        $(document).on('click', '.quick-lt, .quick-th', function() {
            setTimeout(updatePreviewNgayHoc, 100);
        });

        // Sync tiết theo từng buổi (lý thuyết)
        function syncAllTietLyThuyet() {
            $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').each(function() {
                var arr = [];
                $(this).find('.tiet-ly-thuyet:checked').each(function() { arr.push($(this).val()); });
                $(this).find('.tiet-lt-hidden').val(arr.sort(function(a,b){return a-b}).join(','));
            });
        }
        $(document).on('change', '.tiet-ly-thuyet', syncAllTietLyThuyet);

        function syncAllTietThucHanh() {
            $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').each(function() {
                var arr = [];
                $(this).find('.tiet-thuc-hanh:checked').each(function() { arr.push($(this).val()); });
                $(this).find('.tiet-th-hidden').val(arr.sort(function(a,b){return a-b}).join(','));
            });
        }
        $(document).on('change', '.tiet-thuc-hanh', syncAllTietThucHanh);

        // Chọn nhanh 3 tiết - chỉ trong cùng 1 buổi (delegate)
        $(document).on('click', '.quick-lt', function() {
            var row = $(this).closest('.buoi-ly-thuyet-row');
            var start = parseInt($(this).data('start'), 10);
            row.find('.tiet-ly-thuyet').prop('checked', false);
            for (var i = start; i < start + 3 && i <= 16; i++) {
                row.find('.tiet-ly-thuyet[value="' + i + '"]').prop('checked', true);
            }
            syncAllTietLyThuyet();
        });
        $(document).on('click', '.quick-th', function() {
            var row = $(this).closest('.buoi-thuc-hanh-row');
            var start = parseInt($(this).data('start'), 10);
            row.find('.tiet-thuc-hanh').prop('checked', false);
            for (var i = start; i < start + 3 && i <= 16; i++) {
                row.find('.tiet-thuc-hanh[value="' + i + '"]').prop('checked', true);
            }
            syncAllTietThucHanh();
        });

        // Thêm buổi lý thuyết
        var buoiLtCounter = 0;
        $('#btn-them-buoi-ly-thuyet').on('click', function() {
            var tpl = document.getElementById('tpl-buoi-ly-thuyet');
            var clone = $(tpl.content.cloneNode(true));
            buoiLtCounter++;
            var rowNum = $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').length;
            clone.find('.buoi-lt-label').text('Buổi ' + (rowNum + 1));
            var cbHtml = '';
            for (var i = 1; i <= 16; i++) {
                cbHtml += '<div class="form-check form-check-inline"><input class="form-check-input tiet-ly-thuyet" type="checkbox" value="' + i + '" id="lt_' + rowNum + '_' + i + '"><label class="form-check-label small" for="lt_' + rowNum + '_' + i + '">T' + i + '</label></div>';
            }
            clone.find('.tiet-lt-checkboxes').html(cbHtml);
            $('#buoi-ly-thuyet-list').append(clone);
            $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').each(function(i) {
                $(this).find('.buoi-lt-label').first().text('Buổi ' + (i + 1));
            });
            $('#buoi-ly-thuyet-list .remove-buoi-lt').removeClass('d-none');
        });
        $(document).on('click', '.remove-buoi-lt', function() {
            if ($('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').length <= 1) return;
            $(this).closest('.buoi-ly-thuyet-row').remove();
            $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').each(function(i) {
                $(this).find('.buoi-lt-label').first().text('Buổi ' + (i + 1));
            });
            if ($('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').length === 1) {
                $('#buoi-ly-thuyet-list .remove-buoi-lt').addClass('d-none');
            }
        });

        // Thêm buổi thực hành
        $('#btn-them-buoi-thuc-hanh').on('click', function() {
            var tpl = document.getElementById('tpl-buoi-thuc-hanh');
            var clone = $(tpl.content.cloneNode(true));
            var rowNum = $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').length;
            clone.find('.buoi-th-label').text('Buổi TH ' + (rowNum + 1));
            var cbHtml = '';
            for (var i = 1; i <= 16; i++) {
                cbHtml += '<div class="form-check form-check-inline"><input class="form-check-input tiet-thuc-hanh" type="checkbox" value="' + i + '" id="th_' + rowNum + '_' + i + '"><label class="form-check-label small" for="th_' + rowNum + '_' + i + '">T' + i + '</label></div>';
            }
            clone.find('.tiet-th-checkboxes').html(cbHtml);
            $('#buoi-thuc-hanh-list').append(clone);
            $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').each(function(i) {
                $(this).find('.buoi-th-label').first().text('Buổi TH ' + (i + 1));
            });
            $('#buoi-thuc-hanh-list .remove-buoi-th').removeClass('d-none');
        });
        $(document).on('click', '.remove-buoi-th', function() {
            if ($('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').length <= 1) return;
            $(this).closest('.buoi-thuc-hanh-row').remove();
            $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').each(function(i) {
                $(this).find('.buoi-th-label').first().text('Buổi TH ' + (i + 1));
            });
            if ($('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').length === 1) {
                $('#buoi-thuc-hanh-list .remove-buoi-th').addClass('d-none');
            }
        });

        $('#btnOpenCreateOffering').on('click', function() {
            $('#offering_id').val('');
            $('#createCourseOfferingModal').removeClass('modal-edit');
            $('#createCourseOfferingModalLabel').text('Tạo học phần mới');
            $('#submitCourseOfferingBtn').text('Tạo học phần');
        });

        // Chỉnh sửa học phần
        $(document).on('click', '.edit-offering-btn', function() {
            var id = $(this).data('id');
            $('#offering_id').val(id);
            $('#createCourseOfferingModal').addClass('modal-edit');
            $('#createCourseOfferingModalLabel').text('Chỉnh sửa học phần');
            $('#submitCourseOfferingBtn').text('Cập nhật');
            $.ajax({
                url: '{{ url("admin/course-offerings") }}/' + id,
                type: 'GET',
                success: function(res) {
                    $('#ten_hoc_phan').val(res.ten_hoc_phan);
                    $('#class_room_id').val(res.class_room_id);
                    $('#subject_id').val(res.subject_id);
                    $('#teacher_id').val(res.teacher_id);
                    $('#si_so_lop').val(res.si_so_lop);
                    $('#ngay_mo_dang_ky').val(res.ngay_mo_dang_ky);
                    $('#ngay_ket_thuc_dang_ky').val(res.ngay_ket_thuc_dang_ky);
                    $('#ngay_bat_dau_hoc').val(res.ngay_bat_dau_hoc);
                    $('#ngay_ket_thuc_hoc').val(res.ngay_ket_thuc_hoc);

                    var thuLt = res.thu_ly_thuyet || [];
                    var tietLt = res.tiet_ly_thuyet || [];
                    var thiLt = res.ngay_thi_ly_thuyet_buoi_thu || [];
                    while ($('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').length > 1) {
                        $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').last().find('.remove-buoi-lt').click();
                    }
                    for (var i = 1; i < thuLt.length; i++) {
                        $('#btn-them-buoi-ly-thuyet').click();
                    }
                    $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').each(function(idx) {
                        if (idx >= thuLt.length) return;
                        var row = $(this);
                        row.find('select[name="thu_ly_thuyet[]"]').val(thuLt[idx]);
                        row.find('input[name="ngay_thi_ly_thuyet_buoi_thu[]"]').val(thiLt[idx] || '');
                        var tietStr = (tietLt[idx] || '').toString();
                        row.find('.tiet-ly-thuyet').prop('checked', false);
                        if (tietStr) {
                            tietStr.split(',').forEach(function(t) {
                                row.find('.tiet-ly-thuyet[value="' + t.trim() + '"]').prop('checked', true);
                            });
                        }
                        row.find('.buoi-lt-label').first().text('Buổi ' + (idx + 1));
                    });
                    if (thuLt.length > 1) $('#buoi-ly-thuyet-list .remove-buoi-lt').removeClass('d-none');

                    var thuTh = res.thu_thuc_hanh || [];
                    var tietTh = res.tiet_thuc_hanh || [];
                    var thiTh = res.ngay_thi_thuc_hanh_buoi_thu || [];
                    while ($('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').length > 1) {
                        $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').last().find('.remove-buoi-th').click();
                    }
                    for (var j = 1; j < thuTh.length; j++) {
                        $('#btn-them-buoi-thuc-hanh').click();
                    }
                    $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').each(function(idx) {
                        if (idx >= thuTh.length) return;
                        var rowTh = $(this);
                        rowTh.find('select[name="thu_thuc_hanh[]"]').val(thuTh[idx] || '');
                        rowTh.find('input[name="ngay_thi_thuc_hanh_buoi_thu[]"]').val(thiTh[idx] || '');
                        var tietStrTh = (tietTh[idx] || '').toString();
                        rowTh.find('.tiet-thuc-hanh').prop('checked', false);
                        if (tietStrTh) {
                            tietStrTh.split(',').forEach(function(t) {
                                rowTh.find('.tiet-thuc-hanh[value="' + t.trim() + '"]').prop('checked', true);
                            });
                        }
                        rowTh.find('.buoi-th-label').first().text('Buổi TH ' + (idx + 1));
                    });
                    if (thuTh.length > 1) $('#buoi-thuc-hanh-list .remove-buoi-th').removeClass('d-none');
                    syncAllTietLyThuyet();
                    syncAllTietThucHanh();

                    var modal = new bootstrap.Modal(document.getElementById('createCourseOfferingModal'));
                    modal.show();
                },
                error: function() {
                    alert('Không thể tải thông tin học phần.');
                }
            });
        });

        // Submit form tạo / cập nhật học phần
        $('#createCourseOfferingForm').on('submit', function(e) {
            e.preventDefault();
            syncAllTietLyThuyet();
            syncAllTietThucHanh();
            var invalidLt = false;
            $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').each(function(i) {
                if (!$(this).find('.tiet-lt-hidden').val()) {
                    invalidLt = true;
                }
            });
            if (invalidLt) {
                alert('Vui lòng chọn 3 tiết liên tiếp cho từng buổi lý thuyết (dùng nút 1-3, 4-6, 7-9, 10-12 hoặc 13-15).');
                return;
            }
            var data = $(this).serializeArray();
            var offeringId = $('#offering_id').val();
            var url = offeringId
                ? '{{ url("admin/course-offerings") }}/' + offeringId
                : '{{ route("admin.course-offerings.store") }}';
            var method = offeringId ? 'PUT' : 'POST';
            $.ajax({
                url: url,
                type: method,
                data: $.param(data),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('createCourseOfferingModal'));
                    if (modal) modal.hide();
                    $('#offering_id').val('');
                    $('#createCourseOfferingForm')[0].reset();
                    $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row:not(:first)').remove();
                    $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row .tiet-ly-thuyet').prop('checked', false);
                    $('#buoi-ly-thuyet-list .tiet-lt-hidden').val('');
                    $('#buoi-ly-thuyet-list .remove-buoi-lt').addClass('d-none');
                    $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row:not(:first)').remove();
                    $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row .tiet-thuc-hanh').prop('checked', false);
                    $('#buoi-thuc-hanh-list .tiet-th-hidden').val('');
                    $('#buoi-thuc-hanh-list .remove-buoi-th').addClass('d-none');
                    table.ajax.reload(null, false);
                    alert(res.message || 'Lưu thành công!');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Có lỗi xảy ra.';
                    var errors = xhr.responseJSON?.errors || {};
                    if (Object.keys(errors).length) {
                        msg = Object.values(errors).flat().join('\n');
                    }
                    alert(msg);
                }
            });
        });
    });
</script>
@endpush

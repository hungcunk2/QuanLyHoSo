@extends('layouts.admin')

@section('title', 'Quản lý lớp học')
@section('page-title', 'Quản lý lớp học')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                        <h5 class="fw-bold">Quản lý lớp học</h5>
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
                <div class="col-md-12">
                    <form id="quick-action-form" class="form-disabled d-flex gap-3 align-items-center">
                        @csrf
                        <select name="action_type" class="form-select" id="quick-action-type" style="width:150px">
                            <option value="">No Action</option>
                            <option value="delete">Xóa đã chọn</option>
                        </select>
                        <button id="quick-action-apply" class="btn btn-primary" disabled>Áp dụng</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="d-flex align-items-center gap-3 justify-content-end">
                    <div class="d-flex justify-content-end">
                        <div class="input-group input-group-search ms-2">
                            <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control dt-search" placeholder="Search..." 
                                aria-label="Search" aria-describedby="addon-wrapping" aria-controls="classesTable">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive mt-3">
            <table id="classesTable" class="table table-striped border">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="select-all-table" class="form-check-input" onclick="selectAllTable(this)">
                        </th>
                        <th>Mã lớp</th>
                        <th>Tên lớp</th>
                        <th>Giáo viên chủ nhiệm</th>
                        <th>Môn học</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Class -->
<div class="modal fade" id="editClassModal" tabindex="-1" aria-labelledby="editClassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editClassModalLabel">Sửa thông tin lớp học</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editClassForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_class_id" name="id">
                    <div class="mb-3">
                        <label for="edit_ma_lop" class="form-label">Mã lớp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ma_lop" name="ma_lop" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_ten_lop" class="form-label">Tên lớp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ten_lop" name="ten_lop" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_giao_vien_chu_nhiem_id" class="form-label">Giáo viên chủ nhiệm</label>
                        <select class="form-select" id="edit_giao_vien_chu_nhiem_id" name="giao_vien_chu_nhiem_id">
                            <option value="">-- Chọn giáo viên --</option>
                            @foreach(\App\Models\Teacher::all() as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->ho_ten }} ({{ $teacher->msgv }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_subject_id" class="form-label">Môn học</label>
                        <select class="form-select" id="edit_subject_id" name="subject_id">
                            <option value="">-- Chọn môn học --</option>
                            @foreach(\App\Models\Subject::all() as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->ten_mon_hoc }} ({{ $subject->ma_mon_hoc }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete Confirmation -->
<div class="modal fade" id="deleteClassModal" tabindex="-1" aria-labelledby="deleteClassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteClassModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa lớp học này không?</p>
                <p class="text-danger"><strong>Hành động này không thể hoàn tác!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteClassBtn">Xóa</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#classesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.classes.data") }}',
                type: 'GET',
                data: function(d) {
                    d.search = $('.dt-search').val();
                }
            },
            columns: [
                {
                    data: 'check',
                    name: 'check',
                    orderable: false,
                    searchable: false,
                    width: '50px'
                },
                {
                    data: 'ma_lop',
                    name: 'ma_lop'
                },
                {
                    data: 'ten_lop',
                    name: 'ten_lop'
                },
                {
                    data: 'giao_vien_chu_nhiem',
                    name: 'giao_vien_chu_nhiem',
                    orderable: false
                },
                {
                    data: 'mon_hoc',
                    name: 'mon_hoc',
                    orderable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[1, 'asc']],
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
                emptyTable: "Không có dữ liệu",
                zeroRecords: "Không tìm thấy kết quả"
            },
            dom: '<"row align-items-center"><"table-responsive my-3 mt-3 mb-2 pb-1" rt><"row align-items-center data_table_widgets" <"col-md-6" <"d-flex align-items-center flex-wrap gap-3" l i>><"col-md-6" p>><"clear">'
        });
        
        $('.dt-search').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Edit button click
        $(document).on('click', '.edit-btn', function() {
            var classId = $(this).data('id');
            
            $.ajax({
                url: '{{ url("admin/classes") }}/' + classId,
                type: 'GET',
                success: function(response) {
                    $('#edit_class_id').val(response.id);
                    $('#edit_ma_lop').val(response.ma_lop);
                    $('#edit_ten_lop').val(response.ten_lop);
                    $('#edit_giao_vien_chu_nhiem_id').val(response.giao_vien_chu_nhiem_id);
                    $('#edit_subject_id').val(response.subject_id);
                    
                    var editModal = new bootstrap.Modal(document.getElementById('editClassModal'));
                    editModal.show();
                },
                error: function() {
                    alert('Không thể tải thông tin lớp học!');
                }
            });
        });

        // Update form submit
        $('#editClassForm').on('submit', function(e) {
            e.preventDefault();
            var classId = $('#edit_class_id').val();
            var formData = $(this).serialize();

            $.ajax({
                url: '{{ url("admin/classes") }}/' + classId,
                type: 'PUT',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    var editModal = bootstrap.Modal.getInstance(document.getElementById('editClassModal'));
                    editModal.hide();
                    table.ajax.reload();
                    alert('Cập nhật thành công!');
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON?.errors || {};
                    var errorMsg = 'Có lỗi xảy ra:\n';
                    for (var field in errors) {
                        errorMsg += errors[field][0] + '\n';
                    }
                    alert(errorMsg);
                }
            });
        });

        // Delete button click
        var deleteClassId = null;
        $(document).on('click', '.delete-btn', function() {
            deleteClassId = $(this).data('id');
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteClassModal'));
            deleteModal.show();
        });

        // Confirm delete
        $('#confirmDeleteClassBtn').on('click', function() {
            if (deleteClassId) {
                $.ajax({
                    url: '{{ url("admin/classes") }}/' + deleteClassId,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        var deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteClassModal'));
                        deleteModal.hide();
                        table.ajax.reload();
                        alert('Xóa thành công!');
                        deleteClassId = null;
                    },
                    error: function() {
                        alert('Không thể xóa lớp học!');
                    }
                });
            }
        });
    });
</script>
@endpush

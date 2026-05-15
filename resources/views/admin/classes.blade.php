@extends('layouts.admin')

@section('title', 'Quản lý phòng học')
@section('page-title', '')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3 admin-page-header">
                        <h5 class="fw-bold mb-0">Quản lý phòng học</h5>
                        <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal" data-bs-target="#createClassModal">
                            <i class="fas fa-plus"></i> Tạo phòng học mới
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row gy-3 admin-toolbar">
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

        <div class="table-responsive mt-3 admin-table-wrap">
            <table id="classesTable" class="table table-striped border w-100 mb-0">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="select-all-table" class="form-check-input" onclick="selectAllTable(this)">
                        </th>
                        <th>Mã phòng</th>
                        <th>Tên phòng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Create Class -->
<div class="modal fade" id="createClassModal" tabindex="-1" aria-labelledby="createClassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createClassModalLabel">Tạo phòng học mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createClassForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="create_ma_lop" class="form-label">Mã phòng</label>
                        <input type="text" class="form-control" id="create_ma_lop" name="ma_lop">
                    </div>
                    <div class="mb-3">
                        <label for="create_ten_lop" class="form-label">Tên phòng <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_ten_lop" name="ten_lop" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Tạo mới</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Class -->
<div class="modal fade" id="editClassModal" tabindex="-1" aria-labelledby="editClassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editClassModalLabel">Sửa thông tin phòng học</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editClassForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_class_id" name="id">
                    <div class="mb-3">
                        <label for="edit_ma_lop" class="form-label">Mã phòng <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ma_lop" name="ma_lop" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_ten_lop" class="form-label">Tên phòng <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ten_lop" name="ten_lop" required>
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
                <p>Bạn có chắc chắn muốn xóa phòng học này không?</p>
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
    function selectAllTable(checkbox) {
        const isChecked = checkbox.checked;
        $('#classesTable').find('.row-checkbox').prop('checked', isChecked);
        updateQuickAction();
    }

    function updateQuickAction() {
        const checkedCount = $('#classesTable').find('.row-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#quick-action-type').prop('disabled', false);
            $('#quick-action-apply').prop('disabled', $('#quick-action-type').val() === '');
        } else {
            $('#quick-action-type').prop('disabled', true).val('');
            $('#quick-action-apply').prop('disabled', true);
            $('#select-all-table').prop('checked', false);
        }
    }

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
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            responsive: false,
            scrollX: false,
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
            dom: AdminDT.dom
        });
        
        $('.dt-search').on('keyup', function() {
            table.search(this.value).draw();
        });

        table.on('draw', function() {
            $('#select-all-table').prop('checked', false);
            updateQuickAction();
        });

        $(document).on('change', '#classesTable .row-checkbox', function() {
            const total = $('#classesTable').find('.row-checkbox').length;
            const checked = $('#classesTable').find('.row-checkbox:checked').length;
            $('#select-all-table').prop('checked', total > 0 && total === checked);
            updateQuickAction();
        });

        $('#quick-action-type').on('change', function() {
            updateQuickAction();
        });

        $('#quick-action-form').on('submit', function(e) {
            e.preventDefault();
        });
        $('#quick-action-apply').on('click', function(e) {
            e.preventDefault();
            const action = $('#quick-action-type').val();
            const ids = $('#classesTable').find('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
            if (!action || ids.length === 0) return;
            if (action === 'delete') {
                if (!confirm('Bạn có chắc muốn xóa ' + ids.length + ' phòng học đã chọn?')) return;
                $.ajax({
                    url: '{{ route("admin.classes.bulk-delete") }}',
                    type: 'POST',
                    data: { selected_ids: ids },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        table.ajax.reload();
                        $('#quick-action-type').val('');
                        updateQuickAction();
                        alert(res.message || 'Đã xóa thành công!');
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Không thể xóa hàng loạt!';
                        alert(msg);
                    }
                });
            }
        });

        // Reset create form when modal is closed
        $('#createClassModal').on('hidden.bs.modal', function() {
            $('#createClassForm')[0].reset();
        });

        $('#createClassModal').on('shown.bs.modal', function() {
            const $ma = $('#create_ma_lop');
            if ($ma.val()) return;
            $.ajax({
                url: '{{ route("admin.classes.next-ma-lop") }}',
                type: 'GET',
                success: function(res) {
                    if (res?.next_ma_lop) $ma.val(res.next_ma_lop);
                }
            });
        });

        // Create form submit
        $('#createClassForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: '{{ route("admin.classes.store") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    var createModal = bootstrap.Modal.getInstance(document.getElementById('createClassModal'));
                    createModal.hide();
                    table.ajax.reload();
                    alert('Tạo phòng học mới thành công!');
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON?.errors || {};
                    var errorMsg = '';
                    
                    if (Object.keys(errors).length === 0) {
                        errorMsg = xhr.responseJSON?.message || 'Có lỗi xảy ra khi tạo phòng học mới!';
                    } else {
                        errorMsg = 'Vui lòng kiểm tra lại thông tin:\n\n';
                        for (var field in errors) {
                            var fieldName = field === 'ma_lop' ? 'Mã phòng' :
                                          field === 'ten_lop' ? 'Tên phòng' : field;
                            errorMsg += '• ' + fieldName + ': ' + errors[field][0] + '\n';
                        }
                    }
                    alert(errorMsg);
                }
            });
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
                    
                    var editModal = new bootstrap.Modal(document.getElementById('editClassModal'));
                    editModal.show();
                },
                error: function() {
                    alert('Không thể tải thông tin phòng học!');
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
                        alert('Không thể xóa phòng học!');
                    }
                });
            }
        });
    });
</script>
@endpush

@push('scripts')
<script>
    function selectAllLopsTable(checkbox) {
        var isChecked = checkbox.checked;
        $('#lopsTable').find('.lop-row-checkbox').prop('checked', isChecked);
        updateLopQuickAction();
    }

    function updateLopQuickAction() {
        var checkedCount = $('#lopsTable').find('.lop-row-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#lop-quick-action-type').prop('disabled', false);
            $('#lop-quick-action-apply').prop('disabled', $('#lop-quick-action-type').val() === '');
        } else {
            $('#lop-quick-action-type').prop('disabled', true).val('');
            $('#lop-quick-action-apply').prop('disabled', true);
            $('#select-all-lops-table').prop('checked', false);
        }
    }

    $(document).ready(function() {
        if (!$('#lopsTable').length) {
            return;
        }
        var lopsTable = $('#lopsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.lops.data") }}',
                type: 'GET',
                data: function(d) {
                    d.search = $('.dt-search-lop').val();
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
                { data: 'ma_lop', name: 'ma_lop' },
                { data: 'ten_lop', name: 'ten_lop' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
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

        $('.dt-search-lop').on('keyup', function() {
            lopsTable.search(this.value).draw();
        });

        lopsTable.on('draw', function() {
            $('#select-all-lops-table').prop('checked', false);
            updateLopQuickAction();
        });

        $(document).on('change', '#lopsTable .lop-row-checkbox', function() {
            var total = $('#lopsTable').find('.lop-row-checkbox').length;
            var checked = $('#lopsTable').find('.lop-row-checkbox:checked').length;
            $('#select-all-lops-table').prop('checked', total > 0 && total === checked);
            updateLopQuickAction();
        });

        $('#lop-quick-action-type').on('change', function() {
            updateLopQuickAction();
        });

        $('#lop-quick-action-apply').on('click', function(e) {
            e.preventDefault();
            var action = $('#lop-quick-action-type').val();
            var ids = $('#lopsTable').find('.lop-row-checkbox:checked').map(function() { return $(this).val(); }).get();
            if (!action || ids.length === 0) return;
            if (action === 'delete') {
                if (!confirm('Bạn có chắc muốn xóa ' + ids.length + ' lớp đã chọn?')) return;
                $.ajax({
                    url: '{{ route("admin.lops.bulk-delete") }}',
                    type: 'POST',
                    data: { selected_ids: ids },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        lopsTable.ajax.reload();
                        $('#lop-quick-action-type').val('');
                        updateLopQuickAction();
                        alert(res.message || 'Đã xóa thành công!');
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON?.message || 'Không thể xóa hàng loạt!';
                        alert(msg);
                    }
                });
            }
        });

        $('#btnOpenCreateLop').on('click', function() {
            $('#lopForm')[0].reset();
            $('#lop_id').val('');
            $('#lopModalLabel').text('Tạo lớp mới');
            $('#lopSubmitBtn').text('Tạo mới');
            $('#lop_ma_lop').prop('readonly', false);
        });

        $('#lopForm').on('submit', function(e) {
            e.preventDefault();
            var id = $('#lop_id').val();
            var url = id ? '{{ url("admin/lops") }}/' + id : '{{ route("admin.lops.store") }}';
            var method = id ? 'PUT' : 'POST';
            $.ajax({
                url: url,
                type: method,
                data: $(this).serialize(),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    bootstrap.Modal.getInstance(document.getElementById('lopModal')).hide();
                    lopsTable.ajax.reload(null, false);
                    alert(res.message || 'Đã lưu.');
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

        $(document).on('click', '.edit-lop-btn', function() {
            var id = $(this).data('id');
            $.ajax({
                url: '{{ url("admin/lops") }}/' + id,
                type: 'GET',
                success: function(res) {
                    $('#lop_id').val(res.id);
                    $('#lop_ma_lop').val(res.ma_lop);
                    $('#lop_ten_lop').val(res.ten_lop);
                    $('#lopModalLabel').text('Sửa thông tin lớp');
                    $('#lopSubmitBtn').text('Lưu thay đổi');
                    $('#lop_ma_lop').prop('readonly', false);
                    new bootstrap.Modal(document.getElementById('lopModal')).show();
                },
                error: function() {
                    alert('Không tải được dữ liệu lớp.');
                }
            });
        });

        var deleteLopId = null;
        $(document).on('click', '.delete-lop-btn', function() {
            deleteLopId = $(this).data('id');
            $('#deleteLopMa').text($(this).data('ma') || '');
            new bootstrap.Modal(document.getElementById('deleteLopModal')).show();
        });
        $('#confirmDeleteLopBtn').on('click', function() {
            if (!deleteLopId) return;
            $.ajax({
                url: '{{ url("admin/lops") }}/' + deleteLopId,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    bootstrap.Modal.getInstance(document.getElementById('deleteLopModal')).hide();
                    deleteLopId = null;
                    lopsTable.ajax.reload(null, false);
                    alert(res.message || 'Đã xóa.');
                },
                error: function() {
                    alert('Không xóa được lớp.');
                }
            });
        });
    });
</script>
@endpush

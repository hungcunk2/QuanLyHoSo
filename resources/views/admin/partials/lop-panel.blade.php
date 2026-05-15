<div class="card">
    <div class="card-body">
        <div class="row gy-3 admin-toolbar">
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="col-md-12">
                    <form id="lop-quick-action-form" class="form-disabled d-flex gap-2 align-items-center quick-action-form">
                        @csrf
                        <select name="action_type" class="form-select form-select-sm" id="lop-quick-action-type">
                            <option value="">No Action</option>
                            <option value="delete">Xóa đã chọn</option>
                        </select>
                        <button type="button" id="lop-quick-action-apply" class="btn btn-primary" disabled>Áp dụng</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="d-flex align-items-center gap-3 justify-content-end">
                    <div class="d-flex justify-content-end">
                        <div class="input-group input-group-search ms-2">
                            <span class="input-group-text" id="lop-search-addon"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control dt-search-lop" placeholder="Search..."
                                aria-label="Search" aria-describedby="lop-search-addon" aria-controls="lopsTable">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive mt-3 admin-table-wrap">
            <table id="lopsTable" class="table table-striped border w-100 mb-0">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="select-all-lops-table" class="form-check-input" onclick="selectAllLopsTable(this)">
                        </th>
                        <th>Mã lớp</th>
                        <th>Tên lớp</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="lopModal" tabindex="-1" aria-labelledby="lopModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lopModalLabel">Tạo lớp mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form id="lopForm">
                <input type="hidden" id="lop_id" name="lop_id" value="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="lop_ma_lop" class="form-label">Mã lớp</label>
                        <input type="text" class="form-control" id="lop_ma_lop" name="ma_lop" maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="lop_ten_lop" class="form-label">Tên lớp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="lop_ten_lop" name="ten_lop" required maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="lopSubmitBtn">Tạo mới</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteLopModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa lớp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Xóa lớp <strong id="deleteLopMa"></strong>?</p>
                <p class="text-danger small mt-2 mb-0">Hành động không hoàn tác.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteLopBtn">Xóa</button>
            </div>
        </div>
    </div>
</div>

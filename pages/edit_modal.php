<!-- Edit Modal -->
<div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="edit_category">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-3">
                        <!-- Serial No -->
                        <div class="col-4">
                            <label class="form-label">Serial No</label>
                            <input type="number" name="serial_no" class="form-control"
                                value="<?= htmlspecialchars($row['serial_no'] ?? '') ?>" required>
                        </div>

                        <!-- Category -->
                        <div class="col-8">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control"
                                value="<?= htmlspecialchars($cat_name) ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <!-- Subcategory Serial -->
                        <div class="col-4">
                            <label class="form-label">Serial No</label>
                            <input type="number" name="subcategory_serial" class="form-control"
                                value="<?= htmlspecialchars($row['subcategory_serial'] ?? '') ?>">
                        </div>

                        <!-- Subcategory -->
                        <div class="col-8">
                            <label class="form-label">Subcategory</label>
                            <input type="text" name="sub_category" class="form-control"
                                value="<?= htmlspecialchars($row['sub_category'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <!-- Category Keywords -->
                        <label class="form-label">Category Keywords</label>
                        <textarea name="category_keywords" class="form-control" rows="3"><?= htmlspecialchars($row['category_keywords'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

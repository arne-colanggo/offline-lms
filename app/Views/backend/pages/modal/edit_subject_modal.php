<div class="modal fade" id="edit_subject_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    style="display: none;" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= route_to('update.subject') ?>" method="post" id="edit_subject_form">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
                <input type="hidden" name="subject_id">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">

                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        ×
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="">Grade Level</label>
                        <select name="parent_grade_level" id="parent_grade_level" class="form-control">
                            <option value="">No Grade Level</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Subject</label>
                        <input type="text" class="form-control" id="subject_name" name="subject_name"
                            placeholder="Enter Subject">
                        <span class="text-danger error-text subject_name_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="">Description</label>
                        <textarea class="form-control" name="description" id="description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" class="btn btn-primary action" id="save_subject_btn">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
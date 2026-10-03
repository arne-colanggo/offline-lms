<div class="modal fade" id="edit_grade_level_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    style="display: none;" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= route_to('update.gradelevel') ?>" method="post" id="edit_grade_level_form">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
                <input type="hidden" name="grade_level_id" id="grade_level_id">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title text-white" id="myLargeModalLabel">

                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        ×
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="">Grade Level Name</label>
                        <input type="text" class="form-control" id="grade_level_name" name="grade_level_name"
                            placeholder="Enter Grade Level">
                        <span class="text-danger error-text grade_level_name_error"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" class="btn btn-primary action">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
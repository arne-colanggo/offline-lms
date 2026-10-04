<div class="modal fade" id="add_section_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    style="display: none;" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= route_to('post.section') ?>" method="post" id="add_section_form">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title text-white" id="myLargeModalLabel">

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
                        <label for="">Section Name</label>
                        <input type="text" class="form-control" id="section_name" name="section_name"
                            placeholder="Enter Section">
                        <span class="text-danger error-text section_name_error"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" class="btn btn-primary action" id="save_section_btn">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
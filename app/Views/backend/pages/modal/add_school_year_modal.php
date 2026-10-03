<div class="modal fade" id="add_school_year_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    style="display: none;" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= route_to('post.schoolyear') ?>" method="post" id="add_school_year_form">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="myLargeModalLabel">

                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        ×
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="">School Year</label>
                        <input type="text" class="form-control" id="school_year_name" name="school_year_name"
                            placeholder="Enter School Year">
                        <span class="text-danger error-text school_year_name_error"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" class="btn btn-primary action" id="add_school_year_btn">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
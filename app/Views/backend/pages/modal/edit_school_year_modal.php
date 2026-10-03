<div class="modal fade" id="edit_school_year_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    style="display: none;" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= route_to('update.schoolyear') ?>" method="post" id="edit_school_year_form">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
                <input type="hidden" name="school_year_id" id="school_year_id">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">

                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        ×
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="">School Year Name</label>
                        <input type="text" class="form-control" id="school_year_name" name="school_year_name"
                            placeholder="Enter School Year">
                        <span class="text-danger error-text school_year_name_error"></span>
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
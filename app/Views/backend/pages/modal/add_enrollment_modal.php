<div class="modal fade" id="add_enrollment_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    style="display: none;" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= route_to('post-single-enrollment')?>" method="post" id="add_enrollment_form">
                <input type="hidden" name="<?= csrf_token()?>" value = "<?= csrf_hash()?>" class="ci_csrf_data">
                <input type="hidden" name="studentid" id="studentid">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        ×
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="">Section</label>
                        <select name="section" id="section" class="form-control">
                            
                        </select>
                        <span class="text-danger error-text section_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="">Enrollment By</label>
                        <select name="enrollmenttype" id="enrollmenttype" class="form-control">
                            <option value="1">Academic Year</option>
                            <option value="2">1st Semester</option>
                            <option value="3">2nd Semester</option>  
                        </select>
                        <span class="text-danger error-text enrollmenttype_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="">SchoolYear</label>
                        <select name="schoolyear" id="schoolyear" class="form-control">
                            
                        </select>
                        <span class="text-danger error-text schoolyear_error"></span>
                    </div>                    
                    <div class="form-group">
                        <label for="">Date Enrolled</label>
                        <input type="date" class="form-control" id="dateenrolled" name="dateenrolled" >
                        <span class="text-danger error-text dateenrolled_error"></span>
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
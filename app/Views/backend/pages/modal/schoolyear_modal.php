<?php

/*
|--------------------------------------------------------------------------
| School Year Management Modal
|--------------------------------------------------------------------------
|
| Expected variable:
|
| $schoolYears
|
*/
?>

<div class="modal fade" id="addSchoolYearModal" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-md modal-dialog-centered" role="document">

        <div class="modal-content">


            <!-- ================================================= -->
            <!-- HEADER -->
            <!-- ================================================= -->

            <div class="modal-header bg-primary">

                <h5 class="modal-title text-white">

                    <i class="fas fa-calendar-alt mr-2"></i>

                    School Year

                </h5>

                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">

                    <span>&times;</span>

                </button>

            </div>


            <!-- ================================================= -->
            <!-- BODY -->
            <!-- ================================================= -->

            <div class="modal-body">


                <!-- ADD SCHOOL YEAR -->

                <div class="mb-4">

                    <label>

                        <strong>Add School Year</strong>

                    </label>

                    <div class="input-group">

                        <div class="input-group-prepend">

                            <span class="input-group-text">

                                <i class="fas fa-calendar-plus"></i>

                            </span>

                        </div>

                        <input type="text" class="form-control" id="newSchoolYear" name="school_year"
                            placeholder="e.g. 2026-2027" maxlength="9" autocomplete="off">

                        <div class="input-group-append">

                            <button type="button" class="btn btn-primary" id="btnAddSchoolYear">

                                <i class="fas fa-plus mr-1"></i>

                                Add

                            </button>

                        </div>

                    </div>

                    <small class="form-text text-muted">

                        Format: YYYY-YYYY

                    </small>

                    <span class="text-danger error-text schoolyear_error"></span>

                </div>


                <!-- ================================================= -->
                <!-- SCHOOL YEAR LIST -->
                <!-- ================================================= -->

                <div class="d-flex justify-content-between
                            align-items-center mb-2">

                    <h6 class="font-weight-bold mb-0">

                        <i class="fas fa-list mr-1 text-primary"></i>

                        School Years

                    </h6>

                    <span class="badge badge-secondary">

                        <?= count($schoolYears ?? []) ?>

                    </span>

                </div>


                <div class="list-group school-year-list" style="max-height: 300px; overflow-y: auto;">


                    <?php if (!empty($schoolYears)): ?>

                        <?php foreach ($schoolYears as $schoolYear): ?>

                            <div class="list-group-item" data-id="<?= esc($schoolYear['id']) ?>">

                                <div class="d-flex
                                            justify-content-between
                                            align-items-center">


                                    <!-- SCHOOL YEAR -->

                                    <div>

                                        <div class="font-weight-bold">

                                            <?= esc($schoolYear['school_year']) ?>

                                        </div>


                                        <?php if ($schoolYear['status'] === 'active'): ?>

                                            <span class="badge badge-success">

                                                Active

                                            </span>

                                        <?php else: ?>

                                            <span class="badge badge-secondary">

                                                Inactive

                                            </span>

                                        <?php endif; ?>

                                    </div>


                                    <!-- ACTIONS -->

                                    <div class="btn-group">

                                        <button type="button" class="btn btn-sm btn-outline-primary edit-school-year"
                                            data-id="<?= esc($schoolYear['id']) ?>"
                                            data-school-year="<?= esc($schoolYear['school_year']) ?>"
                                            data-status="<?= esc($schoolYear['status']) ?>">

                                            <i class="fas fa-edit"></i>

                                        </button>


                                        <button type="button" class="btn btn-sm btn-outline-danger remove-school-year"
                                            data-id="<?= esc($schoolYear['id']) ?>"
                                            data-school-year="<?= esc($schoolYear['school_year']) ?>">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>


                    <?php else: ?>


                        <!-- EMPTY STATE -->

                        <div class="text-center text-muted py-4">

                            <i class="fas fa-calendar-times fa-2x mb-2"></i>

                            <div>

                                No school years added yet.

                            </div>

                        </div>


                    <?php endif; ?>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- FOOTER -->
            <!-- ================================================= -->

            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" data-dismiss="modal">

                    Close

                </button>

            </div>


        </div>

    </div>

</div>
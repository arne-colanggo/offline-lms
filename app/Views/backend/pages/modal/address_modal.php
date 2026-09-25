<?php

/*
|--------------------------------------------------------------------------
| Address Modal Component
|--------------------------------------------------------------------------
|
| Optional variables:
|
| $addressModalId
| $addressTarget
| $addressTitle
|
*/

$addressModalId = $addressModalId ?? 'addressModal';

$addressTarget = $addressTarget ?? '';

$addressTitle = $addressTitle ?? 'Select Address';
?>

<div class="modal fade address-modal" id="<?= esc($addressModalId) ?>" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-md modal-dialog-centered" role="document">

        <div class="modal-content">

            <!-- ================================================= -->
            <!-- HEADER -->
            <!-- ================================================= -->

            <div class="modal-header bg-primary">

                <h5 class="modal-title text-white">

                    <i class="fas fa-map-marker-alt mr-2"></i>

                    <?= esc($addressTitle) ?>

                </h5>

                <button type="button" class="close text-white" data-dismiss="modal">

                    <span>&times;</span>

                </button>

            </div>


            <!-- ================================================= -->
            <!-- BODY -->
            <!-- ================================================= -->

            <div class="modal-body">

                <div class="address-component">


                    <!-- REGION -->

                    <div class="form-group">

                        <label>

                            <strong>Region</strong>

                        </label>

                        <select class="form-control form-control-lg address-region">

                            <option value="">
                                Select Region
                            </option>

                        </select>

                    </div>


                    <!-- PROVINCE -->

                    <div class="form-group">

                        <label>

                            <strong>Province</strong>

                        </label>

                        <select class="form-control form-control-lg address-province" disabled>

                            <option value="">
                                Select Province
                            </option>

                        </select>

                    </div>


                    <!-- MUNICIPALITY -->

                    <div class="form-group">

                        <label>

                            <strong>
                                Municipality / City
                            </strong>

                        </label>

                        <select class="form-control form-control-lg address-municipality" disabled>

                            <option value="">
                                Select Municipality / City
                            </option>

                        </select>

                    </div>


                    <!-- BARANGAY SEARCH -->

                    <div class="form-group">

                        <label>

                            <strong>Barangay</strong>

                        </label>

                        <div class="input-group">

                            <div class="input-group-prepend">

                                <span class="input-group-text">

                                    <i class="fas fa-search"></i>

                                </span>

                            </div>

                            <input type="text" class="form-control address-barangay-search"
                                placeholder="Search barangay..." disabled>

                        </div>

                    </div>


                    <!-- BARANGAY LIST -->

                    <div class="list-group address-barangay-list" style="
                            max-height:250px;
                            overflow-y:auto;
                        ">

                        <div class="text-center text-muted py-4">

                            Select a municipality/city first.

                        </div>

                    </div>


                    <!-- SELECTED ADDRESS -->

                    <div class="card bg-light mt-4">

                        <div class="card-body">

                            <h6 class="font-weight-bold">

                                <i class="fas fa-map-marker-alt
                                           text-danger mr-2"></i>

                                Selected Address

                            </h6>


                            <div class="address-selected-preview
                                       text-muted">

                                No address selected.

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- FOOTER -->
            <!-- ================================================= -->

            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" data-dismiss="modal">

                    Cancel

                </button>

                <button type="button" class="btn btn-primary address-use" disabled>

                    <i class="fas fa-check mr-1"></i>

                    Use This Address

                </button>

            </div>

        </div>

    </div>

</div>
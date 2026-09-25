<script>

    (function ($) {

        'use strict';

        /*
        |--------------------------------------------------------------------------
        | ADDRESS MODAL
        |--------------------------------------------------------------------------
        */

        window.AddressModal = {

            instances: {},


            /*
            |--------------------------------------------------------------------------
            | INIT
            |--------------------------------------------------------------------------
            */

            init: function (modalId, options = {}) {

                const modal = $('#' + modalId);

                if (!modal.length) {
                    return;
                }


                /*
                | Prevent duplicate initialization
                */

                if (this.instances[modalId]) {
                    return this.instances[modalId];
                }


                const instance = {

                    modal: modal,

                    options: options,

                    selected: {

                        region_id: null,
                        region_name: '',

                        province_id: null,
                        province_name: '',

                        municipality_id: null,
                        municipality_name: '',

                        barangay_id: null,
                        barangay_name: ''

                    }

                };


                /*
                |--------------------------------------------------------------------------
                | ELEMENTS
                |--------------------------------------------------------------------------
                */

                instance.region =
                    modal.find('.address-region');

                instance.province =
                    modal.find('.address-province');

                instance.municipality =
                    modal.find('.address-municipality');

                instance.barangaySearch =
                    modal.find('.address-barangay-search');

                instance.barangayList =
                    modal.find('.address-barangay-list');

                instance.preview =
                    modal.find('.address-selected-preview');

                instance.useButton =
                    modal.find('.address-use');


                /*
                |--------------------------------------------------------------------------
                | LOAD REGIONS
                |--------------------------------------------------------------------------
                */

                instance.loadRegions = function () {

                    instance.region
                        .prop('disabled', true)
                        .html(
                            '<option>Loading regions...</option>'
                        );


                    $.getJSON(
                        '<?= route_to('get.regions') ?>'
                    )

                        .done(function (response) {

                            let html =
                                '<option value="">Select Region</option>';


                            if (
                                response.status &&
                                response.data
                            ) {

                                $.each(
                                    response.data,
                                    function (index, item) {

                                        html += `
                                    <option
                                        value="${item.region_id}"
                                        data-name="${item.region_name}">
                                        ${item.region_name}
                                    </option>
                                `;

                                    }
                                );

                            }


                            instance.region
                                .html(html)
                                .prop('disabled', false);

                        })

                        .fail(function () {

                            instance.region.html(
                                '<option>Error loading regions</option>'
                            );

                        });

                };


                /*
                |--------------------------------------------------------------------------
                | LOAD PROVINCES
                |--------------------------------------------------------------------------
                */

                instance.loadProvinces = function (regionId) {

                    instance.province
                        .prop('disabled', true)
                        .html(
                            '<option>Loading provinces...</option>'
                        );


                    $.getJSON(
                        '<?= base_url('admin/provinces') ?>/'
                        + regionId
                    )

                        .done(function (response) {

                            let html =
                                '<option value="">Select Province</option>';


                            $.each(
                                response.data,
                                function (index, item) {

                                    html += `
                                <option
                                    value="${item.province_id}"
                                    data-name="${item.province_name}">
                                    ${item.province_name}
                                </option>
                            `;

                                }
                            );


                            instance.province
                                .html(html)
                                .prop('disabled', false);

                        });

                };


                /*
                |--------------------------------------------------------------------------
                | LOAD MUNICIPALITIES
                |--------------------------------------------------------------------------
                */

                instance.loadMunicipalities =
                    function (provinceId) {

                        instance.municipality
                            .prop('disabled', true)
                            .html(
                                '<option>Loading municipalities...</option>'
                            );


                        $.getJSON(
                            '<?= base_url('admin/municipalities') ?>/'
                            + provinceId
                        )

                            .done(function (response) {

                                let html =
                                    '<option value="">Select Municipality / City</option>';


                                $.each(
                                    response.data,
                                    function (index, item) {

                                        html += `
                                    <option
                                        value="${item.municipality_id}"
                                        data-name="${item.municipality_name}">
                                        ${item.municipality_name}
                                    </option>
                                `;

                                    }
                                );


                                instance.municipality
                                    .html(html)
                                    .prop('disabled', false);

                            });

                    };


                /*
                |--------------------------------------------------------------------------
                | LOAD BARANGAYS
                |--------------------------------------------------------------------------
                */

                instance.loadBarangays =
                    function (municipalityId, search = '') {

                        instance.barangayList.html(`

                        <div class="text-center py-3">

                            <div
                                class="spinner-border
                                       spinner-border-sm
                                       text-primary"
                            ></div>

                            <span class="ml-2">
                                Loading barangays...
                            </span>

                        </div>

                    `);


                        $.ajax({

                            url:
                                '<?= base_url('admin/barangays') ?>/'
                                + municipalityId,

                            type: 'GET',

                            data: {
                                search: search
                            },

                            dataType: 'json'

                        })

                            .done(function (response) {

                                let html = '';


                                if (
                                    response.status &&
                                    response.data.length
                                ) {

                                    $.each(
                                        response.data,
                                        function (index, item) {

                                            html += `

                                        <button
                                            type="button"
                                            class="
                                                list-group-item
                                                list-group-item-action
                                                barangay-item
                                            "
                                            data-id="${item.barangay_id}"
                                            data-name="${item.barangay_name}"
                                        >

                                            <i
                                                class="
                                                    fas
                                                    fa-map-marker-alt
                                                    mr-2
                                                    text-muted
                                                "
                                            ></i>

                                            ${item.barangay_name}

                                        </button>

                                    `;

                                        }
                                    );

                                } else {

                                    html = `

                                <div
                                    class="
                                        text-center
                                        text-muted
                                        py-4
                                    "
                                >

                                    No barangay found.

                                </div>

                            `;

                                }


                                instance.barangayList.html(html);

                            });

                    };


                /*
                |--------------------------------------------------------------------------
                | RESET
                |--------------------------------------------------------------------------
                */

                instance.resetProvince = function () {

                    instance.province
                        .html(
                            '<option value="">Select Province</option>'
                        )
                        .prop('disabled', true);

                };


                instance.resetMunicipality = function () {

                    instance.municipality
                        .html(
                            '<option value="">Select Municipality / City</option>'
                        )
                        .prop('disabled', true);

                };


                instance.resetBarangay = function () {

                    instance.barangaySearch
                        .val('')
                        .prop('disabled', true);


                    instance.barangayList.html(`

                    <div
                        class="text-center text-muted py-4"
                    >

                        Select a municipality/city first.

                    </div>

                `);


                    instance.useButton
                        .prop('disabled', true);


                    instance.selected.barangay_id = null;

                    instance.selected.barangay_name = '';

                };


                /*
                |--------------------------------------------------------------------------
                | UPDATE PREVIEW
                |--------------------------------------------------------------------------
                */

                instance.updatePreview = function () {

                    const s = instance.selected;


                    if (!s.barangay_id) {

                        instance.preview.html(
                            'No address selected.'
                        );

                        return;

                    }


                    instance.preview.html(`

                    <div class="mb-1">

                        <strong>Region:</strong>

                        ${s.region_name}

                    </div>

                    <div class="mb-1">

                        <strong>Province:</strong>

                        ${s.province_name}

                    </div>

                    <div class="mb-1">

                        <strong>Municipality / City:</strong>

                        ${s.municipality_name}

                    </div>

                    <div>

                        <strong>Barangay:</strong>

                        ${s.barangay_name}

                    </div>

                `);

                };


                /*
                |--------------------------------------------------------------------------
                | REGION CHANGE
                |--------------------------------------------------------------------------
                */

                instance.region.on(
                    'change',
                    function () {

                        const option =
                            $(this).find(':selected');


                        const id =
                            $(this).val();


                        instance.selected.region_id =
                            id;

                        instance.selected.region_name =
                            option.data('name') || '';


                        instance.selected.province_id =
                            null;

                        instance.selected.municipality_id =
                            null;

                        instance.selected.barangay_id =
                            null;


                        instance.resetProvince();

                        instance.resetMunicipality();

                        instance.resetBarangay();


                        if (id) {

                            instance.loadProvinces(id);

                        }

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | PROVINCE CHANGE
                |--------------------------------------------------------------------------
                */

                instance.province.on(
                    'change',
                    function () {

                        const option =
                            $(this).find(':selected');


                        const id =
                            $(this).val();


                        instance.selected.province_id =
                            id;

                        instance.selected.province_name =
                            option.data('name') || '';


                        instance.resetMunicipality();

                        instance.resetBarangay();


                        if (id) {

                            instance.loadMunicipalities(id);

                        }

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | MUNICIPALITY CHANGE
                |--------------------------------------------------------------------------
                */

                instance.municipality.on(
                    'change',
                    function () {

                        const option =
                            $(this).find(':selected');


                        const id =
                            $(this).val();


                        instance.selected.municipality_id =
                            id;

                        instance.selected.municipality_name =
                            option.data('name') || '';


                        instance.resetBarangay();


                        if (id) {

                            instance.barangaySearch
                                .prop('disabled', false)
                                .focus();


                            instance.loadBarangays(id);

                        }

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | BARANGAY SEARCH
                |--------------------------------------------------------------------------
                */

                let searchTimer;


                instance.barangaySearch.on(
                    'keyup',
                    function () {

                        const search =
                            $(this).val();


                        const municipalityId =
                            instance.selected.municipality_id;


                        if (!municipalityId) {
                            return;
                        }


                        clearTimeout(searchTimer);


                        searchTimer =
                            setTimeout(function () {

                                instance.loadBarangays(
                                    municipalityId,
                                    search
                                );

                            }, 300);

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | BARANGAY SELECT
                |--------------------------------------------------------------------------
                */

                instance.barangayList.on(
                    'click',
                    '.barangay-item',
                    function () {

                        instance.barangayList
                            .find('.barangay-item')
                            .removeClass('active');


                        $(this)
                            .addClass('active');


                        instance.selected.barangay_id =
                            $(this).data('id');


                        instance.selected.barangay_name =
                            $(this).data('name');


                        instance.updatePreview();


                        instance.useButton
                            .prop('disabled', false);

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | USE ADDRESS
                |--------------------------------------------------------------------------
                */

                instance.useButton.on(
                    'click',
                    function () {

                        const selected =
                            $.extend(
                                true,
                                {},
                                instance.selected
                            );


                        /*
                        | Callback
                        */

                        if (
                            typeof options.onSelect ===
                            'function'
                        ) {

                            options.onSelect(selected);

                        }


                        /*
                        | Custom event
                        */

                        modal.trigger(
                            'address:selected',
                            [selected]
                        );


                        modal.modal('hide');

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | MODAL OPEN
                |--------------------------------------------------------------------------
                */

                modal.on(
                    'shown.bs.modal',
                    function () {

                        instance.loadRegions();

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | STORE INSTANCE
                |--------------------------------------------------------------------------
                */

                this.instances[modalId] =
                    instance;


                return instance;

            }

        };


    })(jQuery);

</script>
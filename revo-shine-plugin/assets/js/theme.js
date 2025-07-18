jQuery(document).ready(function ($) {
    // utils
    const utils = {
        bodyElement: $("body"),
    }

    const hideSection = (type, hideAll = false) => {
        if (hideAll) {
            jQuery('select[name="link_to_select"], input[name="link_to_input"]')
                .parent()
                .addClass('d-none')
                .removeClass('d-flex');

            return;
        }

        if (type !== "url") {
            jQuery('input[name="link_to_input"]')
                .parent()
                .addClass('d-none')
                .removeClass('d-flex');

            jQuery('select[name="link_to_select"]')
                .val("")
                .trigger("change")
                .parent()
                .removeClass('d-none')
                .addClass('d-flex');
        } else {
            jQuery('select[name="link_to_select"]')
                .val("")
                .trigger("change")
                .parent()
                .removeClass('d-flex')
                .addClass('d-none');

            jQuery('input[name="link_to_input"]')
                .parent()
                .addClass('d-flex')
                .removeClass('d-none');
        }
    };

    const select2Builder = (type, targetEl = ".select2") => {
        var dropdownParentEl = jQuery("#modalAction > .modal-dialog > .modal-content");

        let config = {
            width: '100%',
            dropdownParent: dropdownParentEl,
            allowClear: false,
            placeholder: "Select an option",
            minimumInputLength: 3,
            ajax: {
                type: "POST",
                url: ajaxurl,
                dataType: "json",
                delay: 250,
                cache: true,
                data: function (params) {
                    return {
                        action: "rw_get_data",
                        search: params.term,
                        type,
                    };
                },
                processResults: function (data, params) {
                    let res = [];
                    data.map((val) => {
                        res.push({
                            // id: val.id + "|" + btoa(val.title),
                            id: val.id,
                            text: val.title,
                        });
                    });

                    return {
                        results: res,
                    };
                },
            },
        };

        jQuery(targetEl).select2(config);
    };

    window.hideSection = hideSection;
    window.select2Builder = select2Builder;

    // Datatables
    const RevoDTables = new DataTable('#datatables', {
        columnDefs: [{type: 'num'}],
        layout: {
            topStart: function () {
                return `<select class="form-control form-select" id="datatables-record">
                <option value="10">10 Records</option>
                <option value="25">25 Records</option>
                <option value="50">50 Records</option>
                <option value="100">100 Records</option>
            </select>`;
            },
            topEnd: function () {
                const dataTablesInstance = jQuery('#datatables');
                const sortButtonTitle = dataTablesInstance.data('title');
                const displayButtonModal = dataTablesInstance.data('button-modal');

                const addItemButtonEl = `<button class="btn btn-add-item btn-primary" data-bs-toggle="modal" data-bs-target="#modalAction">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 19C11.7167 19 11.4793 18.904 11.288 18.712C11.096 18.5207 11 18.2833 11 18V13H6C5.71667 13 5.479 12.904 5.287 12.712C5.09567 12.5207 5 12.2833 5 12C5 11.7167 5.09567 11.479 5.287 11.287C5.479 11.0957 5.71667 11 6 11H11V6C11 5.71667 11.096 5.479 11.288 5.287C11.4793 5.09567 11.7167 5 12 5C12.2833 5 12.521 5.09567 12.713 5.287C12.9043 5.479 13 5.71667 13 6V11H18C18.2833 11 18.5207 11.0957 18.712 11.287C18.904 11.479 19 11.7167 19 12C19 12.2833 18.904 12.5207 18.712 12.712C18.5207 12.904 18.2833 13 18 13H13V18C13 18.2833 12.9043 18.5207 12.713 18.712C12.521 18.904 12.2833 19 12 19Z" fill="white"/>
                    </svg>
                    <span class="fs-12 lh-12 fw-600 ms-1">${sortButtonTitle}</span>
                </button>`;

                const sortButtonEl = `<select class="form-control form-select" id="datatables-sort" style="min-width: 120px">
                    <option disabled selected>Sort</option>
                    <option value="asc">Sort by Asc</option>
                    <option value="desc">Sort by Desc</option>                
                </select>`;

                return `<div class="d-flex align-items-center gap-base">
                    ${displayButtonModal ? addItemButtonEl : ''}
                    ${dataTablesInstance.data('page') === 'video' ? `<button class="btn btn-video-setting btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalSetting">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13.8783 22H10.1283C9.87827 22 9.6616 21.9167 9.47827 21.75C9.29493 21.5833 9.1866 21.375 9.15327 21.125L8.85327 18.8C8.6366 18.7167 8.4326 18.6167 8.24127 18.5C8.04927 18.3833 7.8616 18.2583 7.67827 18.125L5.50327 19.025C5.26993 19.1083 5.0366 19.1167 4.80327 19.05C4.56993 18.9833 4.3866 18.8417 4.25327 18.625L2.40327 15.4C2.26993 15.1833 2.22827 14.95 2.27827 14.7C2.32827 14.45 2.45327 14.25 2.65327 14.1L4.52827 12.675C4.5116 12.5583 4.50327 12.4457 4.50327 12.337V11.662C4.50327 11.554 4.5116 11.4417 4.52827 11.325L2.65327 9.9C2.45327 9.75 2.32827 9.55 2.27827 9.3C2.22827 9.05 2.26993 8.81667 2.40327 8.6L4.25327 5.375C4.36993 5.14167 4.54893 4.99567 4.79027 4.937C5.03227 4.879 5.26993 4.89167 5.50327 4.975L7.67827 5.875C7.8616 5.74167 8.05327 5.61667 8.25327 5.5C8.45327 5.38333 8.65327 5.28333 8.85327 5.2L9.15327 2.875C9.1866 2.625 9.29493 2.41667 9.47827 2.25C9.6616 2.08333 9.87827 2 10.1283 2H13.8783C14.1283 2 14.3449 2.08333 14.5283 2.25C14.7116 2.41667 14.8199 2.625 14.8533 2.875L15.1533 5.2C15.3699 5.28333 15.5743 5.38333 15.7663 5.5C15.9576 5.61667 16.1449 5.74167 16.3283 5.875L18.5033 4.975C18.7366 4.89167 18.9699 4.88333 19.2033 4.95C19.4366 5.01667 19.6199 5.15833 19.7533 5.375L21.6033 8.6C21.7366 8.81667 21.7783 9.05 21.7283 9.3C21.6783 9.55 21.5533 9.75 21.3533 9.9L19.4783 11.325C19.4949 11.4417 19.5033 11.554 19.5033 11.662V12.337C19.5033 12.4457 19.4866 12.5583 19.4533 12.675L21.3283 14.1C21.5283 14.25 21.6533 14.45 21.7033 14.7C21.7533 14.95 21.7116 15.1833 21.5783 15.4L19.7283 18.6C19.5949 18.8167 19.4076 18.9627 19.1663 19.038C18.9243 19.1127 18.6866 19.1083 18.4533 19.025L16.3283 18.125C16.1449 18.2583 15.9533 18.3833 15.7533 18.5C15.5533 18.6167 15.3533 18.7167 15.1533 18.8L14.8533 21.125C14.8199 21.375 14.7116 21.5833 14.5283 21.75C14.3449 21.9167 14.1283 22 13.8783 22ZM12.0533 15.5C13.0199 15.5 13.8449 15.1583 14.5283 14.475C15.2116 13.7917 15.5533 12.9667 15.5533 12C15.5533 11.0333 15.2116 10.2083 14.5283 9.525C13.8449 8.84167 13.0199 8.5 12.0533 8.5C11.0699 8.5 10.2406 8.84167 9.56527 9.525C8.8906 10.2083 8.55327 11.0333 8.55327 12C8.55327 12.9667 8.8906 13.7917 9.56527 14.475C10.2406 15.1583 11.0699 15.5 12.0533 15.5ZM12.0533 13.5C11.6366 13.5 11.2826 13.354 10.9913 13.062C10.6993 12.7707 10.5533 12.4167 10.5533 12C10.5533 11.5833 10.6993 11.2293 10.9913 10.938C11.2826 10.646 11.6366 10.5 12.0533 10.5C12.4699 10.5 12.8243 10.646 13.1163 10.938C13.4076 11.2293 13.5533 11.5833 13.5533 12C13.5533 12.4167 13.4076 12.7707 13.1163 13.062C12.8243 13.354 12.4699 13.5 12.0533 13.5ZM11.0033 20H12.9783L13.3283 17.35C13.8449 17.2167 14.3243 17.0207 14.7663 16.762C15.2076 16.504 15.6116 16.1917 15.9783 15.825L18.4533 16.85L19.4283 15.15L17.2783 13.525C17.3616 13.2917 17.4199 13.0457 17.4533 12.787C17.4866 12.529 17.5033 12.2667 17.5033 12C17.5033 11.7333 17.4866 11.4707 17.4533 11.212C17.4199 10.954 17.3616 10.7083 17.2783 10.475L19.4283 8.85L18.4533 7.15L15.9783 8.2C15.6116 7.81667 15.2076 7.49567 14.7663 7.237C14.3243 6.979 13.8449 6.78333 13.3283 6.65L13.0033 4H11.0283L10.6783 6.65C10.1616 6.78333 9.6826 6.979 9.24127 7.237C8.79927 7.49567 8.39493 7.80833 8.02827 8.175L5.55327 7.15L4.57827 8.85L6.72827 10.45C6.64493 10.7 6.5866 10.95 6.55327 11.2C6.51993 11.45 6.50327 11.7167 6.50327 12C6.50327 12.2667 6.51993 12.525 6.55327 12.775C6.5866 13.025 6.64493 13.275 6.72827 13.525L4.57827 15.15L5.55327 16.85L8.02827 15.8C8.39493 16.1833 8.79927 16.504 9.24127 16.762C9.6826 17.0207 10.1616 17.2167 10.6783 17.35L11.0033 20Z" fill="#5258E4"/></svg>
                        <span class="fs-12 lh-12 fw-600 ms-1">Video Setting</span>
                    </button>` : ''}
                    ${sortButtonEl}
                </div>`;
            }
        },
        initComplete: function () {
            jQuery('#datatables-search').on('input', function (el) {
                RevoDTables.search(jQuery(this).val()).draw();
            });
            jQuery("#datatables-record").change(function () {
                RevoDTables.page.len(jQuery(this).val()).draw();
            });
            jQuery("#datatables-sort").change(function () {
                RevoDTables.order([1, jQuery(this).val()]).draw();
            });
        }
    });

    // Uploading & events files
    let file_frame;

    utils.bodyElement.on("click", ".btn-upload-file", function (event) {
        event.preventDefault();

        const target = $(this).data('target');
        const library = $(this).data('library');
        const isMultiple = $(this).data('multiple');

        const selectedElement = $(this);
        const containerElement = $(this).closest('.form-field-upload-container');

        if (containerElement.hasClass('file-attached') && selectedElement[0].nodeName !== 'BUTTON') {
            const previewUrl = containerElement.find('.form-field-file-preview img').attr('src');

            window.open(previewUrl, '_blank');

            return;
        }

        const wp_media_object = {
            title: "Choose File",
            button: {
                text: "Select File",
            },
            library: {
                type: library !== undefined ? library.split(",") : ["image"],
            },
            multiple: isMultiple !== undefined ? isMultiple : false,
        };

        file_frame = wp.media(wp_media_object);

        file_frame.on("open", function () {
            const selection = file_frame.state().get("selection");
            const fileId = $(`input[name="${target}_id"]`).val();

            if (fileId !== "" && fileId !== undefined) {
                let ids = fileId.split(",");

                ids.forEach(function (id) {
                    const attachment = wp.media.attachment(id);
                    attachment.fetch();
                    selection.add(attachment ? [attachment] : []);
                });
            }
        });

        file_frame.on("select", function () {
            const attachment = file_frame.state().get("selection").toJSON();

            // input value to hidden input
            $(`input[name="${target}"]`).val(attachment.map((item) => item.url).join(",")).trigger('change');
            $(`input[name="${target}_id"]`).val(attachment.map((item) => item.id).join(",")).trigger('change');

            // preview image
            if (isMultiple) {
                containerElement.find('.form-field-file-preview').html('');
                attachment.forEach(function (item) {
                    containerElement.find('.form-field-file-preview').append(`<img src="${item.url}" alt="">`);
                });
            } else {

                if (attachment[0].mime.includes('video')) {
                    containerElement.find('.form-field-file-preview img').addClass('d-none');
                    containerElement.find('.form-field-file-preview video').removeClass('d-none');
                    containerElement.find('.form-field-file-preview video')?.attr('src', attachment[0].url).trigger('change');
                } else {
                    containerElement.find('.form-field-file-preview img').removeClass('d-none');
                    containerElement.find('.form-field-file-preview img').attr('src', attachment[0].url);
                    containerElement.find('.form-field-file-preview video')?.addClass('d-none');
                }
            }

            containerElement.addClass('file-attached');
        });

        file_frame.open();
    });

    utils.bodyElement.on('click', '.form-field-file-preview img', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const imageUrl = $(this).attr('src');

        window.open(imageUrl, '_blank');
    });

    utils.bodyElement.on('click', '.form-field-upload-container .btn-remove-file', function (event) {
        event.preventDefault();

        const containerElement = $(this).closest('.form-field-upload-container');

        containerElement.find('input[type="hidden"]').val('');

        containerElement.removeClass('file-attached');
    });

    // Form events
    utils.bodyElement.on('change', '.form-control[type="color"]', function (event) {
        $(this).parent().find('.color-preview').html(`${$(this).val()}`);
    });

    utils.bodyElement.on('change', '.form-check-input', function (event) {

        const toggleOnClass = $(this).data('toggle-on');
        const toggleOffClass = $(this).data('toggle-off');

        const isChecked = $(this).prop('checked');

        if (isChecked) {
            $(`.${toggleOnClass}`).removeClass('d-none');
            $(`.${toggleOffClass}`).addClass('d-none');
        } else {
            $(`.${toggleOnClass}`).addClass('d-none');
            $(`.${toggleOffClass}`).removeClass('d-none');
        }
    });

    utils.bodyElement.on('change', '.form-select', function (event) {

        const toggleClass = $(this).data('toggle');
        const triggerValues = $(this).data('trigger')?.split(',');

        if (toggleClass === undefined || triggerValues === undefined) {
            return;
        }

        const selectedValue = $(this).val();

        const displayClass = triggerValues.includes(selectedValue) ? 'removeClass' : 'addClass';
        $(`.${toggleClass}`)[displayClass]('d-none');
    });

    // temporary checkbox
    utils.bodyElement.on("change", ".tmp_checkbox", function (e) {
        e.preventDefault();

        const inputName = $(this).data("inputname");

        if ($(this).prop("checked")) {
            $(`input[name="${inputName}"]`).val("on");
        } else {
            $(`input[name="${inputName}"]`).val("off");
        }
    });

    // btn submit loading
    utils.bodyElement.on("click", '.btn-save-changes', function (el) {
        $(this).addClass("disabled").html("").append(`<div class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></div>Save Changes`);
    });

    // dark mode
    utils.bodyElement.on('change', '.dark-mode-switch', function (e) {
        e.preventDefault();

        const switchOn = $(this).prop('checked');
        const themeMode = switchOn ? 'dark' : 'light';

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action: "rwt_theme_mode_switch",
                dark_mode: switchOn ? 'on' : 'off',
            },
            beforeSend: () => {
                $('#rw-admin-wrapper').attr('data-bs-theme', themeMode);
                $('#wpcontent').css('background-color', themeMode === 'light' ? '#F6F7F8' : '#323232');
            }
        });
    })

    // dashboard event
    // remove item from list
    utils.bodyElement.on("click", ".btn-destroy", function () {
        Swal.fire({
            title: "Are you sure you want to delete this ?",
            showDenyButton: true,
            showCancelButton: false,
            confirmButtonText: "Delete",
            confirmButtonColor: "#d33",
            denyButtonText: "Cancel",
            denyButtonColor: "#3085d6",
            icon: "question",
        }).then((result) => {
            if (result.isConfirmed) {
                jQuery.ajax({
                    method: "POST",
                    data: {
                        id: jQuery(this).data("id"),
                        action: "destroy",
                    },
                    beforeSend: () => {
                        Swal.fire({
                            title: "",
                            text: "",
                            html: `<h3 style='text-align: center; line-height: 2.2rem margin-top: 0; margin-bottom: 20px;'>Please wait. We are saving <br> your changes....</h3>`,
                            icon: "",
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            },
                        });
                    },
                    complete: () => {
                        location.reload();
                    },
                });
            }
        });
    });

    // link to or redirect type
    utils.bodyElement.on("change", 'input[name="link_to"]', function (el) {
        el.preventDefault();

        const type = jQuery(this).val();

        if (type !== "url") {
            select2Builder(type);
        }

        hideSection(type);
    });

    // change image
    utils.bodyElement.on("change", ".image-type", function (e) {
        jQuery("#fileInput").toggleClass("d-none");
        jQuery("#linkInput").toggleClass("d-none");
    });
});
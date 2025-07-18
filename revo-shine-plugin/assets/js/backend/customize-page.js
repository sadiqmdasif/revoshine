const bodyEL = jQuery('body');

let newData = { homepage: [], header: [], heroBanner: "" };
let oldData = { homepage: [], header: [], heroBanner: "" };
let siteUrl = document.getElementById("js-data").getAttribute("data-siteUrl");

if (innerWidth < 767) {
    jQuery(".panel").hide();
    revoSwalAlert(
        "Oops...",
        "Please use a desktop or laptop to view this page.",
        "warning"
    );
}

jQuery(".selectric").selectric({
    onChange: function (e) {
        filterSelection(jQuery(this).val());
    },
});

new Sortable(homePageDesign, {
    handle: ".handle",
    animation: 150,
    scroll: true,
    forceAutoscrollFallback: false,
    scrollSensitivity: 30,
    scrollSpeed: 15,
    bubbleScroll: true,
    onStart: function () {
        jQuery("#parentScreenContent").addClass("d-none");
        jQuery("#loadingPreview").removeClass("d-none").addClass("d-flex");
    },
    onEnd: function () {
        jQuery("#loadingPreview").addClass("d-none").removeClass("d-flex");
        jQuery("#parentScreenContent").removeClass("d-none");
    },
    onChange: function (e) {
        runPreview();
    },
});

function runPreview(direct = false, callback = null) {
    const switchSections = jQuery("#homePageDesign .form-check-input");
    const headerDesign = jQuery('input[name="header_design"]:checked').val();
    const heroBannerDesign = jQuery(
      'input[name="hero_banner_design"]:checked'
    ).val();
    const fileUploadUrl = jQuery('input[name="header_logo"]').val();

    jQuery("#screenContent").html("");

    // currently homepage design
    let homepageDesign = switchSections
        .filter((key, val) => {
            if (jQuery(val).attr("checked") !== undefined) {
                return val;
            }
        })
        .map((key, val) => {
            let section = jQuery(val).data("inputname");

            jQuery("#screenContent").append(`<img src="${siteUrl}assets/images/builder/${section}.webp" alt="image_${section}" width="100%">`);

            if (direct) {
                oldData.homepage.push(section);
            }

            return section;
        })
        .get();

    if (direct) {
        oldData.header = [headerDesign, fileUploadUrl];
        oldData.heroBanner = heroBannerDesign;
    }    

    if (!direct && oldData.homepage.length > 0) {
        const isHomepageDifferent = JSON.stringify(oldData.homepage) !== JSON.stringify(homepageDesign);
        const isHeaderDifferent = JSON.stringify(oldData.header) !== JSON.stringify([headerDesign, fileUploadUrl]);
        const isHeroBannerDifferent =
          JSON.stringify(oldData.heroBanner) !==
          JSON.stringify(heroBannerDesign);

        if (isHomepageDifferent || isHeaderDifferent || isHeroBannerDifferent) {
            jQuery("#save").removeClass("d-none").addClass("d-flex");

            newData.header = [headerDesign, fileUploadUrl];
            newData.homepage = homepageDesign;
            newData.heroBanner = heroBannerDesign;
        } else {
            jQuery("#save").addClass("d-none").removeClass("d-flex");
        }
    }

    if (callback !== null) {
        jQuery("#parentScreenContent").removeClass("d-none");
        callback();
    }
}

function filterSelection(filter = ".all") {

    const sectionItemEl = jQuery("#homePageDesign .section-item");

    if (filter === ".all") {
        sectionItemEl.addClass("list-show");
        return;
    }

    sectionItemEl.removeClass("list-show");
    sectionItemEl.filter(filter).addClass("list-show");
}

runPreview(true, () => {
    filterSelection();
    jQuery("#loadingPreview").addClass("d-none").removeClass("d-flex");
});

bodyEL.on("click", "#modal-hamburger-menu .btn-add-menu-item", function (el) {
    el.preventDefault();

    jQuery(".menu-item-container").append(`<div class="form-group d-flex flex-column gap-base">
            <div class="row align-items-end">
                <div class="col">
                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input class="form-control" type="text" placeholder="Ex: Page Title" name="titles[]">
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label class="form-label">Link</label>
                        <input class="form-control" type="text" placeholder="Ex: https://yourdomain.com/page" name="links[]">
                    </div>
                </div>
                <div class="col" style="max-width: 75px">
                    <button class="btn btn-outline-danger btn-remove-menu-item" type="button" style="width:40px;height:40px">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>`);
});

bodyEL.on("click", "#modal-hamburger-menu .btn-remove-menu-item", function (el) {
    el.preventDefault();

    jQuery(this).parent().parent().remove();
});

bodyEL.on("change", ".handle-on-change", function (el) {
    el.preventDefault();

    if (jQuery(this).attr("name") === "header_design" && jQuery(this).val() !== "v6") {
        jQuery(".additional-header-data-container").removeClass("d-none");
    } else {
        jQuery(".additional-header-data-container").addClass("d-none");
    }

    runPreview();
});

bodyEL.on("change", 'input[name="header_logo"]', function () {
    runPreview(false, () => {
        jQuery("#save").removeClass("d-none").addClass("d-flex");
    });
});

bodyEL.on("change", "#homePageDesign .form-check-input", function (el) {
    el.preventDefault();

    if (jQuery(this).attr("checked") !== undefined) {
        jQuery(this).removeAttr("checked");
    } else {
        jQuery(this).attr("checked", "checked");
    }

    jQuery(this).closest(".section-item").toggleClass("on");

    runPreview();
});

bodyEL.on("click", "#save button", function (el) {
    el.preventDefault();

    if (newData.homepage.length <= 0) {
        revoSwalAlert(
            "Oops...",
            "Please select at least one section.",
            "warning",
            null,
            true
        );
        return;
    }

    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
            action: "save_customize_page",
            homepage: newData.homepage,
            header: newData.header,
            heroBanner: newData.heroBanner
        },
        beforeSend: () => {
            revoSwalAlert(
                "Please wait. We are saving your changes....",
                "",
                "",
                `<img src='${siteUrl}/assets/icon/loading.svg' width='80px' alt="loading ..."/>`
            );
        },
        success: (data) => {
            location.reload();
        },
    });
});
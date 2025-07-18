jQuery(document).ready(function ($) {
  const swalLoading = (
    text = "Please wait. We are saving <br> your changes...."
  ) => {
    Swal.fire({
      title: "",
      text: "",
      html: `<h3 style='text-align: center; line-height: 2.2rem margin-top: 0; margin-bottom: 20px;'>${text}</h3>`,
      icon: "",
      showConfirmButton: false,
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });
  };

  const swalAlertWarning = (
    title,
    text,
    icon,
    textButton,
    isConfirm = false,
    cb = null
  ) => {
    let config = {
      icon,
      title,
      html: text,
      confirmButtonText: "OK",
      confirmButtonColor: "#0088cc",
      allowOutsideClick: false,
      allowEscapeKey: false,
    };

    if (isConfirm) {
      config.showCancelButton = true;
      config.cancelButtonText = textButton;
    }

    Swal.fire(config).then((res) => {
      if (res.dismiss == "cancel") {
        cb();
      }
    });
  };

  jQuery("#wp-admin-bar-revo-woo-rebuild-cache").on("click", function (e) {
    e.preventDefault();

    jQuery.ajax({
      type: "POST",
      url: ajaxurl,
      data: {
        action: "rw_rebuild_cache",
      },
      beforeSend: () => {
        swalLoading();
      },
      success: () => {
        location.reload();
      },
    });
  });

  jQuery("#wp-admin-bar-revo-woo-generate-static-file").on(
    "click",
    function (e) {
      e.preventDefault();

      jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
          action: "rw_generate_static_file",
        },
        beforeSend: () => {
          swalLoading();
        },
        success: () => {
          location.reload();
        },
      });
    }
  );

  jQuery("body").on("click", ".btn-rw-build", function () {
    const os = jQuery(this).data("os");

    Swal.fire({
      icon: "question",
      html: `
        <h3 style="font-size: 24px; font-weight: 600; line-height: 2.2rem">Do you want to submit a request <br/> to build your ${os} App ?</h3>
        <p style="font-size: 18px; margin-top: 25px; margin-bottom: 10px">This request will be sent <br/> to Revo Apps via whatsapp</p>
      `,
      confirmButtonText: "Yes",
      confirmButtonColor: "#0088cc",
      cancelButtonText: "No",
      cancelButtonColor: "#d2322d",
      focusConfirm: true,
      showCancelButton: true,
      allowOutsideClick: false,
      allowEscapeKey: false,
    }).then((result) => {
      if (result.isConfirmed) {
        jQuery.ajax({
          type: "POST",
          url: ajaxurl,
          data: {
            os,
            action: "build_app",
          },
          beforeSend: () => {
            swalLoading("Please Wait ..");
          },
          success: (res) => {
            const url = `https://api.whatsapp.com/send/?phone=62811369000&text=${res.data.message}`;
            window.open(url, "_blank").focus();

            Swal.close();
          },
          error: (err) => {
            response = err.responseJSON;

            if (response.data.type === "confirm") {
              swalAlertWarning(
                "",
                response.data.message,
                "info",
                "Guide",
                true,
                () => {
                  window.open(response.data.redirect, "_blank");
                }
              );

              return;
            }

            swalAlertWarning("", response.data.message, "info", "");
          },
        });
      }
    });
  });
});

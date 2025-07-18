// jQuery(function () {
console.log("welcome => revo apps");

const revoToast = Swal.mixin({
  width: 200,
  height: 150,
  position: "top-right",
  toast: true,
  showConfirmButton: false,
  timer: 2500,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener("mouseenter", Swal.stopTimer);
    toast.addEventListener("mouseleave", Swal.resumeTimer);
  },
});

const revoSwalAlert = (
  title,
  text,
  icon = "success",
  html = null,
  confirmBtn = false
) => {
  Swal.fire({
    title,
    text,
    html,
    icon,
    showConfirmButton: confirmBtn,
    allowOutsideClick: confirmBtn,
    allowEscapeKey: confirmBtn,
  });
};

const revoSwalAlertConfirm = (title, text, icon, textButton, cb) => {
  Swal.fire({
    title,
    text,
    icon,
    confirmButtonText: textButton,
    confirmButtonColor: "#0088cc",
    allowOutsideClick: false,
    allowEscapeKey: false,
  }).then((res) => {
    if (res.isConfirmed) {
      cb();
    }
  });
};

const revoSwalConfirm = (
  text,
  cb,
  title = "Are you sure ?",
  icon = "question",
  confirmBtnText = "Yes",
  cancelBtnText = "No"
) => {
  Swal.fire({
    title,
    text,
    icon,
    showCancelButton: true,
    confirmButtonColor: "#0088cc",
    cancelButtonColor: "#d2322d",
    confirmButtonText: confirmBtnText,
    cancelButtonText: cancelBtnText,
    focusCancel: true,
  }).then((result) => {
    if (result.isConfirmed) {
      cb();
    }
  });
};
// });

jQuery(".update").click(function () {
  let title = jQuery(this).attr("title");
  let id = jQuery(this).attr("id");
  let sort = jQuery(this).attr("sort");
  let description = jQuery(this).attr("description");

  jQuery("#titleModal").html("Update Intro Page " + id);
  jQuery("#title").val(title);
  jQuery("#id").val(id);
  jQuery("#action").val("intro");
  jQuery("#description").val(description);
  jQuery("#sort").val(sort);

  jQuery("#updateIntroPage").modal("show");
});

function showIntroPage(e) {
  el = e.target;

  jQuery.ajax({
    type: "POST",
    data: {
      action: "status",
      status: el.checked,
    },
    url: "/intro/update",
    beforeSend: () => {
      loading.dispatch(actions.Loading.Action.START);
    },
    complete: (response) => {
      loading.dispatch(actions.Loading.Action.STOP);
    },
  });
}

function destroy(id) {
  revoSwalConfirm("Delete this intro page", () => {
    jQuery.ajax({
      type: "POST",
      data: {
        id,
      },
      url: "/intro/destroy",
      success: (response) => {
        location.reload();
      },
    });
  });
}

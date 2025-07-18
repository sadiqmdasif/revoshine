<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {

	$alert = array(
		'type'    => 'error',
		'title'   => 'Failed !',
		'message' => 'Your request failed to be processed. Try Again Later',
	);

	if ( in_array( $_POST['action'], [ 'store', 'update' ] ) ) {
		$query_data = [
			'slug'        => 'intro_page',
			'title'       => json_encode( [ 'title' => $_POST['title'] ] ),
			'sort'        => $_POST['sort'],
			'description' => json_encode( [ 'description' => $_POST['description'] ] ),
		];

		if ( revo_shine_check_image_type( $_POST['image'] ?? '' ) ) {
			$query_data['image'] = $_POST['image'];

			if ( $_POST['action'] === 'store' ) {
				$wpdb->insert( 'revo_mobile_variable', $query_data );

				if ( @$wpdb->insert_id > 0 ) {
					$alert = array(
						'type'    => 'success',
						'title'   => 'Success !',
						'message' => 'Intro Page Added Successfully',
					);
				}
			} else {
				$update_status = $wpdb->update( 'revo_mobile_variable', $query_data, [ 'id' => $_POST['id'] ] );

				if ( $update_status !== false ) {
					$alert = array(
						'type'    => 'success',
						'title'   => 'Success !',
						'message' => 'Intro Page Updated Successfully',
					);
				}
			}
		} else {
			$alert = array(
				'type'    => 'error',
				'title'   => 'Uploads Error !',
				'message' => 'Your file type is not allowed. Only support jpg, png, jpeg.',
			);
		}
	}

	if ( $_POST['action'] === 'destroy' ) {
		$where         = [ 'id' => $_POST['id'], 'slug' => 'intro_page' ];
		$update_status = $wpdb->update( 'revo_mobile_variable', [ 'is_deleted' => '1' ], $where );

		if ( $update_status !== false ) {
			$alert = array(
				'type'    => 'success',
				'title'   => 'Success !',
				'message' => 'Intro Page Deleted Successfully',
			);
		}
	}

	$_SESSION["alert"] = $alert;

	if ( $_SESSION['alert']['type'] === 'success' ) {
		revo_shine_rebuild_cache( 'revo_home_data' );
	}
}

$intro_data = $wpdb->get_results( "SELECT * FROM `revo_mobile_variable` WHERE slug = 'intro_page' AND is_deleted = 0 ORDER BY sort ASC", OBJECT );

$query_repeat = query_revo_mobile_variable( '"intro_page_status"', 'sort' );
$repeat_intro = empty( $query_repeat ) ? 'hide' : $query_repeat[0]->description;

$query_disable_intro = query_revo_mobile_variable( '"disable_intro_page"', 'sort' );
$disable_intro_data  = empty( $query_disable_intro ) ? 'hide' : $query_disable_intro[0]->description;
?>

<div class="admin-section-container">
    <div class="admin-section-item">
        <div class="admin-section-item-title">Setting Intro Page</div>
        <div class="admin-section-item-body">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex flex-column gap-base">
					<?php

					revo_shine_get_admin_template_part( 'components/mini-switch', [
						'id'         => 'disabled-intro',
						'label'      => 'Disable Intro Page :',
						'name'       => 'disable_intro',
						'is_checked' => ( $disable_intro_data ?? '' ) === 'show'
					] );

					revo_shine_get_admin_template_part( 'components/mini-switch', [
						'id'         => 'repeated-intro',
						'label'      => 'Show Repeated Intros :',
						'name'       => 'repeated_intro',
						'is_checked' => ( $repeat_intro ?? '' ) === 'show'
					] );

					?>
                </div>

                <button class="btn btn-add-item btn-primary" type="button" data-bs-toggle="modal"
                        data-bs-target="#modalAction">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 19C11.7167 19 11.4793 18.904 11.288 18.712C11.096 18.5207 11 18.2833 11 18V13H6C5.71667 13 5.479 12.904 5.287 12.712C5.09567 12.5207 5 12.2833 5 12C5 11.7167 5.09567 11.479 5.287 11.287C5.479 11.0957 5.71667 11 6 11H11V6C11 5.71667 11.096 5.479 11.288 5.287C11.4793 5.09567 11.7167 5 12 5C12.2833 5 12.521 5.09567 12.713 5.287C12.9043 5.479 13 5.71667 13 6V11H18C18.2833 11 18.5207 11.0957 18.712 11.287C18.904 11.479 19 11.7167 19 12C19 12.2833 18.904 12.5207 18.712 12.712C18.5207 12.904 18.2833 13 18 13H13V18C13 18.2833 12.9043 18.5207 12.713 18.712C12.521 18.904 12.2833 19 12 19Z"
                              fill="white"/>
                    </svg>
                    <span class="fs-12 lh-12 ms-1">Add Intro Page</span>
                </button>
            </div>
        </div>
    </div>
    <div class="admin-section-item">
        <div class="admin-section-item-body">
			<?php if ( ! empty( $intro_data ) ) : ?>
                <div class="row g-3">
					<?php foreach ( $intro_data as $intro ) : ?>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="intro-title"><?php echo stripslashes( json_decode( $intro->title )->title ) ?></div>

                                    <img class="intro-image mx-auto"
                                         src="<?php echo $intro->image ?>"
                                         style="height: 200px; width: 100%; object-fit:cover;" alt="intro">

                                    <div class="intro-description"><?php echo stripslashes( json_decode( $intro->description )->description ) ?></div>
                                </div>
                                <div class="d-flex align-items-center justify-content-center gap-2 intro-action mt-large">
                                    <button class="btn btn-danger btn-destroy" data-id="<?php echo $intro->id ?>">
                                        Delete
                                    </button>
                                    <button class="btn btn-primary btn-update mr-2"
                                            data-id="<?php echo $intro->id; ?>"
                                            data-title="<?php echo stripslashes( json_decode( $intro->title )->title ) ?>"
                                            data-description="<?php echo stripslashes( json_decode( $intro->description )->description ) ?>"
                                            data-sort="<?php echo $intro->sort; ?>"
                                            data-image="<?php echo $intro->image ?>"
                                            type="button"
                                    >
                                        Update
                                    </button>
                                </div>
                            </div>
                        </div>
					<?php endforeach; ?>
                </div>
			<?php endif ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAction" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Add Intro Page</div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="#">
                <div class="modal-body">
					<?php

					revo_shine_get_admin_template_part( 'components/input', [
						'id'          => 'title',
						'label'       => 'Title',
						'name'        => 'title',
						'class'       => 'd-flex align-items-start gap-base',
						'placeholder' => 'Input title',
						'is_required' => true
					] );

					revo_shine_get_admin_template_part( 'components/textarea', [
						'id'          => 'description',
						'label'       => 'Description',
						'name'        => 'description',
						'value'       => '',
						'rows'        => 3,
						'class'       => 'd-flex align-items-start gap-base',
						'placeholder' => 'Input Description',
						'is_required' => true
					] );

					revo_shine_get_admin_template_part( 'components/input', [
						'id'          => 'sort',
						'label'       => 'Sort To',
						'type'        => 'number',
						'name'        => 'sort',
						'value'       => ! empty( $intro_data ) ? count( $intro_data ) + 1 : 1,
						'class'       => 'd-flex align-items-start gap-base',
						'placeholder' => 'Input Order',
						'is_required' => true
					] );

					revo_shine_get_admin_template_part( 'components/upload', [
						'id'          => 'image',
						'label'       => 'Image',
						'name'        => 'image',
						'value'       => '',
						'class'       => 'd-flex align-items-start gap-base',
						'accent_text' => 'Upload Photo Here',
						'helper_text' => 'Best Size : 450 x 450 px, Max File Size: 2MB',
						'is_required' => true
					] );

					?>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="action" value="store">
                    <button type="button" class="btn btn-secondary close" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const bodyEL = jQuery('body');

    bodyEL.on('change', 'input[data-inputname="repeated_intro"]', function () {
        const status = jQuery(this).prop('checked') ? 'show' : 'hide';

        jQuery.ajax({
            method: "GET",
            url: `<?php echo get_site_url( null, 'wp-json/revo-admin/v1/set-intro-page' ); ?>?status=${status}`,
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
                Swal.close();
            }
        });
    });

    bodyEL.on('change', 'input[data-inputname="disable_intro"]', function () {
        const status = jQuery(this).prop('checked') ? 'show' : 'hide';

        jQuery.ajax({
            method: "GET",
            url: `<?= get_site_url( null, 'wp-json/revo-admin/v1/set-disable-intro-page' ); ?>?status=${status}`,
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
                Swal.close();
            }
        });
    });

    bodyEL.on('click', '.btn-update', function () {
        let id = jQuery(this).data('id');
        let title = jQuery(this).data('title');
        let sort = jQuery(this).data('sort');
        let description = jQuery(this).data('description');
        let image = jQuery(this).data('image');

        jQuery('#modalAction .modal-title').html("Update Intro Page");
        jQuery('#modalAction input[name="title"]').val(title);
        jQuery('#modalAction textarea[name="description"]').val(description);
        jQuery('#modalAction input[name="sort"]').val(sort);
        jQuery('#modalAction input[name="image"]').val(image);

        jQuery('#modalAction input[name="id"]').val(id);
        jQuery('#modalAction input[name="action"]').val('update');

        jQuery('#modalAction .form-field-upload-container').addClass('file-attached');
        jQuery('#modalAction .form-field-upload-container .form-field-file-preview img').attr('src', image);

        jQuery('#modalAction').modal('show');
    });

    bodyEL.on('hidden.bs.modal', '#modalAction', function () {
        jQuery('#modalAction .modal-title').html("Add Intro Page");
        jQuery('#modalAction input[name="title"]').val('');
        jQuery('#modalAction textarea[name="description"]').val('');
        jQuery('#modalAction input[name="sort"]').val('');
        jQuery('#modalAction input[name="id"]').val('');
        jQuery('#modalAction input[name="image"]').val('');
        jQuery('#modalAction .form-field-upload-container').removeClass('file-attached');
        jQuery('#modalAction input[name="action"]').val('store');
    });
</script>
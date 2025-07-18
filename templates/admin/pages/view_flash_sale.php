<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {

	if ( $_POST['action'] === 'store' || $_POST['action'] === 'update' ) {
		$products         = json_encode( $_POST['products'] ?? [] );
		$flash_sale_date  = explode( ' - ', $_POST['date'] );
		$start_flash_date = explode( '/', $flash_sale_date[0] );
		$end_flash_date   = explode( '/', $flash_sale_date[1] );

		$alert = [
			'type'    => 'error',
			'title'   => 'Failed !',
			'message' => 'Failed to Add Additional Banner',
		];

		if ( ! empty( $_POST['image'] ) ) {
			$alert = array(
				'type'    => 'error',
				'title'   => 'Uploads Error !',
				'message' => 'Your file type is not allowed. Only support jpg, png, jpeg.',
			);

			if ( revo_shine_check_image_type( $_POST['image'] ) ) {
				$data = array(
					'title'     => $_POST['title'],
					'start'     => date( 'Y-m-d H:i:s', strtotime( $start_flash_date[1] . '/' . $start_flash_date[0] . '/' . $start_flash_date[2] ) ),
					'end'       => date( 'Y-m-d H:i:s', strtotime( $end_flash_date[1] . '/' . $end_flash_date[0] . '/' . $end_flash_date[2] ) ),
					'products'  => $products,
					'image'     => $_POST['image'],
					'is_active' => '1'
				);

				if ( empty( $_POST['id'] ) ) {
					$wpdb->insert( 'revo_flash_sale', $data );

					if ( @$wpdb->insert_id > 0 ) {
						$alert = array(
							'type'    => 'success',
							'title'   => 'Success !',
							'message' => 'Flash Sale Success Saved',
						);
					}
				} else {
					$update_status = $wpdb->update( 'revo_flash_sale', $data, [ 'id' => $_POST['id'] ] );

					if ( $update_status !== false ) {
						$alert = array(
							'type'    => 'success',
							'title'   => 'Success !',
							'message' => 'Flash sale updated successfully.',
						);
					}
				}
			}
		}

		$_SESSION["alert"] = $alert;
	}

	if ( $_POST["action"] === 'destroy' ) {
		header( 'Content-type: application/json' );

		$query = $wpdb->update(
			'revo_flash_sale',
			[ 'is_deleted' => '1' ],
			array( 'id' => $_POST['id'] )
		);

		$alert = array(
			'type'    => 'error',
			'title'   => 'Failed !',
			'message' => 'Failed to Delete  Flash Sale',
		);

		if ( $query ) {
			$alert = array(
				'type'    => 'success',
				'title'   => 'Success !',
				'message' => 'Flash Sale Success Deleted',
			);
		}

		$_SESSION["alert"] = $alert;
	}

	if ( $_SESSION['alert']['type'] === 'success' ) {
		revo_shine_rebuild_cache( 'revo_home_data' );
	}
}

cek_flash_sale_end();

$data_flash_sale = $wpdb->get_results( "SELECT * FROM `revo_flash_sale` WHERE is_deleted = 0", OBJECT );
?>

<div class="admin-section-container">
    <div class="admin-section-item">
        <div class="d-flex align-items-center justify-content-between">
            <div class="admin-section-item-title text-capitalize">
                Home Flash Sale <?php echo buttonQuestion() ?>
            </div>
            <input class="input-search" type="text" id="datatables-search" placeholder="Search..">
        </div>
        <div class="admin-section-item-body">
            <table class="table table-bordered" id="datatables" data-title="Add Flash Sale" data-button-modal="true">
                <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th>Details</th>
                    <th class="text-center">Icon Side</th>
                    <th style="width: 35%">List Products</th>
                    <th class="text-center hidden-xs">Action</th>
                </tr>
                </thead>
                <tbody>
				<?php foreach ( $data_flash_sale as $key => $value ) : ?>
					<?php
					$product_ids         = json_decode( $value->products );
					$products_flash_sale = [];

					foreach ( $product_ids as $product_id ) {
						$product = wc_get_product( $product_id );
						if ( ! $product ) {
							continue;
						}

						$products_flash_sale[] = [
							'id'   => $product->get_id(),
							'text' => $product->get_name()
						];
					}
					?>

                    <tr>
                        <td class="text-center"><?php echo ++ $key ?></td>
                        <td>
                            <div class="d-flex flex-column gap-small">
                                <div class="d-flex align-items-center gap-small text-default">
                                    <span class="w-72">Title:</span>
                                    <span class="fw-700 fs-12 lh-16 text-capitalize"><?php echo $value->title ?></span>
                                </div>
                                <div class="d-flex align-items-center gap-small text-default">
                                    <span class="w-72">Start Date:</span>
                                    <span class="fw-700 fs-12 lh-16 text-capitalize"><?php echo revo_shine_formatted_date( $value->start ) ?></span>
                                </div>
                                <div class="d-flex align-items-center gap-small text-default">
                                    <span class="w-72">End date:</span>
                                    <span class="fw-700 fs-12 lh-16 text-capitalize"><?php echo revo_shine_formatted_date( $value->end ) ?></span>
                                </div>
                                <div class="d-flex align-items-center gap-small text-default">
                                    <span class="w-72">Status:</span>
									<?php echo revo_shine_badge_output_html( $value->is_active ) ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center justify-content-center py-2">
                                <img src="<?php echo $value->image ?>" class="img-fluid" style="width: 120px"
                                     alt="flash sale icon">
                            </div>
                        </td>
                        <td class="align-top">
							<?php
							if ( ! empty( $products_flash_sale ) ) {
								foreach ( $products_flash_sale as $product ) {
									echo '<span class="badge badge-primary fs-12 lh-12 fw-600">' . $product['text'] . '</span> ';
								}
							} else {
								echo '<span class="badge badge-danger fs-12 lh-12 fw-600">Empty Products !</span>';
							}
							?>
                        </td>
                        <td>
                            <div class="d-flex flex-column align-items-center gap-small">
                                <button class="btn w-100 btn-primary btn-update" data-id="<?php echo $value->id ?>"
                                        data-data="<?php echo base64_encode( json_encode( $value ) ) ?>"
                                        data-products="<?php echo base64_encode( json_encode( $products_flash_sale ) ) ?>">
                                    Update
                                </button>
                                <button class="btn w-100 btn-outline-danger btn-destroy"
                                        data-id="<?php echo $value->id ?>">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
				<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAction" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Add Flash Sale</div>
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
						'placeholder' => 'Ex: Flash Sale',
						'is_required' => true
					] );

					revo_shine_get_admin_template_part( 'components/select', [
						'id'          => 'products',
						'name'        => 'products[]',
						'label'       => 'Product To Show',
						'options'     => [],
						'class'       => 'd-flex align-items-start gap-base',
						'is_select2'  => true,
						'is_multiple' => true,
						'is_required' => true
					] );

					revo_shine_get_admin_template_part( 'components/input', [
						'id'          => 'datepicker',
						'label'       => 'Start - End Date',
						'name'        => 'date',
						'class'       => 'd-flex align-items-start gap-base',
						'placeholder' => 'MM / DD / YYYY 20:00',
						'is_required' => true
					] );

					revo_shine_get_admin_template_part( 'components/upload', [
						'id'          => 'image',
						'label'       => 'Image',
						'name'        => 'image',
						'value'       => '',
						'class'       => 'd-flex align-items-start gap-base',
						'accent_text' => 'Upload Photo Here',
						'helper_text' => 'Best Size : 72 x 72px, Max File Size : 2MB',
						'is_required' => true
					] );

					?>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="id">
                    <input type="hidden" name="action" value="store">
                    <button type="button" class="btn btn-secondary close" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$img_example = revo_shine_url() . '/assets/images/example_flash_sale.webp';
include REVO_SHINE_TEMPLATE_PATH . 'admin/parts/modal_example.php';
?>

<script>
    jQuery(document).ready(function ($) {
        window.onload = function () {
            window.select2Builder('product', 'select[name="products[]"]');
        }

        const bodyEL = jQuery('body');

        const picker = new easepick.create({
            element: "#datepicker",
            format: "DD/MM/YY HH:mm",
            zIndex: 100,
            css: [
                "https://cdn.jsdelivr.net/npm/@easepick/bundle@1.2.1/dist/index.css"
            ],
            plugins: [
                "RangePlugin",
                "TimePlugin"
            ]
        });

        bodyEL.on("click", ".btn-add", function () {
            picker.setStartDate(Date.now());
            picker.setEndDate(Date.now() + 86400000);
        });

        bodyEL.on("click", ".btn-update", function () {
            const data = JSON.parse(atob(jQuery(this).data("data")));
            const products = JSON.parse(atob(jQuery(this).data("products")));
            let newOption = '';

            products.map(elo => {
                newOption += `<option value='${elo.id}' selected>${elo.text}</option>`
            });

            picker.setStartDate(Date.parse(data.start));
            picker.setEndDate(Date.parse(data.end));

            jQuery("#modalAction .modal-header .modal-title").html("Update Flash Sale");
            jQuery('#modalAction input[name="title"]').val(data.title);
            jQuery('#modalAction input[name="image"]').val(data.image);
            jQuery('#modalAction select[name="products[]"]').append(newOption).trigger('change');

            jQuery('#modalAction .form-field-upload-container').addClass('file-attached');
            jQuery('#modalAction .form-field-upload-container .form-field-file-preview img').attr('src', data.image);

            jQuery('#modalAction .modal-footer input[name="id"]').val(jQuery(this).data("id"));
            jQuery('#modalAction .modal-footer input[name="action"]').val("update");
            jQuery("#modalAction").modal("show");
        });

        bodyEL.on("hidden.bs.modal", "#modalAction", function () {
            jQuery("#modalAction .modal-header .modal-title").html("Add Flash Sale");
            jQuery('#modalAction input[name="title"]').val('');
            jQuery('#modalAction input[name="url_link"]').val('');
            jQuery('#modalAction .form-field-upload-container').removeClass('file-attached');

            jQuery('select[name="products[]"]').html('');
            jQuery('#modalAction .modal-footer input[name="id"]').val('');
            jQuery('#modalAction .modal-footer input[name="action"]').val("store");
        });
    });
</script>
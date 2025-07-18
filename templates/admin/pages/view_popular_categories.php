<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( $_POST['action'] === 'store' || $_POST['action'] === 'update' ) {
		$alert = array(
			'type'    => 'error',
			'title'   => 'Failed !',
			'message' => 'Failed to execute your request',
		);

		$data = [
			'title'      => $_POST['title'],
			'categories' => json_encode( $_POST['categories'] ),
		];

		if ( empty( $_POST['id'] ) ) {
			$wpdb->insert( 'revo_popular_categories', $data );

			if ( @$wpdb->insert_id > 0 ) {
				$alert = array(
					'type'    => 'success',
					'title'   => 'Success !',
					'message' => 'Popular Categories Success Added',
				);
			}
		} else {
			$update_status = $wpdb->update( 'revo_popular_categories', $data, [ 'id' => $_POST['id'] ] );

			if ( $update_status !== false ) {
				$alert = array(
					'type'    => 'success',
					'title'   => 'Success !',
					'message' => 'Popular Categories Success Updated',
				);
			}
		}
	}

	if ( $_POST["action"] === 'destroy' ) {
		header( 'Content-type: application/json' );

		$alert = array(
			'type'    => 'error',
			'title'   => 'Failed !',
			'message' => 'Failed to Delete Popular Category',
		);

		$query = $wpdb->update(
			'revo_popular_categories',
			[ 'is_deleted' => '1' ],
			array( 'id' => $_POST['id'] ),
			array( '%s' ),
			array( '%d' )
		);

		if ( $query ) {
			$alert = array(
				'type'    => 'success',
				'title'   => 'Success !',
				'message' => 'Categories Success Deleted',
			);
		}
	}

	$_SESSION["alert"] = $alert ?? [
		'type'    => 'error',
		'title'   => 'Failed !',
		'message' => 'Failed to execute your request',
	];

	if ( $_SESSION['alert']['type'] === 'success' ) {
		revo_shine_rebuild_cache( 'revo_home_data' );
	}
}

$data_banner     = $wpdb->get_results( "SELECT * FROM revo_popular_categories WHERE is_deleted = 0", OBJECT );
$data_categories = json_decode( revo_shine_get_categories() );
?>

<div class="admin-section-container">
    <div class="admin-section-item">
        <div class="d-flex align-items-center justify-content-between">
            <div class="admin-section-item-title text-capitalize">
                Popular Categories <?php echo buttonQuestion() ?>
            </div>
            <input class="input-search" type="text" id="datatables-search" placeholder="Search..">
        </div>
        <div class="admin-section-item-body">
            <table class="table table-bordered" id="datatables" data-title="Add Category" data-button-modal="true"
                   width="100%">
                <thead>
                <tr>
                    <th width="5%" class="text-center">No</th>
                    <th width="35%">Title</th>
                    <th width="45%">List Categories</th>
                    <th width="15%" class="hidden-xs text-center">Action</th>
                </tr>
                </thead>
                <tbody>
				<?php foreach ( $data_banner as $key => $value ) : ?>
                    <tr>
                        <td class="align-top text-center"><?php echo ++ $key; ?></td>
                        <td class="align-top text-capitalize"><?php echo $value->title ?></td>
                        <td class="align-top">
							<?php
							$categories = json_decode( $value->categories );

							if ( is_array( $categories ) ) {
								for ( $i = 0; $i < count( $categories ); $i ++ ) {
									echo '<div class="badge badge-primary fs-12 lh-12 fw-600">' . ( revo_shine_get_category( $categories[ $i ] )?->name ?? ' - ' ) . '</div> ';
								}
							}
							?>
                        </td>
                        <td class="align-top">
                            <div class="d-flex flex-column align-items-center gap-small">
                                <button class="btn w-100 btn-primary btn-update" data-id="<?php echo $value->id ?>"
                                        data-data="<?php echo base64_encode( json_encode( $value ) ) ?>">
                                    Update
                                </button>
                                <button class="btn w-100 btn-outline-danger btn-destroy"
                                        data-id="<?php echo $value->id ?>">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
				<?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAction" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Add Popular Category</div>
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
						'placeholder' => 'Ex: Popular Categories',
						'is_required' => true
					] );

					$options = [];
					foreach ( $data_categories as $category ) {
						$options[ $category->id ] = $category->text;
					}

					revo_shine_get_admin_template_part( 'components/select', [
						'id'          => 'categories',
						'name'        => 'categories[]',
						'label'       => 'Select Category',
						'value'       => '',
						'options'     => $options,
						'class'       => 'd-flex align-items-start gap-base',
						'is_required' => true,
						'is_multiple' => true,
						'is_select2'  => true
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
$img_example = revo_shine_url() . '/assets/images/example_popular_category.webp';
include REVO_SHINE_TEMPLATE_PATH . 'admin/parts/modal_example.php';
?>

<script>
    jQuery(document).ready(function ($) {
        const bodyEL = jQuery('body');

        $('.select2').select2({
            width: '100%',
            dropdownParent: jQuery("#modalAction"),
            allowClear: false,
            placeholder: "Select an option",
        });

        bodyEL.on("click", ".btn-update", function () {
            let _data = atob(jQuery(this).data("data"));
            let data = JSON.parse(_data);

            jQuery("#modalAction .modal-header .modal-title").html("Update Popular Category");
            jQuery("#modalAction input[name='title']").val(data.title);
            jQuery('#modalAction select.select2').val(JSON.parse(data.categories)).trigger('change');

            jQuery('#modalAction .modal-footer input[name="id"]').val(jQuery(this).data("id"));
            jQuery('#modalAction .modal-footer input[name="action"]').val("update");

            jQuery("#modalAction").modal("show");
        });

        bodyEL.on("hidden.bs.modal", "#modalAction", function () {
            jQuery("#modalAction .modal-header .modal-title").html("Add Popular Category");
            jQuery("#modalAction input[name='title']").val('');
            jQuery('#modalAction select.select2').val('.').trigger('change');

            jQuery('#modalAction .modal-footer input[name="id"]').val('');
            jQuery('#modalAction .modal-footer input[name="action"]').val("store");
        });
    });
</script>
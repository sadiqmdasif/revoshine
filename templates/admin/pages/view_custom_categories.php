<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

$section_type = $_GET['revo_type'] ?? 'mini';
if ( ! in_array( $section_type, [ 'mini', 'big-category', 'category-3', 'category-4', 'category-6', 'categories-two-rows', ] ) ) {
	exit;
}

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {

	if ( $_POST['action'] === 'store' || $_POST['action'] === 'update' ) {
		$alert = [
			'type'    => 'error',
			'title'   => 'Failed !',
			'message' => 'Failed to Add Home Category',
		];

		if ( ! empty( $_POST['image'] ) ) {
			$alert = array(
				'type'    => 'error',
				'title'   => 'Uploads Error !',
				'message' => 'Your file type is not allowed. Only support jpg, png, jpeg.',
			);

			if ( revo_shine_check_image_type( $_POST['image'] ) ) {
				$category = revo_shine_get_category( $_POST['category_id'] );

				$data = [
					'order_by'      => $_POST['sort'],
					'category_id'   => $_POST['category_id'],
					'category_name' => json_encode( [ 'title' => $category->name ] ),
					'image'         => $_POST['image'],
					'section_type'  => $section_type
				];

				if ($GLOBALS['Revo_Shine_Multilang']->get_plugin_status()) {
					$data['lang'] = apply_filters('revo_shine_banner_lang_code', null);
				}

				if ( empty( $_POST['id'] ) ) {
					$wpdb->insert( 'revo_list_categories', $data );

					if ( @$wpdb->insert_id > 0 ) {
						$alert = array(
							'type'    => 'success',
							'title'   => 'Success !',
							'message' => 'Home Category Success Saved',
						);
					}
				} else {
					$update_status = $wpdb->update( 'revo_list_categories', $data, [ 'id' => $_POST['id'] ] );

					if ( $update_status !== false ) {
						$alert = array(
							'type'    => 'success',
							'title'   => 'Success !',
							'message' => 'Home Category Success Updated',
						);
					}
				}
			}
		}

		$_SESSION["alert"] = $alert;
	}

	if ( $_POST['action'] === 'destroy' ) {
		header( 'Content-type: application/json' );

		$alert = array(
			'type'    => 'error',
			'title'   => 'Failed !',
			'message' => 'Failed to Delete Home Category',
		);

		$query = $wpdb->update(
			'revo_list_categories',
			[ 'is_deleted' => '1' ],
			array( 'id' => $_POST['id'] ),
			array( '%s' ),
			array( '%d' )
		);

		if ( $query ) {
			$alert = array(
				'type'    => 'success',
				'title'   => 'Success !',
				'message' => 'Home Category Success Deleted',
			);
		}

		$_SESSION["alert"] = $alert;
	}

	if ( $_SESSION['alert']['type'] === 'success' ) {
		revo_shine_rebuild_cache( 'revo_home_data' );
	}
}

$data_banner     = $wpdb->get_results( "SELECT * FROM `revo_list_categories` WHERE is_deleted = 0 AND section_type = '$section_type' " . apply_filters('revo_shine_query_banner_lang', ''), OBJECT );

$data_categories = json_decode( revo_shine_get_categories() );

?>

<div class="admin-section-container">
    <div class="admin-section-item">
        <div class="d-flex align-items-center justify-content-between">
            <div class="admin-section-item-title text-capitalize">
				<?php if ( $section_type === 'mini' ): ?>
					Home Categories - 1Rows <?php echo buttonQuestion() ?>
				<?php elseif ( $section_type === 'categories-two-rows' ): ?>
					Home Categories - 2Rows <?php echo buttonQuestion() ?>
				<?php else: ?>
					Home Categories - <?php echo str_replace( '-', ' ', $section_type ) ?> <?php echo buttonQuestion() ?>
				<?php endif; ?>
            </div>

            <div class="d-flex align-item-center justify-content-center gap-3">
				<input class="input-search" type="text" id="datatables-search" placeholder="Search..">

				<?php if ($GLOBALS['Revo_Shine_Multilang']->get_plugin_status()) :

                    $languages = $GLOBALS['Revo_Shine_Multilang']->get_languages();
                    $selected_lang = $_GET['banner_lang'] ?? $languages[0]['code'];

					$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
					$currentUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
					$parsedUrl = parse_url($currentUrl);
					$path = $parsedUrl['path'];
					$query = isset($parsedUrl['query']) ? $parsedUrl['query'] : '';
					parse_str($query, $queryParams);
					unset($queryParams['lang']);

					$newQuery = http_build_query($queryParams);

					$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $path;
					if (!empty($newQuery)) {
						$baseUrl .= '?' . $newQuery;
					}
                ?>
				
					<div class="dropdown" style="display: inline-block;">
						<div class="d-flex justify-content-center align-items-center" style="height: 40px; width: 40px; border: 1px solid var(--border-weak); border-radius: var(--cornerbase);" type="button" id="toggleDiv" data-bs-toggle="dropdown" aria-expanded="false">
							<?php include REVO_SHINE_TEMPLATE_PATH . 'admin/components/icons/lang.php'; ?>
						</div>

						<ul class="dropdown-menu" aria-labelledby="toggleDiv">
							<?php foreach ($languages as $language) : ?>
								<li>
									<a class="dropdown-item py-3" style="<?php echo $selected_lang === $language['code'] ? 'background-color: #6267e1; color: white;' : '' ?>" href="<?php echo $baseUrl . '&banner_lang=' . $language['code'] ?>">
										<?php echo $language['name'] ?>
									</a>
								</li>
							<?php endforeach ?>
						</ul>
					</div>

				<?php endif ?>

			</div>
        </div>
        <div class="admin-section-item-body">
			<?php

			$display_button_open_modal = false;

			switch ( $section_type ) {
				case 'mini':
					$display_button_open_modal = true;
					break;
				case 'categories-two-rows':
					$display_button_open_modal = true;
					break;
				case 'big-category':
					if ( count( $data_banner ) < 2 ) {
						$display_button_open_modal = true;
					}
					break;
				case 'category-3':
					if ( count( $data_banner ) < 3 ) {
						$display_button_open_modal = true;
					}
					break;
				case 'category-4':
					if ( count( $data_banner ) < 4 ) {
						$display_button_open_modal = true;
					}
					break;
				case 'category-6':
					if ( count( $data_banner ) < 6 ) {
						$display_button_open_modal = true;
					}
					break;
			}

			?>

            <table class="table table-bordered" id="datatables" data-title="Add Category"
                   data-button-modal="<?php echo $display_button_open_modal ?>" width="100%">
                <thead>
                <tr>
                    <th width="5%" class="text-center">Sort</th>
                    <th width="35%">Title Categories</th>
                    <th width="45%" class="text-center">Icon</th>
                    <th width="15%" class="text-center hidden-xs">Action</th>
                </tr>
                </thead>
                <tbody>
				<?php foreach ( $data_banner as $key => $value ) : ?>
                    <tr>
                        <td class="text-center"><?php echo $value->order_by ?></td>
                        <td><?php echo json_decode( $value->category_name )->title ?></td>
                        <td>
                            <div class="d-flex align-items-center justify-content-center py-2">
                                <img src="<?php echo $value->image ?>" class="img-fluid" style="width: 120px">
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column align-items-center gap-small">
                                <button class="btn w-100 btn-primary btn-update"
                                        data-id="<?php echo $value->id ?>"
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
				<?php endforeach; ?>

				<?php $key = ! empty( $data_banner ) ? $key + 2 : 1 ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAction" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Add Home Categories</div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="#">
                <div class="modal-body">
					<?php

					$options = [];
					foreach ( $data_categories as $category ) {
						$options[ $category->id ] = $category->text;
					}

					revo_shine_get_admin_template_part( 'components/select', [
						'id'          => 'category_id',
						'name'        => 'category_id',
						'label'       => 'Select Category',
						'value'       => $values['includes'] ?? '',
						'options'     => $options,
						'class'       => 'd-flex align-items-start gap-base',
						'is_select2'  => false,
						'is_required' => true
					] );

					revo_shine_get_admin_template_part( 'components/input', [
						'id'          => 'sort',
						'type'        => 'number',
						'label'       => 'Sort',
						'name'        => 'sort',
						'class'       => 'd-flex align-items-start gap-base',
						'placeholder' => 'Ex: 1',
						'is_required' => true
					] );

					revo_shine_get_admin_template_part( 'components/upload', [
						'id'          => 'image',
						'label'       => 'Image',
						'name'        => 'image',
						'value'       => '',
						'class'       => 'd-flex align-items-start gap-base',
						'accent_text' => 'Upload Photo Here',
						'helper_text' => 'Best Size : 75 x 75 px, Max File Size : 500kb',
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
$img_example = revo_shine_url() . "assets/images/categories-{$section_type}.webp";
require_once REVO_SHINE_TEMPLATE_PATH . 'admin/parts/modal_example.php';
?>

<script>
    jQuery(document).ready(function ($) {
        const bodyEL = jQuery('body');

        bodyEL.on("click", ".btn-update", function () {
            let _data = atob(jQuery(this).data("data"));
            let data = JSON.parse(_data);

            jQuery("#modalAction .modal-header .modal-title").html("Update Home Categories");
            jQuery("#modalAction input[name='sort']").val(data.order_by);
            jQuery('#modalAction input[name="image"]').val(data.image);
            jQuery('#modalAction select[name="category_id"]').val(data.category_id).trigger('change');

            jQuery('#modalAction .form-field-upload-container').addClass('file-attached');
            jQuery('#modalAction .form-field-upload-container .form-field-file-preview img').attr('src', data.image);

            jQuery('#modalAction .modal-footer input[name="id"]').val(jQuery(this).data("id"));
            jQuery('#modalAction .modal-footer input[name="action"]').val("update");

            jQuery("#modalAction").modal("show");
        });

        bodyEL.on("hidden.bs.modal", "#modalAction", function () {
            jQuery("#modalAction .modal-header .modal-title").html("Add Home Categories");
            jQuery("#modalAction input[name='sort']").val('');
            jQuery('#modalAction input[name="url_link"]').val('');
            jQuery('#modalAction select[name="category_id"]').val('').trigger('change');
            jQuery('#modalAction .form-field-upload-container').removeClass('file-attached');
            jQuery('#modalAction .modal-footer input[name="id"]').val('');
            jQuery('#modalAction .modal-footer input[name="action"]').val("store");
        });
    });
</script>
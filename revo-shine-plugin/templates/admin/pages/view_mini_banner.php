<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

$section_type = $_GET['revo_type'] ?? 'special-promo';
if ( ! in_array( $section_type, [
	'special-promo',
	'love-these-items',
	'blog-banner',
	'single-banner',
	'popup-promo'
] ) ) {
	exit;
}

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {

	if ( $_POST['action'] === 'store' || $_POST['action'] === 'update' ) {
		$alert = [
			'type'    => 'error',
			'title'   => 'Query Error !',
			'message' => 'Failed to Add Additional Banner',
		];

		if ( ! empty( $_POST['link_to_select'] ) || ! empty( $_POST['link_to_input'] ) ) {

			$selected_value     = $_POST['link_to'] === 'url' ? $_POST['link_to_input'] : $_POST['link_to_select'];
			$res_selected_value = revo_shine_admin_get_selected_linked_value( $selected_value, $_POST['link_to'] );

			if ( empty( $res_selected_value ) ) {
				$alert = array(
					'type'    => 'error',
					'title'   => 'Query Error !',
					'message' => 'Failed to Add Additional Banner. Your selected item is not found.',
				);
			} elseif ( ! revo_shine_check_image_type( $_POST['image'] ?? '' ) ) {
				$alert = array(
					'type'    => 'error',
					'title'   => 'Failed !',
					'message' => 'Failed to Save Slider, Image Type Not Allowed',
				);
			} else {
				$data = [
					'order_by'     => $_POST['sort'],
					'product_id'   => $_POST['link_to_select'] ?? 0,
					'product_name' => $res_selected_value,
					'type'         => ucwords( str_replace( '-', ' ', $section_type ) ),
					'image'        => $_POST['image'],
					'section_type' => $section_type
				];

				if ($GLOBALS['Revo_Shine_Multilang']->get_plugin_status()) {
					$data['lang'] = apply_filters('revo_shine_banner_lang_code', null);
				}

				if ( empty( $_POST['id'] ) ) {
					$wpdb->insert( 'revo_list_mini_banner', $data );

					if ( @$wpdb->insert_id > 0 ) {
						$alert = array(
							'type'    => 'success',
							'title'   => 'Success !',
							'message' => 'Banner Success Saved',
						);
					}
				} else {
					$update_status = $wpdb->update( 'revo_list_mini_banner', $data, [ 'id' => $_POST['id'] ] );

					if ( $update_status !== false ) {
						$alert = array(
							'type'    => 'success',
							'title'   => 'Success !',
							'message' => 'Additional Banner Success Updated',
						);
					}
				}
			}

		}

		$_SESSION["alert"] = $alert;
	}

	if ( $_POST['action'] === 'destroy' ) {
		header( 'Content-type: application/json' );

		$query = $wpdb->update(
			'revo_list_mini_banner',
			[ 'is_deleted' => '1' ],
			array( 'id' => $_POST['id'] )
		);

		$alert = array(
			'type'    => 'error',
			'title'   => 'Query Error !',
			'message' => 'Failed to Delete  Banner',
		);

		if ( $query ) {
			$alert = array(
				'type'    => 'success',
				'title'   => 'Success !',
				'message' => 'Banner Success Deleted',
			);
		}

		$_SESSION["alert"] = $alert;
	}

	if ( $_SESSION['alert']['type'] === 'success' ) {
		revo_shine_rebuild_cache( 'revo_home_data' );
	}
}

$data_banner = $wpdb->get_results("SELECT * FROM revo_list_mini_banner WHERE is_deleted = 0 AND section_type = '$section_type'" . apply_filters('revo_shine_query_banner_lang', ''), OBJECT);

?>

<div class="admin-section-container">
    <div class="admin-section-item">
        <div class="d-flex align-items-center justify-content-between">
            <div class="admin-section-item-title text-capitalize">
                Additional Banner - <?php echo str_replace( '-', ' ', $section_type ) ?> <?php echo buttonQuestion() ?>
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

			if ( ! in_array( $section_type, [ 'single-banner', 'popup-promo' ] ) ) {
				$display_button_open_modal = true;
			} elseif ( count( $data_banner ) < 1 ) {
				$display_button_open_modal = true;
			}

			?>

            <table class="table table-bordered" id="datatables" data-title="Add Banner"
                   data-button-modal="<?php echo $display_button_open_modal ?>">
                <thead>
                <tr>
                    <th class="text-center">Sort</th>
                    <th>Type Banner</th>
                    <th class="text-center">Image</th>
                    <th>Link To</th>
                    <th class="text-center hidden-xs">Action</th>
                </tr>
                </thead>
                <tbody>
				<?php foreach ( $data_banner as $key => $banner ) : ?>
					<?php
					$xplode       = explode( "|", $banner->product_name );
					$is_product   = $xplode[0] == 'cat' || $xplode[0] == 'blog' || $xplode[0] == 'attribute' || $xplode[0] == 'url' ? false : true;
					$is_blog      = $xplode[0] == 'blog' ? true : false;
					$is_cat       = $xplode[0] == 'cat' ? true : false;
					$is_attribute = $xplode[0] == 'attribute' ? true : false;
					$indexIP      = $is_product ? 0 : 1;
					$title_item   = $is_product ? "Product" : ( $is_blog ? "Blog" : ( $is_cat ? "Category" : ( $is_attribute ? "Attribute" : "URL" ) ) );
					?>
                    <tr>
                        <td class="text-center"><?php echo $banner->order_by ?></td>
                        <td><?php echo $banner->type ?></td>
                        <td>
                            <div class="d-flex align-items-center justify-content-center py-2">
                                <img src="<?php echo $banner->image ?>" class="img-fluid" style="width: 120px">
                            </div>
                        </td>
                        <td><?php echo "<strong>$title_item</strong> : $xplode[$indexIP]" ?></td>
                        <td>
                            <div class="d-flex flex-column align-items-center gap-small">
                                <button class="btn w-100 btn-primary btn-update"
                                        data-id="<?php echo $banner->id ?>"
                                        data-data="<?php echo base64_encode( json_encode( $banner ) ) ?>">
                                    Update
                                </button>
                                <button class="btn w-100 btn-outline-danger btn-destroy"
                                        data-id=" <?php echo $banner->id ?> ">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
				<?php endforeach; ?>
				<?php
				if ( ! empty( $data_banner ) ) {
					$key = $key + 2;
				} else {
					$key = 1;
				}
				?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAction" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Add Additional Banner</div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="#">
                <div class="modal-body">
					<?php

					revo_shine_get_admin_template_part( 'components/radio', [
						'id'          => 'link_to',
						'name'        => 'link_to',
						'label'       => 'Link To',
						'value'       => '',
						'is_required' => true,
						'class'       => 'd-flex align-items-start gap-base',
						'options'     => [
							'product'   => 'Product',
							'attribute' => 'Attribute',
							'category'  => 'Category',
							'url'       => 'URL',
							'blog'      => 'Blog'
						]
					] );

					revo_shine_get_admin_template_part( 'components/select', [
						'id'         => 'link_to_select',
						'name'       => 'link_to_select',
						'label'      => '',
						'value'      => $values['includes'] ?? '',
						'options'    => [],
						'is_select2' => true,
						'class'      => 'd-none align-items-start gap-base',
					] );

					revo_shine_get_admin_template_part( 'components/input', [
						'id'          => 'link_to_input',
						'label'       => '',
						'name'        => 'link_to_input',
						'placeholder' => 'Ex: https://yourdomain.com/product-one',
						'class'       => 'd-none align-items-start gap-base',
						'is_required' => false
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
$img_example = revo_shine_url() . "/assets/images/mini-banner-{$section_type}.webp";
require_once REVO_SHINE_TEMPLATE_PATH . 'admin/parts/modal_example.php';
?>

<script>
    jQuery(document).ready(function ($) {
		$('#toggleDiv').on('click', function() {
			$('#mySelect').toggleClass('d-none d-block');
		});

        const bodyEL = jQuery('body');

        bodyEL.on("click", ".btn-update", function () {
            let _data = atob(jQuery(this).data("data"));
            let data = JSON.parse(_data);

            const obj = data.product_name.split('|');

            data.link_to = obj.length > 1 ? obj[0] : "product";
            if (data.link_to === "cat") {
                data.link_to = "category";
            }

            hideSection(data.link_to);

            jQuery("#modalAction .modal-header .modal-title").html("Update Additional Banner");
            jQuery("#modalAction input[name='sort']").val(data.order_by);
            jQuery('#modalAction input[name="image"]').val(data.image);
            jQuery(`#modalAction input[name="link_to"][value='${data.link_to}']`).prop("checked", true);

            jQuery('#modalAction .form-field-upload-container').addClass('file-attached');
            jQuery('#modalAction .form-field-upload-container .form-field-file-preview img').attr('src', data.image);

            if (data.link_to === "url") {
                jQuery('input[name="link_to_input"]').val(obj[1]);
            } else {
                select2Builder(data.link_to);

                const newOption = new Option(
                    data.link_to === 'product' ? obj[0] : obj[1],
                    data.product_id,
                    true,
                    true
                );

                jQuery(".select2").append(newOption).trigger("change");
            }

            jQuery('#modalAction .modal-footer input[name="id"]').val(jQuery(this).data("id"));
            jQuery('#modalAction .modal-footer input[name="action"]').val("update");

            jQuery("#modalAction").modal("show");
        });

        bodyEL.on("hidden.bs.modal", "#modalAction", function () {
            hideSection("", true);

            jQuery("#modalAction .modal-header .modal-title").html("Add Additional Banner");
            jQuery("#modalAction input[name='sort']").val('');
            jQuery('#modalAction input[name="url_link"]').val('');
            jQuery('#modalAction input[name="link_to"]').prop("checked", false);
            jQuery("#modalAction .modal-body select").val('').trigger("change");
            jQuery('#modalAction .form-field-upload-container').removeClass('file-attached');

            jQuery('#modalAction .modal-footer input[name="id"]').val('');
            jQuery('#modalAction .modal-footer input[name="action"]').val("store");
        });
    });
</script>
<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

$section_type = $_GET['revo_type'] ?? 'products-recomendation';
if ( ! in_array( $section_type, [
	'products-recomendation',
	'products-special',
	'products-our-best-seller',
	'other-products',
	'festive-promotions',
] ) ) {
	exit;
}

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( $_POST["action"] == 'update' ) {
		$alert = array(
			'type'    => 'error',
			'title'   => 'Query Error !',
			'message' => 'Failed to Update Additional Products ',
		);

		$products = json_encode( $_POST['products'] ?? [] );

		$update_status = $wpdb->update( 'revo_extend_products', array(
			'title'       => $_POST['title'],
			'description' => $_POST['description'],
			'products'    => $products,
		), array( 'id' => $_POST['id'] ) );

		if ( $update_status !== false ) {
			$alert = array(
				'type'    => 'success',
				'title'   => 'Success !',
				'message' => 'Additional products updated successfully !',
			);
		}

		$_SESSION["alert"] = $alert;
	}

	if ( $_SESSION['alert']['type'] === 'success' ) {
		revo_shine_rebuild_cache( 'revo_home_data' );
	}
}

$data_extend_products = $wpdb->get_results( "SELECT * FROM revo_extend_products WHERE is_deleted = 0 AND section_type = '{$section_type}'", OBJECT );
?>

<div class="admin-section-container">
    <div class="admin-section-item">
        <div class="d-flex align-items-center justify-content-between">
            <div class="admin-section-item-title text-capitalize">
                Home Additional Products - <?php echo str_replace( '-', ' ', $section_type ) ?> <?php echo buttonQuestion() ?>
            </div>
            <input class="input-search" type="text" id="datatables-search" placeholder="Search..">
        </div>
        <div class="admin-section-item-body">
            <table class="table table-bordered" id="datatables" data-title="" data-button-modal="false">
                <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th>Details</th>
                    <th style="width: 35%">List Product</th>
                    <th class="text-center hidden-xs">Action</th>
                </tr>
                </thead>
                <tbody>
				<?php foreach ( $data_extend_products as $key => $value ) : ?>
                    <tr>
                        <td class="align-top text-center"><?php echo ++ $key ?></td>
                        <td>
                            <div class="d-flex flex-column gap-small">
                                <div class="d-flex align-items-center gap-small text-default">
                                    <span class="w-80">Title:</span>
                                    <span class="fw-700 fs-12 lh-16 text-capitalize"><?php echo $value->title ?></span>
                                </div>
                                <div class="d-flex align-items-center gap-small text-default">
                                    <span class="w-80">Description:</span>
                                    <span class="fw-700 fs-12 lh-16 text-capitalize"><?php echo $value->description ?></span>
                                </div>
                                <div class="d-flex align-items-center gap-small text-default">
                                    <span class="w-80">Show In:</span>
									<?php echo revo_shine_get_extend_product_type( $value->type )['text'] ?>
                                </div>
                            </div>
                        </td>
                        <td class="align-top">
							<?php
							$product_ids   = json_decode( $value->products );
							$product_shown = 0;

							if ( ! empty( $product_ids ) && ! is_null( $product_ids ) ) {
								$detail_res_data_flash_sale = [];

								foreach ( $product_ids as $product_id ) {
									$product = wc_get_product( $product_id );
									if ( ! $product ) {
										continue;
									}

									?>

                                    <div class="badge badge-primary fs-12 lh-12 fw-600"><?php echo $product->get_name() ?></div> <?php

									$detail_res_data_flash_sale[] = [
										'id'   => $product->get_id(),
										'text' => $product->get_name()
									];

									$product_shown ++;
								}
							}

							if ( $product_shown <= 0 ) {
								?> <span class="badge badge-danger p-2">empty !</span> <?php
							}
							?>
                        </td>
                        <td class="align-top">
                            <button class="btn w-100 btn-primary" data-bs-toggle="modal" data-bs-target="#modalAction"
                                    onclick="update(this)">
                                Update
                            </button>
                            <div class="modal fade" id="modalAction" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <div class="modal-title">Update <?php echo $value->title ?></div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                        </div>
                                        <form method="POST" action="#">
                                            <div class="modal-body">
												<?php

												revo_shine_get_admin_template_part( 'components/input', [
													'id'          => 'title',
													'label'       => 'Title',
													'name'        => 'title',
													'class'       => 'd-flex align-items-start gap-base',
													'placeholder' => 'Ex: New Additional Products',
													'value'       => $value->title,
													'is_required' => true
												] );

												revo_shine_get_admin_template_part( 'components/input', [
													'id'          => 'description',
													'label'       => 'Description',
													'name'        => 'description',
													'class'       => 'd-flex align-items-start gap-base',
													'placeholder' => 'Ex: Recommended Products',
													'value'       => $value->description,
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

												?>
                                            </div>
                                            <div class="modal-footer">
                                                <input type="hidden" name="id" value="<?php echo $value->id ?>">
                                                <input type="hidden" name="action" value="update">
                                                <button type="button" class="btn btn-secondary close"
                                                        data-bs-dismiss="modal">Close
                                                </button>
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
				<?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$img_example = revo_shine_url() . "/assets/images/additional-product-{$section_type}.webp";
require_once REVO_SHINE_TEMPLATE_PATH . 'admin/parts/modal_example.php';
?>

<script>
    function update(el) {
        const target = jQuery(el).data('bs-target');
        const select2 = jQuery(target).find('select[name="products[]"]');
        const res_data_flash_sale = <?php echo json_encode( $detail_res_data_flash_sale ) ?>;

        select2.html('');

        select2Builder('product', 'select[name="products[]"]');

        const newOptions = res_data_flash_sale.map(elo => {
            return `<option value='${elo.id}' selected>${elo.text}</option>`;
        });

        select2.append(newOptions.join('')).trigger('change');
    }
</script>
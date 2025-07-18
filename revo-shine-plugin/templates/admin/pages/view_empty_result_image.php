<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {

	$fillable = [ '404_images', 'thanks_order', 'empty_transaksi', 'search_empty', 'login_required' ];

	foreach ( $_POST as $post_key => $post_value ) {

		if ( ! in_array( $post_key, $fillable ) ) {
			continue;
		}

		if ( ! revo_shine_check_image_type( $post_value ?? '' ) ) {
			continue;
		}

		$wpdb->update( 'revo_mobile_variable',
			[
				'image' => $post_value,
			],
			[
				'slug'  => 'empty_image',
				'title' => $post_key
			]
		);
	}

	revo_shine_rebuild_cache( 'revo_home_data' );
}

$query_image = "SELECT id, slug, title, image FROM `revo_mobile_variable` WHERE slug = 'empty_image'";
$data_image  = $wpdb->get_results( $query_image, OBJECT );
?>

<form action="#" method="post">
    <div class="admin-section-container">
        <div class="admin-section-item">
            <div class="admin-section-item-title">Setting Result Image</div>
            <div class="admin-section-item-body">
				<?php

				foreach ( $data_image as $data ) {
					$label = str_replace( [ '_', 'images' ], [ ' ', '' ], $data->title );

					revo_shine_get_admin_template_part( 'components/upload', [
						'id'          => $data->title,
						'label'       => 'Image ' . $label,
						'name'        => $data->title,
						'value'       => $data->image ?? '',
						'accent_text' => 'Upload Photo Here',
						'helper_text' => 'Best Size : 450 x 450 px',
					] );
				}

				?>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary btn-save-changes" type="submit">Save Changes</button>
        </div>
    </div>
</form>
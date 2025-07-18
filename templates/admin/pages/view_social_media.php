<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

$data_social_media     = $wpdb->get_row( "SELECT id, slug, title, description FROM `revo_mobile_variable` WHERE slug = 'sosmed_link'", OBJECT );
$data_social_media_url = json_decode( $data_social_media->description, true );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {

	$fillable = [ 'whatsapp', 'facebook', 'instagram', 'youtube', 'tiktok' ];

	foreach ( $_POST as $post_key => $post_value ) {
		if ( ! in_array( $post_key, $fillable ) ) {
			continue;
		}

		$data_social_media_url[ $post_key ] = $post_value;
	}

	$wpdb->update( 'revo_mobile_variable',
		[
			'description' => json_encode( $data_social_media_url ),
		],
		[
			'id' => $data_social_media->id
		]
	);

	$_SESSION['alert'] = [
		'type'    => 'success',
		'title'   => 'Success !',
		'message' => 'Social Media Link has been updated'
	];

	revo_shine_rebuild_cache( 'revo_home_data' );
}

?>

<form action="#" method="post">
    <div class="admin-section-container">
        <div class="admin-section-item">
            <div class="admin-section-item-title">
                <span>Social Media Link</span>
                <span class="small mt-xsmall">Make sure your link is correct</span>
            </div>
            <div class="admin-section-item-body">
				<?php

				$input_placeholder = [
					'whatsapp'  => 'https://wa.me/your-whatsapp-number',
					'facebook'  => 'https://facebook.com/youraccount',
					'instagram' => 'https://instagram.com/youraccount',
					'youtube'   => 'https://youtube.com/@youraccount',
					'tiktok'    => 'https://tiktok.com/@youraccount'
				];

				foreach ( $data_social_media_url as $key => $value ) {
					revo_shine_get_admin_template_part( 'components/input', [
						'id'          => $key,
						'label'       => ucwords( $key ),
						'name'        => $key,
						'value'       => $value,
						'type'        => 'url',
						'is_required' => false,
						'placeholder' => $input_placeholder[ $key ]
					] );
				}
				?>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary btn-save-changes" type="submit">Update Social Media Link</button>
        </div>
    </div>
</form>
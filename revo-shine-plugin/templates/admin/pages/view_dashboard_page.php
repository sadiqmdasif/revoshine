<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {

	$fillable = [
		'logo',
		'kontak',
		'url',
		'splashscreen',
	];

	foreach ( $_POST as $post_key => $post_value ) {

		if ( ! in_array( $post_key, $fillable ) ) {
			continue;
		}

		$data = $wpdb->get_row( "SELECT * FROM `revo_mobile_variable` WHERE slug = '{$post_key}' LIMIT 1", OBJECT );

		switch ( $post_key ) {
			case 'logo':
			case 'splashscreen':
				$updated_data = [
					'slug'        => $post_key,
					'description' => $post_value['description'] ?? ''
				];

				if ( ! empty( $post_value['title'] ) ) {
					$updated_data['title'] = $post_value['title'];
				}

				if ( ! empty( $post_value['image'] ) ) {
					$updated_data['image'] = wp_get_attachment_url( $post_value['image'] );
				}

				if ( ! empty( $data ) ) {
					$wpdb->update( 'revo_mobile_variable', $updated_data, [ 'id' => $data->id ] );
				} else {
					$wpdb->insert( 'revo_mobile_variable', $updated_data );
				}

				break;
			case 'kontak':
				foreach ( $post_value as $kontak_key => $kontak_value ) {
					$kontak_data = $wpdb->get_row( "SELECT * FROM `revo_mobile_variable` WHERE slug = 'kontak' AND title = '{$kontak_key}' LIMIT 1", OBJECT );

					$updated_kontak_data = [
						'slug'        => 'kontak',
						'title'       => $kontak_key,
						'description' => $kontak_value,
					];

					if ( ! empty( $kontak_data ) ) {
						$wpdb->update( 'revo_mobile_variable', $updated_kontak_data, [ 'id' => $kontak_data->id ] );
					} else {
						$wpdb->insert( 'revo_mobile_variable', $updated_kontak_data );
					}
				}

				break;
			case 'url':
				foreach ( $post_value as $url_key => $url_value ) {
					$url_data = $wpdb->get_row( "SELECT * FROM `revo_mobile_variable` WHERE slug = '{$url_key}' LIMIT 1", OBJECT );

					$urls_title = [
						'term_condition' => 'Term & Condition',
						'privacy_policy' => 'Privacy Policy',
						'about'          => 'Link to About'
					];

					$updated_url_data = [
						'slug'        => $url_key,
						'title'       => $urls_title[ $url_key ],
						'description' => $url_value,
					];

					if ( ! empty( $url_data ) ) {
						$wpdb->update( 'revo_mobile_variable', $updated_url_data, [ 'id' => $url_data->id ] );
					} else {
						$wpdb->insert( 'revo_mobile_variable', $updated_url_data );
					}
				}
				break;
		}

	}

	$_SESSION["alert"] = [
		'type'    => 'success',
		'title'   => 'Success !',
		'message' => 'Data has been saved successfully'
	];

	revo_shine_rebuild_cache( 'revo_home_data' );
}

$data_logo         = $wpdb->get_row( "SELECT * FROM `revo_mobile_variable` WHERE slug = 'logo' LIMIT 1", OBJECT );
$data_splash       = $wpdb->get_row( "SELECT * FROM `revo_mobile_variable` WHERE slug = 'splashscreen' LIMIT 1", OBJECT );
$data_contact      = $wpdb->get_results( "SELECT id, slug, title, description FROM `revo_mobile_variable` WHERE slug = 'kontak' AND title IN ('wa', 'phone', 'sms') LIMIT 3", OBJECT );
$data_url_settings = $wpdb->get_results( "SELECT id, slug, title, description FROM `revo_mobile_variable` WHERE slug IN ('term_condition', 'privacy_policy', 'about') ORDER BY id DESC LIMIT 3", OBJECT );

?>

<form action="#" method="post">
    <div class="admin-section-container">
        <div class="admin-section-item">
            <div class="admin-section-item-title">App Title and Logo</div>
            <div class="admin-section-item-body">
				<?php

				revo_shine_get_admin_template_part( 'components/input', [
					'id'          => 'logo-title',
					'label'       => 'Title Apps',
					'name'        => 'logo[title]',
					'value'       => $data_logo->title ?? '',
					'placeholder' => 'Ex: RevoSHINE Apps',
					'is_required' => true
				] );

				revo_shine_get_admin_template_part( 'components/upload', [
					'id'           => 'logo-image',
					'label'        => 'Logo',
					'name'         => 'logo[image]',
					'value'        => $data_logo->image ?? '',
					'accent_text'  => 'Upload Photo Here',
					'preview_mode' => 'thumbnail',
					'helper_text'  => 'Best Size : 100 x 100 px',
					'is_required'  => true
				] );

				?>
            </div>
        </div>
        <hr>
        <div class="admin-section-item">
            <div class="admin-section-item-title">General Settings</div>
            <div class="admin-section-item-body">
                <div class="row">
                    <div class="col-12 mb-xsmall">
                        <div class="text-brand-default fs-16 lh-24">Contact Setting</div>
                    </div>

					<?php
					$inputs = [
						'wa'    => [
							'label'       => 'WhatsApp',
							'placeholder' => 'Ex: 6281234567890',
						],
						'phone' => [
							'label'       => 'Phone',
							'placeholder' => 'Ex: 6281234567890',
						],
						'sms'   => [
							'label'       => 'SMS',
							'placeholder' => 'Ex: 6281234567890',
						],
					];

					$formatted_data_contact = [];
					foreach ( $data_contact as $data ) {
						$formatted_data_contact[ $data->title ] = $data->description;
					}

					foreach ( $inputs as $key => $input ) {
						echo '<div class="col">';
						revo_shine_get_admin_template_part( 'components/input', [
							'id'          => $key,
							'label'       => $input['label'],
							'type'        => 'number',
							'name'        => 'kontak[' . $key . ']',
							'value'       => $formatted_data_contact[ $key ] ?? '',
							'placeholder' => $input['placeholder'],
							'is_required' => false
						] );
						echo '</div>';
					}

					unset( $inputs );
					?>
                </div>
                <div class="row">
                    <div class="col-12 mb-xsmall">
                        <div class="text-brand-default fs-16 lh-24">URL Setting</div>
                    </div>

					<?php
					$inputs = [
						'term_condition' => [
							'label'       => 'Term & Condition',
							'placeholder' => 'Ex: https://revoapps.id/term-condition',
						],
						'privacy_policy' => [
							'label'       => 'Privacy Policy',
							'placeholder' => 'Ex: https://revoapps.id/privacy-policy',
						],
						'about'          => [
							'label'       => 'Link to About',
							'placeholder' => 'Ex: https://revoapps.id/about',
						]
					];

					$formatted_data_url = [];
					foreach ( $data_url_settings as $data ) {
						$formatted_data_url[ $data->slug ] = $data->description;
					}

					foreach ( $inputs as $key => $input ) {
						echo '<div class="col">';
						revo_shine_get_admin_template_part( 'components/input', [
							'id'          => $key,
							'label'       => $input['label'],
							'type'        => 'url',
							'name'        => 'url[' . $key . ']',
							'value'       => $formatted_data_url[ $key ] ?? '',
							'placeholder' => $input['placeholder'],
							'is_required' => false
						] );
						echo '</div>';
					}

					unset( $inputs );
					?>
                </div>
            </div>
        </div>
        <hr>
        <div class="admin-section-item">
            <div class="admin-section-item-title">Setting Splash Screen</div>
            <div class="admin-section-item-body">
				<?php

				revo_shine_get_admin_template_part( 'components/input', [
					'id'          => 'splashscreen-description',
					'label'       => 'Description',
					'name'        => 'splashscreen[description]',
					'value'       => $data_splash->description ?? '',
					'placeholder' => 'Ex: Welcome',
					'is_required' => false,
				] );

				revo_shine_get_admin_template_part( 'components/upload', [
					'id'          => 'splashscreen-image',
					'label'       => 'Image',
					'name'        => 'splashscreen[image]',
					'value'       => $data_splash->image ?? '',
					'accent_text' => 'Upload Photo Here',
					'helper_text' => 'Best Size : 450 x 1000 px',
					'is_required' => true
				] );

				?>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary btn-save-changes" type="submit">Save Changes</button>
        </div>
    </div>
</form>
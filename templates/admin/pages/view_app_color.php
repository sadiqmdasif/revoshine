<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {

	if ( isset( $_POST['typeQuery'] ) && $_POST['typeQuery'] === 'buynow_button_style' ) {
		header( 'Content-type: application/json' );

		$status = $_POST["status"];
		$get    = query_revo_mobile_variable( '"buynow_button_style"', 'sort' );

		if ( empty( $get ) ) {
			$wpdb->insert( 'revo_mobile_variable', array(
				'slug'        => 'buynow_button_style',
				'title'       => 'buynow button style',
				'description' => $status
			) );
		} else {
			$wpdb->query(
				$wpdb->prepare( "UPDATE revo_mobile_variable SET description='$status' WHERE slug='buynow_button_style'" )
			);
		}

		revo_shine_rebuild_cache( 'revo_home_data' );

		http_response_code( 200 );

		wp_send_json( [ 'kode' => 'S' ] );
	}

	$fillable = [
		'primary',
		'secondary',
		'button_color',
		'text_button_color'
	];

	foreach ( $_POST as $post_key => $post_value ) {

		if ( ! in_array( $post_key, $fillable ) ) {
			continue;
		}

		$data = $wpdb->get_row( "SELECT * FROM `revo_mobile_variable` WHERE slug = 'app_color' AND title = '{$post_key}' LIMIT 1", OBJECT );

		$updated_data = [
			'slug'        => 'app_color',
			'title'       => $post_key,
			'description' => str_replace( '#', '', $post_value )
		];

		if ( ! empty( $data ) ) {
			$wpdb->update( 'revo_mobile_variable', $updated_data, [ 'id' => $data->id ] );
		} else {
			$wpdb->insert( 'revo_mobile_variable', $updated_data );
		}

	}

	$_SESSION['alert'] = [
		'type'    => 'success',
		'title'   => 'Success !',
		'message' => 'App Color has been updated successfully'
	];

	revo_shine_rebuild_cache( 'revo_home_data' );
}

$data_app_color = $wpdb->get_results( "SELECT id, slug, title, description FROM `revo_mobile_variable` WHERE slug = 'app_color'", OBJECT );
$res_app_color  = [];

foreach ( $data_app_color as $data ) {
	$res_app_color[ $data->title ] = '#' . $data->description;
}

$buynow_button_style = query_revo_mobile_variable( '"buynow_button_style"', 'sort' );
$buynow_button_style = ! empty( $buynow_button_style ) ? $buynow_button_style[0]->description : '';

?>

<form action="#" method="POST">
    <div class="admin-section-container">
        <div class="admin-section-item">
            <div class="admin-section-item-title">App Theme Color</div>
            <div class="admin-section-item-body">
				<?php

				revo_shine_get_admin_template_part( 'components/input', [
					'id'    => 'primary',
					'label' => 'Primary Color',
					'name'  => 'primary',
					'value' => $res_app_color['primary'] ?? '#000000',
					'type'  => 'color'
				] );

				revo_shine_get_admin_template_part( 'components/input', [
					'id'    => 'secondary',
					'label' => 'Secondary Color',
					'name'  => 'secondary',
					'value' => $res_app_color['secondary'] ?? '#000000',
					'type'  => 'color'
				] );

				?>
            </div>
        </div>
        <hr>
        <div class="admin-section-item">
            <div class="d-flex align-items-center justify-content-between">
                <div class="admin-section-item-title">Buy Now Button with Solid Color</div>

				<?php

				revo_shine_get_admin_template_part( 'components/switch', [
					'id'         => 'switch_buy_now_button_solid',
					'label'      => '',
					'name'       => 'switch_buy_now_button_solid',
					'value'      => '',
					'is_checked' => $buynow_button_style === 'solid'
				] );

				?>
            </div>
            <div class="admin-section-item-body">
				<?php

				revo_shine_get_admin_template_part( 'components/input', [
					'id'    => 'button_color',
					'label' => 'Buy Now Button Color',
					'name'  => 'button_color',
					'value' => $res_app_color['button_color'] ?? '#000000',
					'type'  => 'color'
				] );

				revo_shine_get_admin_template_part( 'components/input', [
					'id'    => 'text_button_color',
					'label' => 'Text Color on Buy Now Button',
					'name'  => 'text_button_color',
					'value' => $res_app_color['text_button_color'] ?? '#000000',
					'type'  => 'color'
				] );

				?>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary btn-save-changes" type="submit">Update App Color</button>
        </div>
    </div>
</form>

<script>
    jQuery('body').on('change', '#tmp_switch_buy_now_button_solid', function (e) {
        e.preventDefault();

        const status = jQuery(this).prop('checked') ? "solid" : "gradation";

        if (status === "solid") {
            swaltitle = "Are you sure to make buy now button with solid style?";
            swaltext = "";
        } else {
            swaltitle = "Are you sure to make buy now button with gradation style?";
            swaltext = "";
        }

        Swal.fire({
            icon: 'warning',
            title: swaltitle,
            text: swaltext,
            showDenyButton: true,
            showCancelButton: false,
            allowOutsideClick: false,
            confirmButtonText: `YES`,
            denyButtonText: `NO`,
        }).then((result) => {
            if (result.isConfirmed) {
                jQuery.ajax({
                    url: "#",
                    method: "POST",
                    data: {
                        status: status,
                        typeQuery: 'buynow_button_style',
                    },
                    datatype: "json",
                    async: true,
                    complete: () => {
                        location.reload();
                    }
                });
            } else if (result.isDenied) {
                el.checked = status !== "solid";
            }
        })
    });
</script>
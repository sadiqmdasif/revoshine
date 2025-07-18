<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

$data           = $wpdb->get_row( "SELECT * FROM `revo_mobile_variable` WHERE slug = 'searchbar_text' limit 1", OBJECT );
$data_searchbar = json_decode( $data->description, true );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$query_data = [
		'slug'        => 'searchbar_text',
		'title'       => 'Search Bar Text',
		'description' => []
	];

	foreach ( $_POST as $key => $value ) {
		if ( str_contains( $key, 'text' ) ) {
			$query_data['description'][ $key ] = $value;
		}
	}

	$data_searchbar            = $query_data['description'];
	$query_data['description'] = json_encode( $query_data['description'] );

	if ( ! empty( $data ) ) {
		$query_status = $wpdb->update( 'revo_mobile_variable', $query_data, [ 'id' => $data->id ] );
	} else {
		$query_status = $wpdb->insert( 'revo_mobile_variable', $query_data );
	}

	if ( $query_status !== false ) {
		$_SESSION["alert"] = [
			'type'    => 'success',
			'title'   => 'Success',
			'message' => 'Search Bar Text has been updated'
		];
	}

	if ( $_SESSION['alert']['type'] === 'success' ) {
		revo_shine_rebuild_cache( 'revo_home_data' );
	}
}

?>

<form action="#" method="post">
    <div class="admin-section-container">
        <div class="admin-section-item">
            <div class="admin-section-item-title">
                <span>Search Bar Text</span>
                <span class="small mt-xsmall">This texts will be appeared inside search bar on home page (maximum 20 character)</span>
            </div>
            <div class="admin-section-item-body">
				<?php

				$placeholder_text = [
					'Coca-Cola',
					'Bread Toaster',
					'Apple Macbook',
					'Vegetables Salad',
					'Fresh Lemon'
				];

				for ( $i = 1; $i < 6; $i ++ ) {
					revo_shine_get_admin_template_part( 'components/input', [
						'id'          => 'text' . $i,
						'label'       => 'Text ' . $i,
						'name'        => 'text_' . $i,
						'value'       => $data_searchbar[ 'text_' . $i ] ?? '',
						'placeholder' => 'Ex: ' . $placeholder_text[ $i - 1 ],
						'is_required' => false
					] );
				}

				?>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary btn-save-changes" type="submit">Update Search Bar Text</button>
        </div>
    </div>
</form>
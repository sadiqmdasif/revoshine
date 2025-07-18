<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'revo_shine_url' ) ) {
	function revo_shine_url() {
		return REVO_SHINE_URL;
	}
}

if ( ! function_exists( 'revo_shine_get_logo' ) ) {

	function revo_shine_get_logo( $type = 'color' ) {
		return revo_shine_url() . ( $type == 'color' ? 'assets/images/logo-revo.png' : 'assets/images/logo-bw.png' );
	}
}

if ( ! function_exists( 'revo_shine_check_exist_database' ) ) {

	function revo_shine_check_exist_database( $tablename ) {
		global $wpdb;

		if ( $wpdb ) {
			$exit_tabel = " SHOW TABLES LIKE '$tablename' ";

			if ( count( $wpdb->get_results( $exit_tabel ) ) == 0 ) {
				return false;
			}
		}

		return true;
	}
}

if ( ! function_exists( 'revo_shine_get_product_variant_detail' ) ) {
	function revo_shine_get_product_variant_detail( $xid ) {
		$products = wc_get_products( [ 'include' => [ $xid ] ] );

		return $products;
	}
}

if ( ! function_exists( 'revo_shine_get_categories' ) ) {
	function revo_shine_get_categories() {
		$categories     = get_terms( [ 'taxonomy' => 'product_cat' ] );
		$all_categories = [];

		foreach ( $categories as $key => $value ) {
			array_push( $all_categories, [
				'id'   => $value->term_id,
				'text' => $value->name
			] );
		}

		return json_encode( $all_categories );
	}
}

if ( ! function_exists( 'revo_shine_get_category' ) ) {
	function revo_shine_get_category( $id ) {
		return get_term( $id );
	}
}

if ( ! function_exists( 'revo_shine_formatted_date' ) ) {

	function revo_shine_formatted_date( $timestamp, $format = "d/m/Y - H:i" ) {

		return date( $format, strtotime( $timestamp ) );
	}
}

if ( ! function_exists( 'revo_shine_badge_output_html' ) ) {

	function revo_shine_badge_output_html( $data ): string {

		$badge_type = $data == 1 ? 'badge-success' : 'badge-danger';
		$badge_text = $data == 1 ? 'Active' : 'Non Active';

		$badge_accent = $data == 1 ? '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><ellipse cx="6" cy="6" rx="3" ry="3" fill="#007A4D"/></svg>' : '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><ellipse cx="6" cy="6" rx="3" ry="3" fill="#D31510"/></svg>';

		return "<span class='badge badge-small {$badge_type} fs-10 lh-12'>
			{$badge_accent}
			{$badge_text}
		</span>";
	}
}

if ( ! function_exists( 'revo_shine_get_extend_product_type' ) ) {

	function revo_shine_get_extend_product_type( $type ): array {
		$data['image'] = '';
		$data['text']  = '';

		if ( $type == 'special' ) {
			$data['image'] = revo_shine_url() . '/assets/images/example_special.jpg';
			$data['text']  = 'Panel 1 ( Default : Special )';
		}

		if ( $type == 'our_best_seller' ) {
			$data['image'] = revo_shine_url() . '/assets/images/example_bestseller.jpg';
			$data['text']  = 'Panel 2 ( Default : Our Best Seller )';
		}

		// typo. betulin jadi recommendation
		if ( $type == 'recomendation' ) {
			$data['image'] = revo_shine_url() . '/assets/images/example_recomend.jpg';
			$data['text']  = 'Panel 2 ( Default : Recommendation )';
		}

		if ( $type == 'festive_promotions' ) {
			$data['image'] = '';
			$data['text']  = 'Panel 2 ( Default : Festive Promotions )';
		}

		$data['text'] = '<span class="badge badge-secondary fs-10 lh-12 fw-600">' . $data['text'] . '</span>';

		return $data;
	}
}

if ( ! function_exists( 'cek_flash_sale_end' ) ) {

	function cek_flash_sale_end() {
		global $wpdb;

		$date = date( 'Y-m-d H:i:s' );

		$get = $wpdb->get_results( "SELECT id FROM `revo_flash_sale` WHERE is_deleted = 0 AND start < '" . $date . "' AND end < '" . $date . "' AND is_active = 1", OBJECT );

		foreach ( $get as $value ) {
			$wpdb->update(
				'revo_flash_sale',
				[ 'is_active' => '0' ],
				array( 'id' => $value->id )
			);
		}
	}
}

if ( ! function_exists( 'buttonQuestion' ) ) {

	function buttonQuestion() {

		return '<span class="position-relative pointer" data-bs-toggle="modal" data-bs-target="#question">
            <svg class="position-absolute top-0" style="left: 8px" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.96732 11.0002C7.20065 11.0002 7.39798 10.9195 7.55932 10.7582C7.72021 10.5973 7.80065 10.4002 7.80065 10.1668C7.80065 9.9335 7.72021 9.73639 7.55932 9.5755C7.39798 9.41416 7.20065 9.3335 6.96732 9.3335C6.73398 9.3335 6.53665 9.41416 6.37532 9.5755C6.21443 9.73639 6.13398 9.9335 6.13398 10.1668C6.13398 10.4002 6.21443 10.5973 6.37532 10.7582C6.53665 10.9195 6.73398 11.0002 6.96732 11.0002ZM7.06732 4.1335C7.37843 4.1335 7.62843 4.2195 7.81732 4.3915C8.00621 4.56394 8.10065 4.78905 8.10065 5.06683C8.10065 5.25572 8.03687 5.44727 7.90932 5.6415C7.78132 5.83616 7.60065 6.03905 7.36732 6.25016C7.03398 6.53905 6.78954 6.81683 6.63398 7.0835C6.47843 7.35016 6.40065 7.61683 6.40065 7.8835C6.40065 8.03905 6.4591 8.1695 6.57598 8.27483C6.69243 8.38061 6.82843 8.4335 6.98398 8.4335C7.13954 8.4335 7.27843 8.37794 7.40065 8.26683C7.52287 8.15572 7.60065 8.01683 7.63398 7.85016C7.66732 7.66127 7.74243 7.48639 7.85932 7.3255C7.97576 7.16416 8.16732 6.95572 8.43398 6.70016C8.77843 6.37794 9.02021 6.0835 9.15932 5.81683C9.29798 5.55016 9.36732 5.25572 9.36732 4.9335C9.36732 4.36683 9.15354 3.90283 8.72598 3.5415C8.29798 3.18061 7.7451 3.00016 7.06732 3.00016C6.60065 3.00016 6.18687 3.08905 5.82598 3.26683C5.46465 3.44461 5.18398 3.71683 4.98398 4.0835C4.90621 4.22794 4.87843 4.3695 4.90065 4.50816C4.92287 4.64727 5.00065 4.76127 5.13398 4.85016C5.27843 4.93905 5.43687 4.96683 5.60932 4.9335C5.78132 4.90016 5.92287 4.80572 6.03398 4.65016C6.15621 4.4835 6.30354 4.35572 6.47598 4.26683C6.64798 4.17794 6.8451 4.1335 7.06732 4.1335ZM7.00065 13.6668C6.08954 13.6668 5.22843 13.4917 4.41732 13.1415C3.60621 12.7917 2.89798 12.3168 2.29265 11.7168C1.68687 11.1168 1.2091 10.4113 0.859318 9.60016C0.509096 8.78905 0.333984 7.92239 0.333984 7.00016C0.333984 6.07794 0.509096 5.21127 0.859318 4.40016C1.2091 3.58905 1.68687 2.8835 2.29265 2.2835C2.89798 1.6835 3.60621 1.20838 4.41732 0.858163C5.22843 0.508385 6.08954 0.333496 7.00065 0.333496C7.93398 0.333496 8.80621 0.508385 9.61732 0.858163C10.4284 1.20838 11.134 1.6835 11.734 2.2835C12.334 2.8835 12.8062 3.58905 13.1507 4.40016C13.4951 5.21127 13.6673 6.07794 13.6673 7.00016C13.6673 7.92239 13.4951 8.78905 13.1507 9.60016C12.8062 10.4113 12.334 11.1168 11.734 11.7168C11.134 12.3168 10.4284 12.7917 9.61732 13.1415C8.80621 13.4917 7.93398 13.6668 7.00065 13.6668ZM7.00065 12.3335C8.48954 12.3335 9.75065 11.8142 10.784 10.7755C11.8173 9.73639 12.334 8.47794 12.334 7.00016C12.334 5.52239 11.8173 4.26394 10.784 3.22483C9.75065 2.18616 8.48954 1.66683 7.00065 1.66683C5.5451 1.66683 4.29221 2.18616 3.24198 3.22483C2.19221 4.26394 1.66732 5.52239 1.66732 7.00016C1.66732 8.47794 2.19221 9.73639 3.24198 10.7755C4.29221 11.8142 5.5451 12.3335 7.00065 12.3335Z" fill="#00719F"/></svg>
        </span>';
	}
}

if ( ! function_exists( 'get_user' ) ) {

	function get_user( $email ) {

		$user = get_user_by( 'email', $email );

		return $user;
	}
}

if ( ! function_exists( 'get_authorization_header' ) ) {
	function get_authorization_header() {
		if ( ! empty( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			return wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ); // WPCS: sanitization ok.
		}

		if ( function_exists( 'getallheaders' ) ) {
			$headers = getallheaders();
			// Check for the authoization header case-insensitively.
			foreach ( $headers as $key => $value ) {
				if ( 'authorization' === strtolower( $key ) ) {
					return $value;
				}
			}
		}

		return '';
	}
}

if ( ! function_exists( 'security_0auth' ) ) {
	function security_0auth() {
		$current_url = home_url( $_SERVER['REQUEST_URI'] );
		$current_url = explode( '/', $current_url );
		$current_url = end( $current_url );

		if ( $current_url !== 'disabled-service' ) {
			require_once REVO_SHINE_ABSPATH . 'includes/services/class-revo-shine-authentication.php';

			$cek = cek_internal_license_code();

			if ( ! $cek ) {
				echo json_encode( [ 'status' => 'error', 'message' => 'input license first !' ] );
				exit();
			}
		}
	}
}

if ( ! function_exists( 'query_revo_mobile_variable' ) ) {

	function query_revo_mobile_variable( $slug, $order_by = 'created_at' ) {
		global $wpdb;

		if ( is_array( $slug ) ) {
			$slug = "'" . implode( "','", $slug ) . "'";
		}

		return $wpdb->get_results( "SELECT * FROM `revo_mobile_variable` WHERE `slug` IN ($slug) AND is_deleted = 0 ORDER BY $order_by DESC", OBJECT );
	}
}

if ( ! function_exists( 'query_check_plugin_active' ) ) {

	function query_check_plugin_active( $search ) {

		$active_plugins = get_option( 'active_plugins' );

		foreach ( $active_plugins as $plugin ) {

			if ( strpos( $plugin, $search ) !== false ) {

				if ( strpos( $plugin, 'revo-kasir' ) ) {

					$cek = cek_internal_license_code_pos();

					if ( $cek == true ) {

						return true;
					}
				} else {
					return true;
				}
			}

			// if ( str_contains($plugin, $search) ) {

			// 	$cek = cek_internal_license_code_pos();

			// 		if ($cek == true) {
			// 			return true;
			// 		}

			// }

		}

		return false;
	}
}

if ( ! function_exists( 'check_live_chat' ) ) {

	function check_live_chat() {

		$query_LiveChatStatus = query_revo_mobile_variable( '"live_chat_status"', 'sort' );
		$liveChatStatus       = empty( $query_LiveChatStatus ) ? 'hide' : $query_LiveChatStatus[0]->description;

		if ( $liveChatStatus == 'hide' ) {
			$result = [ 'status' => 'error', 'message' => 'Live chat disabled !' ];
		}

		$check_revopos_active = query_check_plugin_active( 'Plugin-revo-kasir' );

		if ( ! $check_revopos_active ) {
			$result = [ 'status' => 'error', 'message' => 'Plugin RevoPOS not installed or activated !' ];
		}

		if ( $check_revopos_active && $liveChatStatus == 'show' ) {
			$result = true;
		}

		return $result;
	}
}

if ( ! function_exists( 'query_hit_products' ) ) {

	function query_hit_products( $id, $user_id ) {
		global $wpdb;

		return $wpdb->get_row( "SELECT count(id) as is_wistlist FROM `revo_hit_products` WHERE products = '$id' AND user_id = '$user_id' AND type = 'wistlist'", OBJECT );
	}
}

if ( ! function_exists( 'query_all_hit_products' ) ) {

	function query_all_hit_products( $user_id ) {
		global $wpdb;

		return $wpdb->get_results( "SELECT * FROM `revo_hit_products` WHERE user_id = '$user_id' AND type = 'wistlist' ORDER BY created_at DESC", OBJECT );
	}
}

if ( ! function_exists( 'insert_update_MV' ) ) {

	function insert_update_MV( $where, $id, $desc ) {
		global $wpdb;

		$query_data                = $where;
		$query_data['description'] = $desc;

		$success = 0;
		if ( $id != 0 ) {
			$where['id']   = $id;
			$update_status = $wpdb->update( 'revo_mobile_variable', $query_data, $where );

			if ( $update_status !== false ) {
				$success = 1;
			}
		} else {
			$wpdb->insert( 'revo_mobile_variable', $query_data );
			if ( $wpdb->insert_id > 0 ) {
				$success = 1;
			}
		}

		return $success;
	}
}

if ( ! function_exists( 'revo_shine_access_key' ) ) {

	function revo_shine_access_key() {
		global $wpdb;
		$query = "SELECT * FROM `revo_access_key` ORDER BY created_at DESC limit 1";

		return $wpdb->get_row( $query, OBJECT );
	}
}

if ( ! function_exists( 'get_products_woocomerce' ) ) {

	function get_products_woocomerce( $layout, $api, $request ) {
		$params = array( 'order' => 'desc', 'orderby' => 'date' );
		if ( isset( $layout['category'] ) ) {
			$params['category'] = $layout['category'];
		}
		if ( isset( $layout['tag'] ) ) {
			$params['tag'] = $layout['tag'];
		}
		if ( isset( $layout['feature'] ) ) {
			$params['feature'] = $layout['feature'];
		}

		$request->set_query_params( $params );

		$response = $api->get_items( $request );

		return $response->get_data();
	}
}

if ( ! function_exists( 'revo_shine_fcm_api_v1' ) ) {
	function revo_shine_fcm_api_v1( $notification, $extend, $token = '', $push_notif_web = false ) {
		require_once REVO_SHINE_ABSPATH . 'includes/services/class-revo-shine-google-service.php';

		$service     = new Revo_Shine_Google_Service();
		$oauth_token = $service->configureClient();

		$project_id = get_option( 'revo_shine_fire_project_id', null );
		if ( is_null( $project_id ) ) {
			return;
		}

		$body['message'] = [
			"data"         => $extend,
			"notification" => $notification
		];

		if ( ! $push_notif_web ) {
			$body['message']['token'] = $token;
		} else {
			$body['message']['topic'] = 'news';
		}

		$body = json_encode( $body );
		$curl = curl_init();
		curl_setopt_array( $curl, array(
			CURLOPT_URL            => "https://fcm.googleapis.com/v1/projects/{$project_id}/messages:send",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "POST",
			CURLOPT_HTTPHEADER     => array(
				"Content-Type: application/json",
				"Authorization: Bearer {$oauth_token}}"
			),
			CURLOPT_POSTFIELDS     => $body,
		) );

		$response = curl_exec( $curl );

		$err = curl_error( $curl );

		curl_close( $curl );

		if ( $err ) {
			return 'error';
		}

		return json_decode( $response );
	}
}

if ( ! function_exists( 'cek_raw' ) ) {

	function cek_raw( $key = '' ) {
		$json   = file_get_contents( 'php://input' );
		$params = json_decode( $json );


		if ( $params and $key ) {
			if ( @$params->$key ) {
				$text = $params->$key;

				//    			// Strip HTML Tags
				// $clear = strip_tags($text);
				// // Clean up things like &amp;
				// $clear = html_entity_decode($clear);
				// // Strip out any url-encoded stuff
				// $clear = urldecode($clear);
				// // Replace Multiple spaces with single space
				// $clear = preg_replace('/ +/', ' ', $clear);
				// // Trim the string of leading/trailing space
				// $clear = trim($clear);

				return $text;
			}
		}

		return '';
	}
}

if ( ! function_exists( 'get_user_token' ) ) {

	function get_user_token( $where = '' ) {
		global $wpdb;
		// $query = "SELECT token,user_id FROM `revo_token_firebase` $where GROUP BY token ORDER BY created_at DESC";
		$query = "SELECT token,user_id FROM `revo_token_firebase` $where ORDER BY created_at DESC";

		return $wpdb->get_results( $query, OBJECT );
	}
}

if ( ! function_exists( 'pos_get_user_token' ) ) {

	function pos_get_user_token( $where = '' ) {
		global $wpdb;
		$query = "SELECT token FROM `revo_pos_token_firebase` $where GROUP BY token ORDER BY created_at DESC";

		return $wpdb->get_results( $query, OBJECT );
	}
}

if ( ! function_exists( 'rv_total_sales' ) ) {

	function rv_total_sales( $product ) {
		$product_id = $product->get_id();
		if ( ! $product ) {
			return 0;
		}

		$total_sales = is_a( $product, 'WC_Product_Variation' ) ? get_post_meta( $product_id, 'total_sales', true ) : $product->get_total_sales();

		return $total_sales;
	}
}

if ( ! function_exists( 'load_revo_flutter_mobile_app' ) ) {
	function load_revo_flutter_mobile_app() {
		require_once REVO_SHINE_ABSPATH . 'includes/services/class-revo-shine-flutter-mobile-app.php';

		return Revo_Shine_Flutter_Mobile_App::get_instance();
	}
}

if ( ! function_exists( 'get_popular_categories' ) ) {

	function get_popular_categories() {
		global $wpdb;
		$data_categories = $wpdb->get_results( "SELECT title,categories FROM revo_popular_categories WHERE is_deleted = 0 ORDER BY created_at DESC", OBJECT );

		return $data_categories;
	}
}

if ( ! function_exists( 'cek_license_code' ) ) {
	function cek_license_code( $data ) {

		$body = array(
			"domain"       => $_SERVER['SERVER_NAME'],
			"key"          => $data['description'],
			"product_type" => "revo_shop",
			"useEnvato"    => $data['title'] == 'envato'
		);

		$request = wp_remote_post( "https://activation.revoapps.net/wp-json/license/confirm", [
			'method'  => 'POST',
			'timeout' => 45,
			'body'    => wp_json_encode( $body ),
			'headers' => [
				'Content-Type' => 'application/json'
			]
		] );

		if ( is_wp_error( $request ) ) {
			return [
				'status'  => 'error',
				'message' => 'Something went wrong: ' . $request->get_error_message()
			];
		} else {
			$response_body = json_decode( $request['body'] );

			if ( $request['response']['code'] == 200 ) {
				return $response_body;
			}

			return [
				'status'  => 'error',
				'message' => $response_body['code'] . ' : ' . $response_body['message']
			];
		}
	}
}

if ( ! function_exists( 'data_default_seeder' ) ) {
	function data_default_seeder( $type ) {

		if ( $type == 'splashscreen' ) {
			$data = array(
				'slug'        => 'splashscreen',
				'title'       => '',
				'image'       => revo_shine_url() . 'assets/images/default_logo.png',
				'description' => 'Welcome',
			);
		}

		if ( $type == 'intro_page_status' ) {
			$data = array(
				'slug'        => 'intro_page_status',
				'title'       => '',
				'image'       => revo_shine_get_logo(),
				'description' => 'show',
			);
		}

		if ( $type == 'kontak_wa' ) {
			$data = array(
				'slug'        => 'kontak',
				'title'       => 'wa',
				'image'       => '',
				'description' => '62987654321',
			);
		}

		if ( $type == 'kontak_phone' ) {
			$data = array(
				'slug'        => 'kontak',
				'title'       => 'phone',
				'image'       => '',
				'description' => '62987654321',
			);
		}

		if ( $type == 'kontak_sms' ) {
			$data = array(
				'slug'        => 'sms',
				'title'       => 'link sms',
				'image'       => '',
				'description' => '62987654321',
			);
		}

		if ( $type == 'sms' ) {
			$data = array(
				'slug'        => 'kontak',
				'title'       => 'sms',
				'image'       => '',
				'description' => '62987654321',
			);
		}

		if ( $type == 'about' ) {
			$data = array(
				'slug'        => 'about',
				'title'       => 'link about',
				'image'       => '',
				'description' => get_site_url(),
			);
		}

		if ( $type == 'privacy_policy' ) {
			$data = array(
				'slug'        => 'privacy_policy',
				'title'       => 'link Privacy Policy',
				'image'       => '',
				'description' => get_site_url(),
			);
		}

		if ( $type == 'term_condition' ) {
			$data = array(
				'slug'        => 'term_condition',
				'title'       => 'link term & condition',
				'image'       => '',
				'description' => get_site_url(),
			);
		}

		if ( $type == 'license_code' ) {
			$data = array(
				'slug'        => 'license_code',
				'title'       => '',
				'image'       => '',
				'description' => '',
			);
		}

		if ( $type == 'cs' ) {
			$data = array(
				'slug'        => 'cs',
				'title'       => 'customer service',
				'image'       => '',
				'description' => '',
			);
		}

		if ( $type == 'logo' ) {
			$data = array(
				'slug'        => 'logo',
				'title'       => 'Mobile Revo Apps',
				'image'       => revo_shine_url() . 'assets/images/default_logo.png',
				'description' => '',
			);
		}

		if ( $type == 'intro_page_1' ) {
			$data = array(
				'slug'        => 'intro_page',
				'title'       => '{"title": "Manage Everything"}',
				'image'       => revo_shine_url() . 'assets/images/revo-shine-onboarding-01.jpg',
				'description' => '{"description": "Completely manage your store from the dashboard, including onboarding/intro changes, sliding banners, posters, home, and many more."}',
				'sort'        => 1,
			);
		}

		if ( $type == 'intro_page_2' ) {
			$data = array(
				'slug'        => 'intro_page',
				'title'       => '{"title": "Support All Payments"}',
				'image'       => revo_shine_url() . 'assets/images/revo-shine-onboarding-02.jpg',
				'description' => '{"description": "Pay for the transaction using all the payment methods you want. Including paypal, razorpay, bank transfer, BCA, Mandiri, gopay, or ovo."}',
				'sort'        => 2,
			);
		}

		if ( $type == 'intro_page_3' ) {
			$data = array(
				'slug'        => 'intro_page',
				'title'       => '{"title": "Support All Shipping Methods"}',
				'image'       => revo_shine_url() . 'assets/images/revo-shine-onboarding-03.jpg',
				'description' => '{"description": "The shipping method according to your choice, which is suitable for your business. All can be arranged easily."}',
				'sort'        => 3,
			);
		}

		if ( $type == 'empty_images_1' ) {
			$data = array(
				'slug'        => 'empty_image',
				'title'       => "404_images",
				'image'       => revo_shine_url() . 'assets/images/404.png',
				'description' => "450 x 350px",
			);
		}

		if ( $type == 'empty_images_2' ) {
			$data = array(
				'slug'        => 'empty_image',
				'title'       => "thanks_order",
				'image'       => revo_shine_url() . 'assets/images/thanks_order.png',
				'description' => "600 x 420px",
			);
		}

		if ( $type == 'empty_images_3' ) {
			$data = array(
				'slug'        => 'empty_image',
				'title'       => "empty_transaksi",
				'image'       => revo_shine_url() . 'assets/images/no_transaksi.png',
				'description' => "260 x 300px",
			);
		}

		if ( $type == 'empty_images_4' ) {
			$data = array(
				'slug'        => 'empty_image',
				'title'       => "search_empty",
				'image'       => revo_shine_url() . 'assets/images/search_empty.png',
				'description' => "260 x 300px",
			);
		}

		if ( $type == 'empty_images_5' ) {
			$data = array(
				'slug'        => 'empty_image',
				'title'       => "login_required",
				'image'       => revo_shine_url() . 'assets/images/404.png',
				'description' => "260 x 300px",
			);
		}

		if ( $type == 'empty_images_6' ) {
			$data = array(
				'slug'        => 'empty_image',
				'title'       => "coupon_empty",
				'image'       => revo_shine_url() . 'assets/images/404.png',
				'description' => "260 x 300px",
			);
		}

		if ( $type == 'app_primary_color' ) {
			$data = array(
				'slug'        => 'app_color',
				'title'       => 'primary',
				'description' => 'ED1D1D',
			);
		}

		if ( $type == 'app_secondary_color' ) {
			$data = array(
				'slug'        => 'app_color',
				'title'       => 'secondary',
				'description' => '960000',
			);
		}

		if ( $type == 'app_button_color' ) {
			$data = array(
				'slug'        => 'app_color',
				'title'       => 'button_color',
				'description' => 'ffffff',
			);
		}

		if ( $type == 'app_text_button_color' ) {
			$data = array(
				'slug'        => 'app_color',
				'title'       => 'text_button_color',
				'description' => 'ffffff',
			);
		}

		if ( strpos( $type, 'slider_banner_' ) !== false ) {
			global $wpdb;
			$post = $wpdb->get_row( "SELECT ID, post_title, post_type FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type IN ('product', 'post') ORDER BY RAND()" );

			if ( empty( $post ) ) {
				$post             = new stdClass;
				$post->ID         = null;
				$post->post_type  = '';
				$post->post_title = '';
			}

			$explode_type      = explode( '_', $type );
			$key_slider_banner = end( $explode_type );
			$default_images    = [
				REVO_SHINE_ASSET_URL . 'images/seeders/mobile-slider/banner-1.jpg',
				REVO_SHINE_ASSET_URL . 'images/seeders/mobile-slider/banner-2.jpg',
				REVO_SHINE_ASSET_URL . 'images/seeders/mobile-slider/banner-3.jpg',
				REVO_SHINE_ASSET_URL . 'images/seeders/mobile-slider/banner-4.jpg',
				REVO_SHINE_ASSET_URL . 'images/seeders/mobile-slider/banner-5.jpg',
			];

			$data = array(
				'order_by'     => $key_slider_banner,
				'product_id'   => $post->ID,
				'title'        => 'Slider ' . $key_slider_banner,
				'images_url'   => $default_images[ $key_slider_banner - 1 ],
				'product_name' => $post->post_type == 'post' ? 'blog|' . $post->post_title : $post->post_title,
				'section_type' => 'banner-1',
				'is_active'    => 1,
				'is_deleted'   => 0,
				'created_at'   => date( 'Y-m-d H:i:s' ),
			);
		}

		if ( strpos( $type, 'label_product_' ) !== false ) {
			global $wpdb;
			$products = $wpdb->get_results( "SELECT ID, post_title FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type = 'product' ORDER BY RAND() lIMIT 3" );

			if ( empty( $products ) ) {
				$ids_product = null;
			} else {
				$ids_product = '[';

				foreach ( $products as $p_key => $p ) {
					$ids_product .= '"' . $p->ID . '"';
					$ids_product .= array_key_last( $products ) != $p_key ? ',' : '';
				}

				$ids_product .= ']';
			}

			$explode_type      = explode( '_', $type );
			$key_slider_banner = end( $explode_type );
			$default_images    = array(
				REVO_SHINE_ASSET_URL . 'images/seeders/label-product/label-black-friday-1.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/label-product/label-black-friday-2.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/label-product/label-black-friday-3.png',
			);

			$data = array(
				'name'     => 'Black Friday',
				'slug'     => 'black-friday',
				'type'     => 'label-black-friday',
				'image'    => $default_images[ $key_slider_banner - 1 ],
				'products' => $ids_product
			);
		}

		if ( strpos( $type, 'slider_banner_full_screen_' ) !== false ) {
			global $wpdb;
			$post = $wpdb->get_row( "SELECT ID, post_title, post_type FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type IN ('product', 'post') ORDER BY RAND()" );

			if ( empty( $post ) ) {
				$post             = new stdClass;
				$post->ID         = null;
				$post->post_type  = '';
				$post->post_title = '';
			}

			$explode_type      = explode( '_', $type );
			$key_slider_banner = end( $explode_type );
			$default_images    = [
				REVO_SHINE_ASSET_URL . 'images/seeders/mobile-slider/black-friday-1.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/mobile-slider/black-friday-2.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/mobile-slider/black-friday-3.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/mobile-slider/black-friday-4.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/mobile-slider/black-friday-5.png',
			];

			$data = array(
				'order_by'     => $key_slider_banner,
				'product_id'   => $post->ID,
				'title'        => 'Slider ' . $key_slider_banner,
				'images_url'   => $default_images[ $key_slider_banner - 1 ],
				'product_name' => $post->post_type == 'post' ? 'blog|' . $post->post_title : $post->post_title,
				'section_type' => 'banner-full-screen',
				'is_active'    => 1,
				'is_deleted'   => 0,
				'created_at'   => date( 'Y-m-d H:i:s' ),
			);
		}

		if ( strpos( $type, 'home_categories_' ) !== false ) {
			$category = array_values( get_categories( [
				'limit'   => 1,
				'orderby' => 'rand',
			] ) );

			$explode_type      = explode( '_', $type );
			$key_home_category = end( $explode_type );

			$default_images = [
				REVO_SHINE_ASSET_URL . 'images/seeders/home-categories/one-rows-1.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/home-categories/one-rows-2.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/home-categories/one-rows-3.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/home-categories/one-rows-4.png',
			];

			$data = array(
				'order_by'      => $key_home_category,
				'image'         => $default_images[ $key_home_category - 1 ],
				'category_id'   => ! empty( $category ) ? $category[0]->term_id : '',
				'category_name' => ! empty( $category ) ? '{"title": "' . $category[0]->name . '"}' : '',
				'section_type'  => 'mini',
				'is_active'     => 1,
				'is_deleted'    => 0,
				'created_at'    => date( 'Y-m-d H:i:s' ),
			);
		}

		if ( strpos( $type, 'home_categories_two_rows_' ) !== false ) {
			$category = array_values( get_categories( [
				'limit'   => 1,
				'orderby' => 'rand',
			] ) );

			$explode_type      = explode( '_', $type );
			$key_home_category = end( $explode_type );

			$default_images = [
				REVO_SHINE_ASSET_URL . 'images/seeders/home-categories/two-rows-1.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/home-categories/two-rows-2.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/home-categories/two-rows-3.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/home-categories/two-rows-4.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/home-categories/two-rows-5.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/home-categories/two-rows-6.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/home-categories/two-rows-7.png',
			];

			$data = array(
				'order_by'      => $key_home_category,
				'image'         => $default_images[ $key_home_category - 1 ],
				'category_id'   => ! empty( $category ) ? $category[0]->term_id : '',
				'category_name' => ! empty( $category ) ? '{"title": "' . $category[0]->name . '"}' : '',
				'section_type'  => 'categories-two-rows',
				'is_active'     => 1,
				'is_deleted'    => 0,
				'created_at'    => date( 'Y-m-d H:i:s' ),
			);
		}

		if ( strpos( $type, 'poster_banner_' ) !== false ) {
			global $wpdb;
			$post = $wpdb->get_row( "SELECT ID, post_title, post_type FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type IN ('product', 'post') ORDER BY RAND()" );

			if ( empty( $post ) ) {
				$post             = new stdClass;
				$post->ID         = null;
				$post->post_type  = '';
				$post->post_title = '';
			}

			$explode_type      = explode( '_', $type );
			$key_poster_banner = end( $explode_type );
			$default_images    = [
				REVO_SHINE_ASSET_URL . 'images/seeders/poster-banner/special-promo-1.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/poster-banner/special-promo-2.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/poster-banner/special-promo-3.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/poster-banner/special-promo-4.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/poster-banner/love-these-items-1.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/poster-banner/love-these-items-2.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/poster-banner/love-these-items-3.png',
				REVO_SHINE_ASSET_URL . 'images/seeders/poster-banner/love-these-items-4.png',
				'https://demoonlineshop.revoapps.id/wp-content/uploads/revo/a5db5bd1947780641d79bdfad9858436.jpg',
				'https://demoonlineshop.revoapps.id/wp-content/uploads/revo/df41f9e1d2bf582595984ffd8c709e42.png'
			];

			$data = array(
				'order_by'     => $key_poster_banner == 9 ? 1 : $key_poster_banner,
				'product_id'   => $post->ID,
				'product_name' => $post->post_type == 'post' ? 'blog|' . $post->post_title : $post->post_title,
				'image'        => $default_images[ $key_poster_banner - 1 ],
				'type'         => $key_poster_banner <= 4 ? 'Special Promo' : ( $key_poster_banner == 9 ? 'Blog Banner' : 'Love These Items' ),
				'section_type' => $key_poster_banner <= 4 ? 'special-promo' : ( $key_poster_banner == 9 ? 'blog-banner' : 'love-these-items' ),
				'is_active'    => 1,
				'is_deleted'   => 0,
				'created_at'   => date( 'Y-m-d H:i:s' ),
			);
		}

		if ( $type == 'flash_sale' ) {
			global $wpdb;
			$products = $wpdb->get_results( "SELECT ID, post_title FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type = 'product' ORDER BY RAND() lIMIT 3" );

			if ( empty( $products ) ) {
				$ids_product = null;
			} else {
				$ids_product = '[';

				foreach ( $products as $p_key => $p ) {
					$ids_product .= '"' . $p->ID . '"';
					$ids_product .= array_key_last( $products ) != $p_key ? ',' : '';
				}

				$ids_product .= ']';
			}

			$data = array(
				'title'      => 'Flash Sale 1',
				'start'      => date( 'Y-m-d 00:00:00' ),
				'end'        => date( 'Y-m-d 23:59:59', strtotime( '+30 days' ) ),
				'products'   => $ids_product,
				'image'      => REVO_SHINE_ASSET_URL . 'images/seeders/flash-sale/flash-sale-mobile.png',
				'is_active'  => 1,
				'is_deleted' => 0,
				'created_at' => date( 'Y-m-d H:i:s' ),
			);
		}

		if ( $type == 'popular_categories' ) {
			$categories = array_values( get_categories( [
				'limit'    => 3,
				'order'    => 'rand',
				'order_by' => 'rand'
			] ) );

			if ( empty( $categories ) ) {
				$ids_category = null;
			} else {
				$ids_category = '[';

				foreach ( $categories as $c_key => $c ) {
					$ids_category .= '"' . $c->term_id . '"';
					$ids_category .= array_key_last( $categories ) != $c_key ? ',' : '';
				}

				$ids_category .= ']';
			}

			$data = array(
				'title'      => 'Popular Category 1',
				'categories' => $ids_category,
				'is_deleted' => 0,
				'created_at' => date( 'Y-m-d H:i:s' ),
			);
		}

		if ( $type == 'searchbar' ) {
			$data = array(
				'slug'        => 'searchbar_text',
				'title'       => 'Search Bar Text',
				'description' => json_encode( array(
					"text_1" => "Coca Cola",
					"text_2" => "Bread Toaster",
					"text_3" => "Apple Macbook",
					"text_4" => "Vegetables Salad",
					"text_5" => "Fresh Lemon"
				) )
			);
		}

		if ( $type == 'sosmed_link' ) {
			$sosmed            = new stdClass;
			$sosmed->whatsapp  = "https://wa.me/62345678901";
			$sosmed->facebook  = "https://www.facebook.com/myrevoapps/";
			$sosmed->instagram = "https://www.instagram.com/myrevoapps/";
			$sosmed->youtube   = "https://www.youtube.com/watch?v=myrevoapps";
			$sosmed->tiktok    = "https://www.tiktok.com/@myrevoapps";

			$data = array(
				'slug'        => 'sosmed_link',
				'title'       => 'Social Media Link',
				'image'       => '',
				'description' => json_encode( $sosmed ),
			);
		}

		if ( strpos( $type, 'additional_products_' ) !== false ) {
			global $wpdb;
			$products = $wpdb->get_results( "SELECT ID, post_title FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type = 'product' ORDER BY RAND() LIMIT 3" );

			if ( empty( $products ) ) {
				$ids_product = null;
			} else {
				$ids_product = '[';

				foreach ( $products as $p_key => $p ) {
					$ids_product .= '"' . $p->ID . '"';
					$ids_product .= array_key_last( $products ) != $p_key ? ',' : '';
				}

				$ids_product .= ']';
			}

			$explode_type      = explode( '_', $type );
			$key_poster_banner = end( $explode_type );
			$data_default      = [
				1 => [
					'type'         => 'special',
					'title'        => 'Special Promo : App Only',
					'description'  => 'For You',
					'section_type' => 'products-special',
				],
				2 => [
					'type'         => 'our_best_seller',
					'title'        => 'Best Seller',
					'description'  => 'Get The Best Products',
					'section_type' => 'products-our-best-seller',
				],
				3 => [
					'type'         => 'recomendation',
					'title'        => 'Recomendations For You',
					'description'  => 'Recommendation Products',
					'section_type' => 'products-recomendation',
				],
				4 => [
					'type'         => 'other_products',
					'title'        => 'Other Products',
					'description'  => 'Best Products',
					'section_type' => 'other-products',
				],
				5 => [
					'type'         => 'festive_promotions',
					'title'        => 'Black Friday',
					'description'  => '-',
					'section_type' => 'festive-promotions',
				],
			];

			$data = array(
				'type'         => $data_default[ $key_poster_banner ]['type'],
				'title'        => $data_default[ $key_poster_banner ]['title'],
				'description'  => $data_default[ $key_poster_banner ]['description'],
				'products'     => $ids_product,
				'section_type' => $data_default[ $key_poster_banner ]['section_type']
			);
		}

		return $data;
	}
}

if ( ! function_exists( 'cek_internal_license_code' ) ) {
	function cek_internal_license_code() {
		global $wpdb;

		$now   = date( 'Y-m-d H:i:s' );
		$query = "SELECT update_at FROM `revo_mobile_variable` WHERE slug = 'license_code' AND description != '' AND update_at is not NULL";
		$get   = $wpdb->get_row( $query, OBJECT );

		if ( ! empty( $get ) ) {
			if ( $get->update_at > $now ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'cek_internal_license_code_pos' ) ) {
	function cek_internal_license_code_pos() {
		global $wpdb;

		$now   = date( 'Y-m-d H:i:s' );
		$query = "SELECT update_at FROM `revo_pos_mobile_variable` WHERE slug = 'revo_pos_license_code' AND description != '' AND update_at is not NULL";
		$get   = $wpdb->get_row( $query, OBJECT );

		if ( ! empty( $get ) ) {
			if ( $get->update_at > $now ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'get_conversations' ) ) {

	function get_conversations( $user_id, $receiver_id = null ) {
		global $wpdb;

		$where = 'rc.sender_id = ' . $user_id . ' OR rc.receiver_id = ' . $user_id;
		if ( $receiver_id ) {
			$where = '( rc.sender_id = ' . $user_id . ' AND rc.receiver_id = ' . $receiver_id . ' ) OR ( rc.receiver_id = ' . $user_id . ' AND rc.sender_id = ' . $receiver_id . ' )';
		}

		$query = "SELECT rc.*, 
						(SELECT rcm.message FROM `revo_conversation_messages` rcm WHERE ($where) AND rcm.conversation_id = rc.id ORDER BY rcm.created_at DESC LIMIT 1) AS last_message,
						(SELECT rcm.created_at FROM `revo_conversation_messages` rcm WHERE ($where) AND rcm.conversation_id = rc.id ORDER BY rcm.created_at DESC LIMIT 1) AS created_chat,
						(SELECT count(rcm.id) FROM `revo_conversation_messages` rcm WHERE rcm.receiver_id = $user_id AND rcm.conversation_id = rc.id AND rcm.is_read = 1) AS unread,
						CASE
						   when rc.sender_id != $user_id then 'seller'
						   when rc.receiver_id != $user_id then 'buyer'
						END as status
						FROM `revo_conversations` rc WHERE $where GROUP BY rc.id ORDER BY created_chat DESC ";

		if ( $receiver_id ) {
			return $wpdb->get_row( $query, OBJECT );
		} else {
			return $wpdb->get_results( $query, OBJECT );
		}
	}
}

if ( ! function_exists( 'get_conversations_detail' ) ) {

	function get_conversations_detail( $user_id, $chat_id = null ) {
		global $wpdb;

		$where = ' (rcm.sender_id = ' . $user_id . ' OR rcm.receiver_id = ' . $user_id . ') ';
		if ( $chat_id ) {
			$where .= ' AND rcm.conversation_id = ' . $chat_id;
		}

		// if ($chat_id) {
		// 	$where .= ' AND rcm.conversation_id = '.$chat_id;
		// }

		$seller_id = cek_raw( 'seller_id' );
		if ( $seller_id ) {
			$where .= ' AND rc.receiver_id = ' . $seller_id;
		}

		$data['is_read'] = 0;
		$wpdb->update( 'revo_conversation_messages', $data, [
			'is_read'         => 1,
			'receiver_id'     => $user_id,
			'conversation_id' => $chat_id
		] );

		$query = " SELECT 
						rcm.conversation_id as chat_id,
						rcm.sender_id,
						rcm.receiver_id,
						rcm.message,
						rcm.type,
						rcm.image,
						rcm.post_id,
						CASE
						   when LOCATE('http',rcm.message) > 0 then 'image'
						   when LOCATE('https',rcm.message) > 0 then 'image'
						   else  'text'
						END as type_message,
						CASE
						   when rcm.sender_id = rc.sender_id then 'seller'
						   when rcm.sender_id = rc.receiver_id then 'buyer'
						END as status,
						CASE
						   when rcm.sender_id = $user_id then 'right'
						   when rcm.receiver_id = $user_id then 'left'
						END as potition,
						rcm.created_at
					FROM `revo_conversation_messages` as rcm INNER JOIN `revo_conversations` as rc on rcm.conversation_id = rc.id  WHERE $where GROUP BY rcm.id ORDER BY rcm.created_at ASC ";

		return $wpdb->get_results( $query, OBJECT );
	}
}

if ( ! function_exists( 'get_oauth_parameters' ) ) {

	function get_oauth_parameters() {
		$params = array_merge( $_GET, $_POST ); // WPCS: CSRF ok.
		$params = wp_unslash( $params );

		$header = '';

		if ( ! empty( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			$header = wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ); // WPCS: sanitization ok.
		}

		if ( function_exists( 'getallheaders' ) ) {
			$headers = getallheaders();
			// Check for the authoization header case-insensitively.
			foreach ( $headers as $key => $value ) {
				if ( 'authorization' === strtolower( $key ) ) {
					$header = $value;
				}
			}
		}

		if ( ! empty( $header ) ) {
			// Trim leading spaces.
			$header        = trim( $header );
			$header_params = parse_header( $header );

			if ( ! empty( $header_params ) ) {
				$params = array_merge( $params, $header_params );
			}
		}

		$param_names = array(
			'oauth_consumer_key',
			'oauth_timestamp',
			'oauth_nonce',
			'oauth_signature',
			'oauth_signature_method',
		);

		$errors   = array();
		$have_one = false;

		// Check for required OAuth parameters.
		foreach ( $param_names as $param_name ) {
			if ( empty( $params[ $param_name ] ) ) {
				$errors[] = $param_name;
			} else {
				$have_one = true;
			}
		}

		// All keys are missing, so we're probably not even trying to use OAuth.
		if ( ! $have_one ) {
			return array();
		}

		// If we have at least one supplied piece of data, and we have an error,
		// then it's a failed authentication.
		if ( ! empty( $errors ) ) {
			$message = sprintf(
			/* translators: %s: amount of errors */
				_n( 'Missing OAuth parameter %s', 'Missing OAuth parameters %s', count( $errors ), 'woocommerce' ),
				implode( ', ', $errors )
			);

			return new WP_Error( 'woocommerce_rest_authentication_missing_parameter', $message, array( 'status' => 401 ) );
		}

		return $params;
	}

	function parse_header( $header ) {
		if ( 'OAuth ' !== substr( $header, 0, 6 ) ) {
			return array();
		}

		// From OAuth PHP library, used under MIT license.
		$params = array();
		if ( preg_match_all( '/(oauth_[a-z_-]*)=(:?"([^"]*)"|([^,]*))/', $header, $matches ) ) {
			foreach ( $matches[1] as $i => $h ) {
				$params[ $h ] = urldecode( empty( $matches[3][ $i ] ) ? $matches[4][ $i ] : $matches[3][ $i ] );
			}
			if ( isset( $params['realm'] ) ) {
				unset( $params['realm'] );
			}
		}

		return $params;
	}
}

if ( ! function_exists( 'get_blogs' ) ) {
	function get_blogs() {
		$blogs    = get_posts();
		$all_blog = [];
		foreach ( $blogs as $key => $value ) {
			array_push( $all_blog, [
				'id'   => $value->ID,
				'text' => $value->post_title
			] );
		}

		return json_encode( $all_blog );
	}
}

if ( ! function_exists( 'get_attributes' ) ) {
	function get_attributes( $id = null ) {
		if ( is_null( $id ) ) {
			$attributes = wc_get_attribute_taxonomies();

			$all_attributes = [];
			foreach ( $attributes as $key => $value ) {
				array_push( $all_attributes, [
					'id'   => $value->attribute_id,
					'text' => $value->attribute_label
				] );
			}

			return json_encode( $all_attributes );
		}

		return wc_get_attribute( $id );
	}
}

if ( ! function_exists( 'revo_shine_includes_frontend' ) ) {
	function revo_shine_includes_frontend( $callback = null ) {
		if ( defined( 'WC_ABSPATH' ) ) {
			// WC 3.6+ - Cart and other frontend functions are not included for REST requests.
			require_once WC_ABSPATH . 'includes/wc-cart-functions.php';
			require_once WC_ABSPATH . 'includes/wc-notice-functions.php';
			require_once WC_ABSPATH . 'includes/wc-template-hooks.php';
		}

		if ( null === WC()->session ) {
			$session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );

			WC()->session = new $session_class();
			WC()->session->init();
		}

		if ( null === WC()->customer ) {
			WC()->customer = new WC_Customer( get_current_user_id(), true );
		}

		if ( null === $callback ) {
			if ( null === WC()->cart ) {
				WC()->cart = new WC_Cart();
			}
		} else {
			return $callback();
		}
	}
}

if ( ! function_exists( 'revo_shine_get_json_data' ) ) {
	function revo_shine_get_json_data( $url, $path, $file_name, $search ) {

		global $wp_filesystem;

		$file_url  = $url . $file_name . '.json';
		$file_path = $path . $file_name . '.json';

		try {
			require_once ABSPATH . 'wp-admin/includes/file.php';

			if ( is_null( $wp_filesystem ) ) {
				WP_Filesystem();
			}

			if ( ! $wp_filesystem instanceof WP_Filesystem_Base || ( is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) ) {
				throw new Exception( 'WordPress Filesystem Abstraction classes is not available', 1 );
			}

			if ( ! $wp_filesystem->exists( $file_path ) ) {
				throw new Exception( 'JSON file is not exists or unreadable', 1 );
			}

			$json = $wp_filesystem->get_contents( $file_path );
		} catch ( Exception $e ) {
			$json = wp_remote_retrieve_body( wp_remote_get( esc_url_raw( $file_url ) ) );
		}

		$json_data = json_decode( $json, true );

		if ( ! $json_data ) {
			return false;
		}

		if ( $search ) {
			$datas = [];
			foreach ( $json_data as $row ) {
				if ( array_intersect_assoc( $search, $row ) === $search ) {
					array_push( $datas, $row );
				}
			}

			return $datas;
		}

		return $json_data;
	}
}

if ( ! function_exists( 'revo_shine_send_push_notif_order' ) ) {
	function revo_shine_send_push_notif_order( $order, $order_status = '' ) {
		global $wpdb;

		if ( $order_status === 'checkout-draft' ) {
			return;
		}

		$order_number = $order->get_order_number();
		$user_id      = $order->get_user_id();
		$description  = json_encode( [ 'order_id' => $order_number, 'status' => $order_status ] );

		$existing_notification = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM revo_push_notification 
                WHERE type = %s AND user_id = %d 
                AND description = %s",
				'order',
				$user_id,
				$description
			)
		);

		if ( ! $existing_notification ) {
			$wpdb->insert( 'revo_push_notification', [
				'type'        => 'order',
				'user_id'     => $user_id,
				'description' => json_encode( [ 'order_id' => $order_number, 'status' => $order_status ] ),
				'created_at'  => wp_date( 'Y-m-d H:i:s' )
			] );

			$user_token = get_user_token( " WHERE user_id = '$user_id' " );

			if ( ! empty( $user_token ) ) {
				$title   = ucwords( __( 'Order updates', 'woocommerce' ) );
				$message = sprintf(
				/* translators: 1: order number 2: order date 3: order status */
					__( 'Order #%1$s was placed on %2$s and is currently %3$s.', 'woocommerce' ),
					$order->get_order_number(),
					wc_format_datetime( $order->get_date_created() ),
					wc_get_order_status_name( $order->get_status() )
				);

				foreach ( $user_token as $data ) {
					$notification = [
						'title' => $title,
						'body'  => $message,
					];

					$extend = [
						'id'   => $order_number,
						'type' => 'order'
					];

					revo_shine_fcm_api_v1( $notification, $extend, $data->token );
				}
			}
		}
	}
}

if ( ! function_exists( 'revo_shine_new_order' ) ) {
	function revo_shine_new_order( $order_id ) {
		$order = wc_get_order( $order_id );

		revo_shine_send_push_notif_order( $order, $order->get_status() );
		revo_shine_rebuild_cache( 'revo_home_data' );
	}
}

if ( ! function_exists( 'revo_shine_order_status_changed' ) ) {
	function revo_shine_order_status_changed( $order_id, $status_transition_from, $status_transition_to ) {
		$order = wc_get_order( $order_id );

		revo_shine_send_push_notif_order( $order, $order->get_status() );
	}
}

if ( ! function_exists( 'revo_shine_get_aftership_config' ) ) {
	function revo_shine_get_aftership_config() {
		return [
			'plugin_active'    => is_plugin_active( 'aftership-woocommerce-tracking/aftership-woocommerce-tracking.php' ),
			'aftership_domain' => $GLOBALS['AfterShip']->custom_domain ?? ""
		];
	}
}

if ( ! function_exists( 'revo_shine_get_themehigh_multiple_addresses_config' ) ) {
	function revo_shine_get_themehigh_multiple_addresses_config() {
		$plugin_status = is_plugin_active( 'themehigh-multiple-addresses/themehigh-multiple-addresses.php' );

		$response = [
			'status' => $plugin_status,
			'limit'  => 0
		];

		if ( $plugin_status ) {
			$response['limit'] = THMAF_Utils::get_general_settings()['settings_billing']['billing_address_limit'];
		}

		return $response;
	}
}

if ( ! function_exists( 'revo_shine_get_biteship_config' ) ) {
	function revo_shine_get_biteship_config() {
		if ( $status = is_plugin_active( 'biteship/biteship.php' ) ) {
			$licence = strlen( get_option( 'biteship_licence' ) );
			$service = new Biteship_Rest_Adapter( $licence );

			$get_gmap_api_key = $service->getGmapAPI();
			if ( $get_gmap_api_key['success'] ) {
				$gmap_api_key = $get_gmap_api_key['data'];
				$gmap_api_key = $service->decGmapAPI( $gmap_api_key );
			}
		}

		return [
			'status'       => $status,
			'gmap_api_key' => isset( $gmap_api_key ) ? $gmap_api_key : ''
		];
	}
}

if ( ! function_exists( 'revo_shine_load_component' ) ) {

	function revo_shine_load_component( $component_name, $datas = [] ) {
		$args = $datas;

		include REVO_SHINE_TEMPLATE_PATH . 'components/' . $component_name . '.php';
	}
}

if ( ! function_exists( 'revo_shine_default_mini_banner' ) ) {
	function revo_shine_default_mini_banner( $type_banner ) {
		return [
			'link_to'      => '',
			'name'         => '',
			'product'      => (int) '0',
			'title_slider' => '',
			'type'         => $type_banner,
			'image'        => '',
		];
	}
}

if ( ! function_exists( 'revo_shine_generate_etag' ) ) {
	function revo_shine_generate_etag( $key ) {
		$etag = wp_generate_password( 32, 0, 0 );
		update_option( $key, $etag );

		header( 'Revo-Etag: ' . $etag );

		return $etag;
	}
}

if ( ! function_exists( 'revo_shine_rebuild_cache' ) ) {
	function revo_shine_rebuild_cache( $transient_key ) {
		// if (version_compare){}

		update_option( $transient_key . '_etag', 123 );
		rest_home_api( 'get' );
	}
}

if ( ! function_exists( 'revo_shine_generate_static_file' ) ) {
	function revo_shine_generate_static_file( $key ) {
		$new_etag = md5( uniqid() );
		update_option( $key . '_etag', $new_etag );

		$args = [
			'limit'       => - 1,
			'post_status' => 'publish',
			'post_type'   => [
				'product',
				'product_variation'
			],
		];

		$data                       = [];
		$products                   = wc_get_products( $args );
		$flutter_mobile_app_service = load_revo_flutter_mobile_app();

		foreach ( $products as $product ) {
			$data[] = $flutter_mobile_app_service->reformat_product_result( $product );
		}

		$json_data = json_encode( $data );
		$file_path = REVO_SHINE_ABSPATH . '/storage/cache/' . $new_etag . '.json';
		file_put_contents( $file_path, $json_data );
	}
}

if ( ! function_exists( 'revo_shine_clear_caches' ) ) {

	function revo_shine_clear_caches() {
		$revo_home_etag    = get_option( 'revo_home_data_etag' );
		$revo_product_etag = get_option( 'revo_product_data_etag' );

		$caches = glob( REVO_SHINE_ABSPATH . 'storage/cache/*.json' );

		foreach ( $caches as $cache_file ) {
			if ( ! in_array( $cache_file, [
				REVO_SHINE_ABSPATH . "storage/cache/{$revo_home_etag}.json",
				REVO_SHINE_ABSPATH . "storage/cache/{$revo_product_etag}.json"
			] ) ) {
				unlink( $cache_file );
			}
		}
	}
}

if ( ! function_exists( 'revo_shine_logger' ) ) {
	function revo_shine_logger( $message ) {
		$logger = new WC_Logger();
		$logger->add( 'revo_shine', $message );
	}
}

if ( ! function_exists( 'revo_shine_check_cookie' ) ) {
	function revo_shine_check_cookie( $cookie ) {
		if ( ! isset( $cookie ) || empty( $cookie ) ) {
			return false;
		}
	}
}

if ( ! function_exists( 'revo_shine_open_curl' ) ) {
	function revo_shine_open_curl( $url, $method, $body = [], $headers = [] ) {
		$body = json_encode( $body );

		$curl = curl_init();
		curl_setopt_array( $curl, array(
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => $method,
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_POSTFIELDS     => $body,
		) );

		$response = curl_exec( $curl );
		$err      = curl_error( $curl );

		curl_close( $curl );

		return $response;
	}
}

if ( ! function_exists( 'revo_shine_ck_internal_code' ) ) {
	function revo_shine_ck_internal_code(): bool {
		global $wpdb;

		$data = $wpdb->get_row( "SELECT update_at FROM `revo_mobile_variable` WHERE slug = 'license_code' AND description != '' AND update_at IS NOT NULL", OBJECT );

		if ( ! empty( $data ) ) {
			if ( $data->update_at > date( 'Y-m-d H:i:s' ) ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'revo_shine_file_upload' ) ) {
	function revo_shine_file_upload( $files, $file_name ) {
		$target_dir      = WP_CONTENT_DIR . "/uploads/revo/";
		$uploads_url     = WP_CONTENT_URL . "/uploads/revo/";
		$target_file     = $target_dir . basename( $files[ $file_name ]["name"] );
		$imageFileType   = strtolower( pathinfo( $target_file, PATHINFO_EXTENSION ) );
		$newname         = md5( date( "Y-m-d H:i:s" ) ) . "." . $imageFileType;
		$is_upload_error = 0;

		if ( $files[ $file_name ]["size"] > 0 ) {
			if ( $files[ $file_name ]["size"] > 2000000 ) {
				$alert = array(
					'type'    => 'error',
					'title'   => 'Uploads Error !',
					'message' => 'your file is too large. max 2Mb',
				);

				$is_upload_error = 1;
			}

			if ( $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" ) {
				$alert = array(
					'type'    => 'error',
					'title'   => 'Uploads Error !',
					'message' => 'only JPG, JPEG & PNG files are allowed.',
				);

				$is_upload_error = 1;
			}

			if ( $is_upload_error === 0 ) {
				$alert = array(
					'type'    => 'success',
					'title'   => 'Upload Success !',
					'message' => '',
				);

				move_uploaded_file( $files[ $file_name ]["tmp_name"], $target_dir . $newname );
				$alert['message'] = $uploads_url . $newname;
			}
		}

		return $alert ?? false;
	}
}

if ( ! function_exists( 'revo_shine_load_content_wrapper' ) ) {
	function revo_shine_load_content_wrapper( $part ) {
		require_once REVO_SHINE_TEMPLATE_PATH . "admin/parts/{$part}.php";
	}
}

if ( ! function_exists( 'revo_shine_check_image_type' ) ) {
	function revo_shine_check_image_type( $image_url, $allowed_mimes = [ 'jpg', 'png', 'jpeg' ] ): bool {
		$image_file_type = pathinfo( $image_url, PATHINFO_EXTENSION );

		if ( ! in_array( $image_file_type, $allowed_mimes ) ) {
			return false;
		}

		return true;
	}
}

if ( ! function_exists( 'revo_shine_alert_app_settings' ) ) {
	function revo_shine_alert_app_settings() {
		$template_text = [
			'livechat'                               => [
				'on'  => [
					'title' => 'Live Chat requires the RevoPOS mobile app to reply the chats',
					'text'  => 'Do you want to activate Live Chat?'
				],
				'off' => [ 'title' => '', 'text' => 'Do you want to turn off Live Chat?' ]
			],
			'guestcheckout'                          => [
				'on'  => [
					'title' => 'Are you going to enable Guest Checkout?',
					'text'  => '(User can shop without login)'
				],
				'off' => [
					'title' => 'Are you going to disable Guest Checkout?',
					'text'  => '(User must login to be able to shop)'
				]
			],
			'gift_box'                               => [
				'on'  => [
					'title' => 'Are you going to enable Animated Gift Box',
					'text'  => '(Animated Gift Box will appear if you have a coupon to use)'
				],
				'off' => [
					'title' => 'Are you going to disable Animated Gift Box',
					'text'  => '(Animated Gift Box will not appear even if you have a coupon that can be used)'
				]
			],
			'checkout_native'                        => [
				'on'  => [
					'title' => 'Are you going to enable Native Checkout method',
					'text'  => '(Native Checkout method just using default shipping and payment from woocommerce)'
				],
				'off' => [
					'title' => 'Are you going to disable Native Checkout method',
					'text'  => '(you will use webview checkout on your app)'
				]
			],
			'blog_comment_feature'                   => [
				'on'  => [ 'title' => '', 'text' => 'Are you sure to show comment feature on detail blog page?' ],
				'off' => [ 'title' => '', 'text' => 'are you sure to hide comment feature on detail blog page?' ]
			],
			'guide_feature'                          => [
				'on'  => [
					'title' => 'Are you sure to enable repet guide feature?',
					'text'  => 'Guide will be shown every time when open the app'
				],
				'off' => [ 'title' => 'Are you sure to disable repet guide feature?', 'text' => '' ]
			],
			'popup_biometric'                        => [
				'on'  => [
					'title' => 'Are you sure to enable Popup Biometric?',
					'text'  => 'Pop-up biometric will be shown when the user has not registered biometric'
				],
				'off' => [ 'title' => 'Are you sure to disable Popup Biometric?', 'text' => '' ]
			],
			'show_sold_item_data'                    => [
				'on'  => [ 'title' => '', 'text' => 'Are you sure to enable show sold item data?' ],
				'off' => [ 'title' => '', 'text' => 'Are you sure to disable show sold item data?' ]
			],
			'show_average_rating_data'               => [
				'on'  => [ 'title' => '', 'text' => 'Are you sure to enable show average rating data?' ],
				'off' => [ 'title' => '', 'text' => 'Are you sure to disable show average rating data?' ]
			],
			'show_rating_section'                    => [
				'on'  => [ 'title' => '', 'text' => 'Are you sure to enable show rating section?' ],
				'off' => [ 'title' => '', 'text' => 'Are you sure to disable show rating section?' ]
			],
			'show_variation_with_image'              => [
				'on'  => [ 'title' => '', 'text' => 'Are you sure to enable show variation with image?' ],
				'off' => [ 'title' => '', 'text' => 'Are you sure to disable show variation with image?' ]
			],
			'show_out_of_stock_product'              => [
				'on'  => [ 'title' => '', 'text' => 'Are you sure to enable show out of stock_product?' ],
				'off' => [ 'title' => '', 'text' => 'Are you sure to disable show out of stock_product?' ],
			],
			'enable_affiliate_video'                 => [
				'on'  => [ 'title' => '', 'text' => 'Are you sure to enable show out of affiliate video?' ],
				'off' => [ 'title' => '', 'text' => 'Are you sure to disable show out of affiliate video?' ]
			],
			'enable_floating_sign_in'                => [
				'on'  => [ 'title' => '', 'text' => 'Are you sure to enable show Floating sign in bar?' ],
				'off' => [ 'title' => '', 'text' => 'Are you sure to disable show Floating sign in bar?' ]
			],
			'enable_promo_label_on_product_card'     => [
				'on'  => [ 'title' => '', 'text' => 'Are you sure to enable show Promo label on product card?' ],
				'off' => [ 'title' => '', 'text' => 'Are you sure to disable show Promo label on product card?' ]
			],
			'enable_new_product_display_on_new_menu' => [
				'on'  => [ 'title' => '', 'text' => 'Are you sure to enable show New Products Display on New Menu?' ],
				'off' => [ 'title' => '', 'text' => 'Are you sure to disable show New Products Display on New Menu?' ]
			],
		];

		return apply_filters( 'revo_shine_template_text_alert_app_settings', $template_text );
	}
}

if ( ! function_exists( 'revo_shine_get_page_by_title' ) ) {
	function revo_shine_get_page_by_title( $title, $post_type = 'page' ) {
		$array_of_objects = get_posts( [
			'title'          => $title,
			'post_type'      => $post_type,
			'post_status'    => [ 'publish', 'draft' ],
			'posts_per_page' => 1,
		] );

		return $array_of_objects[0] ?? '';
	}
}

if ( ! function_exists( 'revo_shine_get_admin_template_part' ) ) {
	function revo_shine_get_admin_template_part( $name, $args = [] ): void {

		$args = apply_filters( 'revo_shine_get_admin_template_part_args', $args, $name );

		include REVO_SHINE_TEMPLATE_PATH . "/admin/{$name}.php";
	}
}

if ( ! function_exists( 'revo_shine_admin_get_selected_linked_value' ) ) {

	function revo_shine_admin_get_selected_linked_value( $value, $type = 'url' ): string {

		switch ( $type ) {
			case 'url':
				$selected_value = "url|" . $value;
				break;
			case 'category':
				$selected_value = "cat|" . revo_shine_get_category( $value )->name;
				break;
			case 'blog':
				$selected_value = "blog|" . get_post( $value )->post_title;
				break;
			case 'attribute':
				$selected_value = "attribute|" . get_attributes( $value )->name;
				break;
			default:
				$selected_value = revo_shine_get_product_variant_detail( $value );
				if ( isset( $selected_value[0] ) ) {
					$selected_value = $selected_value[0]->get_title();
				} else {
					$selected_value = '';
				}
				break;
		}

		return $selected_value;
	}
}
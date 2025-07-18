<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

// CRUD function
if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
	if ( isset( $_POST['guide_feature_image'] ) ) {
		$query_guide_feature = query_revo_mobile_variable( '"guide_feature"', 'sort' );

		if ( empty( $query_guide_feature ) ) {
			$wpdb->insert( 'revo_mobile_variable', array(
				'slug'        => 'guide_feature',
				'title'       => '',
				'image'       => $_POST['guide_feature_image'],
				'description' => 'hide'
			) );
		} else {
			$wpdb->query(
				$wpdb->prepare( "UPDATE revo_mobile_variable SET image='" . $_POST['guide_feature_image'] . "' WHERE slug='guide_feature'" )
			);
		}
	}

	if ( isset( $_POST['action'] ) ) {
		$action = $_POST['action'];

		$alert = [
			'type'    => 'success',
			'title'   => 'Success',
			'message' => 'Successfully updated the data !',
		];

		if ( $action === 'login_page' ) {
			$login_page_data = get_option( 'revo_shine_login_page', [
				'design'       => 'classic',
				'text_heading' => '#1 Shop App for your Woocommerce Store',
				'text'         => 'You can change this text at any time in WP-ADMIN',
				'bg_color'     => '#118eea',
				'text_color'   => '#FFFFFF',
				'btn_color'    => '#940000',
				'bg_image'     => REVO_SHINE_ASSET_URL . 'images/bg_login.png'
			] );

			$allowed_login_page_data = [
				'design',
				'text_heading',
				'text',
				'text_color',
				'btn_color',
				'bg_color',
				'bg_image'
			];

			foreach ( $_POST as $key => $value ) {
				if ( in_array( $key, $allowed_login_page_data ) ) {
					$login_page_data[ $key ] = $value;
				}
			}

			update_option( 'revo_shine_login_page', $login_page_data );
		}

		if ( $action === 'floating_signin_bar' ) {
			$title			= $_POST['floating_title'];
			$title_signin	= $_POST['floating_signin_title'];

			$check_floating	= $wpdb->get_row( "SELECT * FROM revo_mobile_variable WHERE slug = 'data_floating_signin' LIMIT 1" );
			$description	= serialize( array(
				'title'			=> $title,
				'title_signin'	=> $title_signin,
			) );

			if ( ! $check_floating ) {
				$data = array(
					'slug'			=> 'data_floating_signin',
					'title'			=> 'Data Floating Sign In',
					'image'			=> '',
					'description'	=> $description,
				);

				$save_data = $wpdb->insert( 'revo_mobile_variable', $data );

				if ( ! $save_data ) {
					$alert = array(
						'type'    => 'error',
						'title'   => 'Error',
						'message' => 'Failed to save data floating for sign in !',
					);
				}
			} else {
				$update_data = $wpdb->update( 'revo_mobile_variable', array( 'description' => $description ), array( 'id' => (int) $check_floating->id ) );
			}
		}

		if ( $action === 'promo_label' ) {
			$products   = json_encode( $_POST['products'] ?? [] );
			$images		= $_POST['image'];

			$alert = array(
				'type'    => 'error',
				'title'   => 'Failed !',
				'message' => 'Failed to Add Promo Label on Product Card',
			);

			if ( ! empty( $images ) ) {
				$alert = array(
					'type'    => 'error',
					'title'   => 'Uploads Error !',
					'message' => 'Your file type is not allowed. Only support jpg, png, jpeg.',
				);

				if ( revo_shine_check_image_type( $images ) ) {
					$data = array(
						'name'     	=> 'Black Friday',
						'slug'     	=> 'black-friday',
						'type'		=> 'label-black-friday',
						'image'     => $images,
						'products'  => $products,
					);

					if ( empty( $_POST['id'] ) ) {
						$wpdb->insert( 'revo_label_product', $data );

						if ( @$wpdb->insert_id > 0 ) {
							$alert = array(
								'type'    => 'success',
								'title'   => 'Success !',
								'message' => 'Promo Label on Product Card Success Saved',
							);
						}
					} else {
						$update_status = $wpdb->update( 'revo_label_product', $data, [ 'id' => $_POST['id'] ] );

						if ( $update_status !== false ) {
							$alert = array(
								'type'    => 'success',
								'title'   => 'Success !',
								'message' => 'Promo Label on Product Card Updated Successfully.',
							);
						}
					}
				}
			}
		}

		if ( $action === 'destroy' ) {
			header( 'Content-type: application/json' );

			$query = $wpdb->delete(
				'revo_label_product',
				array( 'id' => $_POST['id'] )
			);


			$alert = array(
				'type'    => 'error',
				'title'   => 'Failed !',
				'message' => 'Failed to Delete Promo Label on Product Card',
			);

			if ( $query ) {
				$alert = array(
					'type'    => 'success',
					'title'   => 'Success !',
					'message' => 'Promo Label on Product Card Success Deleted',
				);
			}
		}

		$_SESSION["alert"] = $alert;
	}

	if ( isset( $_POST['typeQuery'] ) ) {
		header( 'Content-type: application/json' );

		switch ( $_POST['typeQuery'] ) {
			case 'product_setting':
				$status = $_POST["status"];
				$action = $_POST["action"];
				$get    = query_revo_mobile_variable( '"' . $action . '"', 'sort' );

				if ( empty( $get ) ) {
					$wpdb->insert( 'revo_mobile_variable', array(
						'slug'        => $action,
						'title'       => '',
						'image'       => '',
						'description' => $status
					) );
				} else {
					$wpdb->query(
						$wpdb->prepare( "UPDATE revo_mobile_variable SET description='$status' WHERE slug='{$action}'" )
					);
				}
				break;
			case 'livechat':
				$get    = query_revo_mobile_variable( '"live_chat_status"', 'sort' );
				$status = $_POST["status"];

				if ( empty( $get ) ) {
					$wpdb->insert( 'revo_mobile_variable', array(
						'slug'        => 'live_chat_status',
						'title'       => '',
						'image'       => '',
						'description' => $status
					) );
				} else {
					$wpdb->query(
						$wpdb->prepare( "UPDATE revo_mobile_variable SET description='$status' WHERE slug='live_chat_status'" )
					);
				}
				break;
			case 'guestcheckout':
				$check  = get_option( 'woocommerce_enable_guest_checkout' );
				$status = $_POST["status"] == 'show' ? 'yes' : 'no';

				if ( empty( $check ) ) {
					add_option( 'woocommerce_enable_guest_checkout', $status );
				} else {
					update_option( 'woocommerce_enable_guest_checkout', $status );
				}
				break;
			case 'gift_box':
				$get    = query_revo_mobile_variable( '"gift_box"', 'sort' );
				$status = $_POST["status"];

				if ( empty( $get ) ) {
					$wpdb->insert( 'revo_mobile_variable', array(
						'slug'        => 'gift_box',
						'title'       => '',
						'image'       => '',
						'description' => $status
					) );
				} else {
					$wpdb->query(
						$wpdb->prepare( "UPDATE revo_mobile_variable SET description='$status' WHERE slug='gift_box'" )
					);
				}
				break;
			case 'checkout_native':
				$get    = query_revo_mobile_variable( '"checkout_native"', 'sort' );
				$status = $_POST["status"];

				if ( empty( $get ) ) {
					$wpdb->insert( 'revo_mobile_variable', array(
						'slug'        => 'checkout_native',
						'title'       => '',
						'image'       => '',
						'description' => $status
					) );
				} else {
					$wpdb->query( $wpdb->prepare( "UPDATE revo_mobile_variable SET description='$status' WHERE slug='checkout_native'" ) );
				}
				break;
			case 'blog_comment_feature':
				$get    = query_revo_mobile_variable( '"blog_comment_feature"', 'sort' );
				$status = $_POST["status"];

				if ( empty( $get ) ) {
					$wpdb->insert( 'revo_mobile_variable', array(
						'slug'        => 'blog_comment_feature',
						'title'       => '',
						'image'       => '',
						'description' => $status
					) );
				} else {
					$wpdb->query(
						$wpdb->prepare( "UPDATE revo_mobile_variable SET description='$status' WHERE slug='blog_comment_feature'" )
					);
				}
				break;
			case 'guide_feature':
				$get    = query_revo_mobile_variable( '"guide_feature"', 'sort' );
				$status = $_POST["status"];

				if ( empty( $get ) ) {
					$wpdb->insert( 'revo_mobile_variable', array(
						'slug'        => 'guide_feature',
						'title'       => '',
						'image'       => '',
						'description' => $status
					) );
				} else {
					$wpdb->query(
						$wpdb->prepare( "UPDATE revo_mobile_variable SET description='$status' WHERE slug='guide_feature'" )
					);
				}

				break;
			case 'popup_biometric':
				$get    = query_revo_mobile_variable( '"popup_biometric"', 'sort' );
				$status = $_POST["status"];

				if ( empty( $get ) ) {
					$wpdb->insert( 'revo_mobile_variable', array(
						'slug'        => 'popup_biometric',
						'title'       => '',
						'image'       => '',
						'description' => $status
					) );
				} else {
					$wpdb->query(
						$wpdb->prepare( "UPDATE revo_mobile_variable SET description='$status' WHERE slug='popup_biometric'" )
					);
				}

				break;
			case 'design':
				$get    = query_revo_mobile_variable( '"design_product_page"', 'sort' );
				$status = $_POST["status"];

				if ( empty( $get ) ) {
					$wpdb->insert( 'revo_mobile_variable', array(
						'slug'        => 'design_product_page',
						'title'       => '',
						'image'       => '',
						'description' => $status
					) );
				} else {
					$wpdb->query(
						$wpdb->prepare( "UPDATE revo_mobile_variable SET description='$status' WHERE slug='design_product_page'" )
					);
				}

				break;
			case 'enable_affiliate_video':
				$get    = query_revo_mobile_variable( '"enable_affiliate_video"', 'sort' );
				$status = $_POST["status"];

				if ( empty( $get ) ) {
					$wpdb->insert( 'revo_mobile_variable', array(
						'slug'        => 'enable_affiliate_video',
						'title'       => '',
						'image'       => '',
						'description' => $status
					) );
				} else {
					$wpdb->query(
						$wpdb->prepare( "UPDATE revo_mobile_variable SET description='$status' WHERE slug='enable_affiliate_video'" )
					);
				}

				( Revo_Shine_Video::get_instance() )->change_video_shopping_page_status( $status === 'show' ? 'publish' : 'draft' );

				break;
			case 'enable_floating_sign_in':
				$get    = query_revo_mobile_variable( '"enable_floating_sign_in"', 'sort' );
				$status = $_POST["status"];

				if ( empty( $get ) ) {
					$wpdb->insert( 'revo_mobile_variable', array(
						'slug'        => 'enable_floating_sign_in',
						'title'       => '',
						'image'       => '',
						'description' => $status
					) );
				} else {
					$wpdb->query(
						$wpdb->prepare( "UPDATE revo_mobile_variable SET description='$status' WHERE slug='enable_floating_sign_in'" )
					);
				}

				( Revo_Shine_Video::get_instance() )->change_video_shopping_page_status( $status === 'show' ? 'publish' : 'draft' );

				break;
			case 'enable_promo_label_on_product_card':
				$get    = query_revo_mobile_variable( '"enable_promo_label_on_product_card"', 'sort' );
				$status = $_POST["status"];

				if ( empty( $get ) ) {
					$wpdb->insert( 'revo_mobile_variable', array(
						'slug'        => 'enable_promo_label_on_product_card',
						'title'       => '',
						'image'       => '',
						'description' => $status
					) );
				} else {
					$wpdb->query(
						$wpdb->prepare( "UPDATE revo_mobile_variable SET description='$status' WHERE slug='enable_promo_label_on_product_card'" )
					);
				}

				( Revo_Shine_Video::get_instance() )->change_video_shopping_page_status( $status === 'show' ? 'publish' : 'draft' );

				break;
			case 'enable_new_product_display_on_new_menu':
				$get    = query_revo_mobile_variable( '"enable_new_product_display_on_new_menu"', 'sort' );
				$status = $_POST["status"];

				if ( empty( $get ) ) {
					$wpdb->insert( 'revo_mobile_variable', array(
						'slug'        => 'enable_new_product_display_on_new_menu',
						'title'       => '',
						'image'       => '',
						'description' => $status
					) );
				} else {
					$wpdb->query(
						$wpdb->prepare( "UPDATE revo_mobile_variable SET description='$status' WHERE slug='enable_new_product_display_on_new_menu'" )
					);
				}

				( Revo_Shine_Video::get_instance() )->change_video_shopping_page_status( $status === 'show' ? 'publish' : 'draft' );

				break;
		}

		revo_shine_rebuild_cache( 'revo_home_data' );

		http_response_code( 200 );

		wp_send_json( [ 'kode' => 'S' ] );
	}

	if ( isset( $_POST['other_action'] ) ) {
		$data = $_POST;
		if ( ! empty( $data['membership_category'] ) ) {
			update_option( 'revo_membership_selected_category', $data['membership_category'] );
			$alert['type'] = 'success';
		}
	}

	do_action( 'revo_shine_crud_app_settings' );

	revo_shine_rebuild_cache( 'revo_home_data' );
}

// General settings
$settings = query_revo_mobile_variable( [
	'live_chat_status',
	'gift_box',
	'checkout_native',
	'blog_comment_feature',
	'guide_feature',
	'popup_biometric',
	'design_product_page',
	'show_sold_item_data',
	'show_average_rating_data',
	'show_rating_section',
	'show_variation_with_image',
	'show_out_of_stock_product',
	'enable_affiliate_video',
	'enable_floating_sign_in',
	'enable_promo_label_on_product_card',
	'enable_new_product_display_on_new_menu',
], 'sort' );

$service_status = [
	'revopos_status'            => query_check_plugin_active( 'Plugin-revo-kasir' ),
	'guest_checkout'            => get_option( 'woocommerce_enable_guest_checkout' ) == 'yes' ? 'show' : 'hide',
	'live_chat_status'          => 'hide',
	'gift_box'                  => 'hide',
	'checkout_native'           => 'hide',
	'blog_comment_feature'      => 'hide',
	'popup_biometric'           => 'hide',
	'guide_feature'             => 'hide',
	'guide_feature_image'       => '',
	'design_product_page'       => '',
	// product settings
	'show_sold_item_data'       => 'hide',
	'show_average_rating_data'  => 'hide',
	'show_rating_section'       => 'hide',
	'show_variation_with_image' => 'hide',
	'show_out_of_stock_product' => 'hide',
	'enable_affiliate_video'    => 'hide',
	'enable_floating_sign_in'   => 'hide',
	'enable_promo_label_on_product_card'		=> 'hide',
	'enable_new_product_display_on_new_menu'	=> 'show',
];

if ( ! empty( $settings ) ) {
	foreach ( $settings as $setting ) {
		$service_status[ $setting->slug ] = $setting->description;

		if ( $setting->slug === 'guide_feature' ) {
			$service_status['guide_feature_image'] = ! empty( $setting->image ) ? $setting->image : '';
		}
	}
}

// Other settings
$categories        = json_decode( revo_shine_get_categories() );
$selected_category = get_option( 'revo_membership_selected_category' );
$data_gift_box     = get_option( 'revo_shine_gift_box_destination', 'couponpage' );

$get_data_floating_signin	= query_revo_mobile_variable( "'data_floating_signin'" );
$data_floating_signin		= unserialize( $get_data_floating_signin[0]->description ?? '' );
$get_label_product			= $wpdb->get_results( "SELECT * FROM revo_label_product WHERE type = 'label-black-friday' LIMIT 3" );
?>

<div class="admin-section-container">
    <div class="admin-section-item">
        <div class="admin-section-item-title">App Setting</div>
        <div class="admin-section-item-body">
            <div class="mb-small">
                <ul class="nav nav-underline" id="tabNavigation" role="tablist">
                    <li class="nav-item" role="tab" data-target="#tab-general">
                        <a class="nav-link fs-16 lh-16 fw-600 pointer">General</a>
                    </li>
                    <li class="nav-item" role="tab" data-target="#tab-login-page">
                        <a class="nav-link fs-16 lh-16 fw-600 pointer">Login Page</a>
                    </li>

					<?php

					do_action( 'revo_shine_nav_tab_app_settings' );

					if ( is_plugin_active( 'woocommerce-memberships/class-wc-memberships.php' ) ) {
						?>
                        <li class="nav-item" role="tab" data-target="#tab-other">
                            <a class="nav-link fs-16 lh-16 fw-600 pointer">Other</a>
                        </li>
						<?php
					}

					?>
                </ul>
            </div>
            <div class="tab-content px-0 w-100" id="tabContent">
                <div class="tab-pane fade" id="tab-general" role="tabpanel">
					<?php

					if ( $service_status['revopos_status'] ) {
						revo_shine_get_admin_template_part( 'components/switch', [
							'id'         => 'myonoffswitch',
							'label'      => 'Live Chat (Requires the RevoPOS App)',
							'name'       => 'livechat',
							'value'      => $service_status['live_chat_status'] ?? 'hide',
							'is_checked' => ( $service_status['live_chat_status'] ?? 'hide' ) === 'show'
						] );
					}

					revo_shine_get_admin_template_part( 'components/switch', [
						'id'         => 'switch_guestcheckout',
						'label'      => 'Allow Guest to Checkout',
						'name'       => 'guestcheckout',
						'value'      => $service_status['guest_checkout'] ?? 'hide',
						'is_checked' => ( $service_status['guest_checkout'] ?? 'hide' ) === 'show'
					] );

					revo_shine_get_admin_template_part( 'components/switch', [
						'id'         => 'switch_gift_box',
						'label'      => 'Animated Gift Box',
						'name'       => 'gift_box',
						'value'      => $service_status['gift_box'] ?? 'hide',
						'is_checked' => ( $service_status['gift_box'] ?? 'hide' ) === 'show'
					] );

					revo_shine_get_admin_template_part( 'components/switch', [
						'id'         => 'switch_checkout_native',
						'label'      => 'Native Checkout',
						'name'       => 'checkout_native',
						'value'      => $service_status['checkout_native'] ?? 'hide',
						'is_checked' => ( $service_status['checkout_native'] ?? 'hide' ) === 'show'
					] );

					revo_shine_get_admin_template_part( 'components/switch', [
						'id'         => 'switch_blog_comment_feature',
						'label'      => 'Comments on Blogs',
						'name'       => 'blog_comment_feature',
						'value'      => $service_status['blog_comment_feature'] ?? 'hide',
						'is_checked' => ( $service_status['blog_comment_feature'] ?? 'hide' ) === 'show'
					] );

					revo_shine_get_admin_template_part( 'components/switch', [
						'id'           => 'switch_guide_feature',
						'label'        => 'Repeat Guide',
						'label_helper' => '<div class="fs-10 lh-14 fw-400 text-primary border-bottom border-primary pointer mt-1 pb-1" data-bs-toggle="modal" data-bs-target="#modalGuide">Upload background image for guide</div>',
						'name'         => 'guide_feature',
						'value'        => $service_status['guide_feature'] ?? 'hide',
						'is_checked'   => ( $service_status['guide_feature'] ?? 'hide' ) === 'show'
					] );

					revo_shine_get_admin_template_part( 'components/switch', [
						'id'         => 'switch_popup_biometric',
						'label'      => 'Pop-Up Biometric',
						'name'       => 'popup_biometric',
						'value'      => $service_status['popup_biometric'] ?? 'hide',
						'is_checked' => ( $service_status['popup_biometric'] ?? 'hide' ) === 'show'
					] );

					revo_shine_get_admin_template_part( 'components/switch', [
						'id'         => 'switch_show_sold_item_data',
						'label'      => 'Show Sold Item Data',
						'name'       => 'show_sold_item_data',
						'value'      => $service_status['show_sold_item_data'] ?? 'hide',
						'is_checked' => ( $service_status['show_sold_item_data'] ?? 'hide' ) === 'show',
						'action'     => 'product_settings'
					] );

					revo_shine_get_admin_template_part( 'components/switch', [
						'id'         => 'switch_show_average_rating_data',
						'label'      => 'Show Average Rating Data',
						'name'       => 'show_average_rating_data',
						'value'      => $service_status['show_average_rating_data'] ?? 'hide',
						'is_checked' => ( $service_status['show_average_rating_data'] ?? 'hide' ) === 'show',
						'action'     => 'product_settings'
					] );

					revo_shine_get_admin_template_part( 'components/switch', [
						'id'         => 'switch_show_rating_section',
						'label'      => 'Show Rating Section',
						'name'       => 'show_rating_section',
						'value'      => $service_status['show_rating_section'] ?? 'hide',
						'is_checked' => ( $service_status['show_rating_section'] ?? 'hide' ) === 'show',
						'action'     => 'product_settings'
					] );

					revo_shine_get_admin_template_part( 'components/switch', [
						'id'         => 'switch_show_variation_with_image',
						'label'      => 'Show Variation with Image',
						'name'       => 'show_variation_with_image',
						'value'      => $service_status['show_variation_with_image'] ?? 'hide',
						'is_checked' => ( $service_status['show_variation_with_image'] ?? 'hide' ) === 'show',
						'action'     => 'product_settings'
					] );

					revo_shine_get_admin_template_part( 'components/switch', [
						'id'         => 'switch_show_out_of_stock_product',
						'label'      => 'Show Out of Stock Product',
						'name'       => 'show_out_of_stock_product',
						'value'      => $service_status['show_out_of_stock_product'] ?? 'hide',
						'is_checked' => ( $service_status['show_out_of_stock_product'] ?? 'hide' ) === 'show',
						'action'     => 'product_settings'
					] );

					revo_shine_get_admin_template_part( 'components/switch', [
						'id'         => 'switch_enable_affiliate_video',
						'label'      => 'Enable Video Shopping',
						'name'       => 'enable_affiliate_video',
						'value'      => $service_status['enable_affiliate_video'] ?? 'hide',
						'is_checked' => ( $service_status['enable_affiliate_video'] ?? 'hide' ) === 'show'
					] );

					revo_shine_get_admin_template_part( 'components/switch', [
						'id'         			=> 'switch_enable_floating_sign_in',
						'label'      			=> 'Show Floating Sign In Bar',
						'name'       			=> 'enable_floating_sign_in',
						'value'      			=> $service_status['enable_floating_sign_in'] ?? 'hide',
						'is_checked'			=> ( $service_status['enable_floating_sign_in'] ?? 'hide' ) === 'show',
						'customize_block'		=> true,
						'customize_block_data'	=> $service_status['enable_floating_sign_in'] === 'show' ? '
							<div class="mt-2">
								<form method="POST">
									<div class="row">
										<div class="col-md-6 col-12">
											<div class="d-flex flex-column" bis_skin_checked="1">
												<label class="form-label fs-14" style="color: #414346" for="floating-title">Floating Sign In Text</label>
											</div>

											<input class="form-control" id="floating-title" type="text" name="floating_title" placeholder="Ex: (For Example : Get 1000 points for signing up)" value="' . (isset($data_floating_signin['title']) ? $data_floating_signin['title'] : 'Get 1000 points for signing up') . '" required="">
										</div>
										<div class="col-md-6 col-12">
											<div class="d-flex flex-column" bis_skin_checked="1">
												<label class="form-label fs-14" style="color: #414346" for="floating-signin-title">Button Text:</label>
											</div>
											
											<input class="form-control" id="floating-signin-title" type="text" name="floating_signin_title" placeholder="Ex: (For Example : Sign In)" value="' . (isset($data_floating_signin['title_signin']) ? $data_floating_signin['title_signin'] : 'Sign In') . '" required="">
										</div>

										<div class="col-12 d-flex justify-content-end mt-3">
											<input name="action" value="floating_signin_bar" hidden >
											<button type="submit" class="btn btn-primary">Save Changes</button>
										</div>
									</div>
								</form>
							</div>' : ''
					] );

					revo_shine_get_admin_template_part( 'components/switch', [
						'id'         			=> 'switch_enable_promo_label_on_product_card',
						'label'      			=> 'Promo Label on Product Card ',
						'name'       			=> 'enable_promo_label_on_product_card',
						'value'      			=> $service_status['enable_promo_label_on_product_card'] ?? 'hide',
						'is_checked' 			=> ( $service_status['enable_promo_label_on_product_card'] ?? 'hide' ) === 'show',
						'customize_block'		=> true,
						'customize_block_data'	=> $service_status['enable_promo_label_on_product_card'] === 'show' ? '
							<div class="mt-2 p-3" style="background: #F6F7F880; border-radius: 12px">
								<table class="table table-bordered">
									<thead>
										<tr>
											<th class="text-center">No</th>
											<th class="text-center">Label Promo Image</th>
											<th class="text-center">List Product</th>
											<th class="text-center hidden-xs">Action</th>
										</tr>
									</thead>
									<tbody>' . 
										( empty( $get_label_product ) 
											? '<tr>
													<td colspan="4" class="text-center">No data in this table</td>
											</tr>'
											: implode('', array_map(function($value, $key) {
												$product_ids	= json_decode( $value->products );
												$products_label = array_map(function($product_id) {
													$product = wc_get_product($product_id);
													return $product ? '<span class="badge badge-primary fs-12 lh-12 fw-600">' . esc_html($product->get_name()) . '</span>' : '';
													}, $product_ids
												);

												$data_products = array_map(function($product_id) {
													$product = wc_get_product($product_id);
													return $product ? [
														'id'   => $product->get_id(),
														'text' => $product->get_name()
													] : null;
												}, $product_ids);

												return '
												<tr>
													<td class="text-center">' . ($key + 1) . '</td>
													<td class="text-center" width="30%">
														<img class="w-100" src="' . $value->image . '" />
													</td>
													<td class="align-top">' . implode(' ', $products_label) . '</td>
													<td class="text-center" width="15%">
														<div class="d-flex flex-column align-items-center gap-small">
															<button class="btn w-100 btn-outline-primary btn-update" 
																	data-id="' . $value->id . '"
																	data-data="' . base64_encode(json_encode($value)) . '"
																	data-products="' . base64_encode(json_encode($data_products)) . '">
																Update
															</button>
															<button class="btn w-100 btn-outline-danger btn-destroy" 
																	data-id="' . $value->id . '">
																Delete
															</button>
														</div>
													</td>
												</tr>';
											}, $get_label_product, array_keys($get_label_product)))
										) . '
									</tbody>
								</table>' .

								(count($get_label_product) < 3  ?
									'<div class="w-100">
										<button class="btn btn-add-item btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalAction">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 19C11.7167 19 11.4793 18.904 11.288 18.712C11.096 18.5207 11 18.2833 11 18V13H6C5.71667 13 5.479 12.904 5.287 12.712C5.09567 12.5207 5 12.2833 5 12C5 11.7167 5.09567 11.479 5.287 11.287C5.479 11.0957 5.71667 11 6 11H11V6C11 5.71667 11.096 5.479 11.288 5.287C11.4793 5.09567 11.7167 5 12 5C12.2833 5 12.521 5.09567 12.713 5.287C12.9043 5.479 13 5.71667 13 6V11H18C18.2833 11 18.5207 11.0957 18.712 11.287C18.904 11.479 19 11.7167 19 12C19 12.2833 18.904 12.5207 18.712 12.712C18.5207 12.904 18.2833 13 18 13H13V18C13 18.2833 12.9043 18.5207 12.713 18.712C12.521 18.904 12.2833 19 12 19Z" fill="white"></path>
											</svg>
											<span class="fs-12 lh-12 fw-600 ms-1">Add Label</span>
										</button>
									</div>' : ''
								) . '
							</div>' : '',
					] );

					revo_shine_get_admin_template_part( 'components/switch', array(
						'id'         => 'switch_enable_new_product_display_on_new_menu',
						'label'      => 'Enable New Products Display on New Menu',
						'name'       => 'enable_new_product_display_on_new_menu',
						'value'      => $service_status['enable_new_product_display_on_new_menu'] ?? 'hide',
						'is_checked' => ( $service_status['enable_new_product_display_on_new_menu'] ?? 'hide' ) === 'show'
					) );

					do_action( 'revo_shine_after_toggle_app_settings' );

					?>

					<?php if ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) ) : ?>
                        <div class="col-md-12 border-bottom-primary mb-3 py-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <h5 class="mb-0">Design option on the Product Details Page</h5>
                                </div>

                                <div class="d-flex">
                                    <select class="form-control" name="design" id="design">
                                        <option value="design_1" <?php echo $service_status['design_product_page'] === 'design_1' ? 'selected' : '' ?>>
                                            Design 1
                                        </option>
                                        <option value="design_2" <?php echo $service_status['design_product_page'] === 'design_2' ? 'selected' : '' ?>>
                                            Design 2
                                        </option>
                                    </select>
                                    <button class="ml-2 btn btn-sm btn-success" onclick="onHandleButton('design')">
                                        Save
                                    </button>
                                </div>
                            </div>
                        </div>
					<?php endif; ?>
                </div>
                <div class="tab-pane fade" id="tab-login-page" role="tabpanel">
                    <div class="border-bottom pb-3 mb-large">
                        <span class="fs-20 lh-24 fw-700 text-default">Customize Your Login Page</span>
                    </div>
                    <form method="POST" action="#">
                        <div class="d-flex flex-column gap-base">
							<?php

							$login_page_data = get_option( 'revo_shine_login_page', [
								'design'       => 'classic',
								'text_heading' => '#1 Shop App for your Woocommerce Store',
								'text'         => 'You can change this text at any time in WP-ADMIN',
								'bg_color'     => '#118eea',
								'text_color'   => '#FFFFFF',
								'btn_color'    => '#940000',
								'bg_image'     => REVO_SHINE_ASSET_URL . 'images/bg_login.png'
							] );

							revo_shine_get_admin_template_part( 'components/select', [
								'id'      => 'design',
								'label'   => 'Select Login Page Design',
								'name'    => 'design',
								'value'   => $login_page_data['design'] ?? 'classic',
								'toggle'  => 'form-login-component',
								'trigger' => 'modern',
								'options' => [
									'classic' => 'Classic',
									'modern'  => 'Modern'
								]
							] );

							revo_shine_get_admin_template_part( 'components/textarea', [
								'id'          => 'text_heading',
								'label'       => 'Text Heading',
								'name'        => 'text_heading',
								'value'       => $login_page_data['text_heading'] ?? '',
								'placeholder' => 'Ex: #1 Shop App for your Woocommerce Store',
								'class'       => 'form-login-component' . ( $login_page_data['design'] === 'modern' ? ' d-block' : ' d-none' )
							] );

							revo_shine_get_admin_template_part( 'components/textarea', [
								'id'          => 'text',
								'label'       => 'Text Content',
								'name'        => 'text',
								'value'       => $login_page_data['text'] ?? '',
								'placeholder' => 'Ex: You can change this text at any time in WP-ADMIN',
								'class'       => 'form-login-component' . ( $login_page_data['design'] === 'modern' ? ' d-block' : ' d-none' )
							] );

							revo_shine_get_admin_template_part( 'components/input', [
								'id'    => 'text_color',
								'label' => 'Text Color',
								'name'  => 'text_color',
								'type'  => 'color',
								'value' => $login_page_data['text_color'] ?? '#000000',
								'class' => 'form-login-component' . ( $login_page_data['design'] === 'modern' ? ' d-block' : ' d-none' )
							] );

							revo_shine_get_admin_template_part( 'components/input', [
								'id'    => 'btn_color',
								'label' => 'Button Color',
								'name'  => 'btn_color',
								'type'  => 'color',
								'value' => $login_page_data['btn_color'] ?? '#000000',
								'class' => 'form-login-component' . ( $login_page_data['design'] === 'modern' ? ' d-block' : ' d-none' )
							] );

							revo_shine_get_admin_template_part( 'components/input', [
								'id'    => 'bg_color',
								'label' => 'Background Color',
								'name'  => 'bg_color',
								'type'  => 'color',
								'value' => $login_page_data['bg_color'] ?? '#000000',
								'class' => 'form-login-component' . ( $login_page_data['design'] === 'modern' ? ' d-block' : ' d-none' )
							] );

							revo_shine_get_admin_template_part( 'components/upload', [
								'id'          => 'bg_image',
								'label'       => 'Image',
								'name'        => 'bg_image',
								'value'       => $login_page_data['bg_image'],
								'accent_text' => 'Upload Photo Here',
								'helper_text' => 'Best Size : 450 x 450 px, Max File Size: 2MB',
								'class'       => 'form-login-component' . ( $login_page_data['design'] === 'modern' ? ' d-block' : ' d-none' )
							] );

							?>

                            <div class="d-flex justify-content-end">
                                <input type="hidden" name="action" value="login_page">
                                <button class="btn btn-primary btn-save-changes" type="submit">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>

				<?php

				do_action( 'revo_shine_tab_content_app_settings' );

				if ( is_plugin_active( 'woocommerce-memberships/class-wc-memberships.php' ) ) {
					?>
                    <div class="tab-pane fade py-4" id="tab-other" role="tabpanel">
                        <div class="col-12">
                            <form method="POST">
                                <div class="form-group">
                                    <div class="row justify-content-center">
                                        <div class="col-md-3">
                                            <label for="membership_category">Membership Plan Category</label>
                                        </div>

                                        <div class="col-md-9">
                                            <select class="form-control" name="membership_category"
                                                    id="membership_category">
                                                <option disabled selected>Choose a category</option>
												<?php foreach ( $categories as $cat ) : ?>
                                                    <option value="<?php echo $cat->id ?>" <?php echo $selected_category == $cat->id ? 'selected' : '' ?>><?php echo $cat->text ?></option>
												<?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group" style="margin-top: 35px;">
                                    <input type="hidden" name="other_action" value="other_action">
                                    <button class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
					<?php
				}

				?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalGuide" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Setting Guide Image</div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="#">
                <div class="modal-body">
					<?php

					revo_shine_get_admin_template_part( 'components/upload', [
						'id'          => 'guide_feature_image',
						'label'       => 'Select Image',
						'name'        => 'guide_feature_image',
						'value'       => $service_status['guide_feature_image'] ?? '',
						'class'       => 'd-flex align-items-start gap-base',
						'accent_text' => 'Upload image here',
						'helper_text' => 'Best Size : 100 X 100px'
					] );

					?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL PROMO LABEL -->
<div class="modal fade" id="modalAction" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Add Promo Label on Product Card</div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="#">
                <div class="modal-body">
					<?php
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

						revo_shine_get_admin_template_part( 'components/upload', [
							'id'          => 'image',
							'label'       => 'Image',
							'name'        => 'image',
							'value'       => '',
							'class'       => 'd-flex align-items-start gap-base',
							'accent_text' => 'Upload Photo Here',
							'helper_text' => 'Best Size : 100 x 25 px, Max File Size : 2MB',
							'is_required' => true
						] );

					?>
                </div>
                <div class="modal-footer">
					<input type="hidden" name="id">
					<input type="hidden" name="action" value="promo_label">
                    <button type="button" class="btn btn-secondary close" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include REVO_SHINE_TEMPLATE_PATH . 'admin/parts/modal_example.php';
?>

<script>
    let switchTarget = null;
    let templateText = <?php echo json_encode( revo_shine_alert_app_settings() ) ?>;

    jQuery(function () {
        const params = new Proxy(new URLSearchParams(window.location.search), {
            get: (searchParams, prop) => searchParams.get(prop),
        });

        let tabActive = params.tab_active;

        if (tabActive === null || tabActive === '') {
            tabActive = 'tab-general';
        }

        jQuery(`div#tabContent div[role="tabpanel"]`).addClass('d-none')
        jQuery(`div#tabContent #${tabActive}`).addClass('show active').removeClass('d-none');
        jQuery(`ul#tabNavigation li[data-target="#${tabActive}"] .nav-link`).addClass('active');
    });

    jQuery('#tabNavigation li').on('click', function (event) {
        event.preventDefault();

        // tab button
        jQuery('.nav .nav-item .nav-link').removeClass('active');
        jQuery(this).find('.nav-link').addClass('active');

        // tab content
        const target = jQuery(this).data('target').replace('#', '');
        const tabContent = document.querySelectorAll('div#tabContent div[role="tabpanel"]');

        tabContent.forEach((el) => {
            if (jQuery(el).hasClass('show active')) {
                jQuery(el).removeClass('show active');
                jQuery(el).addClass('d-none');
            }

            if (jQuery(el).attr('id') === target) {
                jQuery(el).addClass('show active');
                jQuery(el).removeClass('d-none');
            }
        });

        // history url
        const url = new URL(window.location);
        url.searchParams.set('tab_active', target);
        window.history.pushState(null, '', url.toString());
    });

    jQuery('#tab-general .form-check-input').on('change', function (e) {
        e.preventDefault();

        const status = jQuery(this).prop('checked') ? 'show' : 'hide';
        const typeQuery = jQuery(this).data('inputname');
        const swaltitle = status === 'show' ? templateText[typeQuery].on.title : templateText[typeQuery].off.title;
        const swaltext = status === 'show' ? templateText[typeQuery].on.text : templateText[typeQuery].off.text;

        switchTarget = jQuery(this);

        if (['show_sold_item_data', 'show_average_rating_data', 'show_rating_section', 'show_variation_with_image', 'show_out_of_stock_product'].includes(typeQuery)) {
            confirmSwalAlert(swaltitle, swaltext, status, 'product_setting', {
                status,
                typeQuery: 'product_setting',
                action: typeQuery
            });
            return;
        }

        confirmSwalAlert(swaltitle, swaltext, status, typeQuery);
    });

    const confirmSwalAlert = (title = '', text = '', status, typeQuery, data = null) => {
        Swal.fire({
            icon: 'warning',
            title,
            text,
            showDenyButton: true,
            showCancelButton: false,
            allowOutsideClick: false,
            confirmButtonText: `YES`,
            confirmButtonColor: '#3085d6',
            denyButtonText: `NO`,
        }).then((result) => {
            if (result.isConfirmed) {
                if (data === null) {
                    data = {
                        status,
                        typeQuery
                    }
                }

                jQuery.ajax({
                    url: "#",
                    method: "POST",
                    data,
                    datatype: "json",
                    async: true,
                    beforeSend: () => {
                        Swal.fire({
                            title: 'Please wait...',
                            text: 'we are saving your changes',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading()
                            },
                        })
                    },
                    success: (data) => {
                        location.reload();
                    },
                    error: (data) => {
                        location.reload();
                    }
                });
            } else if (result.isDenied) {

                if (switchTarget !== null) {
                    switchTarget.prop('checked', !switchTarget.prop('checked'));
                }
            }
        });
    }

    const onHandleButton = (action) => {
        const value = jQuery(`#${action}`).val();
        confirmSwalAlert('', `Are you sure to change the product detail design ?`, value, action);
    }

	// Promo Label on Product Card
	jQuery(document).ready(function ($) {
        window.onload = function () {
            window.select2Builder('product', 'select[name="products[]"]');
        }

		const bodyEL = jQuery('body');

		bodyEL.on("click", ".btn-update", function () {
            const data = JSON.parse(atob(jQuery(this).data("data")));
            const products = JSON.parse(atob(jQuery(this).data("products")));
            let newOption = '';

            products.map(elo => {
                newOption += `<option value='${elo.id}' selected>${elo.text}</option>`
            });

            jQuery("#modalAction .modal-header .modal-title").html("Update Promo Label on Product Card");
            jQuery('#modalAction input[name="image"]').val(data.image);
            jQuery('#modalAction select[name="products[]"]').append(newOption).trigger('change');

            jQuery('#modalAction .form-field-upload-container').addClass('file-attached');
            jQuery('#modalAction .form-field-upload-container .form-field-file-preview img').attr('src', data.image);

            jQuery('#modalAction .modal-footer input[name="id"]').val(jQuery(this).data("id"));
            jQuery('#modalAction .modal-footer input[name="action"]').val("promo_label");
            jQuery("#modalAction").modal("show");
        });

		bodyEL.on("hidden.bs.modal", "#modalAction", function () {
            jQuery("#modalAction .modal-header .modal-title").html("Add Promo Label on Product Card");
            jQuery('#modalAction .form-field-upload-container').removeClass('file-attached');

            jQuery('select[name="products[]"]').html('');
            jQuery('#modalAction .modal-footer input[name="id"]').val('');
            jQuery('#modalAction .modal-footer input[name="action"]').val("promo_label");
        });
    });
</script>
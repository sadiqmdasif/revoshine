<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Revo_Shine_Checkout_Controller' ) ) {
	class Revo_Shine_Checkout_Controller {
		private array $vendor;

		private string $namespace;

		public function __construct() {
			$this->namespace = REVO_SHINE_NAMESPACE_API;

			if ( strpos( $_SERVER['REDIRECT_URL'] ?? '', 'revo-admin' ) !== false ) {
				add_action( 'woocommerce_add_order_item_meta', array( $this, 'product_custom_meta' ), 10, 3 );
				add_action( 'revo_shine_add_order_meta', array( $this, 'revo_shine_add_order_custom_meta' ), 11, 2 );

				add_filter( 'woocommerce_get_shop_coupon_data', array( $this, 'get_discount_data' ), 10, 2 );
				// add_action('woocommerce_after_cart_item_quantity_update', array($this, 'check_update_cart_quantity'), 12, 4);
			}
		}

		public function register_routes_api() {
			register_rest_route( $this->namespace, '/get-cart', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_get_cart' ),
				'permission_callback' => '__return_true',
			) );

			register_rest_route( $this->namespace, '/cart', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_cart' ),
				'permission_callback' => '__return_true',
			) );

			register_rest_route( $this->namespace, '/checkout-datas', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_checkout_datas' ),
				'permission_callback' => '__return_true',
			) );

			register_rest_route( $this->namespace, '/place-order', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_place_order' ),
				'permission_callback' => '__return_true',
			) );
		}

		/**
		 * get product data for checkout native
		 *
		 * cookie: required
		 */
		public function rest_get_cart( $request ) {
			$cookie = $request['cookie'];

			if ( empty( $cookie ) ) {
				return new WP_REST_Response( [ 'status' => 'error', 'message' => 'you must include cookie !' ], 400 );
			}

			$user_id = wp_validate_auth_cookie( $cookie, 'logged_in' );
			$user    = get_userdata( $user_id );

			if ( ! $user_id || ! $user ) {
				return new WP_REST_Response( [
					'status'  => 'error',
					'message' => 'Invalid authentication cookie. Please log out and try to login again !'
				], 400 );
			}

			wp_set_current_user( $user_id, $user->user_login );
			wp_set_auth_cookie( $user_id );

			$cart_items = revo_shine_includes_frontend( function () {
				$cart_items = [];

				if ( null === WC()->cart ) {
					WC()->cart = new WC_Cart();

					$cart_items = WC()->cart->get_cart();
				}

				return $cart_items;
			} );

			$result = [];
			if ( ! empty( $cart_items ) ) {
				foreach ( $cart_items as $cart ) {
					$product_id = $cart['variation_id'] == 0 ? $cart['product_id'] : $cart['variation_id'];
					$product    = wc_get_product( $product_id );

					if ( ! $product ) {
						continue;
					}

					$image = wp_get_attachment_url( $product->get_image_id(), 'full' );

					$data = [
						'product_id'        => $cart['product_id'],
						'name'              => $product->get_name(),
						'sku'               => $product->get_sku(),
						'price'             => $cart['line_subtotal'] / $cart['quantity'],
						'quantity'          => (int) ( $cart['quantity'] ),
						'variation_id'      => $cart['variation_id'],
						'variation'         => $cart['variation'],
						'subtotal_order'    => number_format( ( $cart['line_subtotal'] + $cart['line_subtotal_tax'] ), '2', '.', '' ),
						'line_subtotal'     => $cart['line_subtotal'],
						'line_subtotal_tax' => $cart['line_subtotal_tax'],
						'line_total'        => $cart['line_total'],
						'line_tax'          => $cart['line_tax'],
						'image'             => $image ? $image : '',
						'addons'            => []
					];

					if ( isset( $cart['addons'] ) ) {
						$data['addons'] = $cart['addons'];
					}

					array_push( $result, $data );
				}
			}

			return new WP_REST_Response( $result, 200 );
		}

		/**
		 * cart native action
		 *
		 * action: create, update, delete
		 */
		public function rest_cart( $request ) {
			$cookie     = $request['cookie'];
			$action     = $request['action'];
			$line_items = $request['line_items'];

			if ( empty( $cookie ) ) {
				return new WP_REST_Response( [ 'status' => 'error', 'message' => 'you must include cookie !' ], 400 );
			}

			$user_id = wp_validate_auth_cookie( $cookie, 'logged_in' );
			$user    = get_userdata( $user_id );

			if ( ! $user_id || ! $user ) {
				return new WP_REST_Response( [
					'status'  => 'error',
					'message' => 'Invalid authentication cookie. Please log out and try to login again !'
				], 400 );
			}

			revo_shine_includes_frontend( null, true );

			$cart_handler    = new WC_Cart();
			$session_handler = new WC_Session_Handler();

			WC()->session  = $session_handler;
			WC()->cart     = $cart_handler;
			WC()->customer = new WC_Customer( $user_id, true );

			$wc_session_data = $session_handler->get_session( $user_id );

			if ( ! $wc_session_data ) {
				$this->set_user_session( $user_id );
				$wc_session_data = $session_handler->get_session( $user_id );
			}

			$cart_usermeta = get_user_meta( $user_id, '_woocommerce_persistent_cart_1', true );
			$cart_exist    = empty( $cart_usermeta ) || is_null( $cart_usermeta ) ? [] : ( ! $wc_session_data ? [] : array_values( maybe_unserialize( $wc_session_data['cart'] ) ) );

			$result = [ 'status' => 'error', 'message' => 'you must include line_items !' ];

			if ( ! empty( $line_items ) || $action === 'sync' ) {
				if ( $action === 'create' && ! empty( $cart_exist ) ) {
					foreach ( $cart_exist as $cart_val ) {
						$line_items[] = [
							'product_id'   => $cart_val['product_id'],
							'quantity'     => $cart_val['quantity'],
							'variation_id' => $cart_val['variation_id'] != 0 ? $cart_val['variation_id'] : null,
							'variation'    => $cart_val['variation'],
						];
					}
				} elseif ( $action === 'sync' ) {
					$web_cart  = [];
					$sync_cart = [];

					if ( empty( $line_items ) ) {
						$line_items = [];
					}

					if ( ! empty( $cart_usermeta ) ) {
						foreach ( $cart_exist as $cart ) {
							$temp_web_cart = [
								'product_id'   => $cart['product_id'],
								'quantity'     => $cart['quantity'],
								'variation_id' => $cart['variation_id'] != 0 ? $cart['variation_id'] : null,
								'variation'    => $cart['variation'],
								'sync_cart'    => true
							];

							if ( isset( $cart['addons'] ) ) {
								$temp_web_cart['addons'] = $cart['addons'];
							}

							array_push( $web_cart, $temp_web_cart );
						}
					}

					$before_sync_cart = array_merge( $web_cart, $line_items );

					if ( empty( $before_sync_cart ) ) {
						return [];
					}

					foreach ( $before_sync_cart as $cart ) {
						$cart = (object) $cart;

						if ( ! is_null( $cart->variation_id ) && ! empty( $cart->variation_id ) ) {
							$key_search = $cart->variation_id;
							$col_search = 'variation_id';
						} else {
							$key_search = $cart->product_id;
							$col_search = 'product_id';
						}

						$key_sync_cart = array_search( $key_search, array_column( $sync_cart, $col_search ) );

						if ( $key_sync_cart !== false ) {
							if ( $sync_cart[ $key_sync_cart ]->quantity <= $cart->quantity ) {
								$sync_cart[ $key_sync_cart ]->quantity = $cart->quantity;
							}
						} else {
							array_push( $sync_cart, $cart );
						}
					}

					$line_items = $sync_cart;
					$action     = 'create';

					$cart_handler->empty_cart( true );
				}

				foreach ( $line_items as $line_item ) {
					$line_item    = (object) $line_item;
					$quantity     = $line_item->quantity;
					$product_id   = absint( $line_item->product_id );
					$variation_id = $line_item->variation_id ?? null;
					$cart_data    = $line_item->cart_data ?? [];

					if ( empty( $product_id ) || empty( $quantity ) ) {
						return [
							'status'  => 'error',
							'message' => 'product_id or quantity cannot be empty !'
						];
					}

					if ( isset( $line_item->addons ) && ! empty( $line_item->addons ) ) {
						$cart_data['addons'] = $line_item->addons;
					}

					if ( $action === 'create' ) {
						if ( ! is_null( $variation_id ) && $variation_id != 0 ) {
							$product_variable = new WC_Product_Variable( $product_id );
							$list_variations  = $product_variable->get_available_variations();
							$variable_key     = array_search( $variation_id, array_column( $list_variations, 'variation_id' ) );

							if ( $variable_key === false ) {
								return [
									'status'  => 'error',
									'message' => 'product variation not found !'
								];
							}

							if ( isset( $line_item->sync_cart ) && $line_item->sync_cart ) {
								$attribute = $line_item->variation;
							} elseif ( isset( $line_item->variation ) && ! empty( $line_item->variation ) ) {
								$attribute = [];

								foreach ( $line_item->variation as $variation ) {
									$attribute[ 'attribute_' . $variation->column_name ] = $variation->value;
								}
							} else {
								$attributes = $list_variations[ $variable_key ]['attributes'];
								$attribute  = new stdClass;

								foreach ( $attributes as $att_key => $att ) {
									if ( empty( $att ) ) {
										$check_att_key = explode( 'attribute_', $att_key )[1];

										$default_att = $product_variable->get_variation_attributes()[ $check_att_key ][0];
									}

									$attribute->$att_key = ! empty( $att ) ? $att : $default_att;
								}
							}

							$cart_handler->add_to_cart( $product_id, $quantity, $variation_id, (array) $attribute, (array) $cart_data );
						} else {
							$cart_handler->add_to_cart( $product_id, $quantity, 0, [], (array) $cart_data );
						}
					} elseif ( $action === 'update' ) {
						if ( $variation_id !== 0 && ! is_null( $variation_id ) ) {
							$cart_key = array_search( $variation_id, array_column( $cart_exist, 'variation_id' ) );
						} else {
							$cart_key = array_search( $product_id, array_column( $cart_exist, 'product_id' ) );
						}

						if ( $cart_key !== false ) {
							$cart_exist[ $cart_key ]['quantity'] = $quantity;
						}
					} elseif ( $action === 'delete' ) {
						if ( $variation_id !== 0 && ! is_null( $variation_id ) ) {
							$cart_key = array_search( $variation_id, array_column( $cart_exist, 'variation_id' ) );
						} else {
							$cart_key = array_search( $product_id, array_column( $cart_exist, 'product_id' ) );
						}

						if ( $cart_key !== false ) {
							unset( $cart_exist[ $cart_key ] );
							$cart_exist = array_values( $cart_exist );
						}
					}
				}

				$new_data = $this->cart_items( $wc_session_data, $user_id, ( $action === 'create' ? $cart_handler->cart_contents : $cart_exist ) );

				if ( isset( $sync_cart ) ) {
					$cart_items = array_values( $new_data );

					if ( ! empty( $cart_items ) ) {
						foreach ( $cart_items as $cart ) {
							if ( ! is_null( $cart['variation_id'] ) && ! empty( $cart['variation_id'] ) ) {
								$product_type = 'variation';
								$product_id   = $cart['variation_id'];
							} else {
								$product_type = 'simple';
								$product_id   = $cart['product_id'];
							}

							$attribute_value    = "";
							$raw_attributes     = $cart['variation'];
							$variation_selected = [];

							foreach ( $raw_attributes as $raw_key => $raw ) {
								$attribute_value .= $raw;
								$attribute_value .= array_key_last( $raw_attributes ) != $raw_key ? ' - ' : '';

								$variation_selected[] = [
									'id'          => $cart['variation_id'],
									'column_name' => explode( 'attribute_', $raw_key )[1],
									'value'       => $raw,
								];
							}

							$addon_data[ $product_type ][ $product_id ] = [
								'product_id'         => $cart['product_id'],
								'quantity'           => $cart['quantity'],
								'variation_id'       => $cart['variation_id'] == 0 ? null : $cart['variation_id'],
								'variation_selected' => $variation_selected,
								'variation_value'    => $attribute_value,
								'addons_selected'    => isset( $cart['addons'] ) ? $cart['addons'] : [],
								'subtotal_price'     => $cart['line_subtotal'] + $cart['line_subtotal_tax'],
							];
						}

						$revo_loader = load_revo_flutter_mobile_app();

						$result = $revo_loader->get_products_cart( [
							'addon_data' => $addon_data
						] );
					}

					return $result;
				}

				$result = [
					'status'  => 'success',
					'action'  => $action,
					'message' => 'cart items ' . $action . ' successfully',
				];
			}

			return new WP_REST_Response( $result, 200 );
		}

		/**
		 * get data for place order
		 */
		public function rest_checkout_datas( $request ) {
			$cookie              = $request['cookie'];
			$line_items          = $request['line_items'];
			$coupon_code         = $request['coupon_code'];
			$subdistrict         = $request['subdistrict'];
			$city                = $request['city'];
			$state_id            = $request['state_id'];
			$country_id          = $request['country_id'];
			$postcode            = $request['postcode'];
			$location_coordinate = $request['location_coordinate'];

			if ( ! empty( $cookie ) ) {
				$user_id = wp_validate_auth_cookie( $cookie, 'logged_in' );
				$user    = get_userdata( $user_id );

				if ( ! $user_id || ! $user ) {
					return [
						'status'  => 'error',
						'message' => 'Invalid authentication cookie. Please log out and try to login again!'
					];
				}

				wp_set_current_user( $user_id, $user->user_login );
				wp_set_auth_cookie( $user_id );
			} else {
				$user_id = 0;
			}

			try {
                $formatted_line_items = $this->format_line_items( $line_items, $coupon_code, isset( $user ) ? $user : null, $user_id );
				$shipping_methods     = $this->get_shipping_method( [
					'subdistrict'         => $subdistrict,
					'city'                => $city,
					'state_id'            => $state_id,
					'country_id'          => $country_id,
					'postcode'            => $postcode,
					'location_coordinate' => $location_coordinate
				], $user_id, $formatted_line_items['cart_items'], $formatted_line_items['subtotal'], $coupon_code, $formatted_line_items['coupon_free_shipping'] );

				// user data
				if ( empty( $cookie ) ) {
					$user_meta  = [];
					$state_name = "";
				} else {
					$user_meta  = get_user_meta( get_current_user_id() );
					$state_name = WC()->countries->get_states( $user_meta['billing_country'][0] )[ $user_meta['billing_state'][0] ];
				}

				$coordinate = $user_meta['billing_biteship_location_coordinate'][0] ?? ',';
				$coordinate = explode( ',', $coordinate );

				$user_data = [
					'billing_first_name'   => $user_meta['billing_first_name'][0] ?? '',
					'billing_last_name'    => $user_meta['billing_last_name'][0] ?? '',
					'billing_company'      => $user_meta['billing_company'][0] ?? '',
					'billing_country'      => $user_meta['billing_country'][0] ?? '',
					'billing_address_1'    => $user_meta['billing_address_1'][0] ?? '',
					'billing_address_2'    => $user_meta['billing_address_2'][0] ?? '',
					'billing_city'         => $user_meta['billing_city'][0] ?? '',
					'billing_state'        => $user_meta['billing_state'][0] ?? '',
					'billing_postcode'     => $user_meta['billing_postcode'][0] ?? '',
					'billing_phone'        => $user_meta['billing_phone'][0] ?? '',
					'billing_email'        => $user_meta['billing_email'][0] ?? '',
					'billing_country_name' => WC()->countries->countries[ $user_meta['billing_country'][0] ] ? WC()->countries->countries[ $user_meta['billing_country'][0] ] : "",
					'billing_state_name'   => ! empty( $state_name ) ? $state_name : ( $user_meta['billing_state'][0] ? $user_meta['billing_state'][0] : "" ),
					"location_coordinate"  => [
						"latitude"  => $coordinate[0],
						"longitude" => $coordinate[1],
						"address"   => $user_meta['billing_biteship_address'][0] ?? ''
					]
				];

				// payment methods
				$res_payment_gateways = [];
				$payment_gateways     = WC()->payment_gateways->payment_gateways();
				// $payment_gateways       = WC()->payment_gateways->get_available_payment_gateways();
				$payment_method_allowed = [
					'bacs',
					'cheque',
					'cod',
					'midtrans',
					'midtrans_sub_gopay',
					'xendit_ovo',
					'razorpay'
				];

				// terawallet
				if ( is_plugin_active( 'woo-wallet/woo-wallet.php' ) && ! empty( $cookie ) ) {
					array_push( $payment_method_allowed, 'wallet' );
				}

				foreach ( $payment_gateways as $gateway ) {
					if ( $gateway->enabled === 'yes' && in_array( $gateway->id, $payment_method_allowed ) ) {
						if ( $gateway->id === 'wallet' ) {
							$user_balance         = woo_wallet()->wallet->get_wallet_balance( get_current_user_id(), '' );
							$gateway->description = 'Balance ' . $user_balance;
						}

						array_push( $res_payment_gateways, [
							'id'          => $gateway->id,
							'title'       => $gateway->title,
							'description' => $gateway->description ?? "",
						] );
					}
				}

				// points redemption plugin
				if ( $this->vendor['point_rewards'] && $user_id > 0 ) {

					if ( ! empty( $formatted_line_items['line_items'] ) ) {

						list( $point_ratio, $monetary_value ) = explode( ':', get_option( 'wc_points_rewards_redeem_points_ratio', '' ) );

						$user_points    = WC_Points_Rewards_Manager::get_users_points( $user->ID );
						$subtotal_order = $formatted_line_items['subtotal'];

						if ( $user_points > 0 ) {
							$count_ratio = ( ( $user_points / $point_ratio ) * $monetary_value );

							if ( $count_ratio < $subtotal_order ) {
								$subtotal_order = $count_ratio;
							}
						}
					} else {
						$subtotal_order = WC_Points_Rewards_Cart_Checkout::get_discount_for_redeeming_points( false, null, true );
					}

					if ( isset( $user_points ) && $user_points > 0 ) {
						$points = WC_Points_Rewards_Manager::calculate_points_for_discount( $subtotal_order );

						$point_redemption = [
							'point_redemption' => $points,
							'total_discount'   => (int) $subtotal_order,
							'discount_coupon'  => $points != 0 ? 'wc_points_redemption_' . ( get_current_user_id() ?? random_int( 1000, 9999 ) ) . '_' . wp_date( 'Y_m_d_h_i' ) . "_{$points}_{$subtotal_order}" : "",
						];
					}
				}

				return new WP_REST_Response( [
					'user_data'         => $user_data,
					'line_items'        => $formatted_line_items['line_items'],
					'shipping_lines'    => $shipping_methods,
					'payment_methods'   => $res_payment_gateways,
					'points_redemption' => $point_redemption ?? [
							'point_redemption' => 0,
							'total_discount'   => 0,
							'discount_coupon'  => "",
						],

				], 200 );
			} catch ( \Throwable $th ) {
				return new WP_REST_Response( [
					'status'  => 'error',
					'message' => $th->getMessage()
				], 500 );
			}
		}

		/**
		 * Native Place Order
		 */
		public function rest_place_order( $request ) {
			global $wpdb;

			$cookie          = $request['cookie'];
			$billing_address = $request['billing_address'];
			$products        = $request['line_items'];
			$shipping_lines  = $request['shipping_lines'];
			$payment_method  = $request['payment_method'];
			$coupons         = $request['coupon_lines'];
			$order_notes     = $request['order_notes'];
			$partial_payment = $request['wallet_partial_payment'];
			$referral        = $request['referral'];

			if ( empty( $billing_address ) || empty( $shipping_lines ) || empty( $payment_method ) ) {
				return [ 'status' => 'error', 'message' => 'billing_address, shipping_lines, and payment required !' ];
			}

			if ( ! empty( $cookie ) ) {
				$user_id = wp_validate_auth_cookie( $cookie, 'logged_in' );
				$user    = get_userdata( $user_id );

				if ( ! $user_id || ! $user ) {
					return [
						'status'  => 'error',
						'message' => 'Invalid authentication cookie. Please log out and try to login again!'
					];
				}

				wp_set_current_user( $user_id, $user->user_login );
				wp_set_auth_cookie( $user_id );
			}

			// funtion from helper.php
			revo_shine_includes_frontend();

			// define cart contents
			$query_sync_cart = query_revo_mobile_variable( '"sync_cart"', 'sort' );
			$check_sync_cart = empty( $query_sync_cart ) ? false : ( $query_sync_cart[0]->description === 'hide' ? false : true );

			$cart_items = [];
			if ( ! empty( $products ) ) {
				WC()->cart->empty_cart( true );
                $information_array = [];

				foreach ( $products as $p ) {
					$variation   = [];
					$custom_data = [];

					if ( ! empty( $p['variation'] ) ) {
						foreach ( $p['variation'] as $value ) {
							$variation[ 'attribute_' . $value->column_name ] = $value->value;
						}
					}

					if ( isset( $p['addons'] ) && ! empty( $p['addons'] ) ) {
						$custom_data['addons'] = $p['addons'];
					}

					$cart_items[] = [
						'product_id'   => $p['product_id'],
						'quantity'     => $p['quantity'],
						'variation_id' => $p['variation_id'],
						'variation'    => $variation,
						'addons'       => $p['addons'] ?? [],
                        'view_id'      => $p['view_id'],
					];

					WC()->cart->add_to_cart( $p['product_id'], $p['quantity'], $p['variation_id'], $variation, $custom_data );
				}
			} elseif ( $check_sync_cart && $user_id != 0 ) {
				$session_handler = new WC_Session_Handler();
				$session         = $session_handler->get_session( $user_id );

				$cart_items = array_values( maybe_unserialize( $session['cart'] ) );
			}

			$result = [ 'status' => 'error', 'message' => 'you must include products !' ];

			if ( ! empty( $cart_items ) ) {
				// format & validation address
				if ( ! is_email( $billing_address['billing_email'] ) ) {
					return [ 'status' => 'error', 'message' => 'Invalid billing email address !' ];
				}

				$address = [
					'first_name' => $billing_address['billing_first_name'],
					'last_name'  => $billing_address['billing_last_name'],
					'company'    => $billing_address['billing_company'],
					'email'      => $billing_address['billing_email'],
					'phone'      => $billing_address['billing_phone'],
					'address_1'  => $billing_address['billing_address_1'],
					'address_2'  => $billing_address['billing_address_2'],
					'city'       => $billing_address['billing_city'],
					'state'      => $billing_address['billing_state'],
					'postcode'   => $billing_address['billing_postcode'],
					'country'    => $billing_address['billing_country']
				];

				// start create order
				$order = wc_create_order();
				$order->set_customer_id( $user_id ?? 0 );
				$order->set_created_via( 'rest-api' );

				// add products
				$temp_line_items_id = [];

				foreach ( $cart_items as $item ) {
					$item = (array) $item;

					$product_id = ( is_null( $item['variation_id'] ) || $item['variation_id'] === 0 ) ? $item['product_id'] : $item['variation_id'];
					$product    = wc_get_product( $product_id );

					if ( ! $product ) {
						continue;
					}

					// $price = $product->get_price();
					$price = wc_get_price_excluding_tax( $product );

					if ( array_key_exists( 'free_product', $item ) ) {
						$price = $item['line_subtotal'] != 0 ? $item['line_subtotal'] / $item['quantity'] : 0;
					}

					// define wholesale price
					if ( $this->vendor['wholesale'] && ! is_null( $user ) ) {
						$wholesale_price = get_post_meta( $product_id, 'wholesale_customer_wholesale_price', true );

						if ( ! empty( $wholesale_price ) && in_array( 'wholesale_customer', $user->roles ) ) {
							$price = $wholesale_price;
						}
					}

					$product_list[] = $product->get_name() . ' &times; ' . $item['quantity'];

					if ( ! empty( $item['addons'] ) ) {
						$price += array_sum( array_column( $item['addons'], 'price' ) );
					}

					$add_product_id = $order->add_product( $product, $item['quantity'], [
						'total'    => $price * $item['quantity'],
						'subtotal' => $price * $item['quantity'],
					] );

					if ( $add_product_id !== false && ! empty( $item['addons'] ) ) {
						$pao_ids_value = [];

						foreach ( $item['addons'] as $addon ) {
							$pao_ids_value[] = [
								'key'   => $addon['name'],
								'value' => $addon['value'],
								'id'    => $addon['id']
							];

							wc_add_order_item_meta( $add_product_id, $addon['name'], $addon['value'], true );
						}

						wc_add_order_item_meta( $add_product_id, '_pao_ids', $pao_ids_value, true );
					}

					$temp_line_items_id[] = $add_product_id;
				}

				// terawallet - partial payment (add fee_lines)
				if ( $partial_payment ) {
					$user_balance = apply_filters( 'woo_wallet_partial_payment_amount', woo_wallet()->wallet->get_wallet_balance( get_current_user_id(), '' ) );

					if ( $user_balance <= 0 ) {
						$order->delete( true );

						return [
							'status'  => 'error',
							'message' => 'Your wallet balance is low. Please add balance to proceed with this transaction - partial payment'
						];
					}

					$fee_data = [
						'id'        => '_via_wallet_partial_payment',
						'name'      => __( 'Via wallet', 'woo-wallet' ),
						'amount'    => (float) - 1 * $user_balance,
						'taxable'   => false,
						'tax_class' => 'non-taxable'
					];

					WC()->cart->fees_api()->add_fee( $fee_data );

					$fee = new WC_Order_Item_Fee();
					$fee->set_name( $fee_data['name'] );
					$fee->set_total_tax( 0 );
					$fee->set_taxes( [] );
					$fee->set_amount( $fee_data['amount'] );
					$fee->set_total( $fee_data['amount'] );
					$fee->save();

					$fee->add_meta_data( '_legacy_fee_key', '_via_wallet_partial_payment' );

					$order->add_item( $fee );
				}

				// add & update billing and shipping addresses
				$order->set_address( $address, 'billing' );
				$order->set_address( $address, 'shipping' );

				if ( $user_id !== 0 ) {
					foreach ( $billing_address as $billing_key => $billing_data ) {
						update_user_meta( $user_id, $billing_key, $billing_data );
					}
				}

				// add shipping methods
				$shipping = new WC_Order_Item_Shipping();

				if ( strpos( $shipping_lines['method_id'], 'local_pickup_plus' ) !== false ) {
					$method_id = $shipping_lines['method_id'];
					$method_id = explode( ':', $method_id ); // 0 = method_id, 1 = _pickup_location_name, 2 = _pickup_location_id

					$location_obj = new WC_Local_Pickup_Plus_Pickup_Location( $method_id[0] );

					$shipping->set_method_id( $method_id[0] );
					$shipping->add_meta_data( '_pickup_location_name', $method_id[1], true );
					$shipping->add_meta_data( '_pickup_location_id', $method_id[2], true );
					$shipping->add_meta_data( '_pickup_location_address', $location_obj->get_address(), true );
					$shipping->add_meta_data( '_pickup_location_phone', $location_obj->get_phone( false ), true );
					$shipping->add_meta_data( '_pickup_items', $temp_line_items_id, true );
				} elseif ( strpos( $shipping_lines['method_id'], 'biteship' ) !== false ) {
					$method_id = $shipping_lines['method_id'];
					$method_id = explode( ':', $method_id ); // 0 = method_id, 1 = courier, 2 = service

					$shipping->set_method_id( $method_id[0] );
					$shipping->add_meta_data( 'courier_code', $method_id[1], true );
					$shipping->add_meta_data( 'courier_service_code', $method_id[2], true );
				} else {
					$shipping->set_method_id( $shipping_lines['method_id'] );
				}

				$shipping->set_method_title( $shipping_lines['method_title'] );
				$shipping->set_total( $shipping_lines['cost'] );
				$shipping->add_meta_data( 'Items', implode( ', ', $product_list ), true );
				$order->add_item( $shipping );

				// add payment method
				$order->set_payment_method( $payment_method['id'] );
				$order->set_payment_method_title( $payment_method['title'] );

				// define wholesale metas
				if ( $this->vendor['wholesale'] && ! is_null( $user ) ) {
					if ( in_array( 'wholesale_customer', $user->roles ) ) {
						$order->add_meta_data( 'is_vat_exempt', 'no' );
						$order->add_meta_data( 'wwp_wholesale_role', 'wholesale_customer' );
						$order->add_meta_data( '_wwpp_order_type', 'wholesale' );
						$order->add_meta_data( '_wwpp_wholesale_order_type', 'wholesale_customer' );
					}
				}

				// apply coupons
				if ( ! empty( $coupons ) ) {
					foreach ( $coupons as $coupon ) {
						$order->apply_coupon( $coupon['code'] );
					}
				}

				// order notes
				if ( ! empty( $order_notes ) ) {
					$order->set_customer_note( $order_notes );
				}

				// calculate, save, and set status
				$order->calculate_totals();

				$order->save();
				$order->set_status( 'wc-on-hold' );

                foreach ( $cart_items as $item ) {
                    $get_data      = $wpdb->get_row( "SELECT * FROM revo_video_affiliate_views WHERE id = " . $item['view_id'] );
                    $get_user_data = $wpdb->get_row( "SELECT * FROM revo_video_affiliate WHERE id = $get_data->video_id" );

                    $information_array             = json_decode( $get_data->information, true );
                    $information_array['order_id'] = $order->get_id();

                    $updated_information = json_encode( $information_array );

                    $date_cart    = strtotime( $information_array['date_cart'] );
                    $two_days_ago = strtotime( '-2 days' );

                    if ( empty( $date_cart ) ) {
                        $wpdb->update( 'revo_video_affiliate_views', [ 'information' => $updated_information ], [ 'id' => $item['view_id'] ] );
                    } else {
                        $user_data  = get_userdata( $get_user_data->user_id );
                        $user_login = $user_data->data->user_login;

                        if ( $user_data->roles !== 'administrator' ) {
                            if ( is_plugin_active( 'indeed-affiliate-pro/indeed-affiliate-pro.php' ) ) {

                                add_filter( 'uap_set_affiliate_id_filter', function ( $affiliate_id ) use ( $wpdb, $user_login ) {
                                    if ( get_option( 'uap_default_ref_format' ) !== 'id' ) {
                                        $affiliate_user_data = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}users WHERE user_login = '{$user_login}'", OBJECT );
                                        $affiliate_data      = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}uap_affiliates WHERE uid = '$affiliate_user_data->ID'", OBJECT );
                                        $affiliate_id        = $affiliate_data->ID;
                                    } else {
                                        $affiliate_id = $user_login;
                                    }

                                    return $affiliate_id;
                                } );

                                $obj = new Uap_Woo();
                                $obj->create_referral( $order->get_id() );

                            } else {
                                $wpdb->update( 'revo_video_affiliate_views', [ 'information' => $updated_information ], [ 'id' => $item['view_id'] ] );
                            }

                            $wpdb->update( 'revo_video_affiliate_views', [ 'information' => $updated_information ], [ 'id' => $item['view_id'] ] );
                        }
                    }
                }

				// terawallet - full payment
				if ( $payment_method['id'] === 'wallet' && is_plugin_active( 'woo-wallet/woo-wallet.php' ) ) {
					$transaction_id = woo_wallet()->wallet->debit( $order->get_customer_id(), $order->get_total(), 'For order payment #' . $order->get_order_number() );

					if ( $transaction_id === false ) {
						$order->update_status( 'wc-pending' );

						return [
							'status'  => 'error',
							'message' => 'Your wallet balance is low. Please add balance to proceed with this transaction - Full Payment'
						];
					}

					$order->update_status( 'wc-processing' );
					$order->set_transaction_id( $transaction_id );
				}

				// payments gateway
				if ( in_array( $payment_method['id'], [
						'midtrans',
						'midtrans_sub_gopay'
					] ) && is_plugin_active( 'midtrans-woocommerce/midtrans-gateway.php' ) ) {
					$order->update_status( 'wc-pending' );

					if ( $payment_method['id'] === 'midtrans' ) {
						$midtrans_class = new WC_Gateway_Midtrans();
					} else {
						$midtrans_class = new WC_Gateway_Midtrans_Sub_Gopay();
					}

					$pg_response = $midtrans_class->process_payment( $order->get_id() );

					$payment_link = $pg_response['redirect'];
				} elseif ( $payment_method['id'] === 'xendit_ovo' && is_plugin_active( 'woo-xendit-virtual-accounts/woocommerce-xendit-pg.php' ) ) {
					$xendit_class = new WC_Xendit_OVO();
					$pg_response  = $xendit_class->process_payment( $order->get_id() );   // auto update status to pending

					$payment_link = $pg_response['redirect'];
				} elseif ( $payment_method['id'] === 'razorpay' && is_plugin_active( 'woo-razorpay/woo-razorpay.php' ) ) {
					$order->update_status( 'wc-pending' );

					$razor_class = new WC_Razorpay();
					$pg_response = $razor_class->process_payment( $order->get_id() );

					$payment_link = $pg_response['redirect'];
				}

				if ( $payment_method['id'] === 'cod' ) {
					$order->update_status( 'wc-processing' );
				}

				// terawallet add meta_data to order
				if ( $partial_payment ) {
					$order = $this->wallet_partial_payment( $order->get_id() );
				}

				// result
				$result                 = $order->get_data();
				$result['payment_link'] = isset( $payment_link ) ? $payment_link : "";

				WC()->cart->empty_cart( true );

				if ( is_plugin_active( 'indeed-affiliate-pro/indeed-affiliate-pro.php' ) && ! empty( $referral ) ) {
					add_filter( 'uap_set_affiliate_id_filter', function ( $affiliate_id ) use ( $wpdb, $referral ) {
						if ( get_option( 'uap_default_ref_format' ) !== 'id' ) {
							$affiliate_user_data = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}users WHERE user_login = '{$referral}'", OBJECT );
							$affiliate_data      = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}uap_affiliates WHERE uid = '$affiliate_user_data->ID'", OBJECT );
							$affiliate_id        = $affiliate_data->ID;
						} else {
							$affiliate_id = $referral;
						}

						return $affiliate_id;
					} );

					$obj = new Uap_Woo();
					$obj->create_referral( $order->get_id() );
				}

				do_action( 'revo_shine_add_order_meta', $cart_items, $order->get_id() );

				revo_shine_new_order( $order->get_id() );
			}

			return new WP_REST_Response( $result, 200 );
		}

		public function format_line_items( $line_items, $coupon_code, $user, $user_id ) {
            global $wpdb;

			$cart_items = [];

			if ( empty( $line_items ) ) {
				return [ 'status' => 'error', 'message' => 'you must include products !' ];
			}

			revo_shine_includes_frontend();
			foreach ( $line_items as $line_item ) {
				$variation = [];
                $view_id = null;

                if ( ! empty( $line_item['video_id'] ) && ! empty( $line_item['date_product_cart'] ) ) {
                    $check_data = $wpdb->get_row( "SELECT * FROM revo_video_affiliate WHERE post_id = " . (string) $line_item['video_id'] );

                    if ( $check_data ) {
                        $wpdb->insert( 'revo_video_affiliate_views', [
                            'video_id'    => $check_data->id,
                            'type'        => 'click',
                            'information' => json_encode( [
                                'user_id'    => $user_id,
                                'type'       => 'cart',
                                'date_cart'  => $line_item['date_product_cart'],
                                'product_id' => $line_item['product_id'],
                            ] ),
                        ] );

                        $view_id = $wpdb->insert_id;
                    }
                }

				foreach ( $line_item['variation'] as $value ) {
					$variation[ 'attribute_' . $value['column_name'] ] = $value['value'];
				}

				array_push( $cart_items, [
					'key'          => substr( str_replace( [
						'+',
						'/',
						'='
					], '', base64_encode( random_bytes( 32 ) ) ), 0, 32 ),
					'product_id'   => $line_item['product_id'],
					'quantity'     => $line_item['quantity'],
					'variation_id' => $line_item['variation_id'],
					'variation'    => $variation,
                    'view_id'     => $view_id,
				] );
			}

			// override line_items
			list( $line_items, $group_line_items ) = [ [], [] ];
			list( $subtotal_order, $subtotal_order_with_coupon ) = [ 0, 0 ];

			foreach ( $cart_items as $key => $item ) {
				$item = (array) $item;

				$product_id   = $item['product_id'];
				$variation_id = $item['variation_id'];

				$product = wc_get_product( ( is_null( $item['variation_id'] ) || $item['variation_id'] === 0 ) ? $product_id : $variation_id );

				if ( ! $product ) {
					continue;
				}

				$price = $product->get_price();
				$image = wp_get_attachment_url( $product->get_image_id(), 'full' );

				if ( array_key_exists( 'free_product', $item ) ) {
					$price = $item['line_subtotal'] + $item['line_subtotal_tax'];
				}

				// define wholesale price
				if ( $this->vendor['wholesale'] && ! is_null( $user ) ) {
					$wholesale_price = get_post_meta( $product_id, 'wholesale_customer_wholesale_price', true );

					if ( ! empty( $wholesale_price ) && in_array( 'wholesale_customer', $user->roles ) ) {
						$price = $wholesale_price;
					}
				}

				if ( ! is_null( $variation_id ) && $variation_id !== 0 ) {
					$attribute = "";

					$raw_attributes = $item['variation'];

					foreach ( $raw_attributes as $raw_key => $raw ) {
						$attribute .= ! empty( $raw ) ? $raw : '';
						$attribute .= array_key_last( $raw_attributes ) !== $raw_key ? ' - ' : '';
					}
				} else {
					$attribute = "";
				}

				// coupon code
				$coupon_price = 0;
				if ( ! empty( $coupon_code ) ) {
					$coupon_free_shipping = false;

					$coupon      = new WC_Coupon( $coupon_code );
					$coupon_data = $coupon->get_data();

					if ( $coupon_data['id'] != 0 ) {
						$coupon_amount = $coupon_data['amount'];
						$discount_type = $coupon_data['discount_type'];

						if ( $discount_type === 'percent' ) {
							$coupon_price = ( $item['quantity'] * $price ) - ( ( $price * $item['quantity'] ) * $coupon_amount / 100 );
						} else if ( $discount_type === 'fixed_product' ) {
							$coupon_price = ( $item['quantity'] * $price ) - $coupon_amount * $item['quantity'];
						}

						$subtotal_order_with_coupon += $coupon_price;

						if ( $coupon_data['free_shipping'] ) {
							$coupon_free_shipping = true;
						}
					}
				}

				// addons product
				$addons_price = 0;
				if ( isset( $item['addons'] ) && ! empty( $item['addons'] ) ) {
					$addons_price = array_sum( array_column( $item['addons'], 'price' ) );
				}

				$data = [
					'product_id'        => $product->get_type() === 'variation' ? $product->get_parent_id() : $product->get_id(),
					'name'              => $product->get_name(),
					'sku'               => $product->get_sku(),
					'price'             => (string) number_format( $price, '2', '.', '' ),
					'quantity'          => (int) $item['quantity'],
					'variation_id'      => $item['variation_id'],
					'variation'         => $attribute,
					'subtotal_order'    => (float) number_format( ( $item['quantity'] * $price ) + $addons_price, '2', '.', '' ),
					'image'             => $image ? $image : '',
					'weight'            => ( (int) $product->get_weight() * $item['quantity'] ),
					'shipping_class_id' => $product->get_shipping_class_id(),
					'subtotal_coupon'   => absint( $coupon_price ),
					'data'              => $product,
					'addons'            => isset( $item['addons'] ) ? $item['addons'] : [],
                    'view_id'           => $item['view_id'],
				];

				// extend cart items data
				$product_price_without_tax = wc_get_price_excluding_tax( $product, [
					'price' => $price,
					'qty'   => $item['quantity']
				] );
				$product_total_tax         = round( (int) $data['subtotal_order'] - $product_price_without_tax );

				$cart_items[ $key ]['line_subtotal']     = $product_price_without_tax;
				$cart_items[ $key ]['line_subtotal_tax'] = $product_total_tax < 0 ? 0 : $product_total_tax;
				$cart_items[ $key ]['line_total']        = $product_price_without_tax;
				$cart_items[ $key ]['line_tax']          = $product_total_tax < 0 ? 0 : $product_total_tax;
				$cart_items[ $key ]['data']              = $product;

				array_push( $line_items, $data );
				$group_line_items[ $product->get_shipping_class_id() ][] = $data;

				$subtotal_order += ( $item['quantity'] * $price );
			}

			if ( ! empty( $coupon_code ) && $discount_type === 'fixed_cart' ) {
				$subtotal_order_with_coupon = $subtotal_order - $coupon_amount;
			}

			return [
				'line_items'           => $line_items ?? [],
				'cart_items'           => $cart_items ?? [],
				'subtotal'             => $subtotal_order ?? [],
				'coupon_free_shipping' => $coupon_free_shipping ?? false
			];
		}

		public function get_shipping_method( $address, $user_id, $cart_items, $subtotal_order, $coupon_code, $coupon_free_shipping ) {
			// shipping zones
			$data_store    = WC_Data_Store::load( 'shipping-zone' );
			$raw_zones     = $data_store->get_zones();
			$shipping_zone = null;

			if ( ! empty( $raw_zones ) ) {
				foreach ( $raw_zones as $raw_zone ) {
					$zone      = new WC_Shipping_Zone( $raw_zone );
					$zone_data = $zone->get_data();

					$billing_country  = $user_id === 0 ? $address['country_id'] : get_user_meta( $user_id, 'billing_country' )[0];
					$billing_state    = $user_id === 0 ? $address['state_id'] : get_user_meta( $user_id, 'billing_state' )[0];
					$billing_postcode = $user_id === 0 ? $address['postcode'] : get_user_meta( $user_id, 'billing_postcode' )[0];

					if ( count( $zone_data['zone_locations'] ) >= 1 ) {
						foreach ( $zone_data['zone_locations'] as $location ) {
							if ( $location->code === $billing_country . ':' . $billing_state ) {
								$shipping_zone = $zone;
							} elseif ( $location->code === $billing_country ) {
								$shipping_zone = $zone;
							} elseif ( $location->type === 'postcode' && $location->code === $billing_postcode ) {
								$shipping_zone = $zone;
							}

							if ( ! is_null( $shipping_zone ) ) {
								break;
							}
						}
					} else {
						$shipping_zone = $zone;
					}

					if ( ! is_null( $shipping_zone ) ) {
						break;
					}
				}
			}

			if ( is_null( $shipping_zone ) ) {
				$shipping_zone = new WC_Shipping_Zone( 0 );
			}

			// shipping methods
			$result           = [];
			$shipping_methods = $shipping_zone->get_shipping_methods();

			$shipping_package = [
				'contents'        => ( function ( $cart_items ) {
					foreach ( $cart_items as $cart ) {
						$cart['data']           = wc_get_product( $cart['variation_id'] != null ? $cart['variation_id'] : $cart['product_id'] );
						$result[ $cart['key'] ] = $cart;
					}

					return $result;
				} )( $cart_items ),
				'applied_coupons' => ! empty( $coupon_code ) ? [ $coupon_code ] : [],
				'contents_cost'   => $subtotal_order,
				'user'            => [
					'ID' => $user_id
				],
				'destination'     => [
					'country'   => $user_id !== 0 ? get_user_meta( $user_id, 'billing_country' )[0] : $address['country_id'],
					'state'     => $user_id !== 0 ? get_user_meta( $user_id, 'billing_state' )[0] : $address['state_id'],
					'city'      => $user_id !== 0 ? get_user_meta( $user_id, 'billing_city' )[0] : $address['city'],
					'postcode'  => $user_id !== 0 ? get_user_meta( $user_id, 'billing_postcode' )[0] : $address['postcode'],
					'address'   => $user_id !== 0 ? get_user_meta( $user_id, 'billing_address_1' )[0] : "{$address['city']}, {$address['subdistrict']} {$address['postcode']}",
					'address_1' => $user_id !== 0 ? get_user_meta( $user_id, 'billing_address_1' )[0] : "{$address['city']}, {$address['subdistrict']} {$address['postcode']}",
					'address_2' => $user_id !== 0 ? get_user_meta( $user_id, 'billing_address_2' )[0] : $address['subdistrict'],
				],
				'cart_subtotal'   => $subtotal_order,
				'rates'           => []
			];

			foreach ( $shipping_methods as $shipping_method ) {
				if ( $shipping_method->enabled === 'no' ) {
					continue;
				}

				$rate_id     = $shipping_method->get_rate_id();
				$instance_id = end( explode( ':', $rate_id ) );

				$method_title = $shipping_method->get_title();
				$method_title = empty( $method_title ) ? $shipping_method->get_method_title() : $method_title;

				$data = $shipping_method->instance_settings;
				list( $cost, $total_cost ) = [ 0, 0 ];

				if ( ! in_array( explode( ':', $rate_id )[0], [
					'flat_rate',
					'local_pickup',
					'free_shipping',
					'woongkir'
				] ) ) {
					continue;
				} elseif ( $method_title === 'Woongkir' && is_plugin_active( 'woongkir/woongkir.php' ) ) {
					$woongkir_class = new Woongkir_Shipping_Method( $instance_id );
					$woongkir_class->calculate_shipping( $shipping_package );

					foreach ( $woongkir_class->rates as $value ) {
						$data = $value->meta_data['_woongkir_data'];

						$total_cost = (int) $data['cost'];

						if ( $value->get_shipping_tax() !== null ) {
							$total_cost += $value->get_shipping_tax();
						}

						$data['cost']         = (int) $data['cost']; // digunakan di place order
						$data['total_cost']   = (int) $total_cost; // ditampilkan
						$data['method_title'] = strtoupper( $data['courier'] . ' - ' . $data['service'] );

						$woongkir_services[] = $data;
					}

					array_push( $result, [
						'method_id'    => $rate_id,
						'method_title' => 'other_courier',
						'cost'         => 0,
						'total_cost'   => 0,
						'couriers'     => $woongkir_services ?? [],
					] );

					continue;
				} else {
					// free shipping
					if ( $shipping_method instanceof WC_Shipping_Free_Shipping ) {
						$requires = $data['requires'];

						if ( $data['ignore_discounts'] === 'no' && ! empty( $coupon_code ) ) {
							$subtotal_order = $subtotal_order_with_coupon;
						}

						if ( $requires === 'coupon' && ! $coupon_free_shipping ) {
							continue;
						} elseif ( $requires === 'min_amount' && $subtotal_order < $data['min_amount'] ) {
							continue;
						} elseif ( $requires === 'either' && ( $subtotal_order >= $data['min_amount'] == false ) && ! $coupon_free_shipping ) {
							continue;
						} elseif ( $requires === 'both' ) {
							if ( ( $subtotal_order < $data['min_amount'] && ! $coupon_free_shipping ) || ( $subtotal_order < $data['min_amount'] && $coupon_free_shipping ) || ( $subtotal_order >= $data['min_amount'] && ! $coupon_free_shipping ) ) {
								continue;
							}
						}
					} // flat rate
					elseif ( $shipping_method instanceof WC_Shipping_Flat_Rate ) {
						$shipping_handler = new WC_Shipping_Flat_Rate( $instance_id );
						$shipping_handler->calculate_shipping( $shipping_package );

						foreach ( $shipping_handler->rates as $rate ) {
							$tax = 0;

							if ( ! empty( $rate->taxes ) ) {
								$tax = array_sum( $rate->taxes );
							}

							$cost       = $rate->cost;
							$total_cost = $rate->cost + $tax;
						}
					} // local pickup
					elseif ( $shipping_method instanceof WC_Shipping_Local_Pickup ) {
						$shipping_handler = new WC_Shipping_Local_Pickup( $instance_id );
						$shipping_handler->calculate_shipping( $shipping_package );

						foreach ( $shipping_handler->rates as $rate ) {
							$tax = 0;

							if ( ! empty( $rate->taxes ) ) {
								$tax = array_sum( $rate->taxes );
							}

							$cost       = $rate->cost;
							$total_cost = $rate->cost + $tax;
						}
					}
				}

				array_push( $result, [
					'method_id'    => $rate_id,
					'method_title' => $method_title,
					'cost'         => (int) $cost,
					'total_cost'   => (int) $total_cost,
					'couriers'     => []
				] );
			}

			// biteship
			if ( $this->vendor['biteship'] ) {
				if ( $user_id !== 0 ) {

					$coordinate = get_user_meta( $user_id, 'billing_biteship_location_coordinate', true );
					if ( empty( $coordinate ) ) {
						$coordinate = ' , ';
					}

					$coordinate = explode( ',', $coordinate );
				}

				$shipping_package['destination']['address']   = $user_id !== 0 ? get_user_meta( $user_id, 'billing_biteship_address', true ) : $address['location_coordinate']['address'];
				$shipping_package['destination']['latitude']  = $user_id !== 0 ? $coordinate[0] : $address['location_coordinate']['latitude'];
				$shipping_package['destination']['longitude'] = $user_id !== 0 ? $coordinate[1] : $address['location_coordinate']['longitude'];

				$shipping_handler = new Biteship_Shipping_Method();
				$shipping_handler->calculate_shipping( $shipping_package );

				if ( $shipping_handler->enabled === 'yes' ) {
					$rate_id     = $shipping_handler->get_rate_id();
					$instance_id = end( explode( ':', $rate_id ) );

					$method_title = $shipping_handler->get_title();
					$method_title = empty( $method_title ) ? $shipping_handler->get_method_title() : $method_title;

					$data             = $shipping_handler->instance_settings;
					$total_cost       = 0;
					$biteship_courier = [];

					// get shipping rates
					foreach ( $shipping_handler->rates as $rate ) {
						$tax = 0;

						if ( ! empty( $rate->taxes ) ) {
							$tax = array_sum( $rate->taxes );
						}

						$meta       = $rate->get_meta_data();
						$cost       = $rate->cost;
						$total_cost = $rate->cost + $tax;

						$biteship_courier[] = [
							'service'         => $meta['courier_service_code'],
							'description'     => '',
							'cost'            => (int) $cost,
							'total_cost'      => (int) $total_cost,
							'currency'        => get_woocommerce_currency(),
							'etd'             => '',
							'note'            => '',
							'cost_conversion' => '',
							'courier'         => $meta['courier_code'],
							'method_title'    => $rate->label
						];
					}

					if ( ! empty( $biteship_courier ) ) {
						array_push( $result, [
							'method_id'    => $rate_id,
							'method_title' => 'Other Courier',
							'cost'         => 0,
							'total_cost'   => 0,
							'couriers'     => $biteship_courier
						] );
					}
				}
			}

			// local pickup plus
			if ( $this->vendor['local_pickup_plus'] ) {
				$shipping_handler = new WC_Shipping_Local_Pickup_Plus();
				$shipping_handler->calculate_shipping( $shipping_package );

				if ( $shipping_handler->enabled === 'yes' ) {
					$rate_id     = $shipping_handler->get_rate_id();
					$instance_id = end( explode( ':', $rate_id ) );

					$method_title = $shipping_handler->get_title();
					$method_title = empty( $method_title ) ? $shipping_handler->get_method_title() : $method_title;

					$data       = $shipping_handler->instance_settings;
					$total_cost = 0;

					$pickup_locations = get_posts( [
						'post_type'   => 'wc_pickup_location',
						'post_status' => 'publish',
					] );

					$locations = [];

					foreach ( $pickup_locations as $pickup_location ) {

						$location_obj = new WC_Local_Pickup_Plus_Pickup_Location( $pickup_location );

						$locations[] = [
							'service'         => (string) $location_obj->get_id(),
							'description'     => $location_obj->get_description(),
							'cost'            => $location_obj->get_price_adjustment()->get_amount(),
							'total_cost'      => $location_obj->get_price_adjustment()->get_amount(),
							'currency'        => get_woocommerce_currency(),
							'etd'             => '',
							'note'            => '',
							'cost_conversion' => '',
							'courier'         => $location_obj->get_name(),
							'method_title'    => $location_obj->get_name()
						];
					}

					array_push( $result, [
						'method_id'    => $rate_id,
						'method_title' => $method_title,
						'cost'         => (int) $total_cost,
						'total_cost'   => 0,
						'couriers'     => $locations
					] );
				}
			}

			return $result;
		}

		/**
		 * In experimental
		 */
		public function get_shipping_method_v2( $address, $user_id, $cart_items, $subtotal_order, $coupon_code, $coupon_free_shipping ) {
			$shipping_package = [
				[
					'user'            => [
						'ID' => $user_id
					],
					'destination'     => [
						'country'   => $user_id !== 0 ? get_user_meta( $user_id, 'billing_country' )[0] : $address['country_id'],
						'state'     => $user_id !== 0 ? get_user_meta( $user_id, 'billing_state' )[0] : $address['state_id'],
						'city'      => $user_id !== 0 ? get_user_meta( $user_id, 'billing_city' )[0] : $address['city'],
						'postcode'  => $user_id !== 0 ? get_user_meta( $user_id, 'billing_postcode' )[0] : $address['postcode'],
						'address'   => $user_id !== 0 ? get_user_meta( $user_id, 'billing_address_1' )[0] : "{$address['city']}, {$address['subdistrict']} {$address['postcode']}",
						'address_1' => $user_id !== 0 ? get_user_meta( $user_id, 'billing_address_1' )[0] : "{$address['city']}, {$address['subdistrict']} {$address['postcode']}",
						'address_2' => $user_id !== 0 ? get_user_meta( $user_id, 'billing_address_2' )[0] : $address['subdistrict'],
					],
					'contents_cost'   => 0,
					'cart_subtotal'   => $subtotal_order,
					'applied_coupons' => ! empty( $coupon_code ) ? [ $coupon_code ] : [],
					'rates'           => []
				]
			];

			foreach ( $cart_items as $item ) {
				$shipping_package[0]['contents'][ $item['key'] ] = $item;
				$shipping_package[0]['contents_cost']            += $item['line_total'];
			}

			$WC_Shipping = WC()->shipping();
			$WC_Shipping->calculate_shipping( $shipping_package );

			$packages = WC()->shipping()->get_packages();

			foreach ( $packages[0]['rates'] as $shipping_method ) {
				if ( ! in_array( $shipping_method->method_id, [
					'flat_rate',
					'local_pickup',
					'free_shipping',
					'woongkir',
					'local_pickup_plus'
				] ) ) {
					continue;
				}

				if ( $shipping_method->enabled === 'no' ) {
					continue;
				}

				$result[] = [
					'method_id'    => $shipping_method->id,
					'method_title' => str_replace( '&amp;', '&', $shipping_method->label ),
					'cost'         => (float) $shipping_method->cost,
					'total_cost'   => (float) $shipping_method->cost,
					'couriers'     => []
				];
			}

			return $result;
		}

		public function revo_shine_add_order_custom_meta( $cart_items, $order_id ) {
			$order = new WC_Order( $order_id );

			// biteship
			if ( $this->vendor['biteship'] ) {
				$user_id = $order->get_user_id();

				if ( $user_id > 0 ) {
					$metas = [
						'_biteship_address',
						'_biteship_province',
						'_biteship_city',
						'_biteship_district',
						'_biteship_zipcode',
						'_biteship_location',
						'_biteship_location_coordinate'
					];

					foreach ( [ 'billing', 'shipping' ] as $type ) {
						foreach ( $metas as $meta ) {
							$meta_name = $type . $meta;

							update_post_meta( $order_id, '_' . $meta_name, get_user_meta( $user_id, $meta_name, true ) );
						}
					}
				}
			}

			// add custom meta coupon
			if ( $this->vendor['point_rewards'] ) {
				$coupons = $order->get_coupons();

				foreach ( $coupons as $coupon ) {
					$coupon_code = $coupon->get_code();

					if ( strpos( $coupon_code, 'wc_points_redemption' ) !== false ) {
						$x_coupon_code = explode( '_', $coupon_code );
						$points        = $x_coupon_code[ ( array_key_last( $x_coupon_code ) - 1 ) ];
						$amount        = end( $x_coupon_code );

						$user_points = WC_Points_Rewards_Manager::get_users_points( get_current_user_id() );

						if ( $user_points < $points ) {
							return;
						}

						WC_Points_Rewards_Manager::decrease_points( $order->get_customer_id(), $points, 'order-redeem', array(
							'discount_code'   => $coupon_code,
							'discount_amount' => $amount
						), $order_id );

						add_post_meta( $order_id, '_wc_points_logged_redemption', [
							'points'        => (int) $points,
							'amount'        => (int) $amount,
							'discount_code' => $coupon_code
						] );

						update_post_meta( $order_id, '_wc_points_redeemed', (string) $points );

						break;
					}
				}
			}

			// add custom meta bogo
			if ( $this->vendor['bogo_coupon'] ) {
				$items = $order->get_items();

				$products_giveaway = array_map( function ( $cart_item ) {
					if ( isset( $cart_item['free_gift_coupon'] ) && isset( $cart_item['free_product'] ) && 'wt_give_away_product' == $cart_item['free_product'] ) {
						return $cart_item;
					}
				}, $cart_items );

				$products_giveaway = array_values( array_filter( $products_giveaway, fn( $a ) => $a != null ) );

				if ( ! empty( $products_giveaway ) ) {
					foreach ( $items as $line_item_id => $item ) {

						foreach ( $products_giveaway as $giveaway ) {
							if ( $giveaway['product_id'] == $item->get_product_id() && $giveaway['variation_id'] == $item->get_variation_id() ) {
								wc_add_order_item_meta( $line_item_id, 'free_product', $giveaway['free_product'], true );
								wc_add_order_item_meta( $line_item_id, 'free_gift_coupon', $giveaway['free_gift_coupon'], true );
							}
						}
					}
				}
			}

			// update_post_meta($order_id, '_wc_order_attribution_source_type', 'typein');
			// update_post_meta($order_id, '_wc_order_attribution_device_type', 'Mobile');
		}

		// generate the coupon data required for the discount
		public function get_discount_data( $data, $code ) {
			if ( strpos( $code, 'wc_points_redemption' ) !== false ) {
				$amount = end( explode( '_', $code ) );

				$user_points = WC_Points_Rewards_Manager::get_users_points( get_current_user_id() );

				if ( $user_points <= 0 || $user_points < $amount ) {
					return $data;
				}

				$data = array(
					'id'                         => true,
					'type'                       => 'fixed_cart',
					'amount'                     => $amount,
					'coupon_amount'              => $amount, // 2.2
					'individual_use'             => false,
					'usage_limit'                => '',
					'usage_count'                => '',
					'expiry_date'                => '',
					'apply_before_tax'           => true,
					'free_shipping'              => false,
					'product_categories'         => array(),
					'exclude_product_categories' => array(),
					'exclude_sale_items'         => false,
					'minimum_amount'             => '',
					'maximum_amount'             => '',
					'customer_email'             => '',
				);

				return $data;
			}
		}

		// add meta data to order lines item
		public function product_custom_meta( $item_id, $cart_item_key, $values ) {
			$user_id = wp_validate_auth_cookie( cek_raw( 'cookie' ), 'logged_in' );

			if ( $user_id != 0 ) {
				$user = get_userdata( $user_id );

				if ( in_array( 'wholesale_customer', $user->roles ) ) {
					if ( ! $this->vendor['wholesale'] ) {
						return;
					}

					wc_add_order_item_meta( $item_id, '_wwp_wholesale_priced', 'yes', true );
					wc_add_order_item_meta( $item_id, '_wwp_wholesale_role', 'wholesale_customer', true );
				}
			}
		}

		// check quantity free product
		public function check_update_cart_quantity( $cart_item_key, $quantity, $old_quantity, $cart ) {
			$cart_item_data = $cart->cart_contents[ $cart_item_key ];

			if ( $cart_item_data['free_product'] ) {
				$cart->cart_contents[ $cart_item_key ]['quantity'] = $old_quantity;

				return;
			}
		}

		private function wallet_partial_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			$fees = $order->get_fees();
			foreach ( $fees as $fee ) {
				if ( 'Via wallet' === $fee['name'] ) {
					$fee_tax = $fee->get_total_tax();

					$fee->set_total_tax( 0 );
					$fee->set_taxes( [] );
					$fee->save();
				}
			}

			if ( isset( $fee_tax ) ) {
				$order->set_cart_tax( $order->get_cart_tax() + absint( $fee_tax ) );

				$get_taxes = array_values( $order->get_taxes() )[0];
				if ( isset( $get_taxes ) ) {
					$get_taxes->set_tax_total( $get_taxes->get_tax_total() + absint( $fee_tax ) );
				}

				$order->set_total( $order->get_total() + absint( $fee_tax ) );

				$order->save();
			}

			woo_wallet()->wallet->wallet_partial_payment( $order->get_id() );

			return $order;
		}

		private function set_user_session( $user_id ) {
			global $wpdb;

			$date = ( new DateTime( date( 'Y-m-d H:i:s', strtotime( '+3 days' ) ) ) );

			$data = [
				'cart'                       => serialize( [] ),
				'cart_totals'                => serialize( [] ),
				'applied_coupons'            => serialize( [] ),
				'coupon_discount_totals'     => serialize( [] ),
				'coupon_discount_tax_totals' => serialize( [] ),
				'removed_cart_contents'      => serialize( [] ),
				'is_wallet_partial_payment'  => serialize( [] ),
				'wc_notices'                 => null,
				'customer'                   => serialize( [] ),
			];

			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$wpdb->prefix}woocommerce_sessions (`session_key`, `session_value`, `session_expiry`) VALUES (%s, %s, %d)
				ON DUPLICATE KEY UPDATE `session_value` = VALUES(`session_value`), `session_expiry` = VALUES(`session_expiry`)",
					$user_id,
					maybe_serialize( $data ),
					$date->getTimestamp()
				)
			);
		}

		private function cart_items( $wc_session_data, $user_id = null, $data = [] ) {
			global $wpdb;

			$updated_cart = [];

			if ( ! empty( $data ) ) {
				foreach ( $data as $val ) {
					if ( isset( $val['data'] ) ) {
						unset( $val['data'] );
					}

					$updated_cart[ $val['key'] ] = $val;
				}
			}

			// overwrite session cart with new value
			if ( $wc_session_data ) {

				$wc_session_data['cart'] = serialize( $updated_cart );
				$serialize_data          = maybe_serialize( $wc_session_data );

				$wpdb->query( "UPDATE {$wpdb->prefix}woocommerce_sessions SET session_value = '$serialize_data' WHERE session_key = $user_id" );
			} else {
				$this->set_user_session( $user_id );
			}

			// update usermeta
			$full_user_meta['cart'] = $updated_cart;
			update_user_meta( $user_id, '_woocommerce_persistent_cart_1', $full_user_meta );

			return $updated_cart;
		}

		public function run() {
			$plugin_active = get_option( 'active_plugins' );

			$this->vendor = [
				'wholesale'         => in_array( 'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php', $plugin_active ),
				'point_rewards'     => in_array( 'woocommerce-points-and-rewards/woocommerce-points-and-rewards.php', $plugin_active ),
				'bogo_coupon'       => in_array( 'wt-smart-coupons-for-woocommerce/wt-smart-coupon.php', $plugin_active ),
				'local_pickup_plus' => in_array( 'woocommerce-shipping-local-pickup-plus/woocommerce-shipping-local-pickup-plus.php', $plugin_active ),
				'biteship'          => in_array( 'biteship/biteship.php', $plugin_active ),
			];

			$this->register_routes_api();
		}
	}
}

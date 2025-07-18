<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * new version. otw
 */
function rest_get_home_data($request)
{
	global $wpdb;

	$lang = $_GET['lang'] ?? '';

	$existing_tag = get_option('revo_home_data_etag');

	if ($existing_tag !== false) {
		$cache_path = REVO_SHINE_ABSPATH . "storage/cache/{$existing_tag}{$lang}.json";

		$existing_tag .= $lang;

		if (!is_string($request) && $request->get_header('If-None-Match') === $existing_tag) {
			return new WP_REST_Response([], 304, [
				'ETag' => $existing_tag
			]);
		}

		if (file_exists($cache_path)) {
			$home_data = file_get_contents($cache_path);

			return new WP_REST_Response(json_decode($home_data), 200, [
				'ETag' => $existing_tag
			]);
		}
	}

	try {
		$data_app_slider 		  = revo_shine_get_slider();
		$data_app_mini_categories = rest_categories('get');
		$data_mini_banner 		  = rest_mini_banner('get');
		$data_mobile_variable     = query_revo_mobile_variable('"intro_page", "intro_page_status", "splashscreen"', 'sort');
		$data_app_color   		  = $wpdb->get_results("SELECT slug, title, image, description, update_at FROM `revo_mobile_variable` WHERE slug = 'app_color'", OBJECT);
		$revo_mobile_app_function = load_revo_flutter_mobile_app();

		$categories = get_categories([
			'taxonomy'   => 'product_cat',
			'hide_empty' => 1,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'lang'       => $lang
		]);

		if (!empty($categories)) {
			$categories_temp = [];

			foreach ($categories as $category) {
				$thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
				$image        = wp_get_attachment_url($thumbnail_id);

				$categories_temp[] = [
					'id'    => $category->term_id,
					'name'  => $category->name,
					'slug'  => $category->slug,
					'image' => [
						'src' => $image ? $image : ""
					]
				];
			}

			$categories = array_values($categories_temp);
		}

		$result['app_color']          = $data_app_color;
		$result['main_slider']        = $data_app_slider;
		$result['mini_categories']    = $data_app_mini_categories;
		$result['mini_banner']        = $data_mini_banner['mini_banner'];
		$result['blog_banner']        = $data_mini_banner['blog_banner'];
		$result['popup_promo']        = $data_mini_banner['popup_promo'];
		$result['flash_sale']         = $data_mini_banner['flash_sale'];
		$result['categories']         = $categories;
		$result['general_settings']   = rest_general_settings('result');
		$result['header_design']      = revo_shine_get_header_design();
		$result['hero_banner_design'] = revo_shine_get_hero_banner_design();
		$result['customize_homepage'] = revo_shine_get_customize_homepage();

		if (!empty($data_mobile_variable)) {
			foreach ($data_mobile_variable as $data) {
				switch ($data->slug) {
					case 'intro_page':
						$intro_page = true;

						$result['intro'][] = [
							'slug'        => $data->slug,
							"title"       => stripslashes(json_decode($data->title)->title),
							"image"       => $data->image,
							"description" => stripslashes(json_decode($data->description)->description)
						];
						break;
					case 'splashscreen':
						$result['splashscreen'] = [
							'slug'        => $data->slug,
							"title"       => '',
							"image"       => $data->image,
							"description" => $data->description
						];
						break;
					case 'intro_page_status':
						$result['intro_page_status'] = $data->description;
						break;
				}
			}
		}

		if (!isset($intro_page)) {
			$default_intro = data_default_seeder('intro_page_1');
			$result['intro'][] = [
				'slug'        => 'intro_page',
				"title"       => stripslashes(json_decode($default_intro['title'])->title),
				"image"       => $default_intro['image'],
				"description" => stripslashes(json_decode($default_intro['description'])->description)
			];
		}

		$result['new_product'] 		   = $revo_mobile_app_function->get_products([
			'limit'    => 6,
			'order_by' => 'date',
			'order'    => 'DESC',
			'lang'     => $lang
		]);
		$result['products_flash_sale'] = get_product_flash_sale($revo_mobile_app_function);
		$extend_products 			   = get_additional_products($revo_mobile_app_function);

		$response = array_merge($result, $extend_products);

		if ((!$existing_tag || $existing_tag == 123)) {
			$etag = revo_shine_generate_etag('revo_home_data_etag');
			$etag .= $lang;
		} else {
			$etag = $existing_tag;
		}

		file_put_contents(REVO_SHINE_ABSPATH . "storage/cache/{$etag}.json", json_encode($response));

		if (is_string($request)) {
			return;
		}

		return new WP_REST_Response($response, 200, [
			'ETag' => $etag
		]);
	} catch (\Throwable $th) {
		return new WP_REST_Response(['error' => $th->getMessage()], 500);
	}
}

// delete on next release
function rest_home_api($request)
{
	global $wpdb;

	$lang = $_GET['lang'] ?? '';

	$existing_tag = get_option('revo_home_data_etag');

	if ($existing_tag !== false) {
		$cache_path = REVO_SHINE_ABSPATH . "storage/cache/{$existing_tag}{$lang}.json";

		$existing_tag .= $lang;

		if (!is_string($request) && $request->get_header('If-None-Match') === $existing_tag) {
			return new WP_REST_Response([], 304, [
				'Revo-Etag' => $existing_tag
			]);
		}

		if (file_exists($cache_path)) {
			$home_data = file_get_contents($cache_path);

			return new WP_REST_Response(json_decode($home_data), 200, [
				'Revo-Etag' => $existing_tag
			]);
		}
	}

	try {
		$data_app_slider 		  = revo_shine_get_slider();
		$data_app_mini_categories = rest_categories('get');
		$data_mini_banner 		  = rest_mini_banner('get');
		$data_mobile_variable     = query_revo_mobile_variable('"intro_page", "intro_page_status", "splashscreen"', 'sort');
		$data_app_color   		  = $wpdb->get_results("SELECT slug, title, image, description, update_at FROM `revo_mobile_variable` WHERE slug = 'app_color'", OBJECT);
		$revo_mobile_app_function = load_revo_flutter_mobile_app();

		$categories = get_categories([
			'taxonomy'   => 'product_cat',
			'hide_empty' => 1,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'lang'       => $lang
		]);

		if (!empty($categories)) {
			$categories_temp = [];

			foreach ($categories as $category) {
				$thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
				$image        = wp_get_attachment_url($thumbnail_id);

				$categories_temp[] = [
					'id'    => $category->term_id,
					'name'  => $category->name,
					'slug'  => $category->slug,
					'image' => [
						'src' => $image ? $image : ""
					]
				];
			}

			$categories = array_values($categories_temp);
		}

		$result['app_color']          = $data_app_color;
		$result['main_slider']        = $data_app_slider;
		$result['mini_categories']    = $data_app_mini_categories;
		$result['mini_banner']        = $data_mini_banner['mini_banner'];
		$result['blog_banner']        = $data_mini_banner['blog_banner'];
		$result['popup_promo']        = $data_mini_banner['popup_promo'];
		$result['flash_sale']         = $data_mini_banner['flash_sale'];
		$result['categories']         = $categories;
		$result['general_settings']   = rest_general_settings('result');
		$result['header_design']      = revo_shine_get_header_design();
		$result['hero_banner_design'] = revo_shine_get_hero_banner_design();
		$result['customize_homepage'] = revo_shine_get_customize_homepage();

		$query_repeat_data = query_revo_mobile_variable('"disable_intro_page"', 'sort');
		$repeat_intro_data = empty($query_repeat_data) ? 'hide' : $query_repeat_data[0]->description;

		if (!empty($data_mobile_variable)) {
			foreach ($data_mobile_variable as $data) {
				switch ($data->slug) {
					case 'intro_page':
						if ($repeat_intro_data == 'show') {
							$result['intro'] = [];
						} else {
							$intro_page = true;

							$result['intro'][] = [
								'slug'        => $data->slug,
								"title"       => stripslashes(json_decode($data->title)->title),
								"image"       => $data->image,
								"description" => stripslashes(json_decode($data->description)->description)
							];
						}

						break;
					case 'splashscreen':
						$result['splashscreen'] = [
							'slug'        => $data->slug,
							"title"       => '',
							"image"       => $data->image,
							"description" => $data->description
						];
						break;
					case 'intro_page_status':
						$result['intro_page_status'] = $data->description;
						break;
				}
			}
		}

		if ($repeat_intro_data == 'show') {
			$result['intro'] = [];
		} else {
			if (!isset($intro_page)) {
				$default_intro = data_default_seeder('intro_page_1');
				$result['intro'][] = [
					'slug'        => 'intro_page',
					"title"       => stripslashes(json_decode($default_intro['title'])->title),
					"image"       => $default_intro['image'],
					"description" => stripslashes(json_decode($default_intro['description'])->description)
				];
			}
		}

		$result['new_product'] 		   = $revo_mobile_app_function->get_products([
			'limit'    => 6,
			'order_by' => 'date',
			'order'    => 'DESC',
			'lang'     => $lang
		]);
		$result['products_flash_sale'] = get_product_flash_sale($revo_mobile_app_function);
		$extend_products 			   = get_additional_products($revo_mobile_app_function);

		$response = apply_filters('revo_shine_home_data', array_merge($result, $extend_products));

		if ((!$existing_tag || $existing_tag == 123)) {
			$etag = revo_shine_generate_etag('revo_home_data_etag');
			$etag .= $lang;
		} else {
			$etag = $existing_tag;

			header('Revo-Etag: ' . $etag);
		}

		file_put_contents(REVO_SHINE_ABSPATH . "storage/cache/{$etag}.json", json_encode($response));

		if (is_string($request)) {
			return;
		}

		return new WP_REST_Response($response, 200);
	} catch (\Throwable $th) {
		return new WP_REST_Response(['error' => $th->getMessage()], 500);
	}
}

function rest_general_settings($type = 'rest')
{
	global $wpdb;

	$get_status_variable_affiliate = $wpdb->get_row("SELECT * FROM revo_mobile_variable WHERE slug = 'enable_affiliate_video'");

	$video_affiliate = 'active';
	$video_size = get_option('revo_video_setting');

	if (!$video_size) {
		$video_size = 25;
	}

	if (empty($get_status_variable_affiliate->description) || $get_status_variable_affiliate->description === 'hide') {
		$video_affiliate = 'inactive';
	}

	$status_floating_signin		= query_revo_mobile_variable( "'enable_floating_sign_in'" );
	$status_promo_label 		= query_revo_mobile_variable( "'enable_promo_label_on_product_card'" );
	$status_new_product_display	= query_revo_mobile_variable( "'enable_new_product_display_on_new_menu'" );
	$get_data_floating_signin	= query_revo_mobile_variable( "'data_floating_signin'" );

	$data_floating_signin		= unserialize( $get_data_floating_signin[0]->description ?? '' );

	$result['wa']                  = data_default_seeder('kontak_wa');
	$result['sms']                 = data_default_seeder('kontak_sms');
	$result['phone']               = data_default_seeder('kontak_phone');
	$result['about']               = data_default_seeder('about');
	$result['privacy_policy']      = data_default_seeder('privacy_policy');
	$result['term_condition']      = data_default_seeder('term_condition');
	$result['cs']                  = data_default_seeder('cs');
	$result['logo']                = data_default_seeder('logo');
	$result['sosmed_link']         = data_default_seeder('sosmed_link');
	$result['buynow_button_style'] = 'gradation';
	$result['video']	= [
		'video_setting'			=> $video_affiliate,
		'video_file_size_in_mb'	=> $video_size . 'MB',
		'video_file_size'		=> $video_size,
	];
	$result['floating_signin']	   =  array(
		'status'		=> $status_floating_signin[0]->description ?? 'hide',
		'data_floating'	=> array(
			'title'			=> isset( $data_floating_signin['title'] ) ? $data_floating_signin['title'] : 'Get 1000 points for signing up',
			'title_signin'	=> isset( $data_floating_signin['title_signin'] ) ? $data_floating_signin['title_signin'] : 'Sign In',
		),
	);
	$result['status_promo_label']  = $status_promo_label[0]->description;
	$result['status_new_product_display_on_new_menu']  = $status_new_product_display[0]->description ?? 'show';


	$get = query_revo_mobile_variable('"kontak","about","cs","privacy_policy","logo","empty_image","term_condition","searchbar_text","sosmed_link", "buynow_button_style"', 'sort');

	if (!empty($get)) {
		foreach ($get as $key) {
			if (in_array($key->slug, ['searchbar_text', 'sosmed_link'])) {
				$result[$key->slug] = [
					'slug'        => $key->slug,
					'title'       => $key->title,
					'description' => json_decode($key->description)
				];

				continue;
			}

			if ($key->slug === 'buynow_button_style') {
				$result[$key->slug] = $key->description;
				continue;
			}

			if ($key->slug === 'kontak') {
				$result[$key->title] = [
					'slug'        => $key->slug,
					"title"       => $key->title,
					"image"       => $key->image,
					"description" => $key->description
				];

				continue;
			}

			if ($key->slug === 'empty_image') {
				$result[$key->slug][] = [
					'slug'        => $key->slug,
					"title"       => $key->title,
					"image"       => $key->image,
					"description" => $key->description
				];

				continue;
			}

			$result[$key->slug] = [
				'slug'        => $key->slug,
				"title"       => $key->title,
				"image"       => $key->image,
				"description" => $key->description
			];
		}

		$result["link_playstore"] = [
			'slug'        => "playstore",
			"title"       => "link playstore",
			"image"       => "",
			"description" => "https://play.google.com/store"
		];

		$currency = get_woocommerce_currency_symbol();

		$result["currency"] = [
			'slug'        => "currency",
			"title"       => generate_currency(get_option('woocommerce_currency')),
			"image"       => generate_currency(wp_specialchars_decode(get_woocommerce_currency_symbol($currency))),
			"description" => generate_currency(wp_specialchars_decode($currency)),
			"position"    => get_option('woocommerce_currency_pos')
		];

		$result["format_currency"] = [
			'slug'        => wc_get_price_decimals(),
			"title"       => wc_get_price_decimal_separator(),
			"image"       => wc_get_price_thousand_separator(),
			"description" => "Slug : Number of decimals , title : Decimal separator, image : Thousand separator"
		];
	}

	if (empty($result['empty_image'])) {
		$result['empty_image'][] = data_default_seeder('empty_images_1');
		$result['empty_image'][] = data_default_seeder('empty_images_2');
		$result['empty_image'][] = data_default_seeder('empty_images_3');
		$result['empty_image'][] = data_default_seeder('empty_images_4');
		$result['empty_image'][] = data_default_seeder('empty_images_5');
	}

	if (is_plugin_active('woongkir/woongkir.php')) {
		$dropdown_woongkir = 'dropdown_woongkir';
	}

	$result['additional_billing_address'] = [
		[
			'name' => 'city',
			'type' => isset($dropdown_woongkir) ? $dropdown_woongkir : 'textfield'
		],
		[
			'name' => 'address_2',
			'type' => isset($dropdown_woongkir) ? $dropdown_woongkir : 'textfield'
		],
	];

	$result['aftership']           			= revo_shine_get_aftership_config();
	$result['themehigh_multiple_addresses'] = revo_shine_get_themehigh_multiple_addresses_config();

	$result['photoreviews'] = (function () {
		$photoreview_is_active = false;

		if (is_plugin_active('woocommerce-photo-reviews/woocommerce-photo-reviews.php')) {

			$photoreview_settings = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
			$photoreview_type = 'premium';

			$photoreview_is_active = true;
		} elseif (is_plugin_active('woo-photo-reviews/woo-photo-reviews.php')) {

			$photoreview_settings = new VI_WOO_PHOTO_REVIEWS_DATA();
			$photoreview_type = 'free';

			$photoreview_is_active = true;
		}

		if ($photoreview_is_active) {
			$max_sizes = $photoreview_settings->get_params('photo', 'maxsize');
			$max_files = $photoreview_settings->get_params('photo', 'maxfiles');

			$upload_images_requirement = apply_filters('woocommerce_photo_reviews_upload_images_details', $photoreview_settings->get_params('photo', 'upload_images_requirement'), $max_sizes, $max_files);
			$upload_images_requirement = str_replace(array('{max_size}', '{max_files}'), array(
				$max_sizes . ' KB',
				$max_files
			), $upload_images_requirement);
		}

		return [
			'status'           			    => $photoreview_is_active,
			'is_premium'	   			    => isset($photoreview_type) && $photoreview_type === 'premium' ? true : false,
			'maxsize'          			    => $max_sizes ?? 0,
			'maxfiles'         			    => $max_files ?? 0,
			'text_review_title' 	        => isset($photoreview_type) && $photoreview_type === 'premium' ? $photoreview_settings->get_params('coupons', 'form_title') : '',
			'text_review_title_hint'        => isset($photoreview_type) && $photoreview_type === 'premium' ? $photoreview_settings->get_params('review_title_placeholder') : '',
			'text_upload_files_requirement' => isset($photoreview_type) && $photoreview_type === 'premium' ? $upload_images_requirement : '',
			'gdpr' => [
				'status'    => isset($photoreview_type) && $photoreview_settings->get_params('photo', 'gdpr') === 'on' ? true : false,
				'text_gdpr' => isset($photoreview_type) ? $photoreview_settings->get_params('photo', 'gdpr_message') : '',
			],
			'helpful_button' => [
				'status' 	   => isset($photoreview_type) && $photoreview_type === 'premium' ? ($photoreview_settings->get_params('photo', 'helpful_button_enable') == 1 ? true : false) : false,
				'text_helpful' => isset($photoreview_type) && $photoreview_type === 'premium' ? $photoreview_settings->get_params('photo', 'helpful_button_title') : '',
			],
			'verified_owner' => [
				'color' => isset($photoreview_type) ? $photoreview_settings->get_params('photo', 'verified_color') : '',
				'value' => isset($photoreview_type) ? ($photoreview_settings->get_params('photo', 'verified') === 'badge' ? "{badge}" : $photoreview_settings->get_params('photo', 'verified_text')) : ''
			]
		];
	})();

	$result['guest_checkout']        = get_option('woocommerce_enable_guest_checkout') == "yes" ? "enable" : "disable";
	$result['barcode_active'] 	     = is_plugin_active('yith-woocommerce-barcodes-premium/init.php');
	$result['terawallet']            = is_plugin_active('woo-wallet/woo-wallet.php');
	$result['livechat_to_revopos']   = is_plugin_active('Plugin-revo-kasir/plugin-revo-kasir.php') || is_plugin_active('Plugin-revo-kasir/index.php') ? (!empty($a = query_revo_mobile_variable('"live_chat_status"', 'sort')) ? ($a[0]->description === 'show' ? true : false) : false) : false;
	$result['local_pickup_plus'] 	 = is_plugin_active('woocommerce-shipping-local-pickup-plus/woocommerce-shipping-local-pickup-plus.php');
	$result['point_plugin']			 = is_plugin_active('woocommerce-points-and-rewards/woocommerce-points-and-rewards.php');
	$result['fox_woocs']			 = is_plugin_active('woocommerce-currency-switcher/index.php');
	$result['biteship']	             = revo_shine_get_biteship_config();
	$result['checkout']              = false;
	$result['sync_cart']             = false; // deprecated function. remove response in the future
	$result['blog_comment_feature']  = false;
	$result['popup_biometric'] 		 = false;
	$result['gift_box']              = 'hide';
	$result['design_product_page']   = 'design_1';
	$result['guide_feature'] 		 = [
		'status' => false,
		'image'  => '',
	];

	$settings = query_revo_mobile_variable('"checkout_native","gift_box","blog_comment_feature","guide_feature","popup_biometric","design_product_page"', 'sort');

	if (!empty($settings)) {
		foreach ($settings as $setting) {
			if ($setting->slug === 'guide_feature') {
				$result['guide_feature']['status'] = $setting->description !== 'hide' ? true : false;
				$result['guide_feature']['image']  = !empty($setting->image) ? $setting->image : '';

				continue;
			}

			if ($setting->slug === 'checkout_native') {
				$result['checkout'] = $setting->description !== 'hide' ? true : false;
				continue;
			}

			if (in_array($setting->slug, ['gift_box', 'design_product_page'])) {
				$result[$setting->slug] = $setting->description;
				continue;
			}

			$result[$setting->slug] = $setting->description !== 'hide' ? true : false;
		}
	}

	$result['login_page'] = get_option('revo_shine_login_page', ['design' => 'classic', 'text_heading' => '#1 Shop App for your Woocommerce Store', 'text' => 'You can change this text at any time in WP-ADMIN', 'bg_color' => '#118eea', 'text_color' => '#FFFFFF', 'btn_color' => '#940000', 'bg_image' => REVO_SHINE_ASSET_URL . 'images/bg_login.png']);

	$result['product_settings'] = (function () use ($wpdb) {
		$slug_settings = array(
			'show_sold_item_data',
			'show_average_rating_data',
			'show_rating_section',
			'show_variation_with_image',
			'show_out_of_stock_product'
		);

		$product_settings_datas = $wpdb->get_results("SELECT slug, description FROM revo_mobile_variable WHERE description = 'show' AND slug IN " . "('" . implode("','", $slug_settings) . "')", OBJECT);
		$product_settings_datas = array_map(fn ($data) => $data->slug, $product_settings_datas);

		$result = [];
		foreach ($slug_settings as $slug) {
			if (in_array($slug, $product_settings_datas)) {
				$result[$slug] = true;
				continue;
			}

			$result[$slug] = false;
		}

		return $result;
	})();

	$result = apply_filters('revo_shine_general_settings', $result);

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_mini_banner($request)
{
	global $wpdb;

	$lang = $_GET['lang'] ?? '';

	$where = '';
	if (isset($_GET['blog_banner'])) {
		$where = "AND type = 'Blog Banner' ";
	}

	$result = [];
	$data_banner = $wpdb->get_results("SELECT * FROM revo_list_mini_banner WHERE is_deleted = 0 AND product_id is not null $where " . apply_filters('revo_shine_query_banner_lang', '') . " ORDER BY order_by ASC", OBJECT);

	if (isset($_GET['blog_banner'])) {
		foreach ($data_banner as $blog_banner) {
			$type         = explode('|', $blog_banner->product_name)[0];
			$product_name = explode('|', $blog_banner->product_name)[1] ?? $type;
			$link_to      = $type == 'cat' ? 'category' : ($type == 'blog' ? 'blog' : ($type == 'url' ? 'url' : ($type == 'attribute' ? 'attribute' : 'product')));

			if (in_array($link_to, ['blog', 'product', 'category'])) {
				if ($link_to !== 'category') {
					$p = get_posts([
						'lang'      => $lang,
						'include'   => [$blog_banner->product_id],
						'post_type' => $link_to == 'blog' ? 'post' : $link_to
					]);
				} else {
					$p = get_terms([
						'taxonomy'   => 'product_cat',
						'lang'       => $lang,
						'include'    => [$blog_banner->product_id],
						'hide_empty' => false
					]);
				}

				if (count($p) <= 0) {
					$blog_banner->type = '';
				}
			}

			$result[] = [
				'link_to'      => strtolower($link_to),
				'name'         => $product_name,
				'product'      => (int) $blog_banner->product_id,
				'title_slider' => isset($blog_banner->title) ? $blog_banner->title : '',
				'type'         => $blog_banner->type,
				'image'        => $blog_banner->image,
			];

			break;
		}
	} else {
		foreach ($data_banner as $value) {
			$type         = explode('|', $value->product_name)[0];
			$product_name = explode('|', $value->product_name)[1] ?? $type;
			$link_to      = $type == 'cat' ? 'category' : ($type == 'blog' ? 'blog' : ($type == 'url' ? 'url' : ($type == 'attribute' ? 'attribute' : 'product')));

			if (in_array($link_to, ['blog', 'product', 'category'])) {
				if ($link_to !== 'category') {
					$p = get_posts([
						'lang'      => $lang,
						'include'   => [$value->product_id],
						'post_type' => $link_to == 'blog' ? 'post' : $link_to
					]);
				} else {
					$p = get_terms([
						'taxonomy'   => 'product_cat',
						'lang'       => $lang,
						'include'    => [$value->product_id],
						'hide_empty' => false
					]);
				}

				if (count($p) <= 0) {
					continue;
				}
			}

			$result[$value->section_type][] = [
				'link_to'      => strtolower($link_to),
				'name'         => $product_name,
				'product'      => (int) $value->product_id,
				'title_slider' => isset($value->title) ? $value->title : '',
				'type'         => $value->type,
				'image'        => $value->image,
			];
		}

		$result = [
			'mini_banner' => array_merge($result['special-promo'] ?? [], $result['love-these-items'] ?? [], $result['single-banner'] ?? []),
			'blog_banner' => $result['blog-banner'] ?? [revo_shine_default_mini_banner('blog-banner')],
			'popup_promo' => $result['popup-promo'] ?? [revo_shine_default_mini_banner('popup-promo')],
			'flash_sale'  => $result['flash-sale']  ?? [revo_shine_default_mini_banner('flash-sale')],
		];
	}

	if ($request instanceof WP_REST_Request) {
		return new WP_REST_Response($result, 200);
	}

	return $result;
}

function rest_list_product($request)
{
	$args = [
		'include'       => $request['include'],
		'exclude'       => $request['exclude'],
		'page'          => $request['page'] ?? 1,
		'limit'         => $request['per_page'] ?? 10,
		'parent'        => $request['parent'],
		'search'        => $request['search'],
		'category'      => $request['category'],
		'slug_category' => $request['slug_category'],
		'slug'          => $request['slug'],
		'id'            => $request['id'],
		'featured'      => $request['featured'],
		'order'         => $request['order'] ?? 'DESC',
		'order_by'      => $request['order_by'] ?? 'date',
		'attribute'     => $request['attribute'],
		'price_range'   => $request['price_range'],
		'sku'           => $request['sku'],
		'exclude_sku'   => $request['exclude_sku'],
		'lang'          => $request['lang'],
		'on_sale'       => $request['on_sale'],
		'video_id'		=> $request['video_id'] ?? '',
	];

	try {
		return new WP_REST_Response(
			load_revo_flutter_mobile_app()->get_products($args),
			200
		);
	} catch (\Throwable $th) {
		return new WP_REST_Response([
			'status'  => 'error',
			'message' => $th->getMessage() . ' | ' . $th->getLine() . ' | ' . $th->getFile()
		], 500);
	}
}

function revo_shine_get_header_design()
{
	$default_menus = ['title' => 'Shop', 'link' => 'shop'];
	$header_values = get_option('revo_shine_customize_homepage_header', [
		'type'  => 'v6',
		'logo'  => '',
		'menus' => []
	]);

	$header_values['menus'] = array_merge([$default_menus], $header_values['menus']);

	return $header_values;
}

function revo_shine_get_hero_banner_design()
{
	$hero_banner = get_option( 'revo_shine_customize_homepage_hero_banner', 'v2' );
	$args = array(
		'taxonomy'   => 'product_cat',
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
		'hide_empty' => true,
	);
	
	$categories_raw		= get_terms($args);
	$result_categories	= array();

	if ( $hero_banner === 'v2' ) {
		foreach ($categories_raw as $categories) {
			$result_categories[] = array(
				'id'		=> $categories->term_id,
				'name'		=> $categories->name,
				'slug'		=> $categories->slug,
				'parent'	=> $categories->parent,
			);
		}
	}

	$hero_banner_value = array(
		'type'			=> $hero_banner,
		'categories'	=> $result_categories,
	);

	return $hero_banner_value;
}

function revo_shine_get_customize_homepage()
{
	global $wpdb;

	$raw_customize_homepage = $wpdb->get_row("SELECT description FROM revo_mobile_variable WHERE slug = 'customize_homepage'", OBJECT);
	$customize_homepage 	= unserialize($raw_customize_homepage->description);

	if (!empty($customize_homepage)) {
		$check_sections = [
			'category-3'   => "section_n",
			'category-6'   => "section_l",
			'category-4'   => "section_k",
			'big-category' => "section_i",
		];

		$check_exist_section = array_intersect($check_sections, $customize_homepage);

		if (!empty($check_exist_section)) {
			$categories = $wpdb->get_results("SELECT section_type, count(*) as total FROM `revo_list_categories` WHERE is_deleted = 0 GROUP BY `section_type` ORDER BY `order_by` ASC", OBJECT);

			if (!empty($categories)) {
				foreach ($check_exist_section as $category_exist_key => $category_exist_value) {

					$categories_key = array_search($category_exist_key, array_column($categories, 'section_type'));

					if ($categories_key === false) {
						$customize_homepage_key = array_search($category_exist_value, $customize_homepage);
						unset($customize_homepage[$customize_homepage_key]);
					}
				}
			}
		}

		return array_values($customize_homepage);
	}

	return [];
}

function revo_shine_get_slider($type = 'rest')
{
	global $wpdb;

	$lang = $_GET['lang'] ?? '';

	$data_banner = $wpdb->get_results("SELECT * FROM revo_mobile_slider WHERE is_deleted = 0 AND product_id IS NOT null " . apply_filters('revo_shine_query_banner_lang', '') . " ORDER BY order_by DESC", OBJECT);

	$result = [];
	foreach ($data_banner as $key => $value) {
		$type         = explode('|', $value->product_name)[0];
		$product_name = explode('|', $value->product_name)[1] ?? '';
		$link_to      = $type == 'cat' ? 'category' : ($type == 'blog' ? 'blog' : ($type == 'url' ? 'url' : ($type == 'attribute' ? 'attribute' : 'product')));

		if (empty($product_name)) {
			$product_name = $type;
		}

		if (in_array($link_to, ['blog', 'product', 'category'])) {
			if ($link_to !== 'category') {
				$p = get_posts([
					'lang'      => $lang,
					'include'   => [$value->product_id],
					'post_type' => $link_to == 'blog' ? 'post' : $link_to
				]);
			} else {
				$p = get_terms([
					'taxonomy'   => 'product_cat',
					'lang'       => $lang,
					'include'    => [$value->product_id],
					'hide_empty' => false
				]);
			}

			if (count($p) <= 0) {
				continue;
			}
		}

		array_push($result, [
			'link_to'      => strtolower($link_to),
			'name'         => $product_name,
			'product'      => (int) $value->product_id,
			'title_slider' => $value->title,
			'image'        => $value->images_url,
			'type'		   => !empty($value->section_type) ? $value->section_type : 'banner-1'
		]);
	}

	return $result;
}

function get_additional_products($revo_loader)
{
	global $wpdb;

	list($result, $includes) = [[], []];

	$extend_product_type = $wpdb->get_results("SELECT * FROM `revo_extend_products` WHERE is_deleted = 0 AND is_active = 1", OBJECT);
	foreach ($extend_product_type as $type) {
		$temp_data[str_replace('-', '_', $type->section_type)] = [];

		$product_type_ids = json_decode($type->products);
		if (!is_null($product_type_ids)) {
			$includes = array_merge($includes, $product_type_ids);
			$temp_data[str_replace('-', '_', $type->section_type)] = $product_type_ids;
		}

		$result[str_replace('-', '_', $type->section_type)][] = [
			'title'       => $type->title,
			'description' => $type->description,
			'products'    => [],
			'_ids'        => $product_type_ids ?? [],
		];
	}

	$includes = array_unique($includes);

	if (count($includes) >= 1) {
		$raw_products = $revo_loader->get_products(array(
			'lang'     	     => $_GET['lang'] ?? '',
			'include'  	     => implode(',', $includes),
			'limit'			 => -1,
			'extend_product' => $temp_data
		));

		if (empty($raw_products)) {
			return $result;
		}

		$products = [];
		foreach ($raw_products as $raw_list_product) {
			$products[$raw_list_product['id']] = $raw_list_product;
		}

		foreach ($result as $key => $value) {
			foreach ($value[0]['_ids'] as $product_id) {
				if (isset($products[$product_id])) {
					$result[$key][0]['products'][] = $products[$product_id];
				}
			}

			unset($result[$key][0]['_ids']);
		}
	}

	return $result;
}

function get_product_flash_sale($revo_loader)
{
	global $wpdb;

	cek_flash_sale_end();
	$date            = date('Y-m-d H:i:s');
	$data_flash_sale = $wpdb->get_results("SELECT * FROM `revo_flash_sale` WHERE is_deleted = 0 AND start <= '" . $date . "' AND end >= '" . $date . "' AND is_active = 1  ORDER BY id DESC LIMIT 1", OBJECT);

	$result        = [];
	$list_products = [];
	foreach ($data_flash_sale as $key => $value) {
		if (!empty($value->products)) {
			$list_products    = $revo_loader->get_products(array(
				'lang' 	  => $_GET['lang'] ?? '',
				'include' => implode(',', json_decode($value->products))
			));
		}
		array_push($result, [
			'id'       => (int) $value->id,
			'title'    => $value->title,
			'start'    => $value->start,
			'end'      => $value->end,
			'image'    => $value->image,
			'products' => $list_products,
		]);
	}

	return $result;
}

function rest_categories($type = 'rest')
{
	global $wpdb;

	$lang = $_GET['lang'] ?? '';

	$data_banner = $wpdb->get_results("SELECT * FROM revo_list_categories WHERE is_deleted = 0 " . apply_filters('revo_shine_query_banner_lang', '') . " ORDER BY order_by ASC", OBJECT);

	$result = [];
	if (isset($_GET['show_popular'])) {
		array_push($result, [
			'categories'       => (int) '9911',
			'title_categories' => 'Popular Categories',
			'image'            => revo_shine_url() . 'assets/images/popular.png',
			'type'			   => 'popular-categories'
		]);
	}

	foreach ($data_banner as $value) {
		$term = get_terms([
			'taxonomy'   => 'product_cat',
			'lang'       => $lang,
			'include'    => [$value->category_id],
			'hide_empty' => false
		]);

		if (count($term) && ((int) $value->category_id) != 0) {
			array_push($result, [
				'categories'       => (int) $value->category_id,
				'title_categories' => isset($term[0]) ? $term[0]->name : json_decode($value->category_name)->title,
				'image'            => $value->image,
				'type'			   => !empty($value->section_type) ? $value->section_type : 'mini'
			]);
		}
	}

	if (empty($result)) {
		for ($i = 1; $i < 5; $i++) {
			array_push($result, [
				'categories'       => (int) '0',
				'title_categories' => 'Dummy Categories',
				'image'            => revo_shine_url() . 'assets/images/seeders/home-categories/one-rows-' . $i . '.png',
				'type'			   => 'mini'
			]);
		}

		for ($i = 1; $i <= 7; $i++) {
			array_push($result, [
				'categories'       => (int) '0',
				'title_categories' => 'Dummy Categories',
				'image'            => revo_shine_url() . 'assets/images/seeders/home-categories/two-rows-' . $i . '.png',
				'type'			   => 'categories-two-rows'
			]);
		}
	}

	if (!isset($_GET['show_popular'])) {
		array_push($result, [
			'categories'       => (int) '0',
			'title_categories' => 'view_more',
			'image'            => revo_shine_url() . 'assets/images/viewMore.png',
			'type'			   => 'mini'
		]);
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

/**
 * note: add transient
 */
function rest_categories_list($request)
{
	$args = array(
		'taxonomy'     => 'product_cat',
		'show_count'   => 1,
		'pad_counts'   => 0,
		'hierarchical' => 1,
		'hide_empty'   => 1,
		'menu_order'   => 'ASC',
		'parent'       => 0
	);

	if (!empty($request['page'])) {
		$args['offset'] = $request['page'];
	}

	if (!empty($request['limit'])) {
		$args['number'] = $request['limit'];
	}

	$result = [];
	$wc_placeholder_image = wc_placeholder_img_src();

	if (empty($request['parent'])) {
		$popular_categories = get_popular_categories();
		if (!empty($popular_categories)) {
			$result[] = [
				'id'          => 9911,
				'title'       => 'Popular Categories',
				'description' => '',
				'parent'      => 0,
				'count'       => 0,
				'image'       => revo_shine_url() . 'assets/images/popular.png',
			];
		}

		$categories = get_categories($args);
		if (!empty($categories)) {
			foreach ($categories as $category) {
				if ($category->name !== 'Uncategorized') {
					$image_url = wp_get_attachment_url(get_term_meta($category->term_id, 'thumbnail_id', true));
					if (!$image_url) {
						$image_url = $wc_placeholder_image;
					}

					$sub_categories = get_terms([
						'taxonomy'   => 'product_cat',
						'hide_empty' => false,
						'parent'     => $category->term_id
					]);

					array_push($result, [
						'id'          => $category->term_id,
						'title'       => wp_specialchars_decode($category->name),
						'description' => $category->description,
						'parent'      => $category->parent,
						'count'       => count($sub_categories), // count sub categories
						'image'       => $image_url,
					]);
				}
			}
		}
	} else {
		$categories = get_terms([
			'taxonomy'   => 'product_cat',
			'hide_empty' => 1,
			'parent'     => $request['parent']
		]);

		foreach ($categories as $category) {
			$image_url = wp_get_attachment_url(get_term_meta($category->term_id, 'thumbnail_id', true));
			if (!$image_url) {
				$image_url = $wc_placeholder_image;
			}

			array_push($result, [
				'id'          => $category->term_id,
				'title'       => wp_specialchars_decode($category->name),
				'description' => $category->description,
				'parent'      => $category->parent,
				'count'       => 0,
				'image'       => $image_url,
			]);
		}
	}

	return new WP_REST_Response($result, 200);
}

function popular_categories($type = 'rest')
{
	global $wpdb;


	$lang = $_GET['lang'] ?? '';

	$data_categories = get_popular_categories();

	$result = [];
	if (!empty($data_categories)) {
		foreach ($data_categories as $key) {
			$categories = json_decode($key->categories);
			$list       = [];

			if (is_array($categories)) {
				for ($i = 0; $i < count($categories); $i++) {
					$image = wp_get_attachment_url(get_term_meta($categories[$i], 'thumbnail_id', true));
					$name  = get_terms([
						'taxonomy'   => 'product_cat',
						'include'    => $categories[$i],
						'hide_empty' => false,
						'lang'       => $lang
					]);

					if (!empty($name)) {
						$name   = $name[0]->name;
						$list[] = [
							'id'    => $categories[$i],
							'name'  => !empty($name) ? $name : "",
							'image' => ($image == false ? revo_shine_url() . 'assets/images/default_mini_banner.png' : $image)
						];
					}
				}

				if (!empty($list)) {
					$result[] = array(
						'title'      => $key->title,
						'categories' => $list,
					);
				}
			}
		}
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_flash_sale($type = 'rest')
{
	global $wpdb;

	cek_flash_sale_end();
	$date            = date('Y-m-d H:i:s');
	$data_flash_sale = $wpdb->get_results("SELECT * FROM `revo_flash_sale` WHERE is_deleted = 0 AND start <= '" . $date . "' AND end >= '" . $date . "' AND is_active = 1  ORDER BY id DESC LIMIT 1", OBJECT);

	$result        = [];
	$list_products = [];
	foreach ($data_flash_sale as $key => $value) {
		if (!empty($value->products)) {
			$get_products = json_decode($value->products);
			if (is_array($get_products)) {
				$list_products = implode(",", $get_products);
			}
		}
		array_push($result, [
			'id'       => (int) $value->id,
			'title'    => $value->title,
			'start'    => $value->start,
			'end'      => $value->end,
			'image'    => $value->image,
			'products' => implode(",", json_decode($value->products)),
		]);
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_extend_products($type = 'rest')
{

	global $wpdb;


	$where = '';

	$typeGet = '';
	if (isset($_GET['type'])) {
		$typeGet = $_GET['type'];

		if ($typeGet == 'special') {
			$where = "AND type = 'special'";
		}

		if ($typeGet == 'our_best_seller') {
			$where = "AND type = 'our_best_seller'";
		}

		if ($typeGet == 'recomendation') {
			$where = "AND type = 'recomendation'";
		}
	}

	$products = $wpdb->get_results("SELECT * FROM `revo_extend_products` WHERE is_deleted = 0 AND is_active = 1 $where  ORDER BY id DESC", OBJECT);

	$result        = [];
	$list_products = "";
	if (!empty($products)) {
		foreach ($products as $key => $value) {
			if (!empty($value->products)) {
				$get_products = json_decode($value->products);
				if (is_array($get_products)) {
					$list_products = implode(",", $get_products);
				}
			}
			array_push($result, [
				'title'       => $value->title,
				'description' => $value->description,
				'products'    => $list_products,
			]);
		}
	} else {
		array_push($result, [
			'title'       => $typeGet,
			'description' => "",
			'products'    => "",
		]);
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_get_barcode($type = 'rest')
{

	global $wpdb;


	$code = cek_raw('code');

	if (!empty($code)) {
		$table_name = $wpdb->prefix . 'postmeta';

		$get = $wpdb->get_row("SELECT * FROM `$table_name` WHERE `meta_value` LIKE '$code'", OBJECT);
		if (!empty($get)) {
			$result['id'] = (int) $get->post_id;
		} else {
			$result = ['status' => 'error', 'message' => 'code not found !'];
		}
	} else {
		$result = ['status' => 'error', 'message' => 'code required !'];
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_hit_products($request)
{
	global $wpdb;

	$cookie 	= $request['cookie'];
	$product_id = $request['product_id'];

	if (empty($cookie)) {
		return new WP_REST_Response(['status' => 'error', 'message' => 'Login required !'], 403);
	}

	$user_id = wp_validate_auth_cookie($cookie, 'logged_in');

	if (!$user_id) {
		$result = ['status' => 'error', 'message' => 'User Tidak ditemukan !'];
	} else {
		$result = ['status' => 'error', 'message' => 'Tidak dapat Hit Products !'];

		if (!empty($product_id)) {
			$date = date('Y-m-d');

			$products = $wpdb->get_results("SELECT * FROM `revo_hit_products` WHERE products = '$product_id' AND type = 'hit' AND user_id = '$user_id' AND created_at LIKE '%$date%'", OBJECT);

			if (empty($products)) {
				$wpdb->insert('revo_hit_products', [
					'products'   => $product_id,
					'ip_address' => '',
					'user_id'    => $user_id,
				]);

				if (empty($wpdb->show_errors())) {
					$result = ['status' => 'success', 'message' => 'Berhasil Hit Products !'];
				} else {
					$result = ['status' => 'error', 'message' => 'Server Error 500 !'];
				}
			} else {
				$result = ['status' => 'error', 'message' => 'Hit Product Hanya Bisa dilakukan sekali sehari !'];
			}
		}
	}

	return new WP_REST_Response($result, 200);
}

function rest_insert_review($type = 'rest')
{

	$args  = $_POST;
	$media = $_FILES['media'];

	if (isset($args['cookie']) && !empty($args['cookie'])) {
		$user_id = wp_validate_auth_cookie($args['cookie'], 'logged_in');

		if (!$user_id) {
			return ['status' => 'error', 'message' => 'User not found !'];
		}

		$user = get_userdata($user_id);
	} else {

		if (empty($args['author_name']) || empty($args['author_email'])) {
			return ['status' => 'error', 'message' => 'Author name and email required !'];
		}
	}

	if (is_plugin_active('woocommerce-photo-reviews/woocommerce-photo-reviews.php')) {

		$photoreview_settings = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
		$photoreview_type = 'premium';
	} elseif (is_plugin_active('woo-photo-reviews/woo-photo-reviews.php')) {

		$photoreview_settings = new VI_WOO_PHOTO_REVIEWS_DATA();
		$photoreview_type = 'free';
	}

	if (isset($media) && !empty($media)) {
		$upload_dir  = wp_upload_dir();
		$upload_path = str_replace('/', DIRECTORY_SEPARATOR, $upload_dir['path']) . DIRECTORY_SEPARATOR;
		$max_size    = $photoreview_settings->get_params('photo', 'maxsize') ?? 1500;
		$max_files   = $photoreview_settings->get_params('photo', 'maxfiles') ?? 2;

		if ($max_files < count($media['name'])) {
			return ['status' => 'error', 'message' => 'Max files is ' . $max_files];
		}

		foreach ($media['size'] as $media_size) {
			if (((int) number_format($media_size / 1024, 2)) > $max_size) {
				return ['status' => 'error', 'message' => 'Max size is ' . $max_size . ' KB'];
			}
		}
	}

	$comment_id = wp_insert_comment([
		'comment_post_ID'      => $args['product_id'], // <=== The product ID where the review will show up
		'comment_author'       => isset($user) ? $user->display_name : $args['author_name'],
		'comment_author_email' => isset($user) ? $user->user_email : $args['author_email'], // <== Important
		'comment_author_url'   => '',
		'comment_content'      => $args['comments'],
		'comment_type'         => 'review',
		'comment_parent'       => 0,
		'user_id'              => isset($user) ? $user_id : 0, // <== Important
		'comment_author_IP'    => '',
		'comment_agent'        => '',
		'comment_date'         => date('Y-m-d H:i:s'),
		'comment_approved'     => 0,
	]);

	update_comment_meta($comment_id, 'rating', $args['rating']); // HERE inserting the rating (an integer from 1 to 5)

	if (isset($photoreview_type)) {

		if (isset($args['review_title']) && !empty($args['review_title'])) {
			update_comment_meta($comment_id, 'wcpr_review_title', sanitize_text_field($args['review_title']));
		}

		if (isset($media) && !empty($media)) {

			if (!function_exists('wp_crop_image')) {
				require_once(ABSPATH . 'wp-admin/includes/image.php');
				require_once(ABSPATH . 'wp-admin/includes/media.php');
			}

			$attach_ids = [];
			$media_caption = explode('|', $args['caption']);

			for ($i = 0; $i < count($media['name']); $i++) {

				$filename = time() . '_' . $media['name'][$i];

				move_uploaded_file(
					$media["tmp_name"][$i],
					$upload_path . $filename
				);

				$attachment = [
					'post_author'	 => isset($user) ? $user_id : 0,
					'post_mime_type' => $media['type'][$i],
					'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filename)),
					'post_excerpt'   => isset($media_caption[$i]) ? (trim($media_caption[$i]) !== '{empty}' ? trim($media_caption[$i]) : '') : '',
					'post_content'   => '',
					'post_status'    => 'inherit',
					'guid'           => $upload_dir['url'] . '/' . basename($filename)
				];

				$attach_id   = wp_insert_attachment($attachment, $upload_dir['path'] . '/' . $filename);
				$attach_meta = wp_generate_attachment_metadata($attach_id, $upload_dir['path'] . '/' . $filename);

				update_post_meta($attach_id, '_wp_attachment_metadata', $attach_meta);

				array_push($attach_ids, $attach_id);
			}

			update_comment_meta($comment_id, 'reviews-images', wc_clean($attach_ids));
		}
	}

	$result = ['status' => 'success', 'message' => 'insert rating success !'];

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_get_hit_products($request)
{
	global $wpdb;

	$user_id = wp_validate_auth_cookie($request['cookie'] ?? '', 'logged_in');
	if (!$user_id) {
		return new WP_REST_Response(['status' => 'error', 'message' => 'User Tidak ditemukan !'], 404);
	}

	$list_product = '';
	$raw_products = $wpdb->get_results("SELECT products FROM `revo_hit_products` WHERE user_id = '$user_id' AND type = 'hit' ORDER BY id DESC LIMIT 12", OBJECT);

	if (!empty($raw_products)) {
		$temp_list_product = array_map(function ($value) {
			return $value->products;
		}, $raw_products);

		$list_product = implode(',', array_unique($temp_list_product));
	}

	return new WP_REST_Response([
		'status'   => 'success',
		'products' => $list_product,
	], 200);
}

function rest_intro_page_status($type = 'rest')
{
	global $wpdb;

	$get    = query_revo_mobile_variable('"intro_page_status"', 'sort');
	$status = $_GET['status'];

	if (empty($get)) {
		$wpdb->insert('revo_mobile_variable', array(
			'slug'        => 'intro_page_status',
			'title'       => '',
			'image'       => query_revo_mobile_variable('"splashscreen"')[0]->image,
			'description' => $status
		));
	} else {
		$wpdb->query($wpdb->prepare("UPDATE revo_mobile_variable SET description='$status' WHERE slug='intro_page_status'"));
	}

	revo_shine_rebuild_cache('revo_home_data');

	return $status;
}

function rest_disable_intro_page_status($type = 'rest')
{
	global $wpdb;

	$get    = query_revo_mobile_variable('"disable_intro_page"', 'sort');
	$status = $_GET['status'];

	if (empty($get)) {
		$wpdb->insert('revo_mobile_variable', array(
			'slug'        => 'disable_intro_page',
			'title'       => '',
			'image'       => query_revo_mobile_variable('"splashscreen"')[0]->image,
			'description' => $status
		));
	} else {
		$wpdb->query($wpdb->prepare("UPDATE revo_mobile_variable SET description='$status' WHERE slug='disable_intro_page'"));
	}

	revo_shine_rebuild_cache('revo_home_data');

	return $status;
}

function generate_currency($currency)
{

	if ($currency == 'AED') {
		$result = 'د.إ';
	} else {
		$result = $currency;
	}

	return $result;
}

function rest_get_intro_page($type = 'rest')
{

	global $wpdb;

	$get = query_revo_mobile_variable('"intro_page","splashscreen"', 'sort');

	$result['splashscreen']      = data_default_seeder('splashscreen');
	$result['intro_page_status'] = query_revo_mobile_variable('"intro_page_status"', 'sort')[0]->description;

	$intro_page = true;
	if (!empty($get)) {
		foreach ($get as $key) {

			if ($key->slug == 'splashscreen') {
				$result['splashscreen'] = [
					'slug'        => $key->slug,
					"title"       => '',
					"image"       => $key->image,
					"description" => $key->description
				];
			}

			if ($key->slug == 'intro_page') {
				$result['intro'][] = [
					'slug'        => $key->slug,
					"title"       => stripslashes(json_decode($key->title)->title),
					"image"       => $key->image,
					"description" => stripslashes(json_decode($key->description)->description)
				];

				$intro_page = false;
			}
		}
	}

	if ($intro_page) {
		for ($i = 1; $i < 4; $i++) {
			$result['intro'][] = data_default_seeder('intro_page_' . $i);
		}
	}

	// $res = new WP_REST_Response($result);
	// $res->set_headers(array('Cache-Control' => 'max-age=3600'));

	// return $res;

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_add_remove_wistlist($type = 'rest')
{
	$cookie     = cek_raw('cookie');
	$product_id = cek_raw('product_id');

	$result = ['type' => 'you must include cookie !', 'message' => 'error'];

	if (!empty($cookie)) {
		$result['product_id'] = $product_id;
		$user_id              = wp_validate_auth_cookie($cookie, 'logged_in');

		if (!$user_id) {
			return ['type' => 'users not found !', 'message' => 'error'];
		}

		if (empty($product_id)) {
			return ['type' => 'empty Product id !', 'message' => 'error'];
		}

		$get_hit_product = query_hit_products($product_id, $user_id);

		if (@cek_raw('check')) {
			if ($get_hit_product->is_wistlist == 0) {
				$result['type']    = 'check';
				$result['message'] = false;
			} else {
				$result['type']    = 'check';
				$result['message'] = true;
			}
		} else {
			global $wpdb;

			if ($get_hit_product->is_wistlist == 0) {
				$wpdb->insert('revo_hit_products', [
					'products'   => $result['product_id'],
					'ip_address' => '',
					'type'       => 'wistlist',
					'user_id'    => $user_id,
				]);

				if (empty($wpdb->show_errors())) {
					$result['type']    = 'add';
					$result['message'] = 'success';
				} else {
					$result['type']    = 'add';
					$result['message'] = 'error';
				}
			} else {
				$product_id = $result['product_id'];
				$wpdb->query($wpdb->prepare("DELETE FROM `revo_hit_products` WHERE products = '$product_id' AND user_id = '$user_id' AND type = 'wistlist'"));

				if (empty($wpdb->show_errors())) {
					$result['type']    = 'remove';
					$result['message'] = 'success';
				} else {
					$result['type']    = 'remove';
					$result['message'] = 'error';
				}
			}
		}
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_list_wistlist($type = 'rest')
{

	$cookie 	= cek_raw('cookie');
	$page		= cek_raw('page') ?? 1;
	$per_page	= cek_raw('per_page') ?? 10;

	$result = ['status' => 'error', 'message' => 'you must include cookie !'];

	if (!empty($cookie)) {
		$user_id = wp_validate_auth_cookie($cookie, 'logged_in');

		if (!$user_id) {
			return ['status' => 'error', 'message' => 'users not found !'];
		}

		$list_products    = '';
		$get_hit_products = query_all_hit_products($user_id);

		if (!empty($get_hit_products)) {
			$list_products = [];

			foreach ($get_hit_products as $key) {
				$check_product = wc_get_product($key->products);

				if (!$check_product) {
					continue;
				}

				$list_products[] = $key->products;
			}

			if (is_array($list_products)) {
				$total_products = count($list_products);
				$start_index = ($page - 1) * $per_page;
    			$end_index = min($start_index + $per_page, $total_products);
				$list_products = array_slice($list_products, $start_index, $end_index - $start_index);

				$list_products = implode(",", $list_products);
			}
		}

		$result = [
			'products' => $list_products
		];
	}


	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_key_firebase($type = 'rest')
{

	$key    = revo_shine_access_key();
	$result = array(
		"serverKey"         => 'AAAALwNKHLc:APA91bGY_AkY01vJ_aGszm7yIjLaNbaAM1ivPlfigeFscdSVuUx3drCRGxyIRgLTe7nLB-5_5rF_ShlmqVXCUmrSd_uaJdcEV43MLxUeFrzmKCzyZzBB7AUlziIGxIH0phtw5VNqgY2Z',
		"apiKey"            => 'AIzaSyCYkikCSaf91MbO6f3xEkUgFRDqHeNZgNE',
		"authDomain"        => 'revo-shine.firebaseapp.com',
		"databaseURL"       => 'https://revo-shine.firebaseio.com',
		"projectId"         => 'revo-shine',
		"storageBucket"     => 'revo-shine.appspot.com',
		"messagingSenderId" => '201918651575',
		"appId"             => '1:201918651575:web:dda924debfb0121cf3c132',
		"measurementId"     => 'G-HNR4L3Z0JE',
	);

	if (isset($key->firebase_server_key)) {
		$result['serverKey'] = $key->firebase_server_key;
	}

	if (isset($key->firebase_api_key)) {
		$result['apiKey'] = $key->firebase_api_key;
	}

	if (isset($key->firebase_auth_domain)) {
		$result['authDomain'] = $key->firebase_auth_domain;
	}

	if (isset($key->firebase_database_url)) {
		$result['authDomain'] = $key->firebase_database_url;
	}

	if (isset($key->firebase_database_url)) {
		$result['databaseURL'] = $key->firebase_database_url;
	}

	if (isset($key->firebase_project_id)) {
		$result['projectId'] = $key->firebase_project_id;
	}

	if (isset($key->firebase_storage_bucket)) {
		$result['storageBucket'] = $key->firebase_storage_bucket;
	}

	if (isset($key->firebase_messaging_sender_id)) {
		$result['messagingSenderId'] = $key->firebase_messaging_sender_id;
	}

	if (isset($key->firebase_app_id)) {
		$result['appId'] = $key->firebase_app_id;
	}

	if (isset($key->firebase_measurement_id)) {
		$result['measurementId'] = $key->firebase_measurement_id;
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_token_user_firebase($type = 'rest')
{
	global $wpdb;

	$data['token'] = cek_raw('token');
	$cookie        = cek_raw('cookie');

	$result = ['status' => 'error', 'message' => 'Gagal Input Token !'];
	$insert = true;

	if (!empty($data['token'])) {
		if ($cookie) {
			$user_id = wp_validate_auth_cookie($cookie, 'logged_in');
			if ($user_id) {
				$data['user_id'] = $user_id;
				$get             = get_user_token(" WHERE user_id = '$user_id'  ");
				if (!empty($get)) {
					$insert = false;
					$wpdb->update('revo_token_firebase', $data, ['user_id' => $user_id]);
					if (@$wpdb->show_errors == false) {
						$result = ['status' => 'success', 'message' => 'Update Token Berhasil !'];
					}
				}
			}
		}

		if ($insert) {

			$data_delete = $data['token'];
			$wpdb->query($wpdb->prepare("DELETE FROM revo_token_firebase WHERE token = '$data_delete'"));

			$wpdb->insert('revo_token_firebase', $data);
			if (@$wpdb->show_errors == false) {
				$result = ['status' => 'success', 'message' => 'Insert Token Berhasil !'];
			}
		}
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_check_variation($request)
{
	$product_id = $request['product_id'];
	$variation  = $request['variation'];

	$result = ['status' => 'error', 'variation_id' => 0];

	if (!empty($product_id) && !empty($variation)) {
		$data = [];
		foreach ($variation as $var) {
			$data['attribute_' . str_replace(" ", "-", strtolower($var['column_name']))] = $var['value'];
		}

		if (!empty($data)) {
			$product_object = wc_get_product($product_id);
			if (!$product_object) {
				return ['status' => 'error', 'message' => 'product not found !'];
			}

			$data_store   = new WC_Product_Data_Store_CPT();
			$variation_id = $data_store->find_matching_product_variation($product_object, $data);

			if (isset($variation_id) && $variation_id) {
				$product_variation      = wc_get_product($variation_id);
				$result['status']       = 'success';
				$result['variation_id'] = $variation_id;
				$result['data']         = load_revo_flutter_mobile_app()->reformat_product_result($product_variation);
			}
		}
	}

	return new WP_REST_Response($result, 200);
}

function rest_list_orders($request)
{
	global $wpdb;

	$cookie   = $request['cookie'];
	$page     = $request['page'];
	$limit    = $request['limit'];
	$order_by = $request['order_by'];
	$order_id = $request['order_id'];
	$status   = $request['status'];
	$search   = $request['search'];

	if (empty($cookie)) {
		return [];
	}

	$user_id = wp_validate_auth_cookie($cookie, 'logged_in');
	$revo_loader = load_revo_flutter_mobile_app();

	$fox_currency_service = Revo_Shine_Fox_Currency::instance();
	if ($fox_currency_service->get_plugin_status()) {
		$currencies = $fox_currency_service->get_currencies();
	}

	if ($order_id) { // get single order by id
		$customer_orders = wc_get_order($order_id);

		if ($customer_orders) {
			$get = $revo_loader->get_formatted_item_data($customer_orders);

			if ($meta_link = $customer_orders->get_meta('Xendit_invoice_url')) {
				$payment_link = $meta_link;
			} elseif ($customer_orders->get_meta('_mt_payment_url')) {
				$payment_link = $meta_link;
			} elseif ($customer_orders->get_payment_method_title() === 'razorpay') {
				$payment_link = get_site_url() . "/checkout/order-pay/" . $customer_orders->get_id() . "/?key=" . $customer_orders->get_order_key();
			} else {
				$payment_link = "";
			}

			foreach ($get['meta_data'] as $meta) {
				if (!is_string($meta->value)) {
					$meta->value = "";
				}
			}

			if ($get['customer_id'] == $user_id) {
				$get['payment_link'] 	= $payment_link;
				$get['currency_detail'] = isset($currencies) ? $currencies[$get['currency']] : null;
				$result[]            	= $get;
			}
		}
	} else {
		if (empty($search)) {
			$args = array(
				'orderby'     => 'date',
				'order'       => $order_by ? $order_by : "DESC",
				'customer_id' => $user_id,
				'page'        => $page ? $page : "1",
				'limit'       => $limit ? $limit : "10",
				'parent'      => 0
			);

			if ($status) {
				// Order status. Options: pending, processing, on-hold, completed, cancelled, refunded, failed,trash. Default is pending.
				$args['status'] = $status;
			}

			$customer_orders = wc_get_orders($args);

			foreach ($customer_orders as $value) {
				$get = $revo_loader->get_formatted_item_data($value);

				if ($get && !empty($get['line_items'])) {
					// Payment link
					if ($meta_link = $value->get_meta('Xendit_invoice_url')) {
						$payment_link = $meta_link;
					} else if ($meta_link = $value->get_meta('_mt_payment_url')) {
						$payment_link = $meta_link;
					} else if ($value->get_payment_method_title() === 'razorpay') {
						$payment_link = get_site_url() . "/checkout/order-pay/" . $value->get_id() . "/?key=" . $value->get_order_key();
					} else {
						$payment_link = "";
					}

					foreach ($get['meta_data'] as $meta) {
						if (!is_string($meta->value)) {
							$meta->value = "";
						}
					}

					$get['payment_link']    = $payment_link;
					$get['currency_detail'] = isset($currencies) ? $currencies[$get['currency']] : null;
					$result[]            	= $get;
				}
			}
		} else {
			$table_post       = $wpdb->prefix . 'postmeta';
			$table_order_item = $wpdb->prefix . 'woocommerce_order_items';

			$pagination = '';
			if (!empty($page) || !empty($limit)) {
				$page = ($page - 1) * $limit;

				$pagination = "LIMIT $page, $limit";
			}

			$where = "WHERE pm.post_id LIKE '%{$search}%' OR pm.meta_key = '_billing_phone' AND pm.meta_value LIKE '%{$search}%' OR oi.order_item_name LIKE '%{$search}%'";

			$post_meta_query = $wpdb->get_results("SELECT pm.post_id, oi.order_item_name FROM {$table_post} pm INNER JOIN {$table_order_item} oi ON pm.post_id = oi.order_id {$where} GROUP BY pm.post_id {$pagination}", OBJECT);

			foreach ($post_meta_query as $query) {
				$_order = wc_get_order($query->post_id);
				if (!$_order) {
					continue;
				}

				$get = $revo_loader->get_formatted_item_data($_order);

				if ($get && !empty($get['line_items'])) {
					if (!empty($status) && $status !== $get['status']) {
						continue;
					}

					// Payment link
					if ($meta_link = $_order->get_meta('Xendit_invoice_url')) {
						$payment_link = $meta_link;
					} else if ($meta_link = $_order->get_meta('_mt_payment_url')) {
						$payment_link = $meta_link;
					} else if ($_order->get_payment_method_title() === 'razorpay') {
						$payment_link = get_site_url() . "/checkout/order-pay/" . $_order->get_id() . "/?key=" . $_order->get_order_key();
					} else {
						$payment_link = "";
					}

					foreach ($get['meta_data'] as $meta) {
						if (!is_string($meta->value)) {
							$meta->value = "";
						}
					}

					$get['payment_link'] 	= $payment_link;
					$get['currency_detail'] = isset($currencies) ? $currencies[$get['currency']] : null;
					$result[] 			  	= $get;
				}
			}
		}
	}

	return new WP_REST_Response($result ?? [], 200);
}

function rest_list_review($type = 'rest')
{
	$result = ['status' => 'error', 'message' => 'Login required !'];

	$cookie  = cek_raw('cookie');
	$limit   = cek_raw('limit');
	$post_id = cek_raw('post_id');
	$limit   = cek_raw('limit');
	$page    = cek_raw('page');

	$args = [
		'number'      => $limit,
		'status'      => 'approve',
		'post_status' => 'publish',
		'post_type'   => 'product',
	];

	if ($post_id) {
		$args['post_id'] = $post_id;
	}

	if ($limit) {
		$args['number'] = $limit;
	}

	if ($page) {
		$args['offset'] = $page;
	}

	if ($cookie) {
		$user_id = wp_validate_auth_cookie($cookie, 'logged_in');

		if ($user_id) {
			$args['user_id'] = $user_id;

			$comments = get_comments($args);

			$result = [];
			foreach ($comments as $comment) {
				$product = wc_get_product($comment->comment_post_ID);

				$media_src = [];
				$media_caption = [];

				if (!empty($images = get_comment_meta($comment->comment_ID, 'reviews-images', true))) {
					foreach ($images as $image) {
						$image_post = get_post($image);
						$media = wp_get_attachment_url($image, 'full');

						array_push($media_src, $media);
						array_push($media_caption, $image_post->post_excerpt);
					}
				}

				array_push($result, [
					'product_id'     => $comment->comment_post_ID,
					'title_product'  => $product->get_name(),
					'image_product'  => wp_get_attachment_image_src($product->get_image_id(), 'full')[0],
					'review_title'	 => get_comment_meta($comment->comment_ID, 'wcpr_review_title', true) ?? "",
					'content'        => $comment->comment_content,
					'star'           => get_comment_meta($comment->comment_ID, 'rating', true),
					'verified'       => 'yes' === get_option('woocommerce_review_rating_verification_label') && 1 == get_comment_meta($comment->comment_ID, 'verified', true),
					'comment_author' => apply_filters('comment_author', $comment->comment_author, $comment->comment_ID),
					'user_id'        => $comment->user_id,
					'comment_date'   => $comment->comment_date,
					'media' 	     => count($media_src) ? $media_src : [],
					'media_caption'	 => $media_caption
				]);
			}
		}
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_list_notification($type = 'rest')
{

	global $wpdb;


	$result = ['status' => 'error', 'message' => 'Login required !'];

	$cookie = cek_raw('cookie');

	if ($cookie) {

		$user_id = wp_validate_auth_cookie($cookie, 'logged_in');

		$data_notification = $wpdb->get_results("SELECT * FROM revo_notification WHERE user_id = '$user_id' AND type = 'order'  AND is_read = 0 ORDER BY created_at DESC", OBJECT);

		$revo_loader = load_revo_flutter_mobile_app();
		$result      = [];
		foreach ($data_notification as $key => $value) {
			$order_id     = (int) $value->target_id;
			$imageProduct = "";
			if ($order_id && $imageProduct == "") {
				$customer_orders = wc_get_order($order_id);
				if ($customer_orders) {
					$get = $revo_loader->get_formatted_item_data($customer_orders);
					if (isset($get["line_items"])) {
						for ($i = 0; $i < count($get["line_items"]); $i++) {
							$image_id     = wc_get_product($get["line_items"][$i]["product_id"])->get_image_id();
							$imageProduct = wp_get_attachment_image_url($image_id, 'full') ?? query_revo_mobile_variable("'logo'")[0]->image;
						}
					}
				}
			}

			array_push($result, [
				'user_id'    => (int) $value->product_id,
				'order_id'   => (int) $value->target_id,
				'status'     => $value->message,
				'image'      => $imageProduct,
				'created_at' => $value->created_at,
			]);
		}
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_list_notification_new($type = 'rest')
{

	global $wpdb;

	$result = ['status' => 'error', 'message' => 'Login required !'];

	$cookie = cek_raw('cookie');

	if ($cookie) {

		$user_id = wp_validate_auth_cookie($cookie, 'logged_in');

		if (!$user_id) {
			return [
				'status'  => 'error',
				'message' => 'Invalid authentication cookie. Please log out and try to login again!'
			];
		}

		$result = [];

		$notifications = $wpdb->get_results("SELECT * FROM revo_push_notification WHERE user_id LIKE '%\"$user_id\"%' AND type = 'push_notif' or user_id = $user_id AND type = 'order' ORDER BY created_at DESC", OBJECT);

		foreach ($notifications as $notif) {
			if (!in_array($notif->type, array('order', 'push_notif'))) {
				continue;
			}

			if ($notif->type === 'order') {
				$_description = json_decode($notif->description, true);
				$order        = wc_get_order($_description['order_id']);

				if (empty($order) || $order === false) {
					continue;
				}

				$_product = array_values($order->get_items());
				if (empty($_product)) {
					continue;
				}

				foreach ($_product as $value) {
					$_product = $value->get_data();
					break;
				}

				$product = wc_get_product($_product['variation_id'] == 0 ? $_product['product_id'] : $_product['variation_id']);

				$description = [
					"title"       => "Order #" . $_description['order_id'],
					"link_to"     => $_description['order_id'],
					"description" => $_description['status'],
					"image"       => query_revo_mobile_variable("'logo'")[0]->image,
				];

				if ($product) {
					$product_image_id = $product->get_image_id();
					$product_image    = wp_get_attachment_image_url($product_image_id, 'full');

					if ($product_image !== false) {
						$description['image'] = $product_image;
					}
				}

				$is_read = is_null($notif->user_read) ? 0 : 1;
			}

			if ($notif->type === 'push_notif') {
				$users_read  = json_decode($notif->user_read, true)['users'];
				$description = unserialize($notif->description);

				if ($description === false) {
					$description = json_decode($notif->description, true);
				}

				if (base64_encode(base64_decode($description['title'], true)) === $description['title']) {
					$description['title'] = base64_decode($description['title']);
				}

				if (base64_encode(base64_decode($description['description'], true)) === $description['description']) {
					$description['description'] = base64_decode($description['description']);
				}

				$is_read = is_null($users_read) || !in_array($user_id, $users_read) ? 0 : 1;

				$description['title'] = stripslashes($description['title']);
				$description['description'] = stripslashes($description['description']);
			}

			array_push($result, [
				'id'          => $notif->id,
				'type'        => $notif->type,
				'user_id'     => $user_id,
				'description' => $description,
				'is_read'     => $is_read,     // 1 = read, 0 = unread
				'created_at'  => $notif->created_at,
			]);
		}
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_read_notification($type = 'rest')
{

	global $wpdb;

	$result = ['status' => 'error', 'message' => 'Login required !'];

	$cookie = cek_raw('cookie');
	$id     = cek_raw('id');
	$type   = cek_raw('type');

	if ($cookie) {
		// $data['is_read'] = 1;
		// $wpdb->update('revo_notification', $data, [
		// 	'id' => $id,
		// 	'user_id' => $user_id,
		// ]);

		$user_id = wp_validate_auth_cookie($cookie, 'logged_in');

		if (!$user_id) {
			return [
				'status'  => 'error',
				'message' => 'Invalid authentication cookie. Please log out and try to login again!'
			];
		}

		$get_row = $wpdb->get_row("SELECT id, type, user_id, user_read FROM revo_push_notification WHERE id = $id");

		if (!empty($get_row)) {
			$users_id   = $type === 'order' ? (array) $get_row->user_id : (json_decode($get_row->user_id, true)['users'] ?? []);
			$users_read = $type === 'order' ? (array) $get_row->user_read : (json_decode($get_row->user_read, true)['users'] ?? []);

			if (in_array($user_id, $users_id) && !in_array($user_id, $users_read)) {
				$data = [];

				if ($type === 'order') {
					$data['user_read'] = $user_id;
				}

				if ($type === 'push_notif') {
					if (is_null($users_read)) {
						$data['user_read'] = json_encode(["users" => ["$user_id"]]);
					} else {
						array_push($users_read, (string) $user_id);
						$data['user_read'] = json_encode(["users" => $users_read]);
					}
				}

				$wpdb->update('revo_push_notification', $data, ['id' => $id]);
			}
		}

		if (@$wpdb->show_errors == false) {
			$result = ['status' => 'success', 'message' => 'Berhasil Dibaca !'];
		}
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_disabled_service($type = 'rest')
{
	global $wpdb;


	$result = ['status' => 'error', 'message' => 'Cabut License Gagal !'];

	$query = "SELECT * FROM `revo_mobile_variable` WHERE slug = 'license_code'";
	$get   = $wpdb->get_row($query, OBJECT);
	if (!empty($get->description)) {
		$get = json_decode($get->description);
		if (!empty($get)) {
			if ($get = $get->license_code == cek_raw('code')) {
				if ($get) {
					$data = data_default_seeder('license_code');
					$wpdb->update('revo_mobile_variable', $data, ['slug' => 'license_code']);
					if (@$wpdb->show_errors == false) {
						$result = ['status' => 'success', 'message' => 'Cabut License Berhasil !'];
					}
				}
			}
		}
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_topup_woowallet($type = 'rest')
{

	global $wpdb;

	$cookie = false;

	if (isset($_GET['cookie'])) {
		$cookie = $_GET['cookie'];
	}

	if ($cookie) {
		$userId = wp_validate_auth_cookie($cookie, 'logged_in');

		if (!$userId) {
			echo "Invalid authentication cookie. Please try to login again!";

			return;
		}
		// Check user and authentication
		$user = get_userdata($userId);
		if ($user) {
			wp_set_current_user($userId, $user->user_login);
			wp_set_auth_cookie($userId);
		}
	}


	$urlAccount = get_permalink(get_option('woocommerce_myaccount_page_id')) . 'woo-wallet/add';
	wp_redirect($urlAccount);
	exit();
}

function rest_transfer_woowallet($type = 'rest')
{

	global $wpdb;

	$cookie = false;

	if (isset($_GET['cookie'])) {
		$cookie = $_GET['cookie'];
	}

	if ($cookie) {
		$userId = wp_validate_auth_cookie($cookie, 'logged_in');

		if (!$userId) {
			echo "Invalid authentication cookie. Please try to login again!";

			return;
		}
		// Check user and authentication
		$user = get_userdata($userId);
		if ($user) {
			wp_set_current_user($userId, $user->user_login);
			wp_set_auth_cookie($userId);
		}
	}


	$urlAccount = get_permalink(get_option('woocommerce_myaccount_page_id')) . 'woo-wallet/transfer';
	wp_redirect($urlAccount);
	exit();
}

function rest_data_attribute_bycategory($type = 'rest')
{

	global $wpdb;


	$category = cek_raw('category');

	if (!empty($category)) {
		$categories     = explode(',', $category);
		$categoriesSlug = [];
		if (is_array($categories)) {
			for ($i = 0; $i < count($categories); $i++) {
				$term = get_term_by('id', $categories[$i], 'product_cat', 'ARRAY_A');
				if (!empty($term)) {
					$categoriesSlug[] = $term['slug'];
				}
			}
		}
		$args['category'] = $categoriesSlug;
	}

	$args['status'] = 'publish';

	$result = array();

	foreach (wc_get_products($args) as $product) {

		$all_prices[] = $product->get_price();

		foreach ($product->get_attributes() as $taxonomy => $attribute) {
			$attribute_name = wc_attribute_label($taxonomy);

			foreach ($attribute->get_terms() as $term) {

				$args['tax_query'] = array(
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'slug',
						'terms'    => array($term->name),
						'operator' => 'IN',
					),
				);

				$products = wc_get_products($args);

				$data_filter[]   = array($taxonomy, $attribute_name, $term->term_id, $term->name, count($products));
				$data_taxonomy[] = array($taxonomy, $attribute_name);
			}
		}
	}

	$data_taxonomy = array_map("unserialize", array_unique(array_map("serialize", $data_taxonomy)));

	$data_filter = array_map("unserialize", array_unique(array_map("serialize", $data_filter)));

	for ($i = 0; $i < (count($data_filter) - 1); $i++) {

		$taxonomy       = $data_filter[$i][0];
		$attribute_name = $data_filter[$i][1];
		$term_id        = $data_filter[$i][2];
		$name           = $data_filter[$i][3];
		$count          = $data_filter[$i][4];

		if (!empty($taxonomy)) {
			$result['data_filter'][$taxonomy][] = array(
				'attribute_name' => $attribute_name,
				'term_id'        => $term_id,
				'name'           => $name,
				'product_count'  => $count
			);
		}
	}

	$result['range_price'] = ['min_price' => floor(min($all_prices)), 'max_price' => ceil(max($all_prices))];

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_data_woo_discount_rules($type = 'rest')
{

	global $wpdb;


	$woo_discount = get_option('woo-discount-config-v2')['calculate_discount_from'];

	if (!empty($woo_discount)) {
		$result['calculate_discount_from'] = !empty($woo_discount) ? $woo_discount : 'sale_price';


		$table_name = $wpdb->prefix . 'wdr_rules';

		$get_rules = $wpdb->get_results("SELECT * FROM `$table_name` WHERE `enabled` = 1", OBJECT);

		if (!empty($get_rules)) {

			foreach ($get_rules as $value) {

				$rules = json_decode($value->bulk_adjustments);

				$operator = $rules->operator;

				$ranges = $rules->ranges;

				if ($operator == 'product_cumulative') {
					$result['discount_rules'] = array('operator' => $operator, 'ranges' => $ranges);
				} elseif ($operator == 'variation') {
					$result['discount_rules'] = array('operator' => $operator, 'ranges' => $ranges);
				} elseif ($operator == 'product') {
					$result['discount_rules'] = array('operator' => $operator, 'ranges' => $ranges);
				}
			}
		} else {
			$result = ['status' => 'error', 'message' => 'data not found !'];
		}
	} else {
		$result = ['status' => 'error', 'message' => 'plugin woo discount rules not installed !'];
	}

	return $result;
}

function rest_list_user_chat($type = 'rest')
{

	$result = check_live_chat();

	if ($result == 1) {

		$cookie        = cek_raw('cookie');
		$search        = cek_raw('search');
		$incoming_chat = cek_raw('incoming_chat');

		$result = ['status' => 'error', 'message' => 'Login required !'];

		if ($cookie) {
			global $wpdb;

			$user_id = wp_validate_auth_cookie($cookie, 'logged_in');
			$user    = get_userdata($user_id);

			if (in_array('administrator', $user->roles) || in_array('shop_manager', $user->roles)) {
				$first_admin = get_users([
					'role'    => 'administrator',
					'orderby' => [
						'ID' => 'ASC'
					]
				])[0];

				$user_id = $first_admin->ID;
			}

			$result = ['status' => 'error', 'message' => 'User Not Found !'];
			if ($user_id) {
				$get         = get_conversations($user_id);
				$result      = [];
				$revo_loader = load_revo_flutter_mobile_app();
				foreach ($get as $index => $key) {
					$user_message_id = $key->receiver_id;
					if ($key->status == 'seller') {
						$user_message_id = $key->sender_id;
					}

					$user = get_userdata($user_message_id);

					$role = "";

					if ($user) {
						if ($user->roles[0] == "administrator") {
							$role = " (admin)";
						} elseif ($user->roles[0] == "customer") {
							$role = " (cust)";
						}

						$username = $user->display_name . $role;
					} else {
						$username = "[ account has been deleted ]";
					}

					$photo = get_avatar_url($user_message_id);

					// if($key->status == 'seler'){
					// $_POST['disabled_cookie'] = true;
					// $get = $revo_loader->get_wcfm_vendor_list(1,$user_message_id);
					//
					// if (!empty($get)) {
					// $photo = $get[0]['icon'];
					// $username = $get[0]['name'];
					// }
					// }

					if (!empty($search) && strpos($username, $search) || (empty($search))) {

						$result[$index]['id']           = $key->id;
						$result[$index]['receiver_id']  = $user_message_id;
						$result[$index]['photo']        = $photo;
						$result[$index]['user_name']    = $username;
						$result[$index]['status']       = $key->status;
						$result[$index]['last_message'] = $key->last_message;
						$result[$index]['time']         = $key->created_chat;
						$result[$index]['unread']       = $key->unread;
					}
				}

				if ($incoming_chat && !empty($incoming_chat)) {
					$result_incoming = array_filter($result, function ($arr) {
						return $arr['unread'] >= 1;
					});

					$result_incoming_re_index = array_values($result_incoming);

					usort($result_incoming_re_index, function ($a, $b) {
						$t1 = strtotime($a['time']);
						$t2 = strtotime($b['time']);

						return $t2 - $t1;
					});

					$result = $result_incoming_re_index[0] ?? (object) [];
				}
			}
		}
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_detail_chat($type = 'rest')
{

	$cookie      = cek_raw('cookie');
	$chat_id     = cek_raw('chat_id');
	$receiver_id = cek_raw('receiver_id');

	// $result = ['status' => 'error','message' => 'Login required !'];

	if ($cookie) {
		global $wpdb;

		$user_id = wp_validate_auth_cookie($cookie, 'logged_in');
		$user    = get_userdata($user_id);

		// $result = ['status' => 'error','message' => 'User Not Found !'];
		$first_admin = get_users([
			'role'    => 'administrator',
			'orderby' => [
				'ID' => 'ASC'
			]
		])[0];

		if (!in_array('administrator', $user->roles) && !in_array('shop_manager', $user->roles)) {
			$receiver_id = $first_admin->ID;
		} elseif (in_array('administrator', $user->roles) || in_array('shop_manager', $user->roles)) {
			$user_id = $first_admin->ID;
		}

		if ($user_id && $receiver_id && empty($chat_id)) {
			$get_message = get_conversations($user_id, $receiver_id);
			if (empty($get_message)) {
				$wpdb->insert(
					'revo_conversations',
					[
						'sender_id'   => $user_id,
						'receiver_id' => $receiver_id,
					]
				);

				$chat_id = $wpdb->insert_id;
			} else {
				$chat_id = $get_message->id;
			}
		}

		if ($user_id && $chat_id) {
			$results     = get_conversations_detail($user_id, $chat_id);
			$revo_loader = load_revo_flutter_mobile_app();

			$i = 0;

			foreach ($results as $index => $key) {

				$date = substr($key->created_at, 0, 10);
				$time = substr($key->created_at, -8, -3);

				$key->time = $time;

				if (empty($key->image)) {
					$key->image = null;
				}

				if ($key->type == 'product') {
					$product = wc_get_product($key->post_id);

					$product_data = [
						'id'          => $key->post_id > 0 ? $key->post_id : 0,
						'name'        => 'Product Deleted',
						'price'       => 0,
						'image_first' => revo_shine_url() . '/assets/images/noimage.png'
					];

					if ($key->post_id > 0 && $product) {
						$image_first = wp_get_attachment_image_url($product->get_image_id(), 'full');

						$product_data['id']          = $product->get_id();
						$product_data['name']        = $product->get_name();
						$product_data['price']       = $product->get_price();
						$product_data['image_first'] = $image_first == false ? revo_shine_url() . '/assets/images/noimage.png' : $image_first;
					}

					$key->subject = array(
						'id'          => (int) $product_data['id'],
						'name'        => $product_data['name'],
						'status'      => 'Product',
						'price'       => (float) $product_data['price'],
						'image_first' => $product_data['image_first']
					);
				} elseif ($key->type == 'order') {

					$customer_orders = wc_get_order($key->post_id);
					if ($customer_orders) {
						$get = $revo_loader->get_formatted_item_data($customer_orders);
						if (isset($get["line_items"])) {
							for ($i = 0; $i < count($get["line_items"]); $i++) {
								$image_id                         = wc_get_product($get["line_items"][$i]["product_id"])->get_image_id();
								$get["line_items"][$i]['image'] = wp_get_attachment_image_url($image_id, 'full');
							}
						}

						$image_first = $get['line_items'][0]['image'];

						$key->subject = array(
							'id'          => $get['id'],
							'name'        => 'Order ID : ' . $get['id'],
							'status'      => $get['status'],
							'price'       => $get['total'],
							'image_first' => $image_first == false ? revo_shine_url() . '/assets/images/noimage.png' : $image_first
						);
					}
				} else {
					$key->subject = null;
				}

				$chat[$date][] = $key;
				$chat_date[]     = $date;
			}

			$dates = array_values(array_unique($chat_date));

			foreach ($dates as $date) {
				$result[] = array(
					'date' => $date,
					'chat' => $chat[$date],
				);
			}
		}
	}

	if (empty($result)) {
		$result = [];
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_insert_chat($type = 'rest')
{
	global $wpdb;


	$cookie  = cek_raw('cookie');
	$message = cek_raw('message');
	// seller_id & product_id opsional
	$post_id     = cek_raw('post_id');
	$receiver_id = cek_raw('receiver_id');
	$type        = !empty(cek_raw('type')) ? cek_raw('type') : "chat";
	$image       = cek_raw('image');

	// $result = ['status' => 'error','message' => 'Target ID Required ! product_id & receiver_id'];
	// if (!empty($post_id) AND !empty($receiver_id)) {

	$result = ['status' => 'error', 'message' => 'Login required !'];
	if ($cookie) {
		$user_id = wp_validate_auth_cookie($cookie, 'logged_in');
		$user    = get_userdata($user_id);

		$result = ['status' => 'error', 'message' => 'User Not Found !'];
		if ($user_id) {

			$first_admin = get_users([
				'role'    => 'administrator',
				'orderby' => [
					'ID' => 'ASC'
				]
			])[0];

			if (!in_array('administrator', $user->roles) && !in_array('shop_manager', $user->roles)) {
				$receiver_id = $first_admin->ID;
			} elseif (in_array('administrator', $user->roles) || in_array('shop_manager', $user->roles)) {
				$user_id = $first_admin->ID;
			}

			$result = ['status' => 'error', 'message' => 'Receiver_id & Type Required'];
			if (!empty($receiver_id) && !empty($type)) {
				$get_message = get_conversations($user_id, $receiver_id);
				$result      = ['status' => 'error', 'message' => 'system error !'];
				if (empty($get_message)) {
					$wpdb->insert(
						'revo_conversations',
						[
							'sender_id'   => $user_id,
							'receiver_id' => $receiver_id,
						]
					);

					$conversation_id = $wpdb->insert_id;
				} else {
					$conversation_id = $get_message->id;
				}

				if ($conversation_id && (!empty($message) || !empty($image))) {
					$date = new DateTime('now', new DateTimeZone(wp_timezone()->getName()));
					$wpdb->insert(
						'revo_conversation_messages',
						[
							'conversation_id' => $conversation_id,
							'sender_id'       => $user_id,
							'receiver_id'     => $receiver_id,
							'message'         => $message,
							'type'            => $type,
							'image'           => $image,
							'post_id'         => $post_id,
							'is_read'         => 1,
							'created_at'      => $date->format('Y-m-d H:i:s')
						]
					);

					// send push notif chat
					$notification = array(
						'title' => "New Message",
						'body'  => (isset($message) ? $message : ''),
						/* 'icon' => revo_shine_get_logo(),
						'image' => (isset($data_notif->image) ? $data_notif->image : revo_shine_get_logo()) */
					);

					$where_receiver_id = ($receiver_id == $first_admin->ID ? wp_validate_auth_cookie($cookie, 'logged_in') : $receiver_id);

					$extend['id']           = $where_receiver_id;
					$extend['type']         = "chat";
					$extend['click_action'] = (isset($conversation_id) ? 'chat/' . $conversation_id : '');

					$where = "where user_id = $where_receiver_id";

					if ($receiver_id == $first_admin->ID) {
						$get = pos_get_user_token();
					} else {
						$get = get_user_token($where);
					}

					foreach ($get as $key) {

						$status_send = revo_shine_fcm_api_v1($notification, $extend, $key->token);

						if ($status_send == 'error') {
							$alert = array(
								'type'    => 'error',
								'title'   => 'Failed to Send Notification !',
								'message' => "Try Again Later",
							);
						}
					}
				}

				$result = ['status' => 'success', 'message' => 'success input message !'];
			}
		}
	}


	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_list_users($type = 'rest')
{

	$cookie = cek_raw('cookie');
	$role   = cek_raw('role');

	if ($cookie) {

		global $wpdb;

		$user_id = wp_validate_auth_cookie($cookie, 'logged_in');

		$args = array(
			// 'role'    => 'administrator'
			'role' => $role
		);

		$users = get_users($args);

		foreach ($users as $user) {

			$data = array(
				'id_user'      => $user->data->ID,
				'display_name' => $user->data->display_name,
				'photo'        => get_avatar_url($user->data->ID)
			);

			$result[] = $data;
		}
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_delete_account($type = 'rest')
{

	global $wpdb;


	$cookie = cek_raw('cookie');

	$result = ['status' => 'error', 'message' => 'you must include cookie !'];
	if ($cookie) {
		$user_id = wp_validate_auth_cookie($cookie, 'logged_in');

		if (!$user_id) {
			return ['status' => 'error', 'message' => 'User Tidak ditemukan !'];
		}

		$result = ['status' => 'error', 'message' => 'User gagal dihapus'];

		require_once(ABSPATH . 'wp-admin/includes/user.php');

		if (wp_delete_user($user_id)) {
			$result = ['status' => 'success', 'message' => 'User berhasil dihapus'];
		}

		// $table_name1 = $wpdb->prefix . 'users';
		// $wpdb->query($wpdb->prepare("DELETE FROM $table_name1 WHERE id = $user_id"));

		// $table_name2 = $wpdb->prefix . 'usermeta';
		// $wpdb->query($wpdb->prepare("DELETE FROM $table_name2 WHERE user_id = $user_id"));

		// $result = ['status' => 'success','message' => 'User berhasil dihapus'];
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_post_customer_address($request)
{
	$cookie = $request['cookie'];

	if (empty($cookie)) {
		return ['status' => 'error', 'message' => 'you must include cookie !'];
	}

	$action  = $request['action'] ?? 'billing'; // shipping / billing
	$user_id = wp_validate_auth_cookie($cookie, 'logged_in');

	if (!$user_id) {
		return ['status' => 'success', 'message' => 'User tidak ditemukan'];
	}

	$metas = [
		"{$action}_first_name" => $request['first_name'],
		"{$action}_last_name"  => $request['last_name'],
		"{$action}_company"    => $request['company'],
		"{$action}_address_1"  => $request['address_1'],
		"{$action}_address_2"  => $request['address_2'],
		"{$action}_city"       => $request['city'],
		"{$action}_postcode"   => $request['postcode'],
		"{$action}_country"    => $request['country'],
		"{$action}_state"      => $request['state'],
		"{$action}_phone"      => $request['phone'],
		"{$action}_email"      => $request['email'],
	];

	if (isset($request['location_coordinate']) && !empty($request['location_coordinate']['address'])) {
		foreach (['billing', 'shipping'] as $meta_action) {
			$metas["{$meta_action}_biteship_address"]  = $request['address_1'];
			$metas["{$meta_action}_biteship_city"] 	   = $request['city'];
			$metas["{$meta_action}_biteship_province"] = $request['state'];
			$metas["{$meta_action}_biteship_zipcode"]  = $request['postcode'];
			$metas["{$meta_action}_biteship_location"] = $request['location_coordinate']['address'];
			$metas["{$meta_action}_biteship_location_coordinate"] = $request['location_coordinate']['latitude'] . ',' . $request['location_coordinate']['longitude'];
		}
	}

	foreach ($metas as $key => $value) {
		if (!is_null($value)) {
			update_user_meta($user_id, $key, $value);
		}
	}

	return new WP_REST_Response(['status' => 'success', 'message' => 'Data successfully changed !'], 200);
}

function rest_show_reviews_product($type = 'rest')
{
	$user_id    = $_GET['user_id'];
	$product_id = $_GET['product'];

	$result = ['status' => 'error', 'message' => 'you must include parameter product !'];

	if (isset($product_id) && $product_id != "") {
		$reviews = get_comments(['post_id' => $product_id, 'status' => 'approve']);
		$photoreview_premium_is_active = is_plugin_active('woocommerce-photo-reviews/woocommerce-photo-reviews.php');

		$datas = [];
		foreach ($reviews as $review) {
			$media_src = [];
			$media_caption = [];

			if (!empty($images = get_comment_meta($review->comment_ID, 'reviews-images', true))) {
				foreach ($images as $image) {
					$image_post = get_post($image);
					$media = wp_get_attachment_url($image, 'full');

					array_push($media_src, $media);
					array_push($media_caption, $image_post->post_excerpt);
				}
			}

			if ($photoreview_premium_is_active) {

				$user = get_userdata($user_id);

				if ($user) {
					$vote_info = $user->ID;
				} else {
					$vote_info = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_the_user_ip();
				}

				$up_votes	= get_comment_meta($review->comment_ID, 'wcpr_vote_up', false);
				$down_votes = get_comment_meta($review->comment_ID, 'wcpr_vote_down', false);

				$helpful_button = [
					'user_vote'  => in_array($vote_info, $up_votes) ? 'up' : (in_array($vote_info, $down_votes) ? 'down' : ''),
					'total_up'   => count($up_votes),
					'total_down' => count($down_votes)
				];
			}

			$data = [
				'id'                   => (int) $review->comment_ID,
				'date_created'         => wc_rest_prepare_date_response($review->comment_date),
				'date_created_gmt'     => wc_rest_prepare_date_response($review->comment_date_gmt),
				'product_id'           => (int) $review->comment_post_ID,
				'product_name'         => get_the_title((int) $review->comment_post_ID),
				'product_permalink'    => get_permalink((int) $review->comment_post_ID),
				'status'               => $review->comment_approved ? 'approved' : 'hold',
				'reviewer'             => apply_filters('comment_author', $review->comment_author, $review->comment_ID),
				'reviewer_email'       => $review->comment_author_email,
				'review_title'	 	   => get_comment_meta($review->comment_ID, 'wcpr_review_title', true) ?? "",
				'review'               => !empty($request['context']) ? $request['context'] : wpautop($review->comment_content),
				'review_parent'		   => (int) $review->comment_parent,
				'review_reply'		   => [],
				'rating'               => (int) get_comment_meta($review->comment_ID, 'rating', true),
				'verified'             => wc_review_is_from_verified_owner($review->comment_ID),
				'media'                => count($media_src) ? $media_src : [],
				'media_caption'		   => count($media_caption) ? $media_caption : [],
				'reviewer_avatar_urls' => rest_get_avatar_urls($review->comment_author_email),
				'helpful_button'	   => $helpful_button ?? [],
				'_links'               => [
					'self'       => [
						'href' => rest_url(sprintf('/%s/%s/%d', 'wc/v3', 'products/reviews', $review->comment_ID)),
					],
					'collection' => [
						'href' => rest_url(sprintf('/%s/%s', 'wc/v3', 'products/reviews')),
					],
					'up'         => [
						'href' => rest_url(sprintf('/%s/products/%d', 'wc/v3', $review->comment_post_ID))
					],
					'reviewer'   => [
						'embeddable' => true,
						'href'       => rest_url('wp/v2/users/' . $review->user_id),
					]
				]
			];

			if ($review->comment_parent != '0') {
				$reply[] = $data;
				continue;
			}

			array_push($datas, $data);
		}

		if (isset($reply) && !empty($reply)) {
			foreach ($reply as $reply_value) {
				$parent_key = array_search($reply_value['review_parent'], array_column($datas, 'id'));
				$datas[$parent_key]['review_reply'][] = $reply_value;
			}
		}

		$result = rest_ensure_response($datas);
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_products_attributes()
{
	global $wpdb;


	$attribute_slug = cek_raw('slug');

	$results                  = [];
	$get_attribute_taxonomies = wc_get_attribute_taxonomy_ids();

	if (count($get_attribute_taxonomies)) {
		foreach ($get_attribute_taxonomies as $taxonomy => $id) {
			$attribute = wc_get_attribute($id);

			if ((isset($attribute_slug) && $attribute_slug != '') && $attribute->slug != $attribute_slug) {
				continue;
			}

			$attribute->terms = get_terms([
				'taxonomy'   => $attribute->slug,
				'hide_empty' => false
			]);

			array_push($results, $attribute);
		}
	}

	return $results;
}

function rest_list_blog($type = 'rest')
{
	$lang     = $_GET['lang']     ?? '';
	$page     = $_GET['page']     ?? '';
	$post_id  = $_GET['post_id']  ?? '';
	$per_page = $_GET['per_page'] ?? '';
	$search   = $_GET['search']   ?? '';

	$args   = [];
	$result = [];

	if (isset($per_page) && $per_page != '') {
		$args['posts_per_page'] = $per_page;
	}

	if (isset($page) && $page != '') {
		$args['paged'] = $page;
	}

	if (isset($post_id) && $post_id != '') {
		$args['include'] = $post_id;
	}

	if (isset($search) && $search != '') {
		$args['s'] = $search;
	}

	if (is_plugin_active('polylang/polylang.php')) {
		if (function_exists('pll_default_language') && function_exists('pll_the_languages')) {
			$languages = pll_the_languages([
				'raw'           => true,
				'hide_if_empty' => false
			]);

			$res_lang = pll_default_language();

			if (isset($lang) && $lang != '') {
				if (array_key_exists($lang, $languages)) {
					$countPosts = pll_count_posts($lang, [
						'post_type' => 'post'
					]);

					if ($countPosts >= 1) {
						$res_lang = $lang;
					}
				}
			}

			$args['lang'] = $res_lang;

			$posts = get_posts($args);
		}
	} else if (is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
		$languages = apply_filters('wpml_active_languages', null);
		$res_lang  = apply_filters('wpml_default_language', null);

		if (isset($lang) && $lang != '') {
			if ($lang != $res_lang && array_key_exists($lang, $languages)) {
				do_action('wpml_switch_language', $lang);

				$check_post_exist = get_posts([
					'posts_per_page'   => 1,
					'suppress_filters' => false
				]);

				if ($check_post_exist >= 1) {
					$res_lang = $lang;
				}
			}
		}

		do_action('wpml_switch_language', $res_lang);

		$args['suppress_filters'] = false;

		$posts = get_posts($args);
	} else {
		$posts = get_posts($args);
	}

	if (!empty($posts)) {
		$WP_post_controller = new WP_REST_Posts_Controller('post');
		$request            = $type;

		foreach ($posts as $post) {
			$response = $WP_post_controller->prepare_item_for_response($post, $request);
			array_push($result, $WP_post_controller->prepare_response_for_collection($response));
		}
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_apply_coupon($request)
{
	$cookie 	 = $request['cookie'];
	$coupon_code = $request['coupon_code'];
	$line_items  = $request['products'];

	if (empty($coupon_code)) {
		return new WP_REST_Response(['status'  => 'error', 'message' => 'Please enter a coupon code!'], 404);
	}

	if (empty($line_items)) {
		return new WP_REST_Response(['status'  => 'error', 'message' => 'Your cart is currently empty!'], 404);
	}

	$user_id = wp_validate_auth_cookie($cookie, 'logged_in');
	if (!$user_id) {
		return new WP_REST_Response(['status'  => 'error', 'message' => 'Login is Required!'], 401);
	}

	$user = get_user_by('id', $user_id);

	$coupon      = new WC_Coupon($coupon_code);
	$coupon_data = $coupon->get_data();

	$is_coupon_valid = apply_filters('revo_shine_is_coupon_valid', true, $coupon, array('user' => $user));
	if (!$is_coupon_valid) {
		return new WP_REST_Response(['code' => 'invalid_coupon', 'message' => 'You not allowed to use this coupon', 'data' => array('status' => 400)], 400);
	}

	$products = wc_get_products([
		'status'  => 'publish',
		'include' => array_map(fn ($product) => $product['id'], $line_items),
		'orderby' => 'id',
		'order'   => 'ASC',
		'limit'   => -1
	]);

	usort($line_items, function ($a, $b) {
		return $a['id'] - $b['id'];
	});

	$items             = [];
	$wc_discount_class = new WC_Discounts('api');

	foreach ($products as $key => $product) {
		$product_price = empty($line_items[$key]['variation_id']) ? $product->get_price() : wc_get_product($line_items[$key]['variation_id'])->get_price();

		$item           = new stdClass();
		$item->key      = $product->get_id();
		$item->product  = $product;
		$item->object   = [
			'product_id' => $product->get_id(),
			'variation'  => $line_items[$key]['variation_id'],
			'quantity'   => $line_items[$key]['quantity'],
		];
		$item->quantity = $line_items[$key]['quantity'];
		$item->price    = wc_add_number_precision_deep((float) $product_price * (float) $item->quantity);

		$items[] = $item;
	}

	wp_set_current_user($user->ID, $user->user_login);
	wp_set_auth_cookie($user->ID);

	$wc_discount_class->set_items($items);
	$response = $wc_discount_class->is_coupon_valid($coupon);

	if (is_bool($response) && $response === true) {
		$wc_discount_class->apply_coupon($coupon);
		$coupon_discount_amounts = $wc_discount_class->get_discounts_by_coupon();

		return new WP_REST_Response(array_merge($coupon_data, [
			'discount_amount' => (string) $coupon_discount_amounts[$coupon_code]
		]), 200);
	}

	return new WP_REST_Response([
		'code'    => $response->get_error_code(),
		'message' => strip_tags($response->get_error_message()),
		'data'    => $response->get_error_data()
	], 400);
}

function rest_list_coupons($request)
{
	global $wpdb;

	$cookie = $request['cookie'];
	if (empty($cookie)) {
		return new WP_REST_Response(['status'  => 'error', 'message' => 'you must include cookie!'], 400);
	}

	$user_id = wp_validate_auth_cookie($cookie, 'logged_in');
	if (!$user_id) {
		return new WP_REST_Response(['status'  => 'error', 'message' => 'Login is Required!'], 401);
	}

	$user = get_user_by('id', $user_id);

	$coupon_codes = $wpdb->get_col("SELECT post_title FROM $wpdb->posts WHERE post_type = 'shop_coupon' AND post_status = 'publish' ORDER BY id DESC");
	if (empty($coupon_codes)) {
		return new WP_REST_Response(['status' => 'error', 'message' => 'No Coupon Found!'], 404);
	}

	if ($wjecf_active = is_plugin_active('woocommerce-auto-added-coupons/woocommerce-jos-autocoupon.php')) {
		wp_set_current_user($user->ID, $user->user_login);
		wp_set_auth_cookie($user->ID);

		$wjecf_class = new WJECF_Controller();
	}

	$wc_discount_class = new WC_Discounts('api');

	$list_coupons = [];
	foreach ($coupon_codes as $code) {
		$coupon = new WC_Coupon($code);
		$coupon_data = $coupon->get_data();

		if (apply_filters('revo_shine_is_coupon_valid', true, $coupon, array('user' => $user)) === false) {
			continue;
		}

		if ($wjecf_active) {
			if ($coupon_data['usage_limit'] && ($coupon_data['usage_limit'] > $coupon_data['usage_count'])) {
				if ($wjecf_class->coupon_is_valid(true, $coupon, $wc_discount_class)) {
					$list_coupons[] = $coupon_data;
				}
			} else {
				if ($wjecf_class->coupon_is_valid(true, $coupon, $wc_discount_class)) {
					$list_coupons[] = $coupon_data;
				}
			}
		} else {
			$list_coupons[] = $coupon_data;
		}
	}

	return new WP_REST_Response($list_coupons, 200);
}

function rest_states($type = 'rest')
{
	$state_id = $_GET['code'];

	$result = ['status' => 'error', 'message' => 'you must include code !'];

	if (!is_plugin_active('woongkir/woongkir.php')) {
		return ['status' => 'error', 'message' => 'Plugin woongkir inactive !'];
	}

	if (!is_null($state_id) && !empty($state_id)) {
		$province = woongkir_get_json_data('state', ['value' => strtoupper($state_id)]);

		$result = ['status' => 'error', 'message' => 'province not found !'];

		if (!empty($province)) {
			$args = [
				'state' => $province['value']
			];

			$result = [
				'code'   => $province['value'],
				'name'   => $province['label'],
				'cities' => revo_shine_get_json_data(WOONGKIR_URL . 'data/', WOONGKIR_PATH . 'data/', 'woongkir-city', $args)
			];
		}
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_cities($type = 'rest')
{
	$city_id = $_GET['id'];

	$result = ['status' => 'error', 'message' => 'you must include id !'];

	if (!is_plugin_active('woongkir/woongkir.php')) {
		return ['status' => 'error', 'message' => 'Plugin woongkir inactive !'];
	}

	if (!is_null($city_id) && !empty($city_id)) {
		$city = woongkir_get_json_data('city', ['id' => $city_id]);

		$result = ['status' => 'error', 'message' => 'city not found !'];

		if (!empty($city)) {
			$args = [
				'state'    => $city['state'],
				'state_id' => $city['state_id'],
				'city'     => $city['value'],
				'city_id'  => $city['id'],
			];

			$result = [
				'city_id'      => $city['id'],
				'city'         => $city['value'],
				'state'        => $city['state'],
				'state_id'     => $city['state_id'],
				'subdistricts' => revo_shine_get_json_data(WOONGKIR_URL . 'data/', WOONGKIR_PATH . 'data/', 'woongkir-address_2', $args)
			];
		}
	}

	if ($type == 'rest') {
		echo json_encode($result);
		exit();
	} else {
		return $result;
	}
}

function rest_get_memberships_products($request)
{
	$cookie        = $request->get_param('cookie');
	$slug_category = $request->get_param('slug_category');
	$category_id   = $request->get_param('category_id');

	if (!is_plugin_active('woocommerce-memberships/woocommerce-memberships.php')) {
		return [
			'status'  => 'error',
			'message' => 'requires plugin woocommerce-memberships'
		];
	}

	if (empty($cookie)) {
		return [
			'status'  => 'error',
			'message' => 'you must include cookie !'
		];
	}

	$args = ['membership' => true];
	$user = get_userdata(wp_validate_auth_cookie($cookie, 'logged_in'));

	if (!empty($slug_category)) {
		$args['slug_category'] = $slug_category;
	} else {
		$term = get_term(get_option('revo_membership_selected_category', ''));
		$args['slug_category'] = $term->slug;
	}

	if (!empty($category_id)) {
		$args['category_id'] = $category_id;
	}

	$revo_loader      = load_revo_flutter_mobile_app();
	$membership_plans = wc_memberships_get_membership_plans();

	$response = $revo_loader->get_products($args);

	if (!empty($response)) {
		foreach ($response as $key => $product) {
			$addon_data = [
				'plan_name' => '',
				'status'    => false,
				'end_date'  => ''
			];

			foreach ($membership_plans as $plan) {
				if ($plan->has_product($product['id'])) {
					$user_membership = wc_memberships_get_user_membership($user->ID, $plan->id);

					if (is_null($user_membership)) {
						$addon_data['plan_name'] = $plan->get_name();

						break;
					}

					$user_membership_status = $user_membership->get_status();

					if ($user_membership_status === 'active') {
						$date = $user_membership->get_local_end_date('Y-m-d H:i:s');

						if (is_null($date)) {
							$date = 'unlimited';
						}
					} else if ($user_membership_status === 'cancelled') {
						$date = $user_membership->get_local_cancelled_date('Y-m-d H:i:s');
					} else {
						$date = $user_membership->get_local_end_date('Y-m-d H:i:s');
					}

					$addon_data = [
						'plan_name' => $plan->name,
						'status'    => wc_memberships_is_user_active_member($user->ID, $plan->id),
						'end_date'  => $date
					];

					break;
				}
			}

			$response[$key]['membership'] = $addon_data;
		}
	}

	return $response;
}

function rest_vote_review($request)
{

	$cookie = $request->get_param('cookie');
	$comment_id = $request->get_param('comment_id');
	$vote = $request->get_param('vote');

	if (!is_plugin_active('woocommerce-photo-reviews/woocommerce-photo-reviews.php')) {
		return [
			'status' => 'error',
			'message' => 'requires plugin woocommerce-photo-reviews' . ' premium'
		];
	}

	if ($vote !== 'up' && $vote !== 'down') {
		return [
			'status' => 'error',
			'message' => 'vote must be up or down'
		];
	}

	if (isset($cookie) && !empty($cookie)) {
		$user = get_userdata(wp_validate_auth_cookie($cookie, 'logged_in'));
		wp_set_current_user($user->ID);
	}

	$_POST['vote'] = $vote;
	$_POST['comment_id'] = $comment_id;

	$service = new VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend();

	$response = $service->helpful_button_handle();

	wp_set_current_user(0);

	return $response;
}

// REST FOR SEARCH

function rest_search($request)
{
    $word = $request['product'];
    $page = !empty($request['page']) ? $request['page'] : 1;
    $per_page = !empty($request['per_page']) ? $request['per_page'] : 10;

    if (empty($word)) {
        return new WP_REST_Response([
            'status'	=> 'error',
            'message'	=> 'Product must be filled in.'
        ]);
    }

    $args = [
        'page'   => $page,
        'limit'  => $per_page,
        'search' => $word
    ];

    $product_result = load_revo_flutter_mobile_app()->get_products($args);

    // $get_data = get_option('revo_product_data_etag');
    // $caches = glob(REVO_SHINE_ABSPATH . '/storage/cache/*.json');

    // $data = null;
    // $product_result = [];
    // $closest = [];
    // $tmp = [];
    // $shortest = -1;

    // foreach ($caches as $cache_file) {
    // 	if (basename($cache_file, '.json') !== $get_data) {
    // 		continue;
    // 	}

    // 	$json_data = file_get_contents($cache_file);
    // 	$data = json_decode($json_data);
    // }

    // $get_show_out_of_stock_product = query_revo_mobile_variable('"show_out_of_stock_product"', 'sort');

    // if (!empty($data)) {
    // 	foreach ($data as $item) {
    // 		if (empty($get_show_out_of_stock_product[0]) || $get_show_out_of_stock_product[0]->description === 'hide') {
    // 			if ($item->stock_status === 'outofstock') {
    // 				continue;
    // 			}
    // 		}

    // 		if (is_string($item->name)) {
    // 			if (stripos($item->name, $word) !== false) {
    // 				$product_result[] = $item;
    // 			}

    // 			$lev = levenshtein($word, $item->name);

    // 			if ($lev == 0) {
    // 				$closest[] = $item;
    // 				$shortest = 0;

    // 				break;
    // 			}

    // 			if ($lev <= $shortest || $shortest < 0) {
    // 				$closest[]  = $item;
    // 				$shortest = $lev;

    // 				$tmp[] = $item;
    // 			}
    // 		}
    // 	}
    // }

    // if ($page && $per_page) {
    // 	$start = ($page - 1) * $per_page;
    // 	$closest = array_slice($closest, $start, $per_page);
    // 	$product_result = array_slice($product_result, $start, $per_page);
    // }

    // if ($product_result && $closest) {
    // 	$closest = [];
    // } elseif (!$product_result && $closest) {
    // 	$product_result = [];
    // }

    return new WP_REST_Response([
        'status'  => 'success',
        'message' => [
            'product'    => $product_result,
            'suggestion' => [],
        ]
    ]);
}

// REST FOR VIDEO

function rest_video_affiliate_views($request)
{
	global $wpdb;

	$video_id = $request['video_id'];

	if (empty($video_id)) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'Video ID is required!'
		], 403);
	}

	$check_data = $wpdb->get_row("SELECT * FROM revo_video_affiliate WHERE post_id = $video_id");

	if (!$check_data) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'Video not found!'
		], 404);
	}

	$save_view = $wpdb->insert('revo_video_affiliate_views', [
		'video_id'	=> $check_data->id,
		'type'		=> 'view',
	]);

	if ($save_view) {
		return new WP_REST_Response([
			'status'	=> 'success',
			'message'	=> 'Success saved view video'
		], 200);
	} else {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'An error has occurred, please try again in a few moments.'
		]);
	}
}

function rest_video_affiliate_get_product()
{
	global $wpdb;

	$get_product = isset($_GET['product']) ? $_GET['product'] : '';

	$args = [
		'post_type'			=> 'product',
		'post_status'		=> 'publish',
		'posts_per_page'	=> -1,
		's'           		=> $get_product,
	];

	$data_raw = get_posts($args);
	$result = [];

	foreach ($data_raw as $item) {
		$get_status_setting_stock = $wpdb->get_row("SELECT * FROM `revo_mobile_variable` WHERE slug = 'show_out_of_stock_product'");
		$product = wc_get_product($item->ID);

		if (!$get_status_setting_stock || $get_status_setting_stock->description === 'hide') {
			if ($product->is_in_stock()) {
				$result[] = [
					'id'    => $item->ID,
					'name'  => $item->post_title,
				];
			}
		} else {
			$result[] = [
				'id'	=> $item->ID,
				'name'	=> $item->post_title,
			];
		}
	}

	return new WP_REST_Response([
		'status' => 'success',
		'data'   => $result,
	]);
}

function rest_video_affiliate_get()
{
	global $wpdb;

	$video		= isset($_GET['video']) ? $_GET['video'] : '';
	$page		= isset($_GET['page']) ? intval($_GET['page']) : 1;
	$per_page	= isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;

	if (empty($page) || empty($per_page)) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'Sort, page, per_page is required!'
		], 403);
	}

	$data_lates = [];

	if (!empty($video)) {
		$args_data = [
			'post_type'      	=> 'video',
			'posts_per_page'	=> 1,
			'name'				=> $video
		];

		$video_query = new WP_Query($args_data);

		if ($video_query->have_posts()) {
			while ($video_query->have_posts()) {
				$video_query->the_post();
				$get_video = $wpdb->get_row($wpdb->prepare("SELECT * FROM revo_video_affiliate WHERE post_id = %d", get_the_ID()));

				if ($get_video && $get_video->status === '1') {
					$data_lates[] = process_video_data($wpdb, $get_video);
				}
			}
			wp_reset_postdata();
		}
	} else {
		$get_video = $wpdb->get_results("SELECT * FROM revo_video_affiliate");

		foreach ($get_video as $data) {
			if ($data->status === '0' || $data->status === '2') {
				continue;
			}

			$data_lates[] = process_video_data($wpdb, $data);
		}
	}

	if ($page && $per_page) {
		$start = ($page - 1) * $per_page;
		$data_lates = array_slice($data_lates, $start, $per_page);
	}

	shuffle($data_lates);

	return new WP_REST_Response([
		'status'	=> 'success',
		'data' 		=> $data_lates,
	], 200);
}

function process_video_data($wpdb, $data)
{
	$get_view_data = $wpdb->get_results("SELECT * FROM revo_video_affiliate_views WHERE video_id = $data->id");
	$viewCount = 0;
	$clickCount = 0;
	$sales = 0;

	if ($get_view_data) {
		foreach ($get_view_data as $data_views) {
			if ($data_views->type === "view") {
				$viewCount++;
			} elseif ($data_views->type === "click") {
				$clickCount++;

				if (!empty($data_views->information)) {
					$information_array = json_decode($data_views->information, true);

					if (!empty($information_array['order_id'])) {
						$order = wc_get_order($information_array['order_id']);

						if ($order && $order->get_status() === 'completed') {
							$sales += $order->get_total();
						}
					}
				}
			}
		}
	}

	$data_user_raw = get_userdata($data->user_id);

	if ($data_user_raw) {

		$product_data = get_post($data->product_id);
		$thumbnail = get_the_post_thumbnail_url($product_data->ID);

		if (!$thumbnail) {
			$placeholder_path = 'wp-content/uploads/woocommerce-placeholder.png';
			$thumbnail = site_url($placeholder_path);
		}

		$product_post = get_post($data->post_id);
		$get_product  = wc_get_product($data->product_id);
		$product_type = load_revo_flutter_mobile_app()->reformat_product_result($get_product);

		return [
			'data_user'			=> [
				'user_id'   => $data_user_raw->ID,
				'name'      => $data_user_raw->data->display_name,
				'username'  => $data_user_raw->data->user_nicename,
				'email'     => $data_user_raw->data->user_email,
				'roles'     => $data_user_raw->roles,
			],
			'video_affiliate'   => [
				'video_id'		=> $data->post_id,
				'video_url'		=> $data->video_url,
				'date'			=> date('d/m/Y', strtotime($data->created_at)),
				'views'			=> (string)$viewCount,
				'clicks'		=> (string)$clickCount,
				'sales'			=> (string)$sales,
				'status'		=> ($data->status === '0') ? 'inactive' : (($data->status === '1') ? 'active' : 'reject'),
				'link_share'	=> $product_post->guid,
				'created_at'	=> $data->created_at,
			],
			'product_data'		=> $product_type,
		];
	}
}

function rest_video_affiliate_get_my_video($request)
{
	global $wpdb;

	$cookie		= $request['cookie'];
	$sort		= strtolower($request['sort']);
	$page		= $request['page'];
	$per_page	= $request['per_page'];

	if (empty($cookie)) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'Login required!'
		], 403);
	}

	if (empty($sort) || empty($page) || empty($per_page)) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'Sort, page, per_page is required!'
		], 403);
	}

	if (!in_array($sort, ['popularity', 'latest', 'clicks', 'sales'])) {
		return new WP_REST_Response([
			'status'    => 'error',
			'message'   => 'Sort not found'
		], 404);
	}

	$user_id = wp_validate_auth_cookie($cookie, 'logged_in');
	$check_user_affiliate = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}uap_affiliates WHERE uid=$user_id", OBJECT);

	if (!$user_id || !$check_user_affiliate) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'User not found!'
		], 404);
	}

	$get_video = $wpdb->get_results("SELECT * FROM revo_video_affiliate WHERE user_id = $user_id");

	$data_lates = [];

	foreach ($get_video as $data) {
		$get_view_data = $wpdb->get_results("SELECT * FROM revo_video_affiliate_views WHERE video_id = $data->id");
		$viewCount = 0;
		$clickCount = 0;
		$sales = 0;

		if ($get_view_data) {
			foreach ($get_view_data as $data_views) {
				if ($data_views->type === "view") {
					$viewCount++;
				} elseif ($data_views->type === "click") {
					$clickCount++;

					if (!empty($data_views->information)) {
						$information_array = json_decode($data_views->information, true);

						if (!empty($information_array['order_id'])) {
							$order = wc_get_order($information_array['order_id']);

							if ($order && $order->get_status() === 'completed') {
								$sales += $order->get_total();
							}
						}
					}
				}
			}
		}

		$data_user_raw = get_userdata($data->user_id);

		if ($data_user_raw) {

			$product_data = get_post($data->product_id);
			$thumbnail = get_the_post_thumbnail_url($product_data->ID);

			if (!$thumbnail) {
				$placeholder_path = 'wp-content/uploads/woocommerce-placeholder.png';
				$thumbnail = site_url($placeholder_path);
			}

			$product_post = get_post($data->post_id);
			$product_type = wc_get_product($data->product_id);

			$data_lates[] = [
				'video_affiliate'   => [
					'video_id'		=> $data->post_id,
					'video_url'		=> $data->video_url,
					'date'			=> date('d/m/Y', strtotime($data->created_at)),
					'views'			=> (string)$viewCount,
					'clicks'		=> (string)$clickCount,
					'sales'			=> (string)$sales,
					'status'		=> ($data->status === '0') ? 'inactive' : (($data->status === '1') ? 'active' : 'reject'),
					'link_share'	=> $product_post->guid,
					'created_at'	=> $data->created_at,
				],
				'product_data'		=> [
					'product_id'	=> $product_data->ID,
					'post_title'	=> $product_data->post_title,
					'post_content'	=> $product_data->post_content,
					'type'			=> $product_type->get_type(),
					'thumbnail'		=> $thumbnail
				],
			];
		}
	}

	switch ($sort) {
		case 'popularity':
			usort($data_lates, function ($a, $b) {
				return $b['video_affiliate']['views'] - $a['video_affiliate']['views'];
			});
			break;
		case 'latest':
			usort($data_lates, function ($a, $b) {
				return strtotime($b['video_affiliate']['created_at']) - strtotime($a['video_affiliate']['created_at']);
			});
			break;
		case 'clicks':
			usort($data_lates, function ($a, $b) {
				return $b['video_affiliate']['clicks'] - $a['video_affiliate']['clicks'];
			});
			break;
		case 'sales':
			usort($data_lates, function ($a, $b) {
				return $b['video_affiliate']['sales'] - $a['video_affiliate']['sales'];
			});
			break;
		default:
			usort($data_lates, function ($a, $b) {
				return strtotime($b['video_affiliate']['created_at']) - strtotime($a['video_affiliate']['created_at']);
			});
			break;
	}

	if ($page && $per_page) {
		$start = ($page - 1) * $per_page;
		$data_lates = array_slice($data_lates, $start, $per_page);
	}

	if ($sort) {
		$result = $data_lates;
	} else {
		$result = $data_lates;
	}

	return new WP_REST_Response([
		'status'	=> 'success',
		'data' 		=> $result
	], 200);
}

function rest_video_affiliate_store($request)
{
	global $wpdb;

	$cookie		= $request['cookie'];
	$video		= $_FILES['video'];
	$id_product	= $request['id_product'];

	if (is_plugin_inactive('indeed-affiliate-pro/indeed-affiliate-pro.php')) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'Plugin indeed affiliate pro not found!'
		], 404);
	}

	$get_variable_affiliate = $wpdb->get_row("SELECT * FROM `revo_mobile_variable` WHERE slug = 'enable_affiliate_video' LIMIT 1");

	if (!$get_variable_affiliate || $get_variable_affiliate->description === 'hide') {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'Video not active, please activate in wp-admin'
		], 404);
	}

	if (empty($cookie)) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'Login required!'
		], 403);
	}

	$user_id = wp_validate_auth_cookie($cookie, 'logged_in');
	$check_user_affiliate = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}uap_affiliates WHERE uid=$user_id", OBJECT);

	if (!$user_id || !$check_user_affiliate) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'User not found!'
		], 404);
	}

	$args_product = [
		'p'				=> $id_product,
		'post_type'		=> 'product',
		'post_status'	=> 'publish',
	];

	$product = get_posts($args_product);

	if (empty($id_product) || empty($product)) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'ID product must be filled'
		], 404);
	}

	$video_size = get_option('revo_video_setting');
	if (!$video_size) {
		$video_size = 25;
	}

	$fileSizeInMB = $video['size'] / (1024 * 1024);
	$fileSizeInMBFormatted = number_format($fileSizeInMB, 3);

	if ($fileSizeInMBFormatted >= $video_size) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'The uploaded video file is too large, maximum allowed size is ' . $video_size . 'MB'
		], 413);
	}

	$mimes = [
		'mp4'	=> 'video/mp4',
		'avi'	=> 'video/x-msvideo',
		'mkv'	=> 'video/x-matroska',
		'mov'	=> 'video/quicktime',
	];

	$extension_video = wp_check_filetype($video['name'], $mimes);

	if (!$extension_video['ext']) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'The uploaded file extension is not accepted. Please upload files with extensions mp4, avi, mkv, mov'
		], 415);
	}

	$upload_dir = wp_upload_dir();
	$upload_path = $upload_dir['basedir'] . '/revo/video/';

	if (!file_exists($upload_path)) {
		mkdir($upload_path, 0777, true);
	}

	$file_name = uniqid() . '_' . wp_hash($video['name']) . '.' . $extension_video['ext'];

	try {
		$file_path = $upload_path . $file_name;
		if (move_uploaded_file($video['tmp_name'], $file_path)) {

			$data_user_raw = get_userdata($user_id);

			$post_args = array(
				'post_title'    => 'product ' . $id_product . '-' . $data_user_raw->data->user_nicename,
				'post_content'  => 'Product ' . $id_product . '-' . $data_user_raw->data->user_nicename,
				'post_status'   => 'publish',
				'post_author'   => 1,
				'post_type'     => 'video',
			);

			$post_id = wp_insert_post($post_args);

			$wpdb->insert('revo_video_affiliate', [
				'post_id'		=> $post_id,
				'user_id'		=> $user_id,
				'product_id'	=> $id_product,
				'video_url'		=> home_url() . '/wp-content/uploads/revo/video/' . $file_name,
				'video'			=> $file_name,
				'link'			=> get_permalink($id_product),
			]);

			return new WP_REST_Response([
				'status'	=> 'success',
				'message'	=> 'Successfully saved the video'
			], 200);
		} else {
			return new WP_REST_Response([
				'status'	=> 'error',
				'message'	=> 'An error has occurred, please try again in a few moments.'
			]);
		}
	} catch (Exception $e) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> $e->getMessage()
		], 500);
	}
}

function rest_video_affiliate_delete($request)
{
	global $wpdb;

	$cookie		= $request['cookie'];
	$video_id	= $request['video_id'];

	if (is_plugin_inactive('indeed-affiliate-pro/indeed-affiliate-pro.php')) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'Plugin indeed affiliate pro not found!'
		], 404);
	}

	$get_variable_affiliate = $wpdb->get_row("SELECT * FROM `revo_mobile_variable` WHERE slug = 'enable_affiliate_video' LIMIT 1");

	if (!$get_variable_affiliate || $get_variable_affiliate->description === 'hide') {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'Video not active, please activate in wp-admin'
		], 404);
	}

	if (empty($cookie)) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'Login required!'
		], 403);
	}

	$user_id = wp_validate_auth_cookie($cookie, 'logged_in');
	$check_user_affiliate = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}uap_affiliates WHERE uid=$user_id", OBJECT);

	if (!$user_id || !$check_user_affiliate) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'User not found!'
		], 404);
	}

	$check_video = $wpdb->get_row("SELECT * FROM revo_video_affiliate WHERE post_id = $video_id");

	if (!$check_video) {
		return new WP_REST_Response([
			'status'	=> 'error',
			'message'	=> 'Video not found!'
		], 404);
	}

	$wpdb->delete('revo_video_affiliate', array('id' => $check_video->id));

	$wpdb->delete('revo_video_affiliate_views', array('video_id' => $check_video->id));

	return new WP_REST_Response([
		'status'	=> 'success',
		'message'	=> 'Success delete video!'
	]);
}

function rest_festive_promotions( WP_REST_Request $request ) : WP_REST_Response
{
	global $wpdb;

	$page		= (int) $request->get_param( 'page' ) ?? 1;
	$per_page	= (int) $request->get_param( 'per_page' ) ?? 10;

	$festive_promotions = $wpdb->get_row( "SELECT * FROM revo_extend_products WHERE type = 'festive_promotions'" );
	$festive_promotions_data = json_decode( $festive_promotions->products );

	$festive_promotions_data = array_map('intval', $festive_promotions_data);

	$start = ( $page - 1 ) * $per_page;
	$festive_promotions_data = array_slice( $festive_promotions_data, $start, $per_page );

	$args = [
		'post_type'		=> 'product',
		'post_status'	=> 'publish',
		'include'		=> $festive_promotions_data,
	];

	$products = wc_get_products( $args );
	krsort( $products );
	$result = [];

	foreach ( $products as $key => $product ) {
		$result[] = load_revo_flutter_mobile_app()->reformat_product_result( $product );
	}

	return new WP_REST_Response( [
		'status'	=> 'success',
		'data'		=> $result,
	] );
}
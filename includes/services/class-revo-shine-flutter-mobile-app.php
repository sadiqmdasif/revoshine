<?php

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('Revo_Shine_Flutter_Mobile_App')) {
	class Revo_Shine_Flutter_Mobile_App
	{
		private static $instance = null;

		public function __construct()
		{
			add_filter('rw_format_content_html', array($this, 'rw_format_content_html'), 10, 2);
		}

		public function rw_format_content_html($content, $args)
		{
			if (isset($args['s']) && !empty($args['s'])) {
				return strip_tags($content);
			}

			$content = apply_filters('the_content', $content);

			return str_replace(']]>', ']]&gt;', $content);
		}

		/*
		 * Get product data
		 * 
		 * Priority: include || id > sku > search
		 */
		public function get_products($data = array())
		{
			global $wpdb;

			$args = [
				'post_status'  => 'publish',
				'post_type'    => array('product'),
				'type'		   => array('simple', 'variable'),
				'stock_status' => 'instock',
			];

			if (isset($_GET['lang'])) {
				$args['lang'] = $_GET['lang'];
			}

			if (!empty($data['attribute'])) {

				// $tax_query   = WC()->query->get_tax_query();

				// for ($i=0; $i < count($data['attribute']); $i++) {

				//     $tax_query[] = array(
				//         'taxonomy' => $data['attribute'][$i]->taxonomy,
				//         'field'    => 'slug',
				//         'terms'    => $data['attribute'][$i]->terms,
				//     );

				// }

				$taxonomy = wc_attribute_taxonomy_name_by_id($data['attribute']);

				$terms = get_terms([
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
					'fields'     => 'names'
				]);

				$tax_query[] = [
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $terms,
				];
			}

			if (!empty($data['filters'])) {
				foreach ($data['filters'] as $filter_value) {
					$tax_query[] = [
						'taxonomy' => $filter_value->key,
						'field'    => 'term_id',
						'terms'    => $filter_value->value,
					];
				}
			}

			if (!empty($data['featured'])) {
				$tax_query[] = array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'featured',
					'operator' => 'IN',
				);
			}

			$orderby = isset($data['order_by']) ? $data['order_by'] : 'date';
			$order   = isset($data['order']) ? $data['order'] : 'DESC';

			switch ($orderby) {
				case 'id':
					$args['orderby'] = 'ID';
					break;
				case 'menu_order':
					$args['orderby'] = 'menu_order title';
					break;
				case 'name':
					$args['orderby'] = 'name';
					$args['order']   = ('DESC' === $order) ? 'DESC' : 'ASC';
					break;
				case 'relevance':
					$args['orderby'] = 'relevance';
					$args['order']   = 'DESC';
					break;
				case 'rand':
					$args['orderby'] = 'rand'; // @codingStandardsIgnoreLine
					break;
				case 'date':
					$args['orderby'] = 'date ID';
					$args['order']   = ('ASC' === $order) ? 'ASC' : 'DESC';
					break;
				case 'price':
					$args['orderby']  = 'meta_value_num';
					$args['meta_key'] = '_price';
					if ($order == 'asc') {
						$args['order'] = 'asc';
					} else {
						$args['order'] = 'desc';
					}
					break;
				case 'popularity':
					$args['orderby']  = 'meta_value_num';
					$args['meta_key'] = 'total_sales';
					break;
				case 'rating':
					add_filter('posts_clauses', array($this, 'order_by_rating_post_clauses'));
					break;
			}

			if (!empty($data['tag'])) {
				$args = array(
					'tag' => array($data['tag']),
				);
			}

			if (!empty($data['on_sale'])) {
				$on_sale_key = $data['on_sale'] == '1' ? 'post__in' : 'post__not_in';
				$on_sale_ids = wc_get_product_ids_on_sale();

				// Use 0 when there's no on sale products to avoid return all products.
				$on_sale_ids = empty($on_sale_ids) ? array(0) : $on_sale_ids;

				$args['include'] = $on_sale_ids;
			}

			if (!empty($data['id'])) {
				$args['include'] = array($data['id']);
			}

			if (!empty($data['product_id'])) {
				$args['include'] = explode(',', $data['product_id']);
			}

			if (!empty($data['include'])) {
				$args['include'] = explode(',', $data['include']);
			}

			if (!empty($data['tax_query'])) {
				$tax_query[] = $data['tax_query'];
			}

			if (!empty($data['category'])) {
				$categories     = explode(',', $data['category']);
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

			if (!empty($data['slug_category'])) {

				$args['category'][] = $data['slug_category'];

				$args['category'] = array_unique($args['category']);
			}

			if (!empty($data['page'])) {
				$args['page'] = $data['page'];
			}

			if (!empty($data['slug'])) {
				$args['slug'] = $data['slug'];
				$product_obj  = get_page_by_path($data['slug'], OBJECT, 'product');
				// print_r(get_object_vars($product_obj)['ID']); die;
				$args['include'] = array(get_object_vars($product_obj)['ID']);
			}

			if (!empty($data['limit'])) {
				$args['limit'] = $data['limit'];
			}

			if (!empty($data['stock_status'])) {
				$args['stock_status'] = $data['stock_status'];
			}

			if (!empty($data['search'])) {
				$args['s'] = $data['search'];
			}

			// search product with sku
			if (!empty($data['sku']) || !empty($data['search'])) {
				$data_sku = explode(',', ($data['sku'] ?? '') . ',' . ($data['search'] ?? ''));
				$data_sku = array_values(array_filter($data_sku, fn ($item) => !empty($item)));

				foreach ($data_sku as $sku) {
					if (($get_product_id_by_sku = $this->get_product_by_sku($sku)) !== '') {
						$id_product_by_sku[] = $get_product_id_by_sku;
					}
				}

				if (isset($id_product_by_sku) && !empty($id_product_by_sku)) {
					unset($args['s']);
					$args['sku'] = $data['search'];
				}
			}

			if (!empty($data['exclude_sku'])) {
				$get_data_exclude_product_by_sku = $this->get_exclude_product_by_sku($data['exclude_sku']);

				foreach ($get_data_exclude_product_by_sku as $data => $key) {
					$id_exclude_product_by_sku[] = $key->post_id;
				}

				$args['exclude'] = $id_exclude_product_by_sku;

				if (!empty($args['include'])) {

					foreach ($args['include'] as $data) {

						$cek_exclude_product = array_search($data, $args['exclude']);

						if (empty($cek_exclude_product)) {
							$include_new[] = $data;
						}
					}

					$args['include'] = $include_new;
				}
			}

			$check_config_out_of_stock = query_revo_mobile_variable('"show_out_of_stock_product"', 'sort');
			if (isset($check_config_out_of_stock[0])) {
				if ($check_config_out_of_stock[0]->description === 'show') {
					$args['stock_status'] = ['instock', 'outofstock'];
				}
			}

			$user = get_userdata(wp_validate_auth_cookie($_GET['cookie'] ?? cek_raw('cookie'), 'logged_in'));
			$user_wishlist_product_id = [];
			if ($user) {
				global $wpdb;
				$user_wishlist_product_id = array_map(fn ($value) => $value->products, $wpdb->get_results("SELECT products FROM `revo_hit_products` WHERE user_id = '$user->ID' AND type = 'wistlist'", OBJECT));
			}

			if ($status_membership_plugin = is_plugin_active('woocommerce-memberships/woocommerce-memberships.php')) {
				$service_membership_discount = new WC_Memberships_Member_Discounts();
				if (strpos(($_SERVER['REDIRECT_URL'] ?? ''), 'memberships/products') === false) {
					$tax_query[] = [
						'taxonomy' => 'product_cat',
						'field'    => 'id',
						'terms'    => array(get_option('revo_membership_selected_category', '')),
						'operator' => 'NOT IN',
					];
				}
			}

			if (!empty($args['include'])) {
				unset($args['s'], $args['sku']);
				$args['orderby'] = 'post__in';
			}

			if (!empty($tax_query)) {
				$args['tax_query'] = $tax_query;
			}

			if (!empty($data['video_id'])) {
				global $wpdb;

				$video_id = $data['video_id'];
				$check_data = $wpdb->get_row("SELECT * FROM revo_video_affiliate WHERE post_id = $video_id");

				if (!$check_data) {
					return new WP_REST_Response([
						'status'	=> 'error',
						'message'	=> 'Video not found!'
					], 404);
				}

				$save_view = $wpdb->insert('revo_video_affiliate_views', [
					'video_id'		=> $check_data->id,
					'type'			=> 'click',
				]);

				if (!$save_view) {
					return new WP_REST_Response([
						'status'	=> 'success',
						'message'	=> 'An error has occurred, please try again in a few moments.'
					]);
				}
			}

			$results  = [];
			$products = wc_get_products(apply_filters('revo_shine_get_products_args', $args));

			foreach ($products as $product) {
				$results[] = $this->reformat_product_result($product, $user, array_merge($args, [
					'data'						  => $data,
					'user_wishlist_product_id'    => $user_wishlist_product_id,
					'status_membership_plugin'    => $status_membership_plugin,
					'service_membership_discount' => $service_membership_discount ?? '',
				]));
			}

			return $results;
		}

		public function get_addon_products($product)
		{
			$addon_meta = $product->get_meta('_product_addons');

			if (empty($addon_meta)) {
				return [];
			}

			foreach ($addon_meta as $key => $addon) {
				if ($addon['type'] !== 'checkbox') {
					continue;
				}

				$result[] = [
					'id'         => (string) $addon['id'],
					'name'       => $addon['name'],
					'type'       => $addon['type'],
					'field_name' => $product->get_id() . '-' . $key,
					'options'    => $addon['options']
				];
			}

			return $result ?? [];
		}

		/**
		 * get min max quantity of product
		 * 
		 * min is empty = parse to "1"
		 * max is empty = parse to "999"
		 **/
		public function get_min_max_quantity($product, $from = 'api')
		{

			$sold_individually = $product->get_sold_individually();

			if (is_plugin_active('minmax-quantity-for-woocommerce/woocommerce-minmax-quantity.php')) { //https://id.wordpress.org/plugins/minmax-quantity-for-woocommerce
				$min_qty = get_post_meta($product->get_id(), 'min_quantity', true);   // min is empty = parse to "1"
				$max_qty = get_post_meta($product->get_id(), 'max_quantity', true);   // max is empty = parse to "999"
			} elseif (is_plugin_active('woocommerce-min-max-quantities/woocommerce-min-max-quantities.php')) { // https://woocommerce.com/products/minmax-quantities//
				$min_qty = get_post_meta($product->get_id(), 'minimum_allowed_quantity', true);
				$max_qty = get_post_meta($product->get_id(), 'maximum_allowed_quantity', true);
			} else {
				return [
					'min_quantity' => 1,
					'max_quantity' => !$sold_individually ? 999 : 1,
				];
			}

			if ($sold_individually) {
				$min_qty = 1;
				$max_qty = 1;
			} else {
				$min_qty = empty($min_qty) ? 1 : $min_qty;
				$max_qty = empty($max_qty) ? 999 : $max_qty;
			}

			return [
				'min_quantity' => (int) $min_qty,
				'max_quantity' => (int) $max_qty,
			];
		}

		public function get_variation_min_max_quantity($product, $value, $from = 'api')
		{
			$sold_individually = $value['is_sold_individually'];

			if ($sold_individually === 'yes') {
				$min_qty = 1;
				$max_qty = 1;
			} elseif (is_plugin_active('minmax-quantity-for-woocommerce/woocommerce-minmax-quantity.php')) { //https://id.wordpress.org/plugins/minmax-quantity-for-woocommerce
				$parent_min_qty = get_post_meta($product->get_id(), 'min_quantity', true);
				$parent_max_qty = get_post_meta($product->get_id(), 'max_quantity', true);

				$min_qty = !empty($parent_min_qty) ? $parent_min_qty : get_post_meta($value['variation_id'], 'min_quantity_var', true);   // min is empty = parse to "1"
				$max_qty = !empty($parent_max_qty) ? $parent_max_qty : get_post_meta($value['variation_id'], 'max_quantity_var', true);   // max is empty = parse to "999"
			} else {
				$min_qty = $value['min_qty'];
				$max_qty = $value['max_qty'];
			}

			return [
				'min_quantity' => (int) (empty($min_qty) ? 1   : $min_qty),
				'max_quantity' => (int) (empty($max_qty) ? 999 : $max_qty)
			];
		}

		public function get_product_by_sku($sku)
		{
			global $wpdb;

			$product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value = %s",  $sku));

			if (is_null($product_id)) {
				return '';
			}

			return $product_id;
		}

		public function get_exclude_product_by_sku($sku)
		{
			global $wpdb;

			$products = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value LIKE %s", "%" . $sku . "%"));

			if ($products) {
				return $products;
			}

			return null;
		}

		public function reformat_product_result($product, $user = null, $args = array())
		{
			$available_variations = $product->get_type() === 'variable' ? $product->get_available_variations() : null;
			$selected_variation_options = [];

			if (!is_null($available_variations)) {
				foreach ($available_variations as $variation_key => $value) {
					// variation minmax quantity
					$available_variations[$variation_key]['minmax_quantity'] = $this->get_variation_min_max_quantity($product, $value);

					// membership price
					if (isset($args['status_membership_plugin']) && $args['status_membership_plugin']) {
						if (wc_memberships_user_has_member_discount($value['variation_id'], $user)) {
							$available_variations[$variation_key]['display_price'] = (float)$args['service_membership_discount']->get_discounted_price($value['display_price'], $value['variation_id'], $user->ID);
						}
					}

					// formateed variation data
					unset($available_variations[$variation_key]['min_qty']);
					unset($available_variations[$variation_key]['max_qty']);

					$available_variations[$variation_key]['formated_price']       = strip_tags(wc_price($value['display_regular_price']));
					$available_variations[$variation_key]['formated_sales_price'] = $value['display_price'] !== $value['display_regular_price'] ? strip_tags(wc_price($value['display_price'])) : null;
					$available_variations[$variation_key]['meta_data']            = []; //$variation->get_meta_data()
					$available_variations[$variation_key]['image_id']             = null;

					foreach ($value['attributes'] as $atr_key => $atr_value) {
						$available_variations[$variation_key]['option'][] = array(
							'key'   => $atr_key,
							'value' => $atr_value
							// 'value' => $this->attribute_slug_to_title($atr_key, $atr_value) //make it name
						);
					}
				}

				if (!empty($default_attribute = $product->get_default_attributes())) {
					foreach ($default_attribute as $default_attribute_key => $default_attribute_value) {
						$selected_variation_options[] = array(
							// 'attribute_slug'  => wc_attribute_label($default_attribute_key),
							'attribute_slug'  => $default_attribute_key,
							'variation_value' => $default_attribute_value
						);
					}
				}
			}

			$result = array(
				'id'                       => $product->get_id(),
				'name'                     => $product->get_name(),
				'is_wistlist'              => in_array($product->get_id(), $args['user_wishlist_product_id'] ?? []),
				'sku'                      => $product->get_sku(),
				'type'                     => $product->get_type(),
				'status'                   => $product->get_status(),
				'permalink'                => $product->get_permalink(),
				'short_description'        => $product->get_short_description(),
				'description'              => apply_filters('rw_format_content_html', $product->get_description(), $args),
				'price'                    => (float) $product->get_price(),
				'regular_price'            => (float) $product->get_regular_price(),
				'sale_price'               => (float) $product->get_sale_price(),
				'formated_price'           => $product->get_regular_price() ? strip_tags(wc_price(wc_get_price_to_display($product, array('price' => $product->get_regular_price())))) : strip_tags(wc_price(wc_get_price_to_display($product, array('price' => $product->get_price())))),
				'formated_sales_price'     => $product->get_sale_price() ? strip_tags(wc_price(wc_get_price_to_display($product, array('price' => $product->get_sale_price())))) : null,
				'stock_status'             => $product->get_stock_status(),
				'stock_quantity'           => $product->get_stock_quantity(),
				'sold_individually'        => is_plugin_active('minmax-quantity-for-woocommerce/woocommerce-minmax-quantity.php') ? false : $product->get_sold_individually(),
				'minmax_quantity'          => $this->get_min_max_quantity($product),
				'images'                   => $this->get_images($product),
				'attributes'               => [], // $this->get_attributes( $product )
				'attributes_v2'            => $product->get_type() === 'variable' ? $this->get_attributes_v2($product) : [],
				'availableVariations'      => $available_variations,
				'default_variation_option' => $selected_variation_options,
				'categories'               => get_the_terms($product->get_id(), 'product_cat'),
				'dimensions'               => $product->get_dimensions(false),
				'total_sales'              => (int) $product->get_total_sales(),
				'average_rating'           => wc_format_decimal($product->get_average_rating(), 2),
				'rating_count'             => $product->get_review_count(),
				'meta_data'                => array_values($product->get_meta('_ywcfav_video', false)), // $product->get_meta_data()
				'addon_products'           => $this->get_addon_products($product),
				'promo_label_product'	   => $this->get_label_product( $product->get_id() ),
				//'cashback_amount'        => woo_wallet()->cashback->get_product_cashback_amount($product) //UnComment Whne cashback need only
			);

			$result['dimensions']['weight'] = $product->get_weight();

			if (isset($args['status_membership_plugin']) && $args['status_membership_plugin']) {
				if (wc_memberships_user_has_member_discount($product, $user)) {
					$price = $args['service_membership_discount']->get_discounted_price($product->get_price(), $product, $user->ID);

					$result['price'] = (float) $price;

					if ($result['type'] !== 'variable') {
						$result['formated_sales_price'] = strip_tags(wc_price(wc_get_price_to_display($product, array('price' => $price))));
						$result['sale_price']           = (float) $price;
					}
				}
			}

			if (isset($args['data']['extend_product']) && !empty($args['data']['extend_product'])) {
				foreach ($args['data']['extend_product'] as $extend_product_key => $extend_product_value) {
					if (in_array($product->get_id(), $extend_product_value)) {
						$result['extend_product'][] = $extend_product_key;
					}
				}
			}

			if (!empty($wholesale_data = $product->get_meta('wholesale_customer_wholesale_price', true))) {
				$result['is_variant'] = false;
				$result['wholesales'] = [['price' => $wholesale_data]];
			} else {
				$result['is_variant'] = true;
				$result['wholesales'] = [];

				$wholesale_variant_data = $product->get_meta('wholesale_customer_variations_with_wholesale_price', false);
				if (!empty($wholesale_variant_data)) {
					foreach ($wholesale_variant_data as $wholesale_variant) {
						$result['wholesales'][] = [
							'id' 	=> $wholesale_variant->value,
							'price' => get_post_meta($wholesale_variant->value, 'wholesale_customer_wholesale_price', true)
						];
					}
				}
			}

			return apply_filters('rw_response_product_data', $result, $product);
		}

		public function attribute_slug_to_title($attribute, $slug)
		{
			global $woocommerce;
			$value = $slug;

			if (taxonomy_exists(esc_attr(str_replace('attribute_', '', $attribute)))) {
				$term = get_term_by('slug', $slug, esc_attr(str_replace('attribute_', '', $attribute)));
				if (!is_wp_error($term) && isset($term->name)) {
					$value = $term->name;
				}
			} else {
				//$value = apply_filters( 'woocommerce_variation_option_name', $slug );
			}

			return $value;
		}

		public function get_images($product)
		{
			list($images, $attachment_ids) = [[], []];

			// add featured & gallery images.
			$attachment_ids = array_merge([$product->get_image_id()], $product->get_gallery_image_ids());

			// build image data.
			foreach ($attachment_ids as $key => $attachment_id) {
				// $attachment_post = get_post($attachment_id);
				// if (is_null($attachment_post)) {
				// 	continue;
				// }

				// $attachment_url = wp_get_attachment_url($attachment_id);
				// if (!$attachment_url) {
				// 	continue;
				// }

				$attachment_post = wp_get_attachment_image_src($attachment_id, 'full');
				if (!$attachment_post) {
					continue;
				}

				// $attachment_url = str_replace(['.webp', '-jpg'], ['', '.jpg'], $attachment_url);

				$images[] = array(
					'id'       => (int) $attachment_id,
					'src'      => $attachment_post[0],
					'name'     => 'image-' . $attachment_id,
					'alt'      => 'image alt-' . $attachment_id,
					'position' => (int) $key,
				);
			}

			// Set a placeholder image if the product has no images set.
			if (empty($images)) {
				$images[] = array(
					'id'       => 0,
					'src'      => wc_placeholder_img_src(),
					'name'     => __('Placeholder', 'woocommerce'),
					'alt'      => __('Placeholder', 'woocommerce'),
					'position' => 0,
				);
			}

			return $images;
		}

		public function get_attribute_taxonomy_name($slug, $product)
		{
			$attributes = $product->get_attributes();

			if (!isset($attributes[$slug])) {
				return str_replace('pa_', '', $slug);
			}

			$attribute = $attributes[$slug];

			// Taxonomy attribute name.
			if ($attribute->is_taxonomy()) {
				$taxonomy = $attribute->get_taxonomy_object();

				return $taxonomy->attribute_label;
			}

			// Custom product attribute name.
			return $attribute->get_name();
		}

		/**
		 * Get default attributes.
		 *
		 * @param WC_Product $product Product instance.
		 *
		 * @return array
		 */
		public function get_default_attributes($product)
		{
			$default = array();

			if ($product->is_type('variable')) {
				foreach (array_filter((array) $product->get_default_attributes(), 'strlen') as $key => $value) {
					if (0 === strpos($key, 'pa_')) {
						$default[] = array(
							'id'     => wc_attribute_taxonomy_id_by_name($key),
							'name'   => $this->get_attribute_taxonomy_name($key, $product),
							'option' => $value,
						);
					} else {
						$default[] = array(
							'id'     => 0,
							'name'   => $this->get_attribute_taxonomy_name($key, $product),
							'option' => $value,
						);
					}
				}
			}

			return $default;
		}

		/**
		 * Get attribute options.
		 *
		 * @param int $product_id Product ID.
		 * @param array $attribute Attribute data.
		 *
		 * @return array
		 */
		public function get_attribute_options($product_id, $attribute)
		{
			if (isset($attribute['is_taxonomy']) && $attribute['is_taxonomy']) {
				return wc_get_product_terms(
					$product_id,
					$attribute['name'],
					array(
						'fields' => 'names',
					)
				);
			} elseif (isset($attribute['value'])) {
				return array_map('trim', explode('|', $attribute['value']));
			}

			return array();
		}

		public function get_attribute_options_v2($product_id, $attribute, $available_variation_attributes = [])
		{
			if (isset($attribute['is_taxonomy']) && $attribute['is_taxonomy']) {
				$result = [];
				$terms  = wc_get_product_terms($product_id, $attribute['name'], array('fields' => 'all', ''));

				foreach ($terms as $value) {
					if (!in_array($value->slug, ($available_variation_attributes[$attribute['name']] ?? []))) {
						continue;
					}

					array_push($result, [
						'name' => $value->name,
						'slug' => $value->slug,
					]);
				}

				return $result;
			} elseif (isset($attribute['value'])) {
				$result = [];
				$terms  = !empty($available_variation_attributes) ? ($available_variation_attributes[$attribute['name']] ?? []) : explode('|', $attribute['value']);

				foreach ($terms as $value) {
					array_push($result, [
						'name' => trim($value),
						'slug' => sanitize_title($value)
					]);
				}

				usort($result, function ($a, $b) {
					return $a['name'] > $b['name'];
				});

				return $result;
			}

			return array();
		}

		/**
		 * Get the attributes for a product or product variation.
		 *
		 * @param WC_Product|WC_Product_Variation $product Product instance.
		 *
		 * @return array
		 */
		public function get_attributes($product)
		{
			$attributes = array();

			if ($product->is_type('variation')) {
				$_product = wc_get_product($product->get_parent_id());
				foreach ($product->get_variation_attributes() as $attribute_name => $attribute) {
					$name = str_replace('attribute_', '', $attribute_name);

					if (empty($attribute) && '0' !== $attribute) {
						continue;
					}

					// Taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`.
					if (0 === strpos($attribute_name, 'attribute_pa_')) {
						$option_term  = get_term_by('slug', $attribute, $name);
						$attributes[] = array(
							'id'     => wc_attribute_taxonomy_id_by_name($name),
							'name'   => $this->get_attribute_taxonomy_name($name, $_product),
							'option' => $option_term && !is_wp_error($option_term) ? $option_term->name : $attribute,
						);
					} else {
						$attributes[] = array(
							'id'     => 0,
							'name'   => $this->get_attribute_taxonomy_name($name, $_product),
							'option' => $attribute,
						);
					}
				}
			} else {
				foreach ($product->get_attributes() as $key => $attribute) {
					$attributes[] = array(
						'id'        => $attribute['is_taxonomy'] ? wc_attribute_taxonomy_id_by_name($attribute['name']) : 0,
						'name'      => $this->get_attribute_taxonomy_name($attribute['name'], $product),
						'slug'      => $attribute['name'],
						'position'  => (int) $attribute['position'],
						'visible'   => (bool) $attribute['is_visible'],
						'variation' => (bool) $attribute['is_variation'],
						'options'   => $this->get_attribute_options($product->get_id(), $attribute),
					);
				}
			}

			return $attributes;
		}

		public function get_attributes_v2($product, $action = 'product')
		{
			$attributes = array();

			if ($product->is_type('variation') && $action === 'product') {
				$_product = wc_get_product($product->get_parent_id());
				foreach ($product->get_variation_attributes() as $attribute_name => $attribute) {
					$name = str_replace('attribute_', '', $attribute_name);

					if (empty($attribute) && '0' !== $attribute) {
						continue;
					}

					// Taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`.
					if (0 === strpos($attribute_name, 'attribute_pa_')) {
						$option_term = get_term_by('slug', $attribute, $name);

						$attributes[] = array(
							'id'      => wc_attribute_taxonomy_id_by_name($name),
							'name'    => $this->get_attribute_taxonomy_name($name, $_product),
							// 'options' => $option_term && ! is_wp_error( $option_term ) ? $option_term->name : $attribute,
							'options' => [
								[
									'name' => $option_term && !is_wp_error($option_term) ? $option_term->name : $attribute,
									'slug' => $option_term && !is_wp_error($option_term) ? $option_term->slug : $attribute,
								]
							]
						);
					} else {
						$attributes[] = array(
							'id'      => 0,
							'name'    => $this->get_attribute_taxonomy_name($name, $_product),
							// 'options' => $attribute,
							'options' => [
								[
									'name' => $attribute,
									'slug' => sanitize_title($attribute),
								]
							],
						);
					}
				}
			} else {
				$is_variable = $product->is_type('variable');

				foreach ($product->get_attributes() as $attribute) {
					$attributes[] = array(
						'id'        => $attribute['is_taxonomy'] ? wc_attribute_taxonomy_id_by_name($attribute['name']) : 0,
						'name'      => $this->get_attribute_taxonomy_name($attribute['name'], $product),
						'slug'      => sanitize_title($attribute['name']),
						'position'  => (int) $attribute['position'],
						'visible'   => (bool) $attribute['is_visible'],
						'variation' => (bool) $attribute['is_variation'],
						'options'   => $this->get_attribute_options_v2($product->get_id(), $attribute, $is_variable ? $product->get_variation_attributes() : []),
					);
				}
			}

			return $attributes;
		}

		public function get_categories()
		{

			$taxonomy     = 'product_cat';
			$orderby      = 'name';
			$show_count   = 1;      // 1 for yes, 0 for no
			$pad_counts   = 0;      // 1 for yes, 0 for no
			$hierarchical = 1;      // 1 for yes, 0 for no
			$title        = '';
			$empty        = 0;

			$args = array(
				'taxonomy'     => $taxonomy,
				//'orderby'      => $orderby,
				'show_count'   => $show_count,
				'pad_counts'   => $pad_counts,
				'hierarchical' => $hierarchical,
				'title'        => $title,
				'hide_empty'   => $empty,
				'menu_order'   => 'asc',
			);

			$categories = get_categories($args);

			if (($key = array_search('uncategorized', array_column($categories, 'slug'))) !== false) {
				unset($categories[$key]);
			}

			$data = array();

			foreach ($categories as $key => $value) {

				$image_id = get_term_meta($value->term_id, 'thumbnail_id', true);
				$image    = '';

				if ($image_id) {
					$image = wp_get_attachment_url($image_id);
				}

				$data[] = array(
					'id'          => $value->term_id,
					'name'        => $value->name,
					'description' => $value->description,
					'parent'      => $value->parent,
					'count'       => $value->count,
					'image'       => $image,
				);
			}

			return $data;
		}

		public function get_rates($package)
		{

			$shipping = array();

			//if($package['rates'])
			foreach ($package['rates'] as $i => $method) {
				$shipping[$i]['id']        = $method->get_id();
				$shipping[$i]['label']     = $method->get_label();
				$shipping[$i]['cost']      = $method->get_cost();
				$shipping[$i]['method_id'] = $method->get_method_id();
				$shipping[$i]['taxes']     = $method->get_taxes();
			}

			return $shipping;
		}

		public function get_products_cart($data = array())
		{
			$addon_data = $data['addon_data'];
			$products   = array();

			if (!is_null($addon_data['simple'])) {
				$products = wc_get_products([
					'limit'            => -1,
					'include'          => array_map(fn ($simple_key) => $simple_key, array_keys($addon_data['simple'])),
					'suppress_filters' => true,
				]);
			}

			if (!is_null($addon_data['variation'])) {
				foreach ($addon_data['variation'] as $product_var_key => $product_var_value) {
					$products[] = wc_get_product($product_var_key);
				}
			}

			$results = array();

			foreach ($products as $product) {
				$product_id   = $product->get_id();
				$product_type = $product->get_type();

				$available_variations = $product->get_type() == 'variable' ? $product->get_available_variations() : null;
				$variation_attributes = $product->get_type() == 'variable' ? $product->get_variation_attributes() : null;

				$variation_options = array();
				$emptyValuesKeys   = array();

				if ($available_variations != null) {
					$values = array();

					foreach ($available_variations as $key => $value) {
						foreach ($value['attributes'] as $atr_key => $atr_value) {
							$available_variations[$key]['option'][] = array(
								'key'   => $atr_key,
								'value' => $this->attribute_slug_to_title($atr_key, $atr_value) //make it name
							);

							$values[] = $this->attribute_slug_to_title($atr_key, $atr_value);
							if (empty($atr_value)) {
								$emptyValuesKeys[] = $atr_key;
							}

							$variation = wc_get_product($value['variation_id']);

							// minmax quantity
							$available_variations[$key]['minmax_quantity'] = $this->get_variation_min_max_quantity($product, $value);

							unset($available_variations[$key]['min_qty']);
							unset($available_variations[$key]['max_qty']);

							$regular_price = $variation->get_regular_price();
							$sale_price    = $variation->get_sale_price();

							$available_variations[$key]['formated_price']       = $regular_price ? strip_tags(wc_price(wc_get_price_to_display($variation, array('price' => $regular_price)))) : strip_tags(wc_price(wc_get_price_to_display($variation, array('price' => $variation->get_price()))));
							$available_variations[$key]['formated_sales_price'] = $sale_price ? strip_tags(wc_price(wc_get_price_to_display($variation, array('price' => $sale_price)))) : null;
							$available_variations[$key]['meta_data']            = $variation->get_meta_data();
						}

						$available_variations[$key]['image_id'] = null;
					}

					if ($variation_attributes) {
						foreach ($variation_attributes as $attribute_name => $options) {

							$new_options = array();
							foreach (array_values($options) as $key => $value) {
								$new_options[] = $this->attribute_slug_to_title($attribute_name, $value);
							}
							if (!in_array('attribute_' . $attribute_name, $emptyValuesKeys)) {
								$options = array_intersect(array_values($new_options), $values);
							}
							$variation_options[] = array(
								'name'      => wc_attribute_label($attribute_name),
								'options'   => array_values($options),
								'attribute' => wc_attribute_label($attribute_name),
							);
						}
					}
				}

				$categories = get_the_terms($product->get_id(), 'product_cat');
				if (empty($categories)) {
					$categories = array();
				}

				$result = [
					'id'                   => $addon_data[$product_type][$product_id]['product_id'],
					'name'                 => $product->get_name(),
					'slug'                 => $product->get_slug(),
					'sku'                  => $product->get_sku('view'),
					'status'               => $product->get_status(),
					'type'                 => $product_type,
					'total_sales'          => (int) $product->get_total_sales(),
					'manage_stock'         => $product->get_manage_stock(),
					'stock_status'         => $product->get_stock_status(),
					'stock_quantity'       => $product->get_stock_quantity(),
					'sold_individually'    => is_plugin_active('minmax-quantity-for-woocommerce/woocommerce-minmax-quantity.php') ? false : $product->get_sold_individually(),
					'minmax_quantity'      => $this->get_min_max_quantity($product),
					'description'          => $product->get_description(),
					'short_description'    => $product->get_short_description(),
					'formated_price'       => $product->get_regular_price() ? strip_tags(wc_price(wc_get_price_to_display($product, array('price' => $product->get_regular_price())))) : strip_tags(wc_price(wc_get_price_to_display($product, array('price' => $product->get_price())))),
					'formated_sales_price' => $product->get_sale_price() ? strip_tags(wc_price(wc_get_price_to_display($product, array('price' => $product->get_sale_price())))) : null,
					'price'                => (float) $product->get_price(),
					'regular_price'        => (float) $product->get_regular_price(),
					'sale_price'           => (float) $product->get_sale_price(),
					'images'               => $this->get_images($product),
					'categories'           => $categories,
					'average_rating'       => wc_format_decimal($product->get_average_rating(), 2),
					'rating_count'         => $product->get_rating_count(),
					'attributes_v2'        => $this->get_attributes_v2($product),
					'permalink'            => $product->get_permalink(),
					'meta_data'            => $product->get_meta_data(),
					'availableVariations'  => $available_variations,
					'variationAttributes'  => $variation_attributes,

					// additional response for sync cart

					'is_selected'          => true,
					'cart_quantity'        => $addon_data[$product_type][$product_id]['quantity'],
					'price_total'          => (float) $addon_data[$product_type][$product_id]['subtotal_price'],
					'variant_id'           => $addon_data[$product_type][$product_id]['variation_id'],
					'variation_name'       => $addon_data[$product_type][$product_id]['variation_value'],
					'selected_variation'   => $addon_data[$product_type][$product_id]['variation_selected'],
					'selected_addons'      => $addon_data[$product_type][$product_id]['addons_selected'],
				];

				$wholesale_data = $product->get_meta('wholesale_customer_wholesale_price', true);

				if (!empty($wholesale_data)) {
					$result['is_variant'] = false;
					$result['wholesales'] = [['price' => $wholesale_data]];
				} else {
					$result['is_variant'] = true;
					$result['wholesales'] = [];

					$wholesale_variant_data = $product->get_meta('wholesale_customer_variations_with_wholesale_price', false);
					if (!empty($wholesale_variant_data)) {
						foreach ($wholesale_variant_data as $wholesale_variant) {
							$result['wholesales'][] = [
								'id' 	=> $wholesale_variant->value,
								'price' => get_post_meta($wholesale_variant->value, 'wholesale_customer_wholesale_price', true)
							];
						}
					}
				}

				$results[] = $result;
			}

			return $results;
		}

		public function nonce()
		{

			$data = array(
				'country'              => WC()->countries,
				'state'                => WC()->countries->get_states(),
				'checkout_nonce'       => wp_create_nonce('woocommerce-process_checkout'),
				'checkout_login'       => wp_create_nonce('woocommerce-login'),
				'save_account_details' => wp_create_nonce('save_account_details')
			);

			wp_send_json($data);
		}

		public function get_formatted_item_data($object)
		{
			$data              = $object->get_data();
			$format_decimal    = array(
				'discount_total',
				'discount_tax',
				'shipping_total',
				'shipping_tax',
				'shipping_total',
				'shipping_tax',
				'cart_tax',
				'total',
				'total_tax'
			);
			$format_date       = array('date_created', 'date_modified', 'date_completed', 'date_paid');
			$format_line_items = array('line_items', 'tax_lines', 'shipping_lines', 'fee_lines', 'coupon_lines');

			// Format decimal values.
			foreach ($format_decimal as $key) {
				$data[$key] = wc_format_decimal($data[$key]);
			}

			// Format date values.
			foreach ($format_date as $key) {
				$datetime              = $data[$key];
				$data[$key]          = wc_rest_prepare_date_response($datetime, false);
				$data[$key . '_gmt'] = wc_rest_prepare_date_response($datetime);
			}

			// Format the order status.
			$data['status'] = 'wc-' === substr($data['status'], 0, 3) ? substr($data['status'], 3) : $data['status'];

			// Format line items.
			foreach ($format_line_items as $key) {
				$data[$key] = array_values(array_map(array($this, 'get_order_item_data'), $data[$key]));
			}

			// Refunds.
			$data['refunds'] = array();
			foreach ($object->get_refunds() as $refund) {
				$data['refunds'][] = array(
					'id'     => $refund->get_id(),
					'reason' => $refund->get_reason() ? $refund->get_reason() : '',
					'total'  => '-' . wc_format_decimal($refund->get_amount()),
				);
			}

			// Additional data for biteship
			if (is_plugin_active('biteship/biteship.php') && $data['shipping_lines'][0]['method_id'] === 'biteship') {
				$data['biteship_data'] = $this->get_order_data_biteship($object, $data['shipping_lines'][0]);
			} else {
				$data['biteship_data'] = null;
			}

			// phone shipping is empty
			if (empty($data['shipping']['phone'])) {
				$data['shipping']['phone'] = $data['billing']['phone'];
			}

			$payment_description = wc_get_payment_gateway_by_order($object)->description;

			return array(
				'id'                   => $object->get_id(),
				'parent_id'            => $data['parent_id'],
				'number'               => $data['number'],
				'order_key'            => $data['order_key'],
				'created_via'          => $data['created_via'],
				'version'              => $data['version'],
				'status'               => $data['status'],
				'currency'             => $data['currency'],
				'date_created'         => $data['date_created'],
				'date_created_gmt'     => $data['date_created_gmt'],
				'date_modified'        => $data['date_modified'],
				'date_modified_gmt'    => $data['date_modified_gmt'],
				'discount_total'       => $data['discount_total'],
				'discount_tax'         => $data['discount_tax'],
				'shipping_total'       => $data['shipping_total'],
				'shipping_tax'         => $data['shipping_tax'],
				'cart_tax'             => $data['cart_tax'],
				'subtotal_items'       => (string) $object->get_subtotal(),
				'total'                => $data['total'],
				'total_tax'            => $data['total_tax'],
				'prices_include_tax'   => $data['prices_include_tax'],
				'customer_id'          => $data['customer_id'],
				'customer_ip_address'  => $data['customer_ip_address'],
				'customer_user_agent'  => $data['customer_user_agent'],
				'customer_note'        => $data['customer_note'],
				'billing'              => $data['billing'],
				'shipping'             => $data['shipping'],
				'payment_method'       => $data['payment_method'],
				'payment_method_title' => $data['payment_method_title'],
				'payment_description'  => strip_tags($payment_description),
				'transaction_id'       => $data['transaction_id'],
				'date_paid'            => $data['date_paid'],
				'date_paid_gmt'        => $data['date_paid_gmt'],
				'date_completed'       => $data['date_completed'],
				'date_completed_gmt'   => $data['date_completed_gmt'],
				'cart_hash'            => $data['cart_hash'],
				'meta_data'            => $data['meta_data'],
				'line_items'           => $data['line_items'],
				'tax_lines'            => $data['tax_lines'],
				'shipping_lines'       => $data['shipping_lines'],
				'biteship_data'        => $data['biteship_data'],
				'fee_lines'            => $data['fee_lines'],
				'coupon_lines'         => $data['coupon_lines'],
				'refunds'              => $data['refunds'],
				'decimals'             => wc_get_price_decimals(),
			);
		}

		public function get_order_item_data($item)
		{
			$data 						= $item->get_data();
			$data['selected_variation'] = [];
			$data['selected_addons']    = [];

			$format_decimal = array('subtotal', 'subtotal_tax', 'total', 'total_tax', 'tax_total', 'shipping_tax_total');

			// Format decimal values.
			foreach ($format_decimal as $key) {
				if (isset($data[$key])) {
					$data[$key] = wc_format_decimal($data[$key]);
				}
			}

			// Add SKU and PRICE to products.
			if (is_callable(array($item, 'get_product'))) {
				$data['sku']   = $item->get_product() ? $item->get_product()->get_sku() : null;
				$data['price'] = $item->get_quantity() ? $item->get_total() / $item->get_quantity() : 0;
			}

			// Format taxes.
			if (!empty($data['taxes']['total'])) {
				$taxes = array();

				foreach ($data['taxes']['total'] as $tax_rate_id => $tax) {
					$taxes[] = array(
						'id'       => $tax_rate_id,
						'total'    => $tax,
						'subtotal' => isset($data['taxes']['subtotal'][$tax_rate_id]) ? $data['taxes']['subtotal'][$tax_rate_id] : '',
					);
				}
				$data['taxes'] = $taxes;
			} elseif (isset($data['taxes'])) {
				$data['taxes'] = array();
			}

			// Remove names for coupons, taxes and shipping.
			if (isset($data['code']) || isset($data['rate_code']) || isset($data['method_title'])) {
				unset($data['name']);
			}

			// selected variation
			if (isset($data['variation_id']) && !empty($data['variation_id'])) {
				$variation_product = wc_get_product($data['variation_id']);
				$attributes = $variation_product->get_attributes();

				if (!empty($attributes)) {
					foreach ($attributes as $attribute_key => $attribute_val) {
						$data['selected_variation'][] = [
							'variation_name' => $attribute_key,
							'variation_value' => $attribute_val
						];
					}
				}
			}

			// if product
			$woocommerce_placeholder_url = wc_placeholder_img_src();
			if (isset($data['product_id'])) {
				$product = wc_get_product(!empty($data['variation_id']) ? $data['variation_id'] : $data['product_id']);

				if ($product) {
					$image_id = $product->get_image_id();

					if ($image_id) {
						$product_image = wp_get_attachment_image_url($image_id, 'full');
					}
				}

				$data['image'] = $product_image ?? $woocommerce_placeholder_url;
			}

			// selected addons
			if (!empty($addons = $item->get_meta('_pao_ids'))) {
				$data['selected_addons'] = $addons;
			}

			// Remove props we don't want to expose.
			unset($data['order_id']);
			unset($data['type']);

			return $data;
		}

		/**
		 * Order by rating post clauses.
		 *
		 * @param array $args Query args.
		 *
		 * @return array
		 */
		public function order_by_rating_post_clauses($args)
		{
			$args['join']    = $this->append_product_sorting_table_join($args['join']);
			$args['orderby'] = ' wc_product_meta_lookup.average_rating DESC, wc_product_meta_lookup.product_id DESC ';

			return $args;
		}

		/**
		 * Join wc_product_meta_lookup to posts if not already joined.
		 *
		 * @param string $sql SQL join.
		 *
		 * @return string
		 */
		private function append_product_sorting_table_join($sql)
		{
			global $wpdb;

			if (!strstr($sql, 'wc_product_meta_lookup')) {
				$sql .= " LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON $wpdb->posts.ID = wc_product_meta_lookup.product_id ";
			}

			return $sql;
		}

		private function get_order_data_biteship($order, $shipping_lines)
		{
			$biteship_license = get_option('biteship_licence');

			if (empty($biteship_license)) {
				return null;
			}

			$shipping_method = $order->get_items('shipping');
			$shipping_method = array_values($shipping_method)[0];

			$biteship_order_id     = $shipping_method->get_meta('biteship_order_id');
			$biteship_waybill_id   = $shipping_method->get_meta('tracking_waybill_id');
			$biteship_courier_code = $shipping_method->get_meta('courier_code');

			// order already delivered
			if (!empty($revo_biteship_custom_data = $shipping_method->get_meta('revo_biteship_custom_data'))) {
				return $revo_biteship_custom_data;
			}

			if (!empty($biteship_order_id)) {
				$biteship_base_url = 'https://api.biteship.com/v1/';

				$biteship_get_order_api = revo_pos_open_curl($biteship_base_url . 'orders/' . $biteship_order_id, 'GET', [], [
					'Authorization: Bearer ' . $biteship_license,
					'Content-Type: application/json'
				]);

				if ($biteship_get_order_api['success']) {
					$link_tracking   = $biteship_get_order_api['courier']['link'];
					$shipment_status = $biteship_get_order_api['status'];

					if ($biteship_courier_code === 'grab') {
						$link_tracking = 'https://express.grab.com/track/orders?ids=' . $biteship_waybill_id;
					}

					if ($shipment_status === 'delivered') {
						wc_update_order_item_meta($shipping_method->get_id(), 'revo_biteship_custom_data', [
							'biteship_order_id' => $biteship_order_id,
							'courier_code'      => $biteship_courier_code,
							'shipment_number'   => (string) $shipping_method->get_meta('tracking_waybill_id'),
							'link_tracking'     => $link_tracking ?? '',
							'delivery_status'   => $shipment_status
						]);
					}
				}
			}

			return [
				'biteship_order_id' => $biteship_order_id,
				'courier_code'      => $biteship_courier_code,
				'shipment_number'   => $biteship_waybill_id,
				'link_tracking'     => $link_tracking ?? '',
				'delivery_status'   => $shipment_status ?? ''
			];
		}

		private function get_label_product( $product_id )
		{
			global $wpdb;

			$get_label_product = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT image FROM revo_label_product WHERE type = %s AND products LIKE %s LIMIT 3",
					'label-black-friday',
					"%{$product_id}%"
				)
			);

			$get_status_label	= query_revo_mobile_variable( '"enable_promo_label_on_product_card"' );
			$result				= array();

			if ( empty( $get_status_label ) || $get_status_label[0]->description === 'show' ) {
				foreach ( $get_label_product as $value ) {
					$result[] = $value->image;
				}
			}

			return $result;
		}

		public static function get_instance()
		{
			if (!isset(self::$instance)) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}

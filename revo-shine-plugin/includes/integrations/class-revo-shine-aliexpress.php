<?php

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('Revo_Shine_Aliexpress')) {
	class Revo_Shine_Aliexpress extends Revo_Shine_Integration
	{
		private string $sub, $namespace;

		private static $_instance = null;

		public function __construct()
		{
			if (!$this->collect_plugin('ali2woo/ali2woo.php')) {
				return;
			}

			$this->sub = 'aliexpress';
			$this->namespace = REVO_SHINE_NAMESPACE_API;

			add_action('rest_api_init', array($this, 'register_routes_api'));
		}

		public function register_routes_api()
		{
			register_rest_route($this->namespace, $this->sub . '/countries', array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array($this, 'rest_aliexpress_get_countries'),
				'permission_callback' => '__return_true'
			));

			register_rest_route($this->namespace, $this->sub . '/shipping', array(
				'methods'             => 'POST',
				'callback'            => array($this, 'rest_aliexpress_shipping'),
				'permission_callback' => '__return_true'
			));
		}

		public function rest_aliexpress_get_countries()
		{
			$countries = A2W_Shipping::get_countries();

			return $countries;
		}

		public function rest_aliexpress_shipping()
		{
			$country    = cek_raw('country');
			$quantity   = cek_raw('quantity');
			$product_id = cek_raw('product_id');

			if (!$country) {
				echo json_encode(A2W_ResultBuilder::buildError("load_product_shipping_info: country is required."));

				return;
			}

			$countries     = A2W_Shipping::get_countries();
			$country_label = $countries[$country];

			if (empty($country_label)) {
				echo json_encode(A2W_ResultBuilder::buildError("This product can not be delivered to {$country_label}"));

				return;
			}

			$product = wc_get_product($product_id);

			if (!$product) {
				echo json_encode(A2W_ResultBuilder::buildError("load_product_shipping_info: bad product ID."));

				return;
			}

			$result = A2W_Utils::get_product_shipping_info($product, !empty($quantity) ? $quantity : 1, $country, false);

			$shipping_info = str_replace('{country}', $country_label, a2w_get_setting('aliship_product_not_available_message'));

			$normalized_methods = array();

			foreach ($result['items'] as $method) {
				$normalized_method = A2W_Shipping::get_normalized($method, $country, "select");

				if (!$normalized_method) {
					continue;
				}

				$normalized_methods[] = $normalized_method;
			}

			$result['items'] = $normalized_methods;

			echo json_encode(A2W_ResultBuilder::buildOk(array(
				'products'      => $result,
				'shipping_info' => $shipping_info
			)));
		}

		public static function instance()
		{
			if (is_null(self::$_instance)) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}
}

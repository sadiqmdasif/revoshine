<?php

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('Revo_Shine_Init')) {
	final class Revo_Shine_Init
	{
		private static $_instance = null;

		public function __construct()
		{
			$this->init_hooks();

			$this->load_dependencies();

			$this->load_integrations();
		}

		public function init_hooks()
		{
			add_action('save_post', array($this, 'save_post'), 10, 3);
			add_action('before_delete_post', array($this, 'action_before_delete_post'), 10, 2);

			add_action('created_term', array($this, 'rebuild_cache'), 10, 3);
			add_action('edited_term',  array($this, 'rebuild_cache'), 10, 3);
			add_action('delete_term',  array($this, 'rebuild_cache'), 10, 5);

			add_action('woocommerce_attribute_added',  array($this, 'rebuild_cache'), 10, 2);
			add_action('woocommerce_attribute_updated',  array($this, 'rebuild_cache'), 10, 2);
			add_action('woocommerce_attribute_deleted',  array($this, 'rebuild_cache'), 10, 3);

			add_action('woocommerce_new_order', 'revo_shine_new_order', 10, 1);
			add_action('woocommerce_order_status_changed', 'revo_shine_order_status_changed', 10, 3);
			add_action('woocommerce_checkout_create_order_line_item', array($this, 'checkout_create_order_line_item'), 90, 4);
			add_action('woocommerce_thankyou', array($this, 'custom_woocommerce_thankyou'));

			add_action('init', array($this, 'custom_video_rules'));
			add_action('wp_loaded', array($this, 'wp_loaded'));
		}

		public function load_dependencies()
		{
			if (is_admin()) {
				require_once REVO_SHINE_ABSPATH . 'includes/class-revo-shine-install.php';
				require_once REVO_SHINE_ABSPATH . 'includes/class-revo-shine-admin-api.php';
			}

			require_once REVO_SHINE_ABSPATH . 'includes/class-revo-shine-rest-api.php';
			require_once REVO_SHINE_ABSPATH . 'includes/services/class-revo-shine-coupon.php';
			require_once REVO_SHINE_ABSPATH . 'includes/services/class-revo-shine-video.php';
		}

		public function load_integrations()
		{
			require_once REVO_SHINE_ABSPATH . 'includes/abstracts/class-revo-shine-integration.php';
			require_once REVO_SHINE_ABSPATH . 'includes/integrations/class-revo-shine-aliexpress.php';
			require_once REVO_SHINE_ABSPATH . 'includes/integrations/class-revo-shine-multilang.php';
			require_once REVO_SHINE_ABSPATH . 'includes/integrations/class-revo-shine-multiple-address.php';
			require_once REVO_SHINE_ABSPATH . 'includes/integrations/class-revo-shine-fox-currency.php';

			$classes = [
				Revo_Shine_Aliexpress::class,
				Revo_Shine_Multilang::class,
				Revo_Shine_Multiple_Address::class,
				Revo_Shine_Fox_Currency::class
			];

			foreach ($classes as $class) {
				if (class_exists($class)) {
					$GLOBALS[$class] = $class::instance();
				}
			}
		}

		public function wp_loaded()
		{
			if (is_admin()) {
				return;
			}

			$url_model 	  = $_GET['model'] ?? '';
			$url_redirect = $_SERVER['REDIRECT_URL'] ?? '';

			if (strpos($url_redirect, 'revo-checkout') === false && $url_model !== 'revo-checkout') {
				return;
			}

			require_once REVO_SHINE_ABSPATH . 'includes/services/class-revo-shine-checkout.php';

			$checkout_service = new Revo_Shine_Checkout();

			if ($url_model === 'revo-checkout') {
				return;
			}

			$checkout_service->run_checkout();
		}

		public function rebuild_cache()
		{
			revo_shine_rebuild_cache('revo_home_data');
			revo_shine_generate_static_file('revo_product_data');

			revo_shine_clear_caches();
		}

		public function custom_woocommerce_thankyou($order_id)
		{
			if (is_plugin_active('indeed-affiliate-pro/indeed-affiliate-pro.php')) {
				require_once REVO_SHINE_ABSPATH . 'includes/services/class-revo-shine-checkout.php';

				$checkout_service = new Revo_Shine_Checkout();
				$checkout_service->update_data_checkout($order_id);
			}
		}

		public function checkout_create_order_line_item($item, $cart_item_key, $values, $order)
		{
			if (isset($values['from_api']) && $values['from_api'] === true) {
				$order->update_meta_data('_created_via', 'rest-api');
			}
		}

		public function custom_video_rules()
		{
			register_post_type('video', array(
				'labels'             => array(
					'name'          => __('Videos'),
					'singular_name' => __('Video')
				),
				'public'             => true,
				'publicly_queryable' => true,
				'query_var'          => true,
				'rewrite'            => array('slug' => 'video'),
				'has_archive'        => true,
				'capability_type'    => 'post'
			));

			flush_rewrite_rules();
		}

		public function save_post($post_id, $post, $is_update)
		{
			$woocommerce_product_tax = ['product', 'product_variation'];

			if ($is_update === false || $post->post_status === 'auto-draft' || !in_array($post->post_type, $woocommerce_product_tax)) {
				return;
			}

			$this->rebuild_cache();
		}

		public function action_before_delete_post($post_id, $post)
		{
			$woocommerce_product_tax = ['product', 'product_variation'];

			if (!in_array($post->post_type, $woocommerce_product_tax)) {
				return;
			}

			$this->rebuild_cache();
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

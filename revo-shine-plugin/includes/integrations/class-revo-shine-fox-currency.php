<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Revo_Shine_Fox_Currency')) {
    class Revo_Shine_Fox_Currency extends Revo_Shine_Integration
    {
        private string $sub, $namespace;

        private static $_instance = null;

        public function __construct()
        {
            if (!$this->collect_plugin('woocommerce-currency-switcher/index.php')) {
                return;
            }

            $this->sub = 'woocs';
            $this->namespace = REVO_SHINE_NAMESPACE_API;

            add_action('rest_api_init', array($this, 'register_routes_api'));
        }

        public function register_routes_api()
        {
            register_rest_route($this->namespace, $this->sub . '/currencies', array(
                'methods'             => 'GET',
                'callback'            => [$this, 'rest_get_currencies'],
                'permission_callback' => '__return_true'
            ));
        }

        public function rest_get_currencies($request)
        {
            $currencies = $this->get_currencies();

            return new WP_REST_Response(array_values($currencies), 200);
        }

        public function get_currencies()
        {
            $currencies = get_option('woocs', array());

            return apply_filters('woocs_currency_data_manipulation', $currencies);
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

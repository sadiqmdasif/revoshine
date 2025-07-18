<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Revo_Shine_Rest_Api')) {
    class Revo_Shine_Rest_Api
    {
        private static $_instance = null;

        public function __construct()
        {
            // if ((strpos($_SERVER['REDIRECT_URL'] ?? '', 'revo-admin') !== false) && !empty($oauth_params = get_oauth_parameters())) {
            //     if (isset($oauth_params['oauth_consumer_key'])) {
            //         security_oauth();
            //     }
            // }

            $this->includes();

            $this->init_hooks();
        }

        public function init_hooks()
        {
            add_action('rest_api_init', array($this, 'rest_api_init'));

            add_filter('revo_shine_is_coupon_valid', array($this, 'check_coupon_is_valid'), 10, 3);
            add_filter('woocommerce_rest_prepare_customer', array($this, 'add_customer_data'), 99, 3);
        }

        public function includes()
        {
            require_once REVO_SHINE_ABSPATH . 'includes/api/legacy/revo-shine-legacy-routes-api.php';
            require_once REVO_SHINE_ABSPATH . 'includes/api/legacy/revo-shine-legacy-function-api.php';
            require_once REVO_SHINE_ABSPATH . 'includes/api/class-revo-shine-checkout-controller.php';
            require_once REVO_SHINE_ABSPATH . 'includes/api/class-revo-shine-user-controller.php';
        }

        public function rest_api_init()
        {
            $classes = [
                Revo_Shine_User_Controller::class,
                Revo_Shine_Checkout_Controller::class
            ];

            foreach ($classes as $class) {
                if (class_exists($class)) {
                    $class = new $class();
                    $class->run();
                }
            }
        }

        public function check_coupon_is_valid($is_valid, $coupon, $args)
        {
            // coupon expired
            if (null !== ($coupon_expired_data = $coupon->get_date_expires('date'))) {
                if (current_time('timestamp') > $coupon_expired_data->getOffsetTimestamp()) {
                    return false;
                }
            }

            // usage limits per user
            if (0 < ($usage_limit_per_user = $coupon->get_usage_limit_per_user())) {
                if (in_array($args['user']->ID, $coupon->get_used_by())) {
                    return false;
                }
            }

            // check user restrictions
            $user_restrictions = $coupon->get_email_restrictions();
            if (!empty($user_restrictions)) {
                $allowed_parts_of_email = [];
                foreach ($user_restrictions as $email) {
                    if (substr($email, 0, 1) === '*') {
                        $allowed_parts_of_email[] = str_replace('*@', '', $email);
                    }
                }

                if (!empty($allowed_parts_of_email)) {
                    $filter_allowed_parts_of_email = array_filter($allowed_parts_of_email, function ($email_domain) use ($args) {
                        return strpos($args['user']->user_email, $email_domain) !== false;
                    });

                    if (empty($filter_allowed_parts_of_email)) {
                        return false;
                    }
                } elseif (!in_array($args['user']->user_email, $user_restrictions)) {
                    return false;
                }
            }

            return $is_valid;
        }

        public function add_customer_data($response, $user_data, $request)
        {
            $response->data['revo_custom_data'] = [
                'location_coordinate' => [
                    'latitude'  => '',
                    'longitude' => '',
                    'address'   => ''
                ]
            ];

            if (is_plugin_active('biteship/biteship.php')) {
                $location_coordinate = get_user_meta($user_data->ID, 'billing_biteship_location_coordinate', true);
                $location_coordinate = explode(',', (empty($location_coordinate) ? ',' : $location_coordinate));

                $response->data['revo_custom_data']['location_coordinate']['address']   = get_user_meta($user_data->ID, 'billing_biteship_location', true);
                $response->data['revo_custom_data']['location_coordinate']['latitude']  = $location_coordinate[0];
                $response->data['revo_custom_data']['location_coordinate']['longitude'] = $location_coordinate[1];
            }

            return $response;
        }

        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }
    }

    Revo_Shine_Rest_Api::instance();
}

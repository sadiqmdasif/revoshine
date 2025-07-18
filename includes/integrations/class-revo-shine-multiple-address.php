<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Revo_Shine_Multiple_Address')) {

    class Revo_Shine_Multiple_Address extends Revo_Shine_Integration
    {
        private string $sub, $namespace;

        private static $_instance = null;

        public function __construct()
        {
            if (!$this->collect_plugin('themehigh-multiple-addresses/themehigh-multiple-addresses.php')) {
                return;
            }

            $this->sub       = 'shipping-address';
            $this->namespace = REVO_SHINE_NAMESPACE_API;

            add_action('rest_api_init', array($this, 'register_routes_api'));
        }

        public function register_routes_api()
        {
            register_rest_route($this->namespace, $this->sub, array(
                'methods'             => 'GET',
                'callback'            => array($this, 'rest_get_address'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, $this->sub . '/insert', array(
                'methods'             => 'POST',
                'callback'            => array($this, 'rest_insert_address'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, $this->sub . '/update', array(
                'methods'             => 'POST',
                'callback'            => array($this, 'rest_update_address'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, $this->sub . '/delete', array(
                'methods'             => 'POST',
                'callback'            => array($this, 'rest_delete_address'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, $this->sub . '/set-default', array(
                'methods'             => 'POST',
                'callback'            => array($this, 'rest_set_default'),
                'permission_callback' => '__return_true'
            ));
        }

        public function rest_get_address($request)
        {
            $user = $this->validate_user($request->get_param('user_id'));

            try {
                $response  = array();
                $addresses = get_user_meta($user->ID, 'thwma_custom_address', true);

                if (empty($addresses['billing'])) {
                    $custom_address  = array();
                    $default_address = THMAF_Utils::get_default_address($user->ID, 'billing');

                    $custom_address['address_0'] = $default_address;
                    $custom_addresses['billing'] = $custom_address;

                    update_user_meta($user->ID, THMAF_Utils::ADDRESS_KEY, $custom_addresses);

                    $addresses = get_user_meta($user->ID, 'thwma_custom_address', true);
                }

                if (!empty($addresses['billing'])) {
                    foreach ($addresses['billing'] as $address_key => $address) {
                        if ($address_key !== $addresses['default_billing']) {
                            $address['default_address'] = '0';
                        } else {
                            $address['default_address'] = '1';
                        }

                        if (!isset($address['billing_heading'])) {
                            $address['billing_heading'] = '';
                        }

                        if (!isset($address['billing_address_2'])) {
                            $address['billing_address_2'] = '';
                        }

                        $address['address_key']          = $address_key;
                        $address['billing_state_name']   = $this->get_formated_address_name($address['billing_country'], $address['billing_state'], 'state');
                        $address['billing_country_name'] = $this->get_formated_address_name($address['billing_country']);

                        $response[] = $address;
                    }
                }

                return $response;
            } catch (\Throwable $th) {
                wp_send_json_error($th->getMessage(), 500);
            }
        }

        public function rest_insert_address($request)
        {
            $user_id = $request['user_id'];
            $type    = 'billing';
            $address = $this->validate_data($request->get_params());

            try {
                $custom_addresses = get_user_meta($user_id, THMAF_Utils::ADDRESS_KEY, true);
                $saved_address    = THMAF_Utils::get_custom_addresses($user_id, $type);

                if (!is_array($saved_address)) {
                    if (!is_array($custom_addresses)) {
                        $custom_addresses = array();
                    }

                    $custom_address = array();
                    $default_address = THMAF_Utils::get_default_address($user_id, $type);
                    $custom_address['address_0'] = $default_address;
                    $custom_address['address_1'] = $address;
                    $custom_addresses[$type] = $custom_address;
                } else {
                    if (is_array($saved_address)) {
                        if (count($saved_address) >= THMAF_Utils::get_general_settings()['settings_billing']['billing_address_limit']) {
                            wp_send_json(array(
                                'status'  => 'error',
                                'message' => 'You have reached the maximum number of addresses allowed'
                            ), 200);
                        }

                        if (isset($custom_addresses[$type])) {
                            $exist_custom            = $custom_addresses[$type];
                            $new_key_id              = THMAF_Utils::get_new_custom_id($user_id, $type);
                            $new_key                 = 'address_' . esc_attr($new_key_id);
                            $custom_address[$new_key] = $address;
                            $custom_addresses[$type] = array_merge($exist_custom, $custom_address);
                        }
                    }
                }

                update_user_meta($user_id, THMAF_Utils::ADDRESS_KEY, $custom_addresses);

                wp_send_json(array(
                    'status'  => 'success',
                    'message' => 'Address saved successfully'
                ), 200);
            } catch (\Throwable $th) {
                wp_send_json_error($th->getMessage(), 500);
            }
        }

        public function rest_set_default($request)
        {
            $user_id     = $request['user_id'];
            $address_key = $request['address_key'];
            $type        = 'billing';

            try {
                $service = new THMAF_Public('themehigh-multiple-addresses', THMAF_VERSION);

                $service->change_default_address($user_id, $type, $address_key);

                wp_send_json(array(
                    'status'  => 'success',
                    'message' => 'Address updated successfully'
                ), 200);
            } catch (\Throwable $th) {
                wp_send_json_error($th->getMessage(), 500);
            }
        }

        public function rest_update_address($request)
        {
            $user_id     = $request['user_id'];
            $address_key = $request['address_key'];
            $type        = 'billing';
            $address     = $this->validate_data($request->get_params());

            try {
                THMAF_Utils::update_address_to_user($user_id, $address, $type, $address_key);

                wp_send_json(array(
                    'status'  => 'success',
                    'message' => 'Address updated successfully'
                ), 200);
            } catch (\Throwable $th) {
                wp_send_json_error($th->getMessage(), 500);
            }
        }

        public function rest_delete_address($request)
        {
            $user_id     = $request['user_id'];
            $address_key = $request['address_key'];
            $type        = 'billing';

            try {
                $custom_addresses = get_user_meta($user_id, THMAF_Utils::ADDRESS_KEY, true);

                unset($custom_addresses[$type][$address_key]);

                update_user_meta($user_id, THMAF_Utils::ADDRESS_KEY, $custom_addresses);

                wp_send_json(array(
                    'status'  => 'success',
                    'message' => 'Address deleted successfully'
                ), 200);
            } catch (\Throwable $th) {
                wp_send_json_error($th->getMessage(), 500);
            }
        }

        private function validate_user($user_id)
        {
            if (empty($user_id)) {
                wp_send_json(array(
                    'status'  => 'error',
                    'message' => 'User ID is required'
                ), 422);
            }

            $user = get_userdata($user_id);

            if (!$user) {
                wp_send_json(array(
                    'status'  => 'error',
                    'message' => 'User not found'
                ), 404);
            }

            return $user;
        }

        private function validate_data($datas)
        {
            $address = array();

            foreach ($datas as $data_key => $data_value) {
                if (strpos($data_key, 'billing') !== false) {
                    $address[$data_key] = $data_value;
                }
            }

            return $address;
        }

        private function get_formated_address_name($country_code = '', $state_code = '', $key = 'country')
        {
            if ($key === 'state') {
                return WC()->countries->get_states($country_code)[$state_code] ?? '';
            }

            return WC()->countries->countries[$country_code] ?? '';
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

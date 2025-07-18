<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Revo_Shine_Checkout')) {
    class Revo_Shine_Checkout
    {
        public function __construct()
        {
            if (filter_has_var(INPUT_GET, 'order') && strlen(filter_input(INPUT_GET, 'order')) > 0) {
                add_filter('woocommerce_is_checkout', '__return_true');
            }

            add_action('wp_enqueue_scripts', array($this, 'remove_style_checkout'), 20);
        }

        public function remove_style_checkout()
        {
            wp_enqueue_style('revo-shine-enqueue-1', revo_shine_url() . 'assets/css/enqueue1.css', array(), REVO_SHINE_PLUGIN_VERSION);
        }

        public function run_checkout()
        {
            if (isset($_POST['order'])) {
                $data = json_decode(urldecode(base64_decode($_POST['order'])), true);
            } elseif (filter_has_var(INPUT_GET, 'order')) {
                $data = filter_has_var(INPUT_GET, 'order') ? json_decode(urldecode(base64_decode(filter_input(INPUT_GET, 'order'))), true) : [];
            }

            if (!isset($data)) {
                wp_send_json(['error' => 'Invalid data']);
            }

            global $woocommerce;

            if (isset($data['token']) && $data['token'] != "") {

                // Validate the cookie token
                $userId = wp_validate_auth_cookie($data['token'], 'logged_in');

                if (!$userId) {
                    echo "Invalid authentication cookie. Please log out and try to login again!";

                    return;
                }

                // Check user and authentication
                $user = get_userdata($userId);
                if ($user) {
                    wp_set_current_user($userId, $user->user_login);
                    wp_set_auth_cookie($userId);

                    // $url = filter_has_var(INPUT_SERVER, 'REQUEST_URI') ? filter_input(INPUT_SERVER, 'REQUEST_URI') : '';
                    // header("Refresh: 0; url=$url");
                }

                if (isset($data['wallet_tab'])) {
                    $urlWallet = get_permalink(get_option('woocommerce_myaccount_page_id')) . 'woo-wallet/add';
                    if ($data['wallet_tab'] == 'transfer') {
                        $urlWallet = get_permalink(get_option('woocommerce_myaccount_page_id')) . 'woo-wallet/transfer';
                    }
                    wp_redirect($urlWallet . '?model=revo-checkout');
                    exit();
                }
            } elseif (!isset($data['token']) || $data['token'] == "") {

                if (get_option('woocommerce_enable_guest_checkout') != 'yes') {
                    echo "Store not allow to checkout without an account. You can login to checkout";

                    return;
                }
            }

            setcookie('revo_checkout', true, time() + (86400 * 30), '/');
            $woocommerce->session->set('refresh_totals', true);

            if (!empty($data['line_items'])) {

                $woocommerce->cart->empty_cart();

                $products = $data['line_items'];
                $additional_data = [
                    'from_api' => true,
                ];

                foreach ($products as $product) {
                    $productId   = absint($product['product_id']);
                    $quantity    = $product['quantity'];
                    $variationId = $product['variation_id'] ?? null;

                    // Check the product variation
                    if (!empty($variationId)) {
                        $productVariable = new WC_Product_Variable($productId);
                        $listVariations  = $productVariable->get_available_variations();
                        foreach ($listVariations as $vartiation => $value) {
                            if ($variationId == $value['variation_id']) {
                                if (isset($product['variation'][0]) != false) {
                                    $attribute = $product['variation'][0];
                                } else {
                                    $attribute = $value['attributes'];
                                }
                                $woocommerce->cart->add_to_cart($productId, $quantity, $variationId, $attribute, $additional_data);
                            }
                        }
                    } else {
                        $woocommerce->cart->add_to_cart($productId, $quantity, 0, [], $additional_data);
                    }
                }
            }

            if (!empty($data['coupon_lines'])) {
                $coupons = $data['coupon_lines'];
                foreach ($coupons as $coupon) {
                    $woocommerce->cart->add_discount($coupon['code']);
                }
            }

            // aliexpress
            if (is_plugin_active('ali2woo/ali2woo.php')) {
                $aliexpress = $data['aliexpress'] ?? [];

                if (count($aliexpress) >= 1) {
                    $carts = array_values(WC()->cart->get_cart());

                    foreach ($carts as $key => $cart) {
                        foreach ($aliexpress as $a) {
                            if ($key + 1 == $a['cart']) {
                                WC()->cart->cart_contents[$cart['key']]['a2w_shipping_method'] = $a['shipping_method'];
                            }
                        }
                    }

                    $packages = WC()->cart->get_shipping_packages();
                    foreach ($packages as $package_key => $package) {
                        WC()->session->set('shipping_for_package_' . $package_key, false);
                    }
                }

                WC()->cart->calculate_shipping();
                WC()->cart->calculate_totals();
            }
            // end aliexpress

            $url_woo = wc_get_checkout_url();

            // polylang active
            if (is_plugin_active('polylang/polylang.php')) {
                if (function_exists('pll_default_language') || function_exists('pll_the_languages')) {
                    if (!empty($data['lang'])) {
                        $translations = pll_the_languages(array('raw' => true, 'hide_if_empty' => false));

                        if (array_key_exists($data['lang'], $translations)) {
                            $lang = $data['lang'];

                            $checkout_post_id      = url_to_postid($url_woo);
                            $checkout_translate_id = pll_get_post($checkout_post_id, $lang);

                            if ($checkout_translate_id) {
                                $url_woo = get_page_link($checkout_translate_id);
                            }
                        }
                    }
                }
            }

            // WPML active
            if (is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
                $languages = apply_filters('wpml_active_languages', null);

                if (!empty($data['lang']) && array_key_exists($data['lang'], $languages)) {
                    $url_woo = apply_filters('wpml_permalink', $url_woo, $data['lang'], true);
                }
            }

            $query_args = ['model' => 'revo-checkout'];

            if (!empty($data['referral'])) {
                $query_args[get_option('uap_referral_variable', 'ref')] = $data['referral'];
            }

            if (!empty($data['video_id'] && !empty($data['date_product_cart']))) {
                global $wpdb;

                $check_data = $wpdb->get_row("SELECT * FROM revo_video_affiliate WHERE post_id = " . $data['video_id']);

                if ($check_data) {
                    foreach ($data['line_items'] as $item) {
                        $wpdb->insert('revo_video_affiliate_views', [
                            'video_id'        => $check_data->id,
                            'type'            => 'click',
                            'information'    => json_encode([
                                'user_id'        => $user->ID,
                                'type'            => 'cart',
                                'date_cart'        => $data['date_product_cart'],
                                'product_id'    => $item['product_id'],
                            ]),

                        ]);
                    }

                    $get_id_view = $wpdb->insert_id;

                    setcookie('view_id', $get_id_view, time() + (86400), '/');
                }
            }

            $url_woo = add_query_arg($query_args, $url_woo);

            wp_redirect($url_woo);
            exit;
        }

        public function update_data_checkout($order_id)
        {
            global $wpdb;

            if (!empty($_COOKIE['view_id'])) {
                $view_id = $_COOKIE['view_id'];
                if (!empty($view_id)) {
                    $get_data = $wpdb->get_row("SELECT * FROM revo_video_affiliate_views WHERE id = $view_id");
                    $get_user_data = $wpdb->get_row("SELECT * FROM revo_video_affiliate WHERE id = $get_data->video_id");

                    $information_array = json_decode($get_data->information, true);
                    $information_array['order_id'] = $order_id;

                    $updated_information = json_encode($information_array);

                    $date_cart = strtotime($information_array['date_cart']);
                    $two_days_ago = strtotime('-2 days');

                    if (empty($date_cart)) {
                        $wpdb->update('revo_video_affiliate_views', ['information' => $updated_information], ['id' => $view_id]);
                    } else {
                        $user_data = get_userdata($get_user_data->user_id);
                        $user_login = $user_data->data->user_login;

                        if ($user_data->roles !== 'administrator') {
                            if (is_plugin_active('indeed-affiliate-pro/indeed-affiliate-pro.php')) {

                                add_filter('uap_set_affiliate_id_filter', function ($affiliate_id) use ($wpdb, $user_login) {
                                    if (get_option('uap_default_ref_format') !== 'id') {
                                        $affiliate_user_data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}users WHERE user_login = '{$user_login}'", OBJECT);
                                        $affiliate_data      = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}uap_affiliates WHERE uid = '$affiliate_user_data->ID'", OBJECT);
                                        $affiliate_id          = $affiliate_data->id;
                                    } else {
                                        $affiliate_id = $user_login;
                                    }

                                    return $affiliate_id;
                                });

                                $obj = new Uap_Woo();
                                $obj->create_referral($order_id);
                            } else {
                                $wpdb->update('revo_video_affiliate_views', ['information' => $updated_information], ['id' => $view_id]);
                            }

                            $wpdb->update('revo_video_affiliate_views', ['information' => $updated_information], ['id' => $view_id]);
                        }

                        unset($_COOKIE['view_id']);
                        setcookie('view_id', '', time() - 3600, '/');
                    }
                }
            }
        }
    }
}

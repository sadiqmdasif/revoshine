<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Revo_Shine_Coupon')) {

    class Revo_Shine_Coupon
    {
        public $meta_key = 'revo_mobile_app_coupon';

        private static $_instance = null;

        public function __construct()
        {
            add_filter('woocommerce_coupon_data_tabs', array($this, 'add_coupon_data_tabs'));
            add_filter('woocommerce_coupon_data_panels', array($this, 'add_coupon_data_tabs_content'));
            add_filter('woocommerce_coupon_options_save', array($this, 'save_custom_coupon_fields'));
            add_filter('woocommerce_coupon_is_valid', array($this, 'mobile_app_coupon_check'), 99, 2);
        }

        public function add_coupon_data_tabs($tabs)
        {
            $tabs['revo_mobile_app_coupon_tab'] = array(
                'label'    => __('Mobile App', 'revoshine'),
                'target'   => 'revo_mobile_app_coupon_data',
                'class'    => array('show_if_coupon'),
            );

            return $tabs;
        }

        public function add_coupon_data_tabs_content()
        {
            global $post;

            $data_checkbox = get_post_meta($post->ID, $this->meta_key, true);

            ?>
                        <div id="revo_mobile_app_coupon_data" class="panel woocommerce_options_panel">
                            <div class="options_group">
                                <?php
                                woocommerce_wp_checkbox([
                                    'id'            => $this->meta_key,
                                    'label'         => 'Mobile app coupon',
                                    'wrapper_class' => 'form-field-wide',
                                    'description'   => __('Check this box if the coupon can only be used in Revo Apps mobile app.', 'revoshine'),
                                    'cbvalue'       => 'yes',
                                    'checked'       => $data_checkbox === 'yes',
                                ]);
                                ?>
                            </div>
                        </div>
            <?php
        }

        public function save_custom_coupon_fields($post_id)
        {
            update_post_meta($post_id, $this->meta_key, isset($_POST[$this->meta_key]) ? 'yes' : 'no');
        }

        public function mobile_app_coupon_check($is_valid, $coupon)
        {
            if ((defined('REST_REQUEST') && REST_REQUEST) || isset($_COOKIE['revo_checkout'])) {
                return $is_valid;
            }

            $data_checkbox = get_post_meta($coupon->get_id(), $this->meta_key, true);

            if ($data_checkbox === 'yes') {
                throw new Exception(__('This coupon code can only be used on the mobile app', 'revoshine'));
            }

            return $is_valid;
        }

        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }
    }

    Revo_Shine_Coupon::instance();
}

<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Revo_Shine_Multilang')) {
    class Revo_Shine_Multilang extends Revo_Shine_Integration
    {
        protected $plugin = '';

        private static $_instance = null;

        public function __construct()
        {
            if (is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
                $this->plugin = 'wpml';
            } elseif (is_plugin_active('polylang/polylang.php')) {
                $this->plugin = 'polylang';
            }

            if (empty($this->plugin)) {
                return;
            }

            $this->set_plugin_status(true);

            $this->init_hooks();
        }

        public function init_hooks()
        {
            add_filter('revo_shine_banner_lang_code', array($this, 'get_banner_lang_code'), 10);
            add_filter('revo_shine_query_banner_lang', array($this, 'add_query_banner_lang'), 10);
            add_filter('revo_shine_get_products_args', array($this, 'add_lang_to_args'), 10);
        }

        public function add_lang_to_args($args)
        {
            $args['lang'] = $_GET['lang'] ?? '';

            return $args;
        }

        public function add_query_banner_lang()
        {
            return " AND lang='" . apply_filters('revo_shine_banner_lang_code', null) . "' ";
        }

        public function get_banner_lang_code()
        {
            if (strpos($_SERVER['REQUEST_URI'], 'revo-admin')  !== false && isset($_GET['lang'])) {
                return $_GET['lang'];
            }

            if (isset($_GET['banner_lang'])) {
                return $_GET['banner_lang'];
            }

            if ($this->plugin === 'wpml') {
                return apply_filters('wpml_default_language', NULL);
            } elseif ($this->plugin === 'polylang') {
                return pll_default_language();
            }
        }

        public function get_languages()
        {
            if ($this->plugin === 'wpml') {
                $languages = apply_filters('wpml_active_languages', []);
                if (empty($languages)) {
                    return [];
                }

                return array_values(array_map(function ($lang) {
                    return [
                        'code' => $lang['code'],
                        'name' => $lang['native_name'],
                    ];
                }, $languages));
            } elseif ($this->plugin === 'polylang') {
                $languages = pll_the_languages(['raw' => 1]);
                if (empty($languages)) {
                    return [];
                }

                return array_values(array_map(function ($lang) {
                    return [
                        'code' => $lang['slug'],
                        'name' => $lang['name'],
                    ];
                }, $languages));
            }

            return [];
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

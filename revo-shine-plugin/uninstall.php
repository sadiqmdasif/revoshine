<?php

/**
 * Trigger this file on Plugin uninstall
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

// if (!defined('WP_UNINSTALL_PLUGIN')) {
//     global $wpdb;
//     $wpdb->query("
//         DROP TABLE IF EXISTS revo_access_key;
//         DROP TABLE IF EXISTS revo_extend_products;
//         DROP TABLE IF EXISTS revo_flash_sale;
//         DROP TABLE IF EXISTS revo_hit_products;
//         DROP TABLE IF EXISTS revo_list_categories;
//         DROP TABLE IF EXISTS revo_list_mini_banner;
//         DROP TABLE IF EXISTS revo_mobile_slider;
//         DROP TABLE IF EXISTS revo_mobile_variable;
//         DROP TABLE IF EXISTS revo_notification;
//         DROP TABLE IF EXISTS revo_popular_categories;
//         DROP TABLE IF EXISTS revo_token_firebase;
//     ");

//     delete_option("2.3.2");
// }

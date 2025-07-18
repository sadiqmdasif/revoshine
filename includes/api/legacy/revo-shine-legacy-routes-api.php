<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register API Routes
 */
function revo_shine_register_api_routes(): void {	
	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home', array(
		'methods'             => 'GET',
		'callback'            => 'rest_get_home_data',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api', array(
		'methods'             => 'GET',
		'callback'            => 'rest_home_api',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/categories', array(
		'methods'             => 'GET',
		'callback'            => 'rest_categories',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/mini-banner', array(
		'methods'             => 'GET',
		'callback'            => 'rest_mini_banner',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/flash-sale', array(
		'methods'             => 'GET',
		'callback'            => 'rest_flash_sale',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/extend-products', array(
		'methods'             => 'GET',
		'callback'            => 'rest_extend_products',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/hit-products', array(
		'methods'             => 'POST',
		'callback'            => 'rest_hit_products',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/recent-view-products', array(
		'methods'             => 'POST',
		'callback'            => 'rest_get_hit_products',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/intro-page', array(
		'methods'             => 'GET',
		'callback'            => 'rest_get_intro_page',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/general-settings', array(
		'methods'             => 'GET',
		'callback'            => 'rest_general_settings',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/add-remove-wistlist', array(
		'methods'             => 'POST',
		'callback'            => 'rest_add_remove_wistlist',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/list-product-wistlist', array(
		'methods'             => 'POST',
		'callback'            => 'rest_list_wistlist',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/popular-categories', array(
		'methods'             => 'GET',
		'callback'            => 'popular_categories',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/key-firebase', array(
		'methods'             => 'GET',
		'callback'            => 'rest_key_firebase',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/input-token-firebase', array(
		'methods'             => 'POST',
		'callback'            => 'rest_token_user_firebase',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/check-produk-variation', array(
		'methods'             => 'POST',
		'callback'            => 'rest_check_variation',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/list-orders', array(
		'methods'             => 'POST',
		'callback'            => 'rest_list_orders',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/list-review-user', array(
		'methods'             => 'POST',
		'callback'            => 'rest_list_review',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/list-notification', array(
		'methods'             => 'POST',
		'callback'            => 'rest_list_notification',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/list-notification-new', array(
		'methods'             => 'POST',
		'callback'            => 'rest_list_notification_new',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/home-api/read-notification', array(
		'methods'             => 'POST',
		'callback'            => 'rest_read_notification',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/list-categories', array(
		'methods'             => 'POST',
		'callback'            => 'rest_categories_list',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/insert-review', array(
		'methods'             => 'POST',
		'callback'            => 'rest_insert_review',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/vote-review', array(
		'methods'             => 'POST',
		'callback'            => 'rest_vote_review',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/get-barcode', array(
		'methods'             => 'POST',
		'callback'            => 'rest_get_barcode',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/list-produk', array(
		'methods'             => 'POST',
		'callback'            => 'rest_list_product',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/disabled-service', array(
		'methods'             => 'POST',
		'callback'            => 'rest_disabled_service',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/topup-woowallet', array(
		'methods'             => 'GET',
		'callback'            => 'rest_topup_woowallet',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/transfer-woowallet', array(
		'methods'             => 'GET',
		'callback'            => 'rest_transfer_woowallet',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/data-filter-attribute-by-category', array(
		'methods'             => 'POST',
		'callback'            => 'rest_data_attribute_bycategory',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/data-woo-discount-rules', array(
		'methods'             => 'GET',
		'callback'            => 'rest_data_woo_discount_rules',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/list-user-chat', array(
		'methods'             => 'POST',
		'callback'            => 'rest_list_user_chat',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/detail-chat', array(
		'methods'             => 'POST',
		'callback'            => 'rest_detail_chat',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/insert-chat', array(
		'methods'             => 'POST',
		'callback'            => 'rest_insert_chat',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/list-users', array(
		'methods'             => 'POST',
		'callback'            => 'rest_list_users',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/delete-account', array(
		'methods'             => 'POST',
		'callback'            => 'rest_delete_account',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/list_coupons', array(
		'methods'             => 'POST',
		'callback'            => 'rest_list_coupons',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/customer/address', array(
		'methods'             => 'POST',
		'callback'            => 'rest_post_customer_address',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/products/reviews', array(
		'methods'             => 'GET',
		'callback'            => 'rest_show_reviews_product',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/products/attributes', array(
		'methods'             => 'GET',
		'callback'            => 'rest_products_attributes',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/list-blog', array(
		'methods'             => 'GET',
		'callback'            => 'rest_list_blog',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/apply-coupon', array(
		'methods'             => 'POST',
		'callback'            => 'rest_apply_coupon',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/list-coupons', array(
		'methods'             => 'POST',
		'callback'            => 'rest_list_coupons',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/states', array(
		'methods'             => 'GET',
		'callback'            => 'rest_states',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/cities', array(
		'methods'             => 'GET',
		'callback'            => 'rest_cities',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/memberships/products', array(
		'methods'             => 'POST',
		'callback'            => 'rest_get_memberships_products',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/set-intro-page', array(
		'methods'             => 'GET',
		'callback'            => 'rest_intro_page_status',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/set-disable-intro-page', array(
		'methods'             => 'GET',
		'callback'            => 'rest_disable_intro_page_status',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/search', array(
		'methods'             => 'GET',
		'callback'            => 'rest_search',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/video/views', array(
		'methods'             => 'POST',
		'callback'            => 'rest_video_affiliate_views',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/video/get-product', array(
		'methods'             => 'GET',
		'callback'            => 'rest_video_affiliate_get_product',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/video/get', array(
		'methods'             => 'GET',
		'callback'            => 'rest_video_affiliate_get',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/video/get/my-video', array(
		'methods'             => 'POST',
		'callback'            => 'rest_video_affiliate_get_my_video',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/video/store', array(
		'methods'             => 'POST',
		'callback'            => 'rest_video_affiliate_store',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/video/delete', array(
		'methods'             => 'POST',
		'callback'            => 'rest_video_affiliate_delete',
		'permission_callback' => '__return_true'
	) );

	register_rest_route( REVO_SHINE_NAMESPACE_API, '/festive/promotions', array(
		'methods'             => 'GET',
		'callback'            => 'rest_festive_promotions',
		'permission_callback' => '__return_true'
	) );
}

add_action( 'rest_api_init', 'revo_shine_register_api_routes' );

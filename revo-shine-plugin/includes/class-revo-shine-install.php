<?php

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('Revo_Shine_Install')) {
	/**
	 * run on wp-admin
	 */
	final class Revo_Shine_Install
	{
		/**
		 * @var array
		 *
		 * (string) update_[version (ie: 7.0.0)]
		 */
		protected static array $db_update = [
			'update_7.0.62',
			'update_8.0.0',
			'update_8.1.1',
            'update_8.2.2',
			'update_9.0.0',
		];

		public function __construct()
		{
			$this->update();
		}

		/**
		 * @return void
		 *
		 * action update plugin
		 */
		protected function update()
		{
			global $wpdb;

			if (!revo_shine_check_exist_database('revo_mobile_variable')) {
				return;
			}

			$plugin_version = $wpdb->get_row("SELECT * FROM `revo_mobile_variable` WHERE slug = 'plugin' AND title = 'version'", OBJECT);

			if (empty($plugin_version)) {
				return;
			}

			if (version_compare($plugin_version->description, REVO_SHINE_PLUGIN_VERSION, '<')) {
				if (!empty(self::$db_update)) {
					foreach (self::$db_update as $version) {
						$v = explode('_', $version)[1];

						if (version_compare($v, $plugin_version->description, '>')) {
							call_user_func([$this, str_replace('.', '', $version)]);
						}
					}
				}

				$wpdb->update('revo_mobile_variable', ['description' => REVO_SHINE_PLUGIN_VERSION], array('slug' => 'plugin'));
			}
		}

		/**
		 * @return void
		 *
		 * plugin activator
		 */
		public static function activator()
		{
			global $wpdb;

			if (!revo_shine_check_exist_database('revo_mobile_variable')) {

				$revo_mobile_variable = "CREATE TABLE `revo_mobile_variable` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`slug` varchar(55) COLLATE utf8mb4_general_ci NOT NULL,
					`title` varchar(1000) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
					`image` TEXT COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
					`description` TEXT NULL DEFAULT NULL,
					`sort` tinyint(2) NOT NULL DEFAULT 0,
					`is_deleted` tinyint(1) NOT NULL DEFAULT 0,
					`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					`update_at` timestamp NULL,
				PRIMARY KEY (`id`) USING BTREE)";

				$wpdb->query($revo_mobile_variable);

				if (!revo_shine_check_exist_database('revo_mobile_slider')) {
					$revo_mobile_slider = "CREATE TABLE `revo_mobile_slider` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`order_by` int(55) NOT NULL,
						`product_id` int(11) NULL DEFAULT NULL,
						`title` varchar(500) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
						`images_url` varchar(500) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
						`product_name` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
						`section_type` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
						`is_active` int(1) NULL DEFAULT 1,
						`is_deleted` int(1) NULL DEFAULT 0,
						`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					PRIMARY KEY (`id`) USING BTREE ) ";

					$wpdb->query($revo_mobile_slider);
				}

				if (!revo_shine_check_exist_database('revo_list_categories')) {
					$revo_list_categories = " CREATE TABLE `revo_list_categories` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`order_by` int(55) NOT NULL,
						`image` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
						`category_id` int(55) NOT NULL,
						`category_name` varchar(1000) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
						`section_type` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
						`is_active` int(1) NULL DEFAULT 1,
						`is_deleted` int(1) NULL DEFAULT 0,
						`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					PRIMARY KEY (`id`) USING BTREE) ";

					$wpdb->query($revo_list_categories);
				}

				if (!revo_shine_check_exist_database('revo_list_mini_banner')) {
					$revo_list_mini_banner = " CREATE TABLE `revo_list_mini_banner` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`order_by` int(55) NOT NULL,
						`product_id` int(11) NULL DEFAULT NULL,
						`product_name` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
						`section_type` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
						`image` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
						`type` varchar(55) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
						`is_active` int(1) NULL DEFAULT 1,
						`is_deleted` int(1) NULL DEFAULT 0,
						`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					PRIMARY KEY (`id`) USING BTREE) ";

					$wpdb->query($revo_list_mini_banner);
				}

				if (!revo_shine_check_exist_database('revo_flash_sale')) {
					$revo_flash_sale = "CREATE TABLE `revo_flash_sale` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
						`start` timestamp NULL DEFAULT NULL,
						`end` timestamp NULL DEFAULT NULL,
						`products` longtext COLLATE utf8mb4_general_ci NOT NULL,
						`image` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
						`is_active` tinyint(1) NOT NULL DEFAULT 1,
						`is_deleted` tinyint(1) NOT NULL DEFAULT 0,
						`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					PRIMARY KEY (`id`) USING BTREE)";

					$wpdb->query($revo_flash_sale);
				}

				if (!revo_shine_check_exist_database('revo_extend_products')) {
					$revo_extend_products = "CREATE TABLE `revo_extend_products` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'special',
						`title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
						`description` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
						`products` longtext COLLATE utf8mb4_general_ci NOT NULL,
						`section_type` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
						`is_active` tinyint(1) NOT NULL DEFAULT 1,
						`is_deleted` tinyint(1) NOT NULL DEFAULT 0,
						`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					PRIMARY KEY (`id`) USING BTREE)";

					$wpdb->query($revo_extend_products);
				}

				if (!revo_shine_check_exist_database('revo_popular_categories')) {
					$revo_popular_categories = "CREATE TABLE `revo_popular_categories` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`title` varchar(55) COLLATE utf8mb4_general_ci NOT NULL,
						`categories` TEXT COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
						`is_deleted` tinyint(1) NOT NULL DEFAULT 0,
						`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					PRIMARY KEY (`id`) USING BTREE)";

					$wpdb->query($revo_popular_categories);
				}

				// NOTE: ada typo disini, betulin ok [wistlist -> wishlist]
				if (!revo_shine_check_exist_database('revo_hit_products')) {
					$revo_hit_products = "CREATE TABLE `revo_hit_products` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`products` varchar(55) NOT NULL,
						`user_id` varchar(55) NULL,
						`type` enum('hit','wistlist') NOT NULL DEFAULT 'hit',
						`ip_address` varchar(55) NOT NULL,
						`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					PRIMARY KEY (`id`) USING BTREE)";

					$wpdb->query($revo_hit_products);
				}

				if (!revo_shine_check_exist_database('revo_access_key')) {
					$revo_access_key = "CREATE TABLE `revo_access_key` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`firebase_server_key` TEXT NULL DEFAULT NULL,
						`firebase_api_key` TEXT NULL DEFAULT NULL,
						`firebase_auth_domain` TEXT NULL DEFAULT NULL,
						`firebase_database_url` TEXT NULL DEFAULT NULL,
						`firebase_project_id` TEXT NULL DEFAULT NULL,
						`firebase_storage_bucket` TEXT NULL DEFAULT NULL,
						`firebase_messaging_sender_id` TEXT NULL DEFAULT NULL,
						`firebase_app_id` TEXT NULL DEFAULT NULL,
						`firebase_measurement_id` TEXT NULL DEFAULT NULL,
						`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					PRIMARY KEY (`id`) USING BTREE)";

					$wpdb->query($revo_access_key);
				}

				if (!revo_shine_check_exist_database('revo_token_firebase')) {
					$revo_token_firebase = "CREATE TABLE `revo_token_firebase` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`token` TEXT NULL DEFAULT NULL,
						`user_id` varchar(55) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
						`is_deleted` tinyint(1) NOT NULL DEFAULT 0,
						`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					PRIMARY KEY (`id`) USING BTREE)";

					$wpdb->query($revo_token_firebase);
				}

				if (!revo_shine_check_exist_database('revo_notification')) {
					$revo_notification = "CREATE TABLE `revo_notification` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`user_id` varchar(55) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
						`target_id` varchar(55) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
						`type` varchar(55) COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
						`message` TEXT COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
						`is_read` tinyint(1) NOT NULL DEFAULT 0,
						`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					PRIMARY KEY (`id`) USING BTREE)";

					$wpdb->query($revo_notification);
				}

				if (!revo_shine_check_exist_database('revo_conversations')) {
					$revo_conversations = "CREATE TABLE `revo_conversations` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`sender_id` int(11) NOT NULL,
						`receiver_id` int(11) NOT NULL,
						`is_delete_sender` tinyint(2) NOT NULL DEFAULT 0,
						`is_delete_receiver` tinyint(2) NOT NULL DEFAULT 0,
						`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					PRIMARY KEY (`id`) USING BTREE, KEY (`sender_id`) USING BTREE, KEY (`receiver_id`) USING BTREE );";

					$wpdb->query($revo_conversations);
				}

				if (!revo_shine_check_exist_database('revo_conversation_messages')) {
					$revo_conversation_messages = "CREATE TABLE `revo_conversation_messages` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`conversation_id` int(11) NOT NULL,
						`sender_id` int(11) NOT NULL,
						`receiver_id` int(11) NOT NULL,
						`message` varchar(1000) COLLATE utf8mb4_general_ci NOT NULL,
						`image` TEXT COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
						`is_read` tinyint(2) NOT NULL DEFAULT 0,
						`type` enum('store','product','order','chat') NOT NULL DEFAULT 'chat',
						`post_id` int(11) NOT NULL,
						`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					PRIMARY KEY (`id`) USING BTREE, KEY (`post_id`) USING BTREE, KEY (`sender_id`) USING BTREE, KEY (`receiver_id`) USING BTREE, KEY (`conversation_id`) USING BTREE );";

					$wpdb->query($revo_conversation_messages);
				}

				if (!revo_shine_check_exist_database('revo_push_notification')) {
					$revo_push_notification = "CREATE TABLE `revo_push_notification` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`type` varchar(55) NULL DEFAULT NULL,
						`description` TEXT NULL COLLATE utf8mb4_general_ci DEFAULT NULL,
						`user_id` TEXT NULL DEFAULT NULL,
						`user_read` TEXT NULL DEFAULT NULL,
						`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
					PRIMARY KEY (`id`) USING BTREE)";

					$wpdb->query($revo_push_notification);
				}

				(new self)->update_800();
				(new self)->update_811();
                (new self)->update_822();
                (new self)->update_900();

				(new self)->data_seeder();
			}
		}

		/**
		 * @return bool
		 *
		 * plugin deactivator
		 */
		public static function deactivator()
		{
			global $wpdb;

			$queryLC = $wpdb->get_row("SELECT id, description, update_at FROM `revo_mobile_variable` WHERE slug = 'license_code' AND description != '' AND update_at is not NULL", OBJECT);

			if (!$queryLC) {
				return true;
			}

			$update = ["description" => null];
			$wpdb->update('revo_mobile_variable', $update, ['id' => $queryLC->id]);

			$curl         = curl_init();
			$body         = json_encode(compact('license_code'));
			$license_code = json_decode($queryLC->description)->license_code;

			curl_setopt_array($curl, array(
				CURLOPT_URL            => "https://activation.revoapps.net/wp-json/license/uninstall",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => "",
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => "POST",
				CURLOPT_HTTPHEADER     => array(
					"Content-Type: application/json",
				),
				CURLOPT_POSTFIELDS     => $body,
			));

			$response = curl_exec($curl);

			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
				return 'error';
			}

			return json_decode($response);
		}

		/**
		 * data seeder
		 */
		private function data_seeder()
		{
			global $wpdb;

			// $post_ids      = [];
			// $product_ids   = [];
			// $category_ids  = [];
			// $attribute_ids = [];

			// revo_mobile_variables
			$wpdb->insert('revo_mobile_variable', data_default_seeder('splashscreen'));
			$wpdb->insert('revo_mobile_variable', data_default_seeder('kontak_wa'));
			$wpdb->insert('revo_mobile_variable', data_default_seeder('kontak_phone'));
			$wpdb->insert('revo_mobile_variable', data_default_seeder('kontak_sms'));
			$wpdb->insert('revo_mobile_variable', data_default_seeder('sms'));
			$wpdb->insert('revo_mobile_variable', data_default_seeder('about'));
			$wpdb->insert('revo_mobile_variable', data_default_seeder('cs'));
			$wpdb->insert('revo_mobile_variable', data_default_seeder('privacy_policy'));
			$wpdb->insert('revo_mobile_variable', data_default_seeder('term_condition'));
			$wpdb->insert('revo_mobile_variable', data_default_seeder('logo'));
			$wpdb->insert('revo_mobile_variable', data_default_seeder('app_primary_color'));
			$wpdb->insert('revo_mobile_variable', data_default_seeder('app_secondary_color'));
			$wpdb->insert('revo_mobile_variable', data_default_seeder('searchbar'));
			$wpdb->insert('revo_mobile_variable', data_default_seeder('sosmed_link'));
			$wpdb->insert('revo_mobile_variable', data_default_seeder('app_button_color'));
			$wpdb->insert('revo_mobile_variable', data_default_seeder('app_text_button_color'));
			$wpdb->insert('revo_mobile_variable', data_default_seeder('license_code'));

			$wpdb->insert('revo_mobile_variable', [
				'slug'        => 'plugin',
				'title'       => 'version',
				'description' => REVO_SHINE_PLUGIN_VERSION,
			]);

			$wpdb->insert('revo_mobile_variable', data_default_seeder('intro_page_status'));

			for ($i = 1; $i <= 3; $i++) {
				$wpdb->insert('revo_mobile_variable', data_default_seeder('intro_page_' . $i));
			}

			for ($i = 1; $i <= 6; $i++) {
				$wpdb->insert('revo_mobile_variable', data_default_seeder('empty_images_' . $i));
			}

			for ($i = 1; $i <= 3; $i++) {
				$wpdb->insert( 'revo_label_product', data_default_seeder( 'label_product_' . $i ) );
			}

			// customize page
			$wpdb->insert('revo_mobile_variable', array(
				'slug'        => 'customize_homepage',
				'title'       => 'Customize Homepage',
				'image'       => '',
				'description' => serialize([ 'section_wallet_and_point', 'section_categories_two_rows', 'section_black_friday', 'section_f', 'section_q', 'section_p', 'section_g', 'section_d', 'section_e', 'section_h', 'section_bestdeal'])
			));

			// revo_mobile_slider
			for ($i = 1; $i <= 5; $i++) {
				$wpdb->insert('revo_mobile_slider', data_default_seeder('slider_banner_' . $i));
			}
			
			// revo_mobile_slider
			for ($i = 1; $i <= 5; $i++) {
				$wpdb->insert('revo_mobile_slider', data_default_seeder('slider_banner_full_screen_' . $i));
			}
			
			// revo_list_categories
			for ($i = 1; $i <= 4; $i++) {
				$wpdb->insert('revo_list_categories', data_default_seeder('home_categories_' . $i));
			}

			// revo_list_categories
			for ($i = 1; $i <= 7; $i++) {
				$wpdb->insert('revo_list_categories', data_default_seeder('home_categories_two_rows_' . $i));
			}

			// revo_list_mini_banner
			for ($i = 1; $i <= 9; $i++) {
				$wpdb->insert('revo_list_mini_banner', data_default_seeder('poster_banner_' . $i));
			}

			// revo_flash_sale
			$wpdb->insert('revo_flash_sale', data_default_seeder('flash_sale'));

			// revo_extend_products
			for ($i = 1; $i <= 5; $i++) {
				$wpdb->insert('revo_extend_products', data_default_seeder('additional_products_' . $i));
			}

			// revo_popular_categories
			$wpdb->insert('revo_popular_categories', data_default_seeder('popular_categories'));

			// revo_access_key
			$wpdb->insert('revo_access_key', ['firebase_server_key' => '']);
		}

		private function update_7062()
		{
			global $wpdb;

			// customize page
			$wpdb->insert('revo_mobile_variable', array(
				'slug'        => 'customize_homepage',
				'title'       => 'Customize Homepage',
				'image'       => '',
				'description' => serialize(['section_a', 'section_c', 'section_f', 'section_q', 'section_p', 'section_g', 'section_d', 'section_e', 'section_h', 'section_bestdeal'])
			));

			// mobile slider
			$wpdb->query("ALTER TABLE revo_mobile_slider ADD `section_type` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT 'banner-1' AFTER `product_name`");

			// home categories
			$wpdb->query("ALTER TABLE revo_list_categories ADD `section_type` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT 'mini' AFTER `category_name`");

			// mini banner
			$wpdb->query("ALTER TABLE revo_list_mini_banner ADD `section_type` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT 'special-promo' AFTER `product_name`");

			// additional product
			$wpdb->query("ALTER TABLE revo_extend_products MODIFY COLUMN `type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'special'");
			$wpdb->query("ALTER TABLE revo_extend_products ADD `section_type` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `products`");

			$wpdb->update('revo_extend_products', ['section_type' => 'products-special'], ['type' => 'special']);
			$wpdb->update('revo_extend_products', ['section_type' => 'products-our-best-seller'], ['type' => 'our_best_seller']);
			$wpdb->update('revo_extend_products', ['section_type' => 'products-recomendation'], ['type' => 'recomendation']);

			$wpdb->insert('revo_extend_products', [
				'type'  	   => 'other_products',
				'title' 	   => 'Other Products',
				'description'  => 'Other Products',
				'section_type' => 'other-products'
			]);
		}

		private function update_800()
		{
			global $wpdb;

			if (!revo_shine_check_exist_database('revo_video_affiliate')) {
				$revo_video_affiliate = "CREATE TABLE `revo_video_affiliate` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`post_id` varchar(55) NOT NULL,
					`user_id` int(11) NOT NULL,
					`product_id` int(11) NOT NULL,
					`video_url` TEXT NOT NULL,
					`video` varchar(255) NOT NULL,
					`status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = Inactive, 1 = Active, 2 = Reject',
					`link` varchar(100) NOT NULL,
					`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
				PRIMARY KEY (`id`) USING BTREE)";

				$wpdb->query($revo_video_affiliate);
			}

			if (!revo_shine_check_exist_database('revo_video_affiliate_views')) {
				$revo_video_affiliate_views = "CREATE TABLE `revo_video_affiliate_views` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`video_id` int(11) NOT NULL,
					`type` ENUM('view', 'click') NOT NULL,
					`information` TEXT NULL DEFAULT NULL,
					`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
				PRIMARY KEY (`id`) USING BTREE)";

				$wpdb->query($revo_video_affiliate_views);
			}
		}

		private function update_811()
		{
			global $wpdb;
			
			$wpdb->query("ALTER TABLE revo_mobile_slider ADD `lang` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT 'en' AFTER `product_name`");
			$wpdb->query("ALTER TABLE revo_list_mini_banner ADD `lang` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT 'en' AFTER `type`");
			$wpdb->query("ALTER TABLE revo_list_categories ADD `lang` varchar(255) COLLATE utf8mb4_general_ci NULL DEFAULT 'en' AFTER `section_type`");
		}

        private function update_822()
        {
            global $wpdb;

            $wpdb->query("ALTER TABLE revo_mobile_slider MODIFY `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT ''");
            $wpdb->query("ALTER TABLE revo_list_mini_banner MODIFY `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT ''");
        }

		private function update_900()
		{
			global $wpdb;

			if (!revo_shine_check_exist_database('revo_label_product')) {
				$revo_label_product = "CREATE TABLE `revo_label_product` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`name` varchar(255) NOT NULL,
					`slug` varchar(255) NOT NULL,
					`desc` TEXT NULL,
					`type` varchar(55) NOT NULL,
					`image` TEXT NULL,
					`products` TEXT NOT NULL,
					`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
				PRIMARY KEY (`id`) USING BTREE)";

				$wpdb->query( $revo_label_product );
			}
		}
    }

	new Revo_Shine_Install();
}

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Revo_Shine_Admin_Api' ) ) {
	class Revo_Shine_Admin_Api {
		protected string $plugin_slug;

		protected string $page;

		private static $_instance = null;

		public function __construct() {
			$this->init_hooks();

			$this->revo_media_directory();

			$this->plugin_slug = REVO_SHINE_PLUGIN_SLUG;
		}

		public function init_hooks(): void {
			add_action( 'admin_init', array( $this, 'hide_all_admin_notices' ), 10 );
			add_action( 'admin_menu', array( $this, 'register_admin_menus' ), 99 );
			add_action( 'admin_bar_menu', array( $this, 'register_admin_bar' ), 99 );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ), 99, 1 );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'add_new_shop_order_column_content' ) );

			add_filter( 'plugin_action_links', array( $this, 'add_plugin_link' ), 10, 2 );
			add_filter( 'revo_shine_register_submenus', array( $this, 'register_submenus' ), 10, 1 );
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_new_shop_order_column' ) );

			// ajax
			add_action( 'wp_ajax_rw_get_data', array( $this, 'get_data' ) );
			add_action( 'wp_ajax_nopriv_rw_rebuild_cache', array( $this, 'run_rebuild_cache' ) );
			add_action( 'wp_ajax_rw_rebuild_cache', array( $this, 'run_rebuild_cache' ) );
			add_action( 'wp_ajax_nopriv_rw_generate_static_file', array( $this, 'run_generate_static_file' ) );
			add_action( 'wp_ajax_rw_generate_static_file', array( $this, 'run_generate_static_file' ) );
			add_action( 'wp_ajax_nopriv_save_customize_page', array( $this, 'save_customize_page' ) );
			add_action( 'wp_ajax_save_customize_page', array( $this, 'save_customize_page' ) );
			add_action( 'wp_ajax_nopriv_build_app', array( $this, 'validate_build_app' ) );
			add_action( 'wp_ajax_build_app', array( $this, 'validate_build_app' ) );

			add_action( 'wp_ajax_rwt_theme_mode_switch', array( $this, 'ajax_theme_switch' ) );
		}

		public function add_plugin_link( $plugin_actions, $plugin_file ) {
			if ( 'revo-shine-plugin/revo-shine-plugin.php' === $plugin_file ) {
				$plugin_actions['settings'] = sprintf( '<a href="%s">Settings</a>', esc_url( admin_url( 'admin.php?page=revo-apps-setting' ) ) );
			}

			return $plugin_actions;
		}

		public function register_admin_menus(): void {
			global $submenu;

			add_menu_page( "Mobile Revo Settings", REVO_SHINE_PLUGIN_NAME, 'manage_options', $this->plugin_slug, array(
				$this,
				'load_admin_view'
			), revo_shine_get_logo( 'black_white' ) );

			$submenus = apply_filters( 'revo_shine_register_submenus', [] );
			foreach ( $submenus as $slug => $menu ) {
				if ( ! $menu['status'] ) {
					continue;
				}

				add_submenu_page( $this->plugin_slug, $menu['title'], $menu['title'], 'manage_options', $slug, array(
					$this,
					'load_admin_view'
				) );
			}

			if ( current_user_can( 'administrator' ) ) {
				$submenu[ $this->plugin_slug ][0][0] = 'Dashboard';
			}
		}

		public function register_admin_bar( $wp_admin_bar ): void {
			if ( ! is_admin() ) {
				return;
			}

			$menu_id = 'revo-shine-admin-bar';

			$wp_admin_bar->add_menu(
				array(
					'id'     => $menu_id,
					'parent' => null,
					'href'   => null,
					'title'  => '<div style="display: flex; align-items: center"><img style="width: 18px; height: 18px; padding-right: 5px" src="' . revo_shine_get_logo( 'black_white' ) . '" /> <span>RevoSHINE</span></div>',
				)
			);

			$wp_admin_bar->add_menu(
				array(
					'parent' => $menu_id,
					'title'  => __( 'Rebuild Cache', 'text-domain' ),
					'id'     => 'revo-shine-rebuild-cache',
					'href'   => 'javascript:void(0)',
				)
			);

			$wp_admin_bar->add_menu(
				array(
					'parent' => $menu_id,
					'title'  => __( 'Generate Static File', 'text-domain' ),
					'id'     => 'revo-shine-generate-static-file',
					'href'   => 'javascript:void(0)',
				)
			);
		}

		public function register_admin_scripts( string $handle ): void {
			wp_enqueue_media();

			wp_enqueue_style( 'revo-shine-global-style', REVO_SHINE_ASSET_URL . 'css/global-style.css', array() );

			wp_enqueue_script( 'revo-shine-sweetalert', REVO_SHINE_ASSET_URL . 'vendor/sweetalert2/sweetalert2.all.min.js', array(), true );
			wp_enqueue_script( 'revo-shine-global-script', REVO_SHINE_ASSET_URL . 'js/global-script.js', array( 'jquery' ), true );

			// register admin page
			if ( $handle === 'toplevel_page_revo-apps-setting' || strpos( $handle, 'revoshine' ) !== false ) {
				wp_enqueue_style( 'revo-shine-bootstrap', REVO_SHINE_ASSET_URL . 'vendor/bootstrap/bootstrap.min.css', array(), REVO_SHINE_PLUGIN_VERSION );
				wp_enqueue_style( 'revo-shine-fontawesome', REVO_SHINE_ASSET_URL . 'vendor/font-awesome/css/all.min.css', array(), REVO_SHINE_PLUGIN_VERSION );
				wp_enqueue_style( 'revo-shine-select2', REVO_SHINE_ASSET_URL . 'vendor/select2/css/select2.css', array(), REVO_SHINE_PLUGIN_VERSION );
				wp_enqueue_style( 'revo-shine-select2-min', REVO_SHINE_ASSET_URL . 'vendor/select2/css/select2.min.css', array(), REVO_SHINE_PLUGIN_VERSION );
				wp_enqueue_style( 'revo-shine-select2-theme', REVO_SHINE_ASSET_URL . 'vendor/select2/theme/select2-bootstrap.min.css', array(), REVO_SHINE_PLUGIN_VERSION );
				wp_enqueue_style( 'revo-shine-datatables', REVO_SHINE_ASSET_URL . 'vendor/datatables/dataTables.dataTables.min.css', array(), REVO_SHINE_PLUGIN_VERSION );
				wp_enqueue_style( 'rw-admin-custom-style', REVO_SHINE_ASSET_URL . 'css/admin.custom.css', array(), REVO_SHINE_PLUGIN_VERSION, 'all' );

				wp_enqueue_script( 'revo-shine-bootstrap-script', REVO_SHINE_ASSET_URL . 'vendor/bootstrap/bootstrap.bundle.min.js', array(), REVO_SHINE_PLUGIN_VERSION, true );
				wp_enqueue_script( 'revo-shine-select2-script', REVO_SHINE_ASSET_URL . 'vendor/select2/js/select2.js', array(), REVO_SHINE_PLUGIN_VERSION, true );
				wp_enqueue_script( 'revo-shine-tables-script', REVO_SHINE_ASSET_URL . 'vendor/datatables/dataTables.min.js', array(), REVO_SHINE_PLUGIN_VERSION, true );
				wp_enqueue_script( 'revo-shine-easepick-script', REVO_SHINE_ASSET_URL . 'vendor/easepick/index.umd.min.js', array(), REVO_SHINE_PLUGIN_VERSION, true );
				wp_enqueue_script( 'revo-shine-theme-script', REVO_SHINE_ASSET_URL . 'js/theme.js', array( 'jquery' ), REVO_SHINE_PLUGIN_VERSION, true );
			}

			// register customize page
			if ( $handle === 'revoshine_page_revo-customize-page' ) {
				wp_enqueue_style( 'revo-shine-cp-device', REVO_SHINE_ASSET_URL . 'css/devices.min.css', array(), REVO_SHINE_PLUGIN_VERSION );
				wp_enqueue_style( 'revo-shine-cp-selectric', REVO_SHINE_ASSET_URL . 'vendor/selectric/selectric.css', array(), REVO_SHINE_PLUGIN_VERSION );

				wp_enqueue_script( 'revo-shine-js-cp-sortable', REVO_SHINE_ASSET_URL . 'vendor/sortable/Sortable.min.js', array(), REVO_SHINE_PLUGIN_VERSION, true );
				wp_enqueue_script( 'revo-shine-js-cp-isotope', REVO_SHINE_ASSET_URL . 'vendor/isotope/isotope.pkgd.min.js', array(), REVO_SHINE_PLUGIN_VERSION, true );
				wp_enqueue_script( 'revo-shine-js-cp-selectric', REVO_SHINE_ASSET_URL . 'vendor/selectric/jquery.selectric.min.js', array(), REVO_SHINE_PLUGIN_VERSION, true );

				wp_enqueue_script( 'revo-shine-js-revo-init', REVO_SHINE_ASSET_URL . 'js/revo.init.js', array( 'jquery' ), REVO_SHINE_PLUGIN_VERSION, true );
				wp_enqueue_script( 'revo-shine-js-cp-main', REVO_SHINE_ASSET_URL . 'js/backend/customize-page.js', array( 'jquery' ), REVO_SHINE_PLUGIN_VERSION, true );
			}
		}

		public function load_admin_view(): void {
			global $submenus, $user_preferred_theme;

			$user_id              = get_current_user_id();
			$user_preferred_theme = get_option( 'rw_user_preferred_theme_mode', [] );
			$user_preferred_theme = $user_preferred_theme[ $user_id ] ?? 'light';

			$page     = $_GET['page'] ?? '';
			$submenus = apply_filters( 'revo_shine_register_submenus', [] );

			$this->page = ! isset( $submenus[ $page ] ) ? 'view_dashboard_page' : $submenus[ $page ]['view'];

			$this->page_content_wrapper_style( $user_preferred_theme );
			$this->check_your_license();

			?>

            <div id="rw-admin-wrapper" data-bs-theme="<?php echo $user_preferred_theme ?>">
				<?php include_once REVO_SHINE_TEMPLATE_PATH . 'admin/parts/topbar.php' ?>

                <div id="rw-content-wrapper">
                    <div class="d-flex flex-column flex-md-row sub-content-wrapper" id="content-wrapper">
						<?php
						include_once REVO_SHINE_TEMPLATE_PATH . 'admin/parts/sidebar.php';

						include_once REVO_SHINE_TEMPLATE_PATH . 'admin/content-wrapper.php';
						?>
                    </div>
                </div>
            </div>

			<?php
		}

		public function run_rebuild_cache(): void {
			revo_shine_rebuild_cache( 'revo_home_data' );

			revo_shine_clear_caches();
		}

		public function run_generate_static_file(): void {
			revo_shine_generate_static_file( 'revo_product_data' );

			revo_shine_clear_caches();
		}

		public function validate_build_app(): void {
			if ( ! wp_doing_ajax() ) {
				return;
			}

			global $wpdb;

			$license_key = $wpdb->get_row( "SELECT description FROM `revo_mobile_variable` WHERE slug = 'license_code'", OBJECT );

			if ( ! isset( json_decode( $license_key->description, true )['license_code'] ) ) {
				wp_send_json_error( [
					'message' => '<p style="font-size: 17.5px; margin-bottom: 0">Your license is not active yet. Make sure you have input the license/purchase code before requesting to build the App.</p>',
					'type'    => 'alert'
				], 400 );
			}

			$consumer_key = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}woocommerce_api_keys WHERE permissions = 'read_write'", OBJECT );

			if ( ! isset( $consumer_key ) ) {
				wp_send_json_error( [
					'message'  => '<p style="font-size: 17.5px">Please generate API keys : consumer key (CK) & consumer secret (CS) with read_write permissions.</p> <p style="font-size: 17.5px; margin-bottom: 0">Click the Guide button to learn <br/> How to generate API key.</p>',
					'type'     => 'confirm',
					'redirect' => 'https://woocommerce.com/document/woocommerce-rest-api'
				], 400 );
			}

			$data = [
				'rest_api'    => 'active',
				'php_version' => PHP_VERSION,
				'ck'          => $consumer_key->consumer_key,
				'cs'          => $consumer_key->consumer_secret,
				'license_key' => json_decode( $license_key->description, true )['license_code'],
				'message'     => ''
			];

			$message = "Hello Revo Apps, I'm requesting an {$_POST['os']} App for my Woocommerce Store. %0a %0a";
			$message .= "✅ Rest API is Active: {$data['rest_api']} %0a%0a";
			$message .= "✅ PHP Version: {$data['php_version']} %0a%0a";
			$message .= "✅ URL: {$_SERVER['HTTP_HOST']} %0a%0a";
			$message .= "✅ Woocommerce Consumer Key: {$data['ck']} %0a%0a";
			$message .= "✅ Woocommerce Consumer Secret: {$data['cs']} %0a%0a";
			$message .= "✅ License Key: {$data['license_key']}";

			$data['message'] = $message;

			wp_send_json( [ 'status' => 'success', 'data' => $data ] );
		}

		public function save_customize_page(): void {
			global $wpdb;

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				$header_sections      = $_POST['header'];
				$homepage_sections    = $_POST['homepage'];
				$hero_banner_sections = $_POST['heroBanner'];

				// header
				$header_values = get_option( 'revo_shine_customize_homepage_header', [
					'type'  => 'v6',
					'logo'  => '',
					'menus' => []
				] );

				$header_values['type'] = $header_sections[0];
				$header_values['logo'] = $header_sections[1];
				update_option( 'revo_shine_customize_homepage_header', $header_values );
				update_option( 'revo_shine_customize_homepage_hero_banner', $hero_banner_sections );

				// homepage
				// array_push($homepage_sections, 'section_bestdeal');

				$wpdb->update(
					'revo_mobile_variable',
					array(
						'description' => serialize( $homepage_sections )
					),
					array(
						'slug' => 'customize_homepage'
					)
				);

				revo_shine_rebuild_cache( 'revo_home_data' );

				wp_send_json_success( [ 'message' => 'Homepage has been updated successfully.' ] );
			}
		}

		public function register_submenus( $submenus ): array {
			$template_path = REVO_SHINE_TEMPLATE_PATH . 'admin/pages/';

			return [
				'revo-customize-page'          => [
					'title'  => 'Customize Homepage',
					'view'   => $template_path . 'view_customize_page.php',
					'status' => true,
					'icon'   => 'customize'
				],
				'revo-intro-page'              => [
					'title'  => 'Intro Page',
					'view'   => $template_path . 'view_intro_page.php',
					'status' => true,
					'icon'   => 'intro'
				],
				'revo-searchbar'               => [
					'title'  => 'Home Search Bar Text',
					'view'   => $template_path . 'view_searchbar_text.php',
					'status' => true,
					'icon'   => 'searchbar'
				],
				'revo-mobile-banner'           => [
					'title'  => 'Home Sliding Banner',
					'view'   => $template_path . 'view_banner_slider.php',
					'status' => true,
					'icon'   => 'sliding-banner'
				],
				'revo-mobile-categories'       => [
					'title'  => 'Home Categories',
					'view'   => $template_path . 'view_custom_categories.php',
					'status' => true,
					'icon'   => 'home-categories'
				],
				'revo-mini-banner'             => [
					'title'  => 'Home Additional Banner',
					'view'   => $template_path . 'view_mini_banner.php',
					'status' => true,
					'icon'   => 'additional-banner'
				],
				'revo-flash-sale'              => [
					'title'  => 'Home Flash Sale',
					'view'   => $template_path . 'view_flash_sale.php',
					'status' => true,
					'icon'   => 'flash-sale'
				],
				'revo-additional-products'     => [
					'title'  => 'Home Additional Products',
					'view'   => $template_path . 'view_extend_products.php',
					'status' => true,
					'icon'   => 'additional-product'
				],
				'revo-popular-categories'      => [
					'title'  => 'Popular Categories',
					'view'   => $template_path . 'view_popular_categories.php',
					'status' => true,
					'icon'   => 'popular-categories'
				],
				'revo-empty-result-image'      => [
					'title'  => 'Empty Result Image',
					'view'   => $template_path . 'view_empty_result_image.php',
					'status' => true,
					'icon'   => 'empty-result-image'
				],
				'revo-post-notification'       => [
					'title'  => 'Push Notification',
					'view'   => $template_path . 'view_post_notification.php',
					'status' => true,
					'icon'   => 'notification'
				],
				'revo-color-setting'           => [
					'title'  => 'App Color Setting',
					'view'   => $template_path . 'view_app_color.php',
					'status' => true,
					'icon'   => 'app-color'
				],
				'revo-social-media-setting'    => [
					'title'  => 'Social Media Setting',
					'view'   => $template_path . 'view_social_media.php',
					'status' => true,
					'icon'   => 'social-media'
				],
				'revo-video-shopping'          => [
					'title'  => 'Video Shopping',
					'view'   => $template_path . 'view_video_affiliate.php',
					'status' => true,
					'icon'   => 'video-shopping'
				],
				'revo-apps-additional-setting' => [
					'title'  => 'Apps Setting',
					'view'   => $template_path . 'view_app_setting.php',
					'status' => true,
					'icon'   => 'apps-setting'
				]
			];
		}

		public function get_data(): void {
			$result    = [];
			$data_type = $_POST['type'];
			$search    = $_POST['search'];

			switch ( $data_type ) {
				case 'product':
					$datas = wc_get_products( [
						'limit'        => 10,
						'status'       => 'publish',
						'type'         => [ 'simple', 'variable' ],
						'orderby'      => 'date',
						'order'        => 'DESC',
						's'            => $search,
						'stock_status' => 'instock',
					] );

					if ( ! empty( $datas ) ) {
						foreach ( $datas as $data ) {
							$result[] = [
								'id'    => $data->get_id(),
								'title' => $data->get_title(),
							];
						}
					}
					break;
				case 'category':
					$datas = get_terms( [
						'taxonomy'   => 'product_cat',
						'hide_empty' => false,
						'name__like' => $search
					] );

					if ( ! empty( $datas ) ) {
						foreach ( $datas as $data ) {
							$result[] = [
								'id'    => $data->term_id,
								'title' => $data->name,
							];
						}
					}
					break;
				case 'blog':
					$posts = get_posts( [ 's' => $search, 'post_type' => 'post', 'posts_per_page' => 20 ] );

					if ( ! empty( $posts ) ) {
						foreach ( $posts as $value ) {
							$result[] = [
								'id'    => $value->ID,
								'title' => $value->post_title
							];
						}
					}
					break;
				case 'attribute':
					global $wpdb;

					$attributes = $wpdb->get_results( 'SELECT attribute_id, attribute_label FROM `wp_woocommerce_attribute_taxonomies` WHERE attribute_label LIKE "%' . $search . '%"', OBJECT );

					if ( ! empty( $attributes ) ) {
						foreach ( $attributes as $key => $value ) {
							$result[] = [
								'id'    => $value->attribute_id,
								'title' => $value->attribute_label
							];
						}
					}

					break;
			}

			wp_send_json( $result );
		}

		public function hide_all_admin_notices(): void {
			if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'revo' ) !== false ) {
				global $wp_filter;

				if ( isset( $wp_filter['admin_notices'] ) ) {
					unset( $wp_filter['admin_notices'] );
				}
			}
		}

		public function add_new_shop_order_column( $columns ) {
			$columns['revo_shine_order_created_via'] = 'Created Via';

			return $columns;
		}

		public function add_new_shop_order_column_content( $column ): void {
			global $post;

			if ( 'revo_shine_order_created_via' === $column ) {
				$order             = wc_get_order( $post->ID );
				$order_created_via = $order->get_created_via();

				echo $order_created_via === 'rest-api' ? 'Mobile App' : 'Website';
			}
		}

		public function ajax_theme_switch(): void {
			$selected_theme       = $_POST['dark_mode'] === 'on' ? 'dark' : 'light';
			$user_preferred_theme = get_option( 'rw_user_preferred_theme_mode', [] );

			$user_preferred_theme[ get_current_user_id() ] = $selected_theme;

			update_option( 'rw_user_preferred_theme_mode', $user_preferred_theme );

			wp_send_json( [
				'status'  => 'success',
				'message' => 'Theme mode switched successfully!',
				'theme'   => $selected_theme
			] );
		}

		protected function revo_media_directory(): void {
			$upload     = wp_upload_dir();
			$upload_dir = $upload['basedir'];
			$upload_dir = $upload_dir . '/revo';

			if ( ! is_dir( $upload_dir ) ) {
				mkdir( $upload_dir, 0777 );
			}
		}

		protected function check_your_license(): void {
			if ( ! revo_shine_ck_internal_code() ) {
				$this->page = REVO_SHINE_TEMPLATE_PATH . 'admin/pages/view_license_page.php';
			}
		}

		protected function page_content_wrapper_style( string $theme = 'light' ): void {
			?>

            <style>
                #wpcontent {
                    background-color: <?php echo $theme === 'light' ? '#F6F7F8' : '#323232' ?>
                }
            </style>

			<?php
		}

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}

	Revo_Shine_Admin_Api::instance();
}
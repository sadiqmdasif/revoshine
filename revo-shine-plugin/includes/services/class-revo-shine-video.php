<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Revo_Shine_Video')) {

    class Revo_Shine_Video
    {
        private static $instance = null;

        public function __construct()
        {
            add_action('wp_loaded', array($this, 'check_video_shopping_page'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_revo_shine_styles_scripts'));
            add_filter('display_post_states', array($this, 'custom_video_state'));

            add_shortcode('video_shopping', array($this, 'shortcode_video_shopping'));
        }

        public function enqueue_revo_shine_styles_scripts()
        {
            if (get_the_title() !== 'Video Shopping') {
                return;
            }

            wp_enqueue_style('revo-shine-video-style', REVO_SHINE_ASSET_URL . 'css/video-page.css', array(),);
            wp_enqueue_style('revo-shine-iconsax-style', 'https://iconsax.gitlab.io/i/icons.css', array(), REVO_SHINE_PLUGIN_VERSION);

            wp_enqueue_script('revo-shine-video-script', REVO_SHINE_ASSET_URL . 'js/video.js', array(), null, true);
        }

        public function check_video_shopping_page()
        {
            if (!current_user_can('administrator') || wp_doing_ajax()) {
                return;
            }

            $video_shopping_page = revo_shine_get_page_by_title('Video Shopping');

            if (!empty($video_shopping_page)) {
                return;
            }

            $get_status_video_setting = query_revo_mobile_variable('"enable_affiliate_video"', 'sort');
            $status = isset($get_status_video_setting[0]) ? $get_status_video_setting[0]->description : 'show';

            $this->create_video_shopping_page('[video_shopping]', $status === 'show' ? 'publish' : 'draft');
        }

        public function create_video_shopping_page($template_video_page, $status)
        {
            $page_data = [
                'post_title'   => 'Video Shopping',
                'post_type'    => 'page',
                'post_content' => '<!-- wp:shortcode -->' . $template_video_page . '<!-- /wp:shortcode -->',
                'post_status'  => $status
            ];

            return wp_insert_post($page_data);
        }

        public function change_video_shopping_page_status($status)
        {
            $video_shopping_page = revo_shine_get_page_by_title('Video Shopping');

            if (empty($video_shopping_page)) {
                return $this->check_video_shopping_page();
            }

            return wp_update_post([
                'ID'          => $video_shopping_page->ID,
                'post_status' => $status,
                'post_type'   => 'page',
            ]);
        }

        public function custom_video_state($post_states)
        {
            global $post;

            if ($post && $post->post_title === 'Video Shopping') {
                $post_states['video_shopping'] = __('<span style="background: #404040; color: white; padding: 5px 10px; border-radius: 4px; font-size: 7pt">RevoSHINE - Video Shopping Page</span>', 'revo_video_shopping');
            }

            return $post_states;
        }

        /*
        * Data Video Shopping
        */
        public function process_video_data($wpdb, $data)
        {
            $get_view_data = $wpdb->get_results("SELECT * FROM revo_video_affiliate_views WHERE video_id = $data->id");
            $viewCount = 0;
            $clickCount = 0;
            $sales = 0;

            if ($get_view_data) {
                foreach ($get_view_data as $data_views) {
                    if ($data_views->type === "view") {
                        $viewCount++;
                    } elseif ($data_views->type === "click") {
                        $clickCount++;

                        if (!empty($data_views->information)) {
                            $information_array = json_decode($data_views->information, true);

                            if (!empty($information_array['order_id'])) {
                                $order = wc_get_order($information_array['order_id']);

                                if ($order && $order->get_status() === 'completed') {
                                    $sales += $order->get_total();
                                }
                            }
                        }
                    }
                }
            }

            $data_user_raw = get_userdata($data->user_id);

            if ($data_user_raw) {

                $product_data = get_post($data->product_id);
                $thumbnail = get_the_post_thumbnail_url($product_data->ID);

                if (!$thumbnail) {
                    $placeholder_path = 'wp-content/uploads/woocommerce-placeholder.png';
                    $thumbnail = site_url($placeholder_path);
                }

                $product_post = get_post($data->post_id);
                $product_type = wc_get_product($data->product_id);

                return [
                    'data_user'            => [
                        'user_id'   => $data_user_raw->ID,
                        'name'      => $data_user_raw->data->display_name,
                        'username'  => $data_user_raw->data->user_nicename,
                        'email'     => $data_user_raw->data->user_email,
                        'roles'     => $data_user_raw->roles,
                    ],
                    'video_affiliate'   => [
                        'video_id'        => $data->post_id,
                        'video_url'    => $data->video_url,
                        'date'            => date('d/m/Y', strtotime($data->created_at)),
                        'views'        => (string)$viewCount,
                        'clicks'        => (string)$clickCount,
                        'sales'        => (string)$sales,
                        'status'        => ($data->status === '0') ? 'inactive' : (($data->status === '1') ? 'active' : 'reject'),
                        'link_share'    => $product_post->guid,
                        'created_at'    => $data->created_at,
                    ],
                    'product_data'        => [
                        'product_id'    => $product_data->ID,
                        'post_title'    => $product_data->post_title,
                        'post_content'    => $product_data->post_content,
                        'type'            => $product_type->get_type(),
                        'thumbnail'    => $thumbnail
                    ],
                ];
            }
        }

        /*
        *  Shortcode Video Shopping
        */
        public function shortcode_video_shopping()
        {
            global $wpdb;

            $video_data = $wpdb->get_results("SELECT * FROM revo_video_affiliate");

            $output = '';

            $template = REVO_SHINE_TEMPLATE_PATH . 'user/video/view_video_template.php';

            foreach ($video_data as $data) {
                $processed_data = $this->process_video_data($wpdb, $data);

                if (file_exists($template)) {
                    $template_content = file_get_contents($template);

                    $custom_html = str_replace(
                        [
                            '{post_title}',
                            '{user_name}',
                            '{views}',
                            '{likes}',
                            '{post_video}',
                            '{description}',
                            '{roles}',
                        ],
                        [
                            $processed_data['product_data']['post_title'],
                            $processed_data['data_user']['name'],
                            $processed_data['video_affiliate']['views'],
                            $processed_data['video_affiliate']['clicks'],
                            $processed_data['video_affiliate']['video_url'],
                            $processed_data['product_data']['post_content'],
                            $processed_data['data_user']['roles'][0],
                        ],
                        $template_content
                    );

                    $output .= $custom_html;
                } else {
                    $output .= '<p>Error: Template file not found.</p>';
                }
            }

            return $output;
        }

        public static function get_instance()
        {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }

            return self::$instance;
        }
    }
}

Revo_Shine_Video::get_instance();
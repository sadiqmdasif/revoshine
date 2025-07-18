<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Revo_Shine_User_Controller')) {
    class Revo_Shine_User_Controller
    {
        protected $namespace;

        public function __construct()
        {
            $this->namespace = REVO_SHINE_NAMESPACE_API;
        }

        public function register_routes_api()
        {
            register_rest_route($this->namespace, '/register', array(
                'methods'             => 'POST',
                'callback'            => array($this, 'register'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, '/generate_auth_cookie', array(
                'methods'             => 'POST',
                'callback'            => array($this, 'generate_auth_cookie'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, '/fb_connect', array(
                'methods'             => 'GET',
                'callback'            => array($this, 'fb_connect'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, '/firebase_sms_login/v2', array(
                'methods'             => 'GET',
                'callback'            => array($this, 'firebase_sms_login_v2'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, '/apple_login', array(
                'methods'             => 'POST',
                'callback'            => array($this, 'apple_login'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, '/google_login', array(
                'methods'             => 'GET',
                'callback'            => array($this, 'google_login'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, '/post_comment', array(
                'methods'             => 'POST',
                'callback'            => array($this, 'post_comment'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, '/get_currentuserinfo', array(
                'methods'             => 'POST',
                'callback'            => array($this, 'get_currentuserinfo'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, '/update_user_profile', array(
                'methods'             => 'POST',
                'callback'            => array($this, 'update_user_profile'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, '/send-email-forgot-password', array(
                'methods'             => 'POST',
                'callback'            => array($this, 'send_email_forgot_password'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, '/upload-image', array(
                'methods'             => WP_REST_Server::CREATABLE,
                'args'                => $this->get_params_upload(),
                'callback'            => array($this, 'upload_image'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, '/check-password-reset-key', array(
                'methods'             => 'POST',
                'callback'            => array($this, 'check_password_reset_key'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, '/reset-password', array(
                'methods'             => 'POST',
                'callback'            => array($this, 'reset_password'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, '/affiliate/dashboard', array(
                'methods'             => 'GET',
                'callback'            => array($this, 'rest_affiliate_dashboard'),
                'permission_callback' => '__return_true'
            ));

            register_rest_route($this->namespace, '/check-phone-number', array(
                'methods'             => 'POST',
                'callback'            => array($this, 'check_phone_number'),
                'permission_callback' => '__return_true'
            ));

            // register_rest_route($this->namespace, '/sms_login', array(
            //     'methods'             => 'GET',
            //     'callback'            => array($this, 'sms_login'),
            //     'permission_callback' => '__return_true'
            // ));

            // register_rest_route($this->namespace, '/firebase_sms_login', array(
            //     'methods'             => 'GET',
            //     'callback'            => array($this, 'firebase_sms_login'),
            //     'permission_callback' => '__return_true'
            // ));

            // register_rest_route($this->namespace, '/firebase_sms_login/custom-username', array(
            //     'methods'             => 'GET',
            //     'callback'            => array($this, 'firebase_sms_login_custom_username'),
            //     'permission_callback' => '__return_true'
            // ));
        }

        public function register()
        {
            $json          = file_get_contents('php://input');
            $params        = json_decode($json);
            $usernameReq   = $params->username;
            $emailReq      = $params->email;
            $secondsReq    = $params->seconds ?? '';
            $roleReq       = $params->role ?? '';
            $userPassReq   = $params->user_pass;
            $userLoginReq  = $params->user_login;
            $userEmailReq  = $params->user_email;
            $referral_code = $params->ref;

            if ($roleReq && $roleReq != "subscriber" && $roleReq != "wcfm_vendor" && $roleReq != "seller") {
                return self::sendError("invalid_role", "Role is invalid.", 400);
            }

            $username = sanitize_user($usernameReq);
            $email    = sanitize_email($emailReq);

            if ($secondsReq) {
                $seconds = (int) $secondsReq;
            } else {
                $seconds = 120960000;
            }

            if (!validate_username($username)) {
                return self::sendError("invalid_username", "Username is invalid.", 400);
            } elseif (username_exists($username)) {
                return self::sendError("existed_username", "Username already exists.", 400);
            } else {
                if (!is_email($email)) {
                    return self::sendError("invalid_email", "E-mail address is invalid.", 400);
                } elseif (email_exists($email)) {
                    return self::sendError("existed_email", "E-mail address is already in use.", 400);
                } else {
                    if (!$userPassReq) {
                        $params->user_pass = wp_generate_password();
                    }

                    $allowed_params = array(
                        'user_login',
                        'user_email',
                        'user_pass',
                        'display_name',
                        'user_nicename',
                        'user_url',
                        'nickname',
                        'first_name',
                        'last_name',
                        'description',
                        'rich_editing',
                        'user_registered',
                        'role',
                        'jabber',
                        'aim',
                        'yim',
                        'comment_shortcuts',
                        'admin_color',
                        'use_ssl',
                        'show_admin_bar_front',
                    );

                    $dataRequest = $params;

                    foreach ($dataRequest as $field => $value) {
                        if (in_array($field, $allowed_params)) {
                            $user[$field] = trim(sanitize_text_field($value));
                        }
                    }

                    $user['role'] = $roleReq ? sanitize_text_field($roleReq) : get_option('default_role');
                    $user_id      = wp_insert_user($user);

                    if (is_wp_error($user_id)) {
                        return self::sendError($user_id->get_error_code(), $user_id->get_error_message(), 400);
                    }
                }
            }

            if (!empty($referral_code)) {
                $this->insert_signup_referral($referral_code, $user_id);
            }

            $expiration = time() + apply_filters('auth_cookie_expiration', $seconds, $user_id, true);
            $cookie     = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

            return array(
                "cookie"  => $cookie,
                "user_id" => $user_id,
            );
        }

        public function generate_auth_cookie($request)
        {
            if (empty($request['username']) || empty($request['password'])) {
                return self::sendError("invalid_login", "Invalid params", 400);
            }

            if (!empty($request['seconds'])) {
                $seconds = (int) $request['seconds'];
            } else {
                $seconds = 31536000;
            }

            if (!($user = get_user_by('login', $request['username']))) {
                $user = get_user_by('email', $request['username']);
            }

            if ($user) {
                $check_pass = wp_check_password($request['password'], $user->user_pass, $user->ID);

                if (!$check_pass && md5($user->user_pass) !== $request['password']) {
                    return self::sendError(401, "Invalid username/email and/or password.", 401);
                }

                $expiration = time() + apply_filters('auth_cookie_expiration', $seconds, $user->ID, true);
                $cookie     = wp_generate_auth_cookie($user->ID, $expiration, 'logged_in');
                preg_match('|src="(.+?)"|', get_avatar($user->ID, 512), $avatar);

                return array(
                    "cookie"      => $cookie,
                    "cookie_name" => LOGGED_IN_COOKIE,
                    "user"        => array(
                        "id"           => $user->ID,
                        "username"     => $user->user_login,
                        "nicename"     => $user->user_nicename,
                        "email"        => $user->user_email,
                        "url"          => $user->user_url,
                        "registered"   => $user->user_registered,
                        "displayname"  => $user->display_name,
                        "firstname"    => $user->user_firstname,
                        "lastname"     => $user->last_name,
                        "nickname"     => $user->nickname,
                        "description"  => $user->user_description,
                        "capabilities" => $user->wp_capabilities,
                        "role"         => array_values($user->roles),
                        "avatar"       => $avatar[1] ?? '',
                    ),
                );
            }

            return self::sendError(401, "Invalid username/email and/or password.", 401);
        }

        public function generate_auth_cookie_2()
        {
            $json   = file_get_contents('php://input');
            $params = json_decode($json);

            if (!isset($params->username) || !isset($params->username)) {
                return self::sendError("invalid_login", "Invalid params", 400);
            }

            $username = $params->username;
            $password = $params->password;

            if ($params->seconds) {
                $seconds = (int) $params->seconds;
            } else {
                $seconds = 1209600;
            }

            $user = get_user_by('login', $username);

            if (!$user) {
                $user = get_user_by('email', $username);
            }

            if ($user) {
                $check_pass = wp_check_password($password, $user->user_pass, $user->ID);

                if (!$check_pass) {
                    return self::sendError(401, "Invalid username/email and/or password.", 401);
                }

                $expiration = time() + apply_filters('auth_cookie_expiration', $seconds, $user->ID, true);
                $cookie     = wp_generate_auth_cookie($user->ID, $expiration, 'logged_in');
                preg_match('|src="(.+?)"|', get_avatar($user->ID, 512), $avatar);

                return array(
                    "cookie"      => $cookie,
                    "cookie_name" => LOGGED_IN_COOKIE,
                    "user"        => array(
                        "id"           => $user->ID,
                        "username"     => $user->user_login,
                        "nicename"     => $user->user_nicename,
                        "email"        => $user->user_email,
                        "url"          => $user->user_url,
                        "registered"   => $user->user_registered,
                        "displayname"  => $user->display_name,
                        "firstname"    => $user->user_firstname,
                        "lastname"     => $user->last_name,
                        "nickname"     => $user->nickname,
                        "description"  => $user->user_description,
                        "capabilities" => $user->wp_capabilities,
                        "role"         => array_values($user->roles),
                        "avatar"       => $avatar[1] ?? '',

                    ),
                );
            }

            return self::sendError(401, "Invalid username/email and/or password.", 401);
        }

        public function firebase_sms_login_v2($request)
        {
            global $wpdb;

            $phone            = $request["phone"];
            $country_code     = $request["country_code"];
            $referral_code    = $request["ref_code"];
            $username_custom  = $request["username"];
            $firstname_custom = $request['firstname'];
            $lastname_custom  = $request['lastname'];
            $email_custom     = $request['email'];

            if (empty($phone) || empty($country_code)) {
                return $this->sendError("invalid_login", "You must include a 'phone' & 'country_code' variable .", 400);
            }

            $domain = $_SERVER['SERVER_NAME'];
            if (count(explode(".", $domain)) == 1) {
                $domain = "flutter.io";
            }

            $user_name    = $phone;
            $user_email   = $phone . "@" . $domain;
            $email_exists = email_exists($user_email);

            $check_digit_meta_data = $wpdb->get_row("SELECT user_id, meta_key, meta_value FROM {$wpdb->prefix}usermeta WHERE meta_key = 'digits_phone' AND meta_value = '+{$phone}'", OBJECT);

            // account registered => check by email, digit meta
            if ($email_exists || !is_null($check_digit_meta_data)) {
                $user = get_user_by('email', $user_email);
                if (!$user) {
                    $user = get_user_by('id', $check_digit_meta_data->user_id);
                }

                $user_id   = $user->ID;
                $user_name = $user->user_login;
                $user_pass = md5($user->user_pass);

                // wp_update_user(array('ID' => $user_id, 'user_pass' => $user_pass));

                $user_account = 'user logged in.';

                if ($user->first_name != $user_name) {
                    $result = $user->first_name;

                    if ($user->last_name) {
                        $result .= " " . $user->last_name;
                    }
                }
            }

            // account not registered
            if (!isset($user_id) && $email_exists === false) {

                if (!empty($email_custom) && email_exists($email_custom)) {
                    return $this->sendError("invalid_login", "email already exist, please try using another email", 400);
                }

                // custom username by user -> output $user_name
                if (!$username_custom) {
                    return self::sendError("invalid_login", "create username first", 400);
                } else {
                    if (username_exists($username_custom)) {
                        return self::sendError("invalid_login", "username already exist, please try using another username", 400);
                    } else {
                        $user_name = $username_custom;
                    }
                }

                $user_pass = wp_generate_password(24, true, true);

                $userdata = array(
                    'user_login'   => $user_name,
                    'user_email'   => !empty($email_custom) ? $email_custom : $user_email,
                    'user_pass'    => $user_pass,
                    'display_name' => $user_name
                );

                $user_id = wp_insert_user($userdata);

                if (is_wp_error($user_id)) {
                    return $this->sendError($user_id->get_error_code(), $user_id->get_error_message(), 400);
                }

                $user_account = 'user registered.';

                update_user_meta($user_id, 'first_name', !empty($firstname_custom) ? $firstname_custom : $user_name);
                update_user_meta($user_id, 'last_name', !empty($lastname_custom) ? $lastname_custom : $phone);
                update_user_meta($user_id, 'nickname', $user_name);

                if (!empty($referral_code)) {
                    $this->insert_signup_referral($referral_code, $user_id);
                }
            }

            // add digits meta datas
            $split_phone    = str_split($phone, strlen($country_code));
            $split_phone[0] = null;

            add_user_meta($user_id, 'digt_countrycode', '+' . $country_code, true);
            add_user_meta($user_id, 'digits_phone_no', implode('', $split_phone), true);
            add_user_meta($user_id, 'digits_phone', '+' . $phone, true);

            // update user meta
            update_user_meta($user_id, 'billing_phone', $phone);

            // response
            $expiration = time() + apply_filters('auth_cookie_expiration', 120960000, $user_id, true);
            $cookie     = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

            $response['msg']        = $user_account;
            $response['wp_user_id'] = $user_id;
            $response['cookie']     = $cookie;
            $response['user_login'] = $user_name;
            $response['user']       = $result ?? "User OTP";
            $response['token']      = $user_pass;

            return $response;
        }

        public function fb_connect($request)
        {
            $enable_ssl      = true;
            $fields          = 'id,name,first_name,last_name,email';
            $access_token    = $request["access_token"];
            $username_custom = $request["username"];
            $referral_code   = $request["ref_code"];

            if (!isset($access_token)) {
                return self::sendError("invalid_login", "You must include a 'access_token' variable. Get the valid access_token for this app from Facebook API.", 400);
            }

            $url = 'https://graph.facebook.com/me/?fields=' . $fields . '&access_token=' . $access_token;

            //  Initiate curl
            $ch = curl_init();
            // Enable SSL verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $enable_ssl);
            // Will return the response, if false it print the response
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // Set the url
            curl_setopt($ch, CURLOPT_URL, $url);
            // Execute
            $result = curl_exec($ch);
            // Closing
            curl_close($ch);

            $result = json_decode($result, true);

            if (isset($result["email"])) {
                $user_email   = $result["email"];
                $email_exists = email_exists($user_email);

                if ($email_exists) {
                    $user         = get_user_by('email', $user_email);
                    $user_id      = $user->ID;
                    $user_name    = $user->user_login;
                    $user_account = 'user logged in.';
                    $user_pass    = md5($user->user_pass);
                } else {
                    // $user_name = strtolower($result['first_name'] . '.' . $result['last_name']);

                    // custom username by user
                    // if (empty($username_custom) || is_null($username_custom) || $username_custom === "null") {
                    //     return self::sendError("invalid_login", "create username first", 400);
                    // } else {
                    //     if (username_exists($username_custom)) {
                    //         return self::sendError("invalid_login", "username already exist, please try using another username", 400);
                    //     } else {
                    //         $user_name = $username_custom;
                    //     }
                    // }
                    // end custom username by user -> output $user_name
                    $user_name = $result['email'];

                    $user_pass = wp_generate_password(24, true, true);
                    $userdata  = array(
                        'user_login'   => $user_name,
                        'user_email'   => $user_email,
                        'user_pass'    => $user_pass,
                        'display_name' => $result["name"],
                        'first_name'   => $result['first_name'],
                        'last_name'    => $result['last_name'],
                        'user'         => $result
                    );

                    $user_id = wp_insert_user($userdata);
                    if ($user_id) {
                        $user_account = 'user registered.';
                    }

                    if (!empty($referral_code)) {
                        $this->insert_signup_referral($referral_code, $user_id);
                    }
                }

                $expiration = time() + apply_filters('auth_cookie_expiration', 1209600, $user_id, true);
                $cookie     = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

                $response['msg']        = $user_account;
                $response['wp_user_id'] = $user_id;
                $response['cookie']     = $cookie;
                $response['user_login'] = $user_name;
                $response['user']       = $result;
                $response['token']      = $user_pass;
            } else {
                return self::sendError("invalid_login", "Your 'access_token' did not return email of the user. Without 'email' user can't be logged in or registered. Get user email extended permission while joining the Facebook app.", 400);
            }

            return $response;
        }

        public function apple_login($request)
        {
            $email           = $request["email"];
            $display_name    = $request["display_name"];
            $referral_code   = $request["ref_code"];
            // $username_custom = $request["username"];
            // $user_name       = $request["user_name"];

            if (!isset($email)) {
                return self::sendError("invalid_login", "You must include a 'email' variable.", 400);
            }

            $email_exists = email_exists($email);

            if (!$email_exists) {
                // custom username by user
                // if (empty($username_custom) || !$username_custom) {
                //     return self::sendError("invalid_login", "create username first", 400);
                // } else {
                //     if (username_exists($username_custom)) {
                //         return self::sendError("invalid_login", "username already exist, please try using another username", 400);
                //     } else {
                //         $user_name = $username_custom;
                //     }
                // }
                // end custom username by user -> output $user_name

                $user_pass = wp_generate_password(24, true, true);
                $userdata  = array(
                    'user_login'   => $email,
                    'user_email'   => $email,
                    'user_pass'    => $user_pass,
                    'display_name' => $display_name,
                    'first_name'   => $display_name,
                    'last_name'    => ""
                );

                $user_id = wp_insert_user($userdata);
                if ($user_id) {
                    $user_account = 'user registered.';
                }

                if (!empty($referral_code)) {
                    $this->insert_signup_referral($referral_code, $user_id);
                }
            } else {
                $user_account = 'user logged in.';

                $user      = get_user_by('email', $email);
                $user_id   = $user->ID;
                $user_name = $user->user_login;

                if ($user_id) {
                    $user_account = 'user logged in.';
                    $user_pass    = md5($user->user_pass);
                    // wp_update_user(array('ID' => $user_id, 'user_pass' => $user_pass));
                }
            }

            $expiration = time() + apply_filters('auth_cookie_expiration', 120960000, $user_id, true);
            $cookie     = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

            $response['msg']        = $user_account;
            $response['wp_user_id'] = $user_id;
            $response['cookie']     = $cookie;
            $response['user_login'] = $user_name;
            $response['user']       = $user;
            $response['token']      = $user_pass;

            return $response;
        }

        public function google_login($request)
        {
            $access_token    = $request["access_token"];
            $username_custom = $request["username"];
            $referral_code   = $request["ref_code"];

            if (!isset($access_token)) {
                return self::sendError("invalid_login", "You must include a 'access_token' variable. Get the valid access_token for this app from Google API.", 400);
            }

            $url = 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token=' . $access_token;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($result, true);

            if (isset($result["email"])) {
                $firstName = $result["given_name"];
                $lastName  = $result["family_name"];
                $email     = $result["email"];
                $avatar    = $result["picture"];

                $display_name = $firstName . " " . $lastName;
                // $user_name    = $firstName . "." . $lastName;
                $user_email   = $email;
                $email_exists = email_exists($user_email);

                if ($email_exists) {
                    $user         = get_user_by('email', $user_email);
                    $user_id      = $user->ID;
                    $user_name    = $user->user_login;
                    $user_account = 'user logged in.';
                    $user_pass    = md5($user->user_pass);

                    // wp_update_user(array('ID' => $user_id, 'user_pass' => $user_pass));
                } else {
                    // custom username by user
                    // if (!$username_custom) {
                    //     return self::sendError("invalid_login", "create username first", 400);
                    // } else {
                    //     if (username_exists($username_custom)) {
                    //         return self::sendError("invalid_login", "username already exist, please try using another username", 400);
                    //     } else {
                    //         $user_name = $username_custom;
                    //     }
                    // }
                    // end custom username by user -> output $user_name
                    $user_name = $email;
                    $user_pass = wp_generate_password(24, true, true);
                    $userdata  = array(
                        'user_login'   => $user_name,
                        'user_email'   => $user_email,
                        'user_pass'    => $user_pass,
                        'display_name' => $display_name,
                        'first_name'   => $display_name,
                        'last_name'    => ""
                    );

                    $user_id = wp_insert_user($userdata);
                    if ($user_id) {
                        $user_account = 'user registered.';
                    }

                    if (!empty($referral_code)) {
                        $this->insert_signup_referral($referral_code, $user_id);
                    }
                }

                $expiration = time() + apply_filters('auth_cookie_expiration', 120960000, $user_id, true);
                $cookie     = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

                $response['msg']        = $user_account;
                $response['wp_user_id'] = $user_id;
                $response['cookie']     = $cookie;
                $response['user_login'] = $user_name;
                $response['user']       = $result;
                $response['token']      = $user_pass;

                return $response;
            } else {
                return self::sendError("invalid_login", "Your 'token' did not return email of the user. Without 'email' user can't be logged in or registered. Get user email extended permission while joining the Google app.", 400);
            }
        }

        /*
		* Post commment function
		*/
        public function post_comment()
        {
            global $json_api;
            $json   = file_get_contents('php://input');
            $params = json_decode($json);

            $cookie  = $params->cookie;
            $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
            if (!$user_id) {
                return self::sendError("invalid_login", "Invalid cookie. Use the `generate_auth_cookie` method.", 401);
            }
            if (!$params->post) {
                return self::sendError("invalid_data", "No post specified. Include 'post_id' var in your request.", 400);
            } elseif (!$params->comment) {
                return self::sendError("invalid_data", "Please include 'content' var in your request.", 400);
            }

            $comment_approved = 1;
            $user_info        = get_userdata($user_id);
            $time             = current_time('mysql');
            $agent            = filter_has_var(INPUT_SERVER, 'HTTP_USER_AGENT') ? filter_input(INPUT_SERVER, 'HTTP_USER_AGENT') : 'Mozilla';
            $ips              = filter_has_var(INPUT_SERVER, 'REMOTE_ADDR') ? filter_input(INPUT_SERVER, 'REMOTE_ADDR') : '127.0.0.1';
            $data             = array(
                'comment_post_ID'      => $params->post,
                'comment_author'       => $user_info->user_login,
                'comment_author_email' => $user_info->user_email,
                'comment_author_url'   => $user_info->user_url,
                'comment_content'      => $params->comment,
                'comment_type'         => '',
                'comment_parent'       => 0,
                'user_id'              => $user_info->ID,
                'comment_author_IP'    => $ips,
                'comment_agent'        => $agent,
                'comment_date'         => $time,
                'comment_approved'     => $comment_approved,
            );
            //print_r($data);
            $comment_id = wp_insert_comment($data);
            // //add metafields
            // $meta = json_decode(stripcslashes($request["meta"]), true);
            // //extra function
            // add_comment_meta($comment_id, 'rating', $meta['rating']);
            // add_comment_meta($comment_id, 'verified', 0);

            return array(
                "code"    => "insert_comment_success",
                "message" => "$comment_id",
                "data"    => ['status' => 200],
            );
        }

        public function get_currentuserinfo($request)
        {
            global $json_api;

            $json   = file_get_contents('php://input');
            $params = json_decode($json);
            $cookie = $params->cookie;

            if (!isset($cookie)) {
                return self::sendError("invalid_login", "You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
            }

            $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
            if (!$user_id) {
                return self::sendError("invalid_login", "You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
            }

            $user      = get_userdata($user_id);
            $user_meta = get_user_meta($user_id);
            preg_match('|src="(.+?)"|', get_avatar($user->ID, 32), $avatar);

            $data = array(
                "user" => array(
                    "id"                 => $user->ID,
                    "username"           => $user->user_login,
                    "nicename"           => $user->user_nicename,
                    "email"              => $user->user_email,
                    "url"                => $user->user_url,
                    "registered"         => $user->user_registered,
                    "displayname"        => $user->display_name,
                    "firstname"          => $user->user_firstname,
                    "lastname"           => $user->last_name,
                    "nickname"           => $user->nickname,
                    "description"        => $user->user_description,
                    "capabilities"       => $user->wp_capabilities,
                    "role"               => array_values($user->roles),
                    "avatar"             => $avatar[1] ?? '',
                    'phone_number'       => isset($user_meta['digits_phone'][0])     ? str_replace('+', '', $user_meta['digits_phone'][0]) : '',
                    'billing_state_name' => isset($user_meta['billing_state'][0])    ? $user_meta['billing_state'][0] : '',
                    'billing_country'    => isset($user_meta['billing_country'][0])  ? $user_meta['billing_country'][0] : '',
                    'billing_postcode'   => isset($user_meta['billing_postcode'][0]) ? $user_meta['billing_postcode'][0] : '',
                )
            );

            $user_affiliate = 'inactive';

            if (is_plugin_active('indeed-affiliate-pro/indeed-affiliate-pro.php')) {
                $data['referral'] = ['referral_value' => '', 'referral_path'  => '', 'referral_link'  => '', 'dashboard_link' => ''];

                global $wpdb;

                $check_user_affiliate = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}uap_affiliates WHERE uid=$user->ID", OBJECT);

                if ($check_user_affiliate) {
                    $link_for_aff      = get_option('uap_referral_custom_base_link');
                    $referral_format   = get_option('uap_default_ref_format');
                    $referral_variable = get_option('uap_referral_variable', 'ref');

                    if (empty($link_for_aff)) {
                        $link_for_aff = get_home_url() . '/';
                    }

                    if ($referral_format === 'id') {
                        $affiliate = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}uap_affiliates WHERE uid = '$user_id'", OBJECT);
                        $referral  = $affiliate->id;
                    } else {
                        $referral = urlencode($user->user_login);
                    }

                    $data['referral'] = [
                        'referral_value' => $referral,
                        'referral_path'  => "?{$referral_variable}={$referral}",
                        'referral_link'  => "{$link_for_aff}?{$referral_variable}={$referral}",
                        'dashboard_link' => get_permalink(get_option('uap_general_user_page')),
                    ];

                    $user_affiliate = 'active';
                }
            }

            $data['user_affiliate'] = $user_affiliate;

            if (is_plugin_active('woocommerce-memberships/woocommerce-memberships.php')) {
                $data['membership_plan'] = wc_memberships_get_user_active_memberships($user_id)[0]->plan->name ?? '';
            }

            global $wc_points_rewards;
            if (isset($wc_points_rewards)) {
                $points_balance = WC_Points_Rewards_Manager::get_users_points($user_id);
                $points_label   = $wc_points_rewards->get_points_label($points_balance);
                $count          = apply_filters('wc_points_rewards_my_account_points_events', 5, $user_id);
                $current_page   = empty($current_page) ? 1 : absint($current_page);

                $args       = array(
                    'calc_found_rows' => true,
                    'orderby'         => array(
                        'field' => 'date',
                        'order' => 'DESC',
                    ),
                    'per_page'        => $count,
                    'paged'           => $current_page,
                    'user'            => $user_id,
                );
                $total_rows = WC_Points_Rewards_Points_Log::$found_rows;
                $events     = WC_Points_Rewards_Points_Log::get_points_log_entries($args);
                $events_fix = [];

                foreach ($events as $value) {
                    if ($value->points === "0") {
                        continue;
                    }

                    $events_fix[] = $value;
                }

                $data['poin'] = array(
                    'points_balance' => $points_balance,
                    'points_label'   => $points_label,
                    'total_rows'     => $total_rows,
                    'page'           => $current_page,
                    'count'          => $count,
                    'events'         => $events_fix
                );
            }

            return $data;
        }

        public function update_user_profile()
        {
            global $json_api, $wpdb;

            $json   = file_get_contents('php://input');
            $params = json_decode($json);
            $cookie = $params->cookie;

            if (!isset($cookie)) {
                return self::sendError("invalid_login", "You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
            }

            $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
            if (!$user_id) {
                return self::sendError("invalid_login", "You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
            }

            $user_update = array('ID' => $user_id);

            if ($params->user_pass) {
                $user       = get_user_by('id', $user_id);
                $pass_check = wp_check_password($params->old_pass, $user->data->user_pass, $user_id);
                if (!$pass_check) {
                    return self::sendError("invalid_login", "wrong password!", 401);
                }
                $user_update['user_pass'] = $params->user_pass;
            }

            if ($params->user_nicename) {
                $user_update['user_nicename'] = $params->user_nicename;
            }

            if ($params->user_email) {
                $user_update['user_email'] = $params->user_email;
            }

            if ($params->user_url) {
                $user_update['user_url'] = $params->user_url;
            }

            if ($params->display_name) {
                $user_update['display_name'] = $params->display_name;
            }

            if ($params->first_name) {
                $user_update['first_name'] = $params->first_name;
            }

            if ($params->last_name) {
                $user_update['last_name'] = $params->last_name;
            }

            $user_data = wp_update_user($user_update);

            if (is_wp_error($user_data)) {

                // There was an error; possibly this user doesn't exist.
                $return['is_success'] = false;
                $return['cookie']     = $token ?? null;
                $return['message']    = $user_data->get_error_message();
            } else {

                if (!empty($params->country_code) && !empty($params->phone_number)) {
                    $phone        = $params->phone_number;
                    $country_code = $params->country_code;

                    $check_digit_meta_data = $wpdb->get_row("SELECT user_id, meta_key, meta_value FROM {$wpdb->prefix}usermeta WHERE meta_key = 'digits_phone' AND meta_value = '+{$phone}'", OBJECT);

                    if ($check_digit_meta_data && $check_digit_meta_data->user_id != $user_id) {
                        return self::sendError("invalid_login", "phone number already registered!", 401);
                    }

                    // add / update digits meta datas
                    $split_phone    = str_split($phone, strlen($country_code));
                    $split_phone[0] = null;

                    update_user_meta($user_id, 'digt_countrycode', '+' . $country_code);
                    update_user_meta($user_id, 'digits_phone_no', implode('', $split_phone));
                    update_user_meta($user_id, 'digits_phone', '+' . $phone);

                    // update user meta
                    update_user_meta($user_id, 'billing_phone', $phone);
                }

                $expiration           = time() + apply_filters('auth_cookie_expiration', 120960000, $user_id, true);
                $return['is_success'] = true;
                $return['cookie']     = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');
            }

            return $return;
        }

        public function check_phone_number($request)
        {
            global $wpdb;

            $cookie       = $request->get_param('cookie');
            $country_code = $request->get_param('country_code');
            $phone_number = $request->get_param('phone_number');

            if (empty($cookie)) {
                return self::sendError("invalid_login", "You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
            }

            if (empty($country_code) || empty($phone_number)) {
                return self::sendError("invalid_login", "You must include country_code & phone_number '.", 422);
            }

            $user_id = wp_validate_auth_cookie($cookie, 'logged_in');
            if (!$user_id) {
                return self::sendError("invalid_login", "You must include a 'cookie' var in your request. Use the `generate_auth_cookie` method.", 401);
            }

            $check_digit_meta_data = $wpdb->get_row("SELECT user_id, meta_key, meta_value FROM {$wpdb->prefix}usermeta WHERE meta_key = 'digits_phone' AND meta_value = '+{$phone_number}'", OBJECT);

            if ($check_digit_meta_data && $check_digit_meta_data->user_id != $user_id) {
                return [
                    'status'  => 'error',
                    'message' => 'phone number already registered'
                ];
            }

            return [
                'status'  => 'success',
                'message' => $phone_number
            ];
        }

        public function send_email_forgot_password()
        {
            include '../wp-load.php';

            $json   = file_get_contents('php://input');
            $params = json_decode($json);
            $login  = $params->email;

            if (empty($login)) {
                $json = array('status' => 'error', 'message' => 'Please enter login user detail');
                echo json_encode($json);
                exit;
            }

            $userdata = get_user_by('email', $login);

            if (empty($userdata)) {
                $userdata = get_user_by('login', $login);
            }

            if (empty($userdata)) {
                $json = array('code' => '101', 'message' => 'User not found');
                echo json_encode($json);
                exit;
            }

            $user      = new WP_User(intval($userdata->ID));
            $reset_key = get_password_reset_key($user);
            $wc_emails = WC()->mailer()->get_emails();
            $wc_emails['WC_Email_Customer_Reset_Password']->trigger($user->user_login, $reset_key);

            $result = [
                'status'  => 'success',
                'message' => 'Password reset link has been sent to your registered email !'
            ];
            echo json_encode($result);
            exit;
        }

        public function get_params_upload()
        {
            $params = array(
                'media_attachment' => array(
                    'required'    => false,
                    'description' => __('Image encoded as base64.', 'image-from-base64'),
                    'type'        => 'string'
                ),
                'title'            => array(
                    'required'    => false,
                    'description' => __('The title for the object.', 'image-from-base64'),
                    'type'        => 'json'
                ),
                'media_path'       => array(
                    'description' => __('Path to directory where file will be uploaded.', 'image-from-base64'),
                    'type'        => 'string'
                )
            );

            return $params;
        }

        public function upload_image($request)
        {
            $response = array();
            $json     = file_get_contents('php://input');
            $params   = json_decode($json);
            try {
                $request['media_path']       = (@$params->media_path != '' ? $params->media_path : '');
                $request['title']            = array('rendered' => (@$params->title != '' ? $params->title : ''));
                $request['media_attachment'] = (@$params->media_attachment != '' ? $params->media_attachment : '');
                $filename                    = $request['title']['rendered'];
                $img                         = $request['media_attachment'];
                if (!empty($request['media_path'])) {
                    $this->upload_dir = $request['media_path'];
                    $this->upload_dir = '/' . trim($this->upload_dir, '/');
                    add_filter('upload_dir', array($this, 'change_wp_upload_dir'));
                }

                if (!class_exists('WP_REST_Attachments_Controller')) {
                    throw new Exception('WP API not installed.');
                }
                $media_controller = new WP_REST_Attachments_Controller('attachment');
                $decoded          = base64_decode($img);

                $permission_check = $media_controller->create_item_permissions_check($request);
                if (is_wp_error($permission_check)) {
                    throw new Exception($permission_check->get_error_message());
                }

                $request->set_body($decoded);
                $request->add_header('Content-Disposition', "attachment;filename=\"{$filename}\"");
                $result   = $media_controller->create_item($request);
                $response = rest_ensure_response($result);
            } catch (Exception $e) {
                $response['result']  = "error";
                $response['message'] = $e->getMessage();

                return $response;
            }

            if (!empty($request['media_path'])) {
                remove_filter('upload_dir', array($this, 'change_wp_upload_dir'));
                // $response = $request['id'];
            }

            $return = array(
                'id'    => $response->data['id'],
                'image' => $response->data['source_url'],
            );

            return $return;
        }

        public function check_password_reset_key()
        {
            global $wp_hasher;

            $params = json_decode(file_get_contents('php://input'));
            $key    = preg_replace('/[^a-z0-9]/i', '', $params->key);
            $user   = get_userdata($params->id);

            if (empty($key) || !is_string($key)) {
                return new WP_Error('invalid_key', __('Invalid key.'));
            }

            if (empty($wp_hasher)) {
                require_once ABSPATH . WPINC . '/class-phpass.php';
                $wp_hasher = new PasswordHash(8, true);
            }

            if (!$user) {
                return new WP_Error('user_not_found', __("User id {$params->id} not found."));
            }

            list($expiration_time, $pass_key) = $this->check_user_activation_key($user);

            $hash_is_correct = $wp_hasher->CheckPassword($key, $pass_key);

            if ($hash_is_correct && $expiration_time && time() < $expiration_time) {
                return $user;
            } elseif ($hash_is_correct && $expiration_time) {
                // Key has an expiration time that's passed
                return new WP_Error('expired_key', __('Invalid key.'));
            }

            if (hash_equals($user->user_activation_key, $key) || ($hash_is_correct && !$expiration_time)) {
                $return  = new WP_Error('expired_key', __('Invalid key.'));
                $user_id = $user->ID;

                return apply_filters('password_reset_key_expired', $return, $user_id);
            }

            return new WP_Error('invalid_key', __('Invalid key.'));
        }

        public function reset_password()
        {
            global $wpdb, $wp_hasher;

            $params   = json_decode(file_get_contents('php://input'));
            $key      = preg_replace('/[^a-z0-9]/i', '', $params->key);
            $user_id  = $params->id;
            $password = $params->new_password;

            $user = get_userdata($params->id);

            if (empty($password) || empty($user_id) || empty($key)) {
                return [
                    'status'  => 'error',
                    'message' => 'parameter id, new_password, and key is required'
                ];
            }

            if (!$user) {
                return new WP_Error('user_not_found', __("User id {$params->id} not found."));
            }

            if (empty($wp_hasher)) {
                require_once ABSPATH . WPINC . '/class-phpass.php';
                $wp_hasher = new PasswordHash(8, true);
            }

            list($expiration_time, $pass_key) = $this->check_user_activation_key($user);

            $hash_is_correct = $wp_hasher->CheckPassword($key, $pass_key);

            if ($hash_is_correct && $expiration_time && time() < $expiration_time) {
                $wpdb->update(
                    $wpdb->users,
                    [
                        'user_pass'           => wp_hash_password($password),
                        'user_activation_key' => '',
                    ],
                    ['ID' => $user_id]
                );

                clean_user_cache($user_id);

                return [
                    'status'  => 'success',
                    'message' => 'Password changed successfully'
                ];
            } elseif ($hash_is_correct && $expiration_time) {
                // Key has an expiration time that's passed
                return new WP_Error('expired_key', __('Invalid key.'));
            }

            return new WP_Error('invalid_key', __('Invalid key.'));
        }

        public function rest_affiliate_dashboard()
        {

            $cookie = $_GET['cookie'];

            if (empty($cookie)) {
                return [
                    'status'  => false,
                    'message' => 'You must include cookie!'
                ];
            }

            $userId = wp_validate_auth_cookie($cookie, 'logged_in');

            if (!$userId) {
                return [
                    'status'  => false,
                    'message' => 'Invalid authentication cookie. Please try to login again!'
                ];
            }

            $user = get_userdata($userId);
            if ($user) {
                wp_set_current_user($userId, $user->user_login);
                wp_set_auth_cookie($userId);
            }

            $dashboard_link = get_permalink(get_option('uap_general_user_page'));

            wp_redirect($dashboard_link);
            exit();
        }

        protected static function sendError($code, $message, $statusCode)
        {
            return new WP_Error($code, $message, array('status' => $statusCode));
        }

        protected function check_user_activation_key($user)
        {
            $expiration_duration = apply_filters('password_reset_expiration', DAY_IN_SECONDS);

            if (false !== strpos($user->user_activation_key, ':')) {
                list($pass_request_time, $pass_key) = explode(':', $user->user_activation_key, 2);
                $expiration_time = $pass_request_time + $expiration_duration;
            } else {
                $pass_key        = $user->user_activation_key;
                $expiration_time = false;
            }

            if (!$pass_key || empty($pass_key)) {
                return [false, "invalid_key"];
            }

            return [$expiration_time, $pass_key];
        }

        /**
         * add support for plugin ultimate affiliate
         * run if plugin is active
         *
         * @param string $referral_code
         * @param int $user_id
         *
         * @return bool
         */
        private function insert_signup_referral($referral_code, $user_id = null)
        {

            global $wpdb;

            if (is_null($user_id)) {
                return false;
            }

            if (is_plugin_active('indeed-affiliate-pro/indeed-affiliate-pro.php')) {
                require_once UAP_PATH . 'public/Affiliate_Referral_Amount.class.php';

                try {
                    // user parent detail
                    $parent    = get_user_by('login', $referral_code);
                    $parent_id = $parent->ID;

                    $affiliate_id = $wpdb->get_row("SELECT id FROM {$wpdb->prefix}uap_affiliates WHERE uid = {$parent_id}", OBJECT);
                    if (is_null($affiliate_id)) {
                        throw new Exception("Parent not found", 1);
                    }

                    $do_math = new Affiliate_Referral_Amount($affiliate_id->id, '');
                    $amount  = $do_math->get_signup_amount();

                    // wp_uap_affiliate_referral_users_relations
                    $wpdb->insert($wpdb->prefix . "uap_affiliate_referral_users_relations", array(
                        'affiliate_id'    => $affiliate_id->id,
                        'referral_wp_uid' => $user_id,
                        'date'            => wp_date('Y-m-d H:i:s'),
                    ));

                    // wp_uap_visits
                    $wpdb->insert($wpdb->prefix . "uap_visits", array(
                        'ref_hash'      => md5($_SERVER['REMOTE_ADDR'] . time()),
                        'referral_id'   => 0,
                        'affiliate_id'  => $affiliate_id->id,
                        'campaign_name' => '',
                        'ip'            => $_SERVER['SERVER_ADDR'],
                        'url'           => get_site_url() . '/',
                        'browser'       => 'Chrome',
                        'device'        => wp_is_mobile() ? 'mobile' : 'web',
                        'visit_date'    => wp_date('Y-m-d H:i:s'),
                        'status'        => null
                    ));

                    $uap_visit_id = $wpdb->insert_id;

                    // wp_uap_referrals
                    $wpdb->insert($wpdb->prefix . "uap_referrals", array(
                        'refferal_wp_uid'    => $user_id,
                        'campaign'           => '',
                        'affiliate_id'       => $affiliate_id->id,
                        'visit_id'           => $uap_visit_id,
                        'description'        => 'User SignUp',
                        'source'             => 'User SignUp',
                        'reference'          => 'user_id_' . $user_id,
                        'reference_details'  => 'User SignUp',
                        'parent_referral_id' => 0,
                        'child_referral_id'  => 0,
                        'amount'             => $amount,
                        'currency'           => !get_option('uap_currency') ? 'USD' : get_option('uap_currency'),
                        'status'             => 2,
                    ));

                    // wp_uap_visits - update
                    $wpdb->update($wpdb->prefix . "uap_visits", ['referral_id' => $wpdb->insert_id], ['id' => $uap_visit_id]);

                    return true;
                } catch (\Throwable $th) {
                    return false;
                }
            }

            return false;
        }

        /**
         * Deprecated method
         */
        public function sms_login($request)
        {
            $access_token = $request["access_token"];
            if (!isset($access_token)) {
                return self::sendError("invalid_login", "You must include a 'access_token' variable. Get the valid access_token for this app from Facebook API.", 400);
            }
            $url = 'https://graph.accountkit.com/v1.3/me/?access_token=' . $access_token;

            $WP_Http_Curl = new WP_Http_Curl();
            $result       = $WP_Http_Curl->request($url, array(
                'method'      => 'GET',
                'timeout'     => 5,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking'    => true,
                'headers'     => array(),
                'body'        => null,
                'cookies'     => array(),
            ));

            $result = json_decode($result, true);

            if (isset($result["phone"])) {
                $user_name    = $result["phone"]["number"];
                $user_email   = $result["phone"]["number"] . "@flutter.io";
                $email_exists = email_exists($user_email);

                if ($email_exists) {
                    $user      = get_user_by('email', $user_email);
                    $user_id   = $user->ID;
                    $user_name = $user->user_login;
                }

                if (!$user_id && $email_exists == false) {
                    $i = 1;
                    while (username_exists($user_name)) {
                        $i++;
                        $user_name = strtolower($user_name) . '.' . $i;
                    }
                    $random_password = wp_generate_password();
                    $userdata        = array(
                        'user_login'   => $user_name,
                        'user_email'   => $user_email,
                        'user_pass'    => $random_password,
                        'display_name' => $user_name,
                        'first_name'   => $user_name,
                        'last_name'    => "",
                    );

                    $user_id = wp_insert_user($userdata);
                    if ($user_id) {
                        $user_account = 'user registered.';
                    }
                } else {
                    if ($user_id) {
                        $user_account = 'user logged in.';
                    }
                }
                $expiration = time() + apply_filters('auth_cookie_expiration', 120960000, $user_id, true);
                $cookie     = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

                $response['msg']        = $user_account;
                $response['wp_user_id'] = $user_id;
                $response['cookie']     = $cookie;
                $response['user_login'] = $user_name;
                $response['user']       = $result;
            } else {
                return self::sendError("invalid_login", "Your 'access_token' did not return email of the user. Without 'email' user can't be logged in or registered. Get user email extended permission while joining the Facebook app.", 400);
            }

            return $response;
        }

        public function firebase_sms_login($request)
        {
            $phone         = $request["phone"];
            $referral_code = $request["ref_code"];

            if (!isset($phone)) {
                return self::sendError("invalid_login", "You must include a 'phone' variable.", 400);
            }

            $domain = $_SERVER['SERVER_NAME'];
            if (count(explode(".", $domain)) == 1) {
                $domain = "flutter.io";
            }

            $user_name    = $phone;
            $user_email   = $phone . "@" . $domain;
            $email_exists = email_exists($user_email);
            $user_pass    = wp_generate_password($length = 12, $include_standard_special_chars = false);
            if ($email_exists) {
                $user      = get_user_by('email', $user_email);
                $user_id   = $user->ID;
                $user_name = $user->user_login;
                wp_update_user(array('ID' => $user_id, 'user_pass' => $user_pass));
            }

            $result = "User OTP";

            if (!$user_id && $email_exists == false) {
                $i = 1;

                while (username_exists($user_name)) {
                    $i++;
                    $user_name = strtolower($user_name) . '.' . $i;
                }

                $userdata = array(
                    'user_login'   => $user_name,
                    'user_email'   => $user_email,
                    'user_pass'    => $user_pass,
                    'display_name' => $user_name,
                    // 'first_name' => $user_name,
                    // 'last_name' => ""
                );

                $user_id = wp_insert_user($userdata);
                if ($user_id) {
                    $user_account = 'user registered.';
                }

                update_user_meta($user_id, 'first_name', $user_name);
                update_user_meta($user_id, 'last_name', $phone);
                update_user_meta($user_id, 'nickname', $user_name);

                if (!empty($referral_code)) {
                    $this->insert_signup_referral($referral_code, $user_id);
                }
            } else {
                if ($user_id) {
                    $user_account = 'user logged in.';
                }

                $user = get_userdata($user_id);

                if ($user->first_name != $user_name) {
                    $result = $user->first_name;

                    if ($user->last_name) {
                        $result .= " " . $user->last_name;
                    }
                }
            }

            update_user_meta($user_id, 'billing_phone', $phone);
            // update_user_meta( $user_id, 'billing_email', $phone );

            $expiration = time() + apply_filters('auth_cookie_expiration', 120960000, $user_id, true);
            $cookie     = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

            $response['msg']        = $user_account;
            $response['wp_user_id'] = $user_id;
            $response['cookie']     = $cookie;
            $response['user_login'] = $user_name;
            $response['user']       = $result;
            $response['user_pass']  = $user_pass;

            return $response;
        }

        public function firebase_sms_login_custom_username($request)
        {
            $phone           = $request["phone"];
            $username_custom = $request["username"];
            $referral_code   = $request["ref_code"];

            if (!isset($phone)) {
                return self::sendError("invalid_login", "You must include a 'phone' variable.", 400);
            }

            $domain = $_SERVER['SERVER_NAME'];
            if (count(explode(".", $domain)) == 1) {
                $domain = "flutter.io";
            }

            $user_name    = $phone;
            $user_email   = $phone . "@" . $domain;
            $email_exists = email_exists($user_email);
            $user_pass    = wp_generate_password($length = 12, $include_standard_special_chars = false);

            if ($email_exists) {
                $user      = get_user_by('email', $user_email);
                $user_id   = $user->ID;
                $user_name = $user->user_login;
                wp_update_user(array('ID' => $user_id, 'user_pass' => $user_pass));
            }

            $result = "User OTP";

            if (!$user_id && $email_exists == false) {
                // while (username_exists($user_name)) {
                //     $i++;
                //     $user_name = strtolower($user_name) . '.' . $i;
                // }

                // custom username by user
                if (!$username_custom) {
                    return self::sendError("invalid_login", "create username first", 400);
                } else {
                    if (username_exists($username_custom)) {
                        return self::sendError("invalid_login", "username already exist, please try using another username", 400);
                    } else {
                        $user_name = $username_custom;
                    }
                }
                // end custom username by user -> output $user_name

                $userdata = array(
                    'user_login'   => $user_name,
                    'user_email'   => $user_email,
                    'user_pass'    => $user_pass,
                    'display_name' => $user_name,
                    // 'first_name' => $user_name,
                    // 'last_name' => ""
                );

                $user_id = wp_insert_user($userdata);
                if ($user_id) {
                    $user_account = 'user registered.';
                }

                update_user_meta($user_id, 'first_name', $user_name);
                update_user_meta($user_id, 'last_name', $phone);
                update_user_meta($user_id, 'nickname', $user_name);

                if (!empty($referral_code)) {
                    $this->insert_signup_referral($referral_code, $user_id);
                }
            } else {

                if ($user_id) {
                    $user_account = 'user logged in.';
                }

                $user = get_userdata($user_id);

                if ($user->first_name != $user_name) {
                    $result = $user->first_name;

                    if ($user->last_name) {
                        $result .= " " . $user->last_name;
                    }
                }
            }

            update_user_meta($user_id, 'billing_phone', $phone);
            // update_user_meta( $user_id, 'billing_email', $phone );

            $expiration = time() + apply_filters('auth_cookie_expiration', 120960000, $user_id, true);
            $cookie     = wp_generate_auth_cookie($user_id, $expiration, 'logged_in');

            $response['msg']        = $user_account;
            $response['wp_user_id'] = $user_id;
            $response['cookie']     = $cookie;
            $response['user_login'] = $user_name;
            $response['user']       = $result;
            $response['user_pass']  = $user_pass;

            return $response;
        }
        // end

        public function run()
        {
            $this->register_routes_api();
        }
    }
}

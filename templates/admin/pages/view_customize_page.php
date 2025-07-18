<?php

defined( 'ABSPATH' ) || exit;

$header_design	= [ 'header_v6', 'header_v1', 'header_v2', 'header_v3', 'header_v4', 'header_v5' ];
$hero_banner	= array( 
	'hero_banner_v2'	=> array(
		'redirect'      => 'revo-mobile-banner',
		'redirect_type' => 'banner-full-screen',
	),
	'hero_banner_v1'	=> array(
		'redirect'      => 'revo-mobile-banner',
		'redirect_type' => 'banner-1',
	),
);
$header_values	= get_option( 'revo_shine_customize_homepage_header', [
	'type'  => 'v6',
	'logo'  => '',
	'menus' => []
] );
$hero_banner_value = get_option( 'revo_shine_customize_homepage_hero_banner', 'v2' );

$sections = ( function () {
	global $wpdb;

	$raw_sections = [
		'section_a'             => [
			'type'          => 'banner',
			'redirect'      => 'revo-mobile-banner',
			'redirect_type' => 'banner-1',
			'is_active'     => 0
		],
		'section_b'             => [
			'type'          => 'banner',
			'redirect'      => 'revo-mobile-banner',
			'redirect_type' => 'banner-2',
			'is_active'     => 0
		],
		'section_c'             => [
			'type'          => 'categories',
			'redirect'      => 'revo-mobile-categories',
			'redirect_type' => 'mini',
			'is_active'     => 0
		],
		'section_d'             => [
			'type'          => 'additional-products',
			'redirect'      => 'revo-additional-products',
			'redirect_type' => 'products-our-best-seller',
			'is_active'     => 0
		],
		'section_e'             => [
			'type'          => 'additional-banner',
			'redirect'      => 'revo-mini-banner',
			'redirect_type' => 'love-these-items',
			'is_active'     => 0
		],
		'section_f'             => [
			'type'          => 'additional-products',
			'redirect'      => '',
			'redirect_type' => '',
			'is_active'     => 0
		],
		'section_g'             => [
			'type'          => 'additional-products',
			'redirect'      => 'revo-additional-products',
			'redirect_type' => 'products-special',
			'is_active'     => 0
		],
		'section_h'             => [
			'type'          => 'additional-products',
			'redirect'      => 'revo-additional-products',
			'redirect_type' => 'products-recomendation',
			'is_active'     => 0
		],
		'section_i'             => [
			'type'          => 'categories',
			'redirect'      => 'revo-mobile-categories',
			'redirect_type' => 'big-category',
			'is_active'     => 0
		],
		'section_j'             => [
			'type'          => 'additional-banner',
			'redirect'      => 'revo-mini-banner',
			'redirect_type' => 'single-banner',
			'is_active'     => 0
		],
		'section_k'             => [
			'type'          => 'categories',
			'redirect'      => 'revo-mobile-categories',
			'redirect_type' => 'category-4',
			'is_active'     => 0
		],
		'section_l'             => [
			'type'          => 'categories',
			'redirect'      => 'revo-mobile-categories',
			'redirect_type' => 'category-6',
			'is_active'     => 0
		],
		'section_m'             => [
			'type'          => 'additional-products',
			'redirect'      => 'revo-additional-products',
			'redirect_type' => 'other-products',
			'is_active'     => 0
		],
		'section_n'             => [
			'type'          => 'categories',
			'redirect'      => 'revo-mobile-categories',
			'redirect_type' => 'category-3',
			'is_active'     => 0
		],
		'section_o'             => [
			'type'          => 'banner',
			'redirect'      => 'revo-mobile-banner',
			'redirect_type' => 'banner-3',
			'is_active'     => 0
		],
		'section_p'             => [
			'type'          => 'other-section',
			'redirect'      => 'revo-flash-sale',
			'redirect_type' => '',
			'is_active'     => 0
		],
		'section_q'             => [
			'type'          => 'additional-banner',
			'redirect'      => 'revo-mini-banner',
			'redirect_type' => 'special-promo',
			'is_active'     => 0
		],
		'section_bestdeal'      => [
			'type'          => 'other-section',
			'redirect'      => '',
			'redirect_type' => '',
			'is_active'     => 0
		],
		'section_recently_view' => [
			'type'          => 'other-section',
			'redirect'      => '',
			'redirect_type' => '',
			'is_active'     => 0
		],
		'section_wallet_and_point' => [
			'type'          => 'other-section',
			'redirect'      => '',
			'redirect_type' => '',
			'is_active'     => 0
		],
		'section_categories_two_rows' => [
			'type'          => 'categories',
			'redirect'      => 'revo-mobile-categories',
			'redirect_type' => 'categories-two-rows',
			'is_active'     => 0
		],
		'section_black_friday' => [
			'type'          => 'additional-products',
			'redirect' 		=> 'revo-additional-products',
			'redirect_type'	=> 'festive-promotions',
			'is_active'     => 0
		],
	];

	$existing_section = $wpdb->get_row( "SELECT * FROM revo_mobile_variable WHERE slug = 'customize_homepage'" );
	$existing_section = unserialize( $existing_section->description );

	$sections = [];
	foreach ( $existing_section as $section ) {
		// if ($section !== 'section_bestdeal') {}

		$sections[ $section ] = [
			'type'          => $raw_sections[ $section ]['type'],
			'redirect'      => $raw_sections[ $section ]['redirect'],
			'redirect_type' => $raw_sections[ $section ]['redirect_type'],
			'is_active'     => 1
		];
	}

	foreach ( $raw_sections as $section_key => $section_val ) {
		if ( ! array_key_exists( $section_key, $sections ) ) {
			$sections[ $section_key ] = $section_val;
		}
	}

	return $sections;
} )();

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$action = $_POST['action'];

	if ( $action === 'header_menus' ) {
		$menus = [];

		if ( isset( $_POST['titles'] ) && isset( $_POST['links'] ) ) {
			foreach ( $_POST['titles'] as $key => $title ) {
				$menus[] = [
					'title' => $title ?? '',
					'link'  => $_POST['links'][ $key ] ?? ''
				];
			}
		}

		$header_values['menus'] = $menus;

		update_option( 'revo_shine_customize_homepage_header', $header_values );
		// update_option( 'revo_shine_customize_homepage_hero_banner', $_POST['hero_banner_design'] );

		revo_shine_rebuild_cache( 'revo_home_data' );
	}
}

?>

<div class="admin-section-container" id="customize-page">
    <div class="admin-section-item">
        <div class="admin-section-item-title">Header</div>
        <div class="admin-section-item-body">
            <div class="row">
                <div class="col-7">
                    <div class="d-flex flex-column gap-base header-container" style="max-width: 640px">
                        <div class="d-flex flex-column gap-small">
							<?php foreach ( $header_design as $key => $header ) : ?>
                                <div class="d-flex align-items-start header-item">
                                    <input class="handle-on-change" type="radio" name="header_design"
                                           id="<?php echo $header ?>"
                                           value="<?php echo explode( '_', $header )[1] ?>" <?php echo $header === 'header_' . $header_values['type'] ? 'checked' : '' ?>>
                                    <label class="w-100" for="<?php echo $header ?>">
                                        <img src="<?php echo REVO_SHINE_ASSET_URL . 'images/builder/header/' . $header . '.webp' ?>"
                                             alt="<?php echo $header ?>">
                                    </label>
                                </div>
							<?php endforeach ?>
                        </div>
                        <div class="d-flex flex-column gap-base ps-base additional-header-data-container<?php echo ( $header_values['type'] !== 'v6' && $header_values['type'] !== 'v1' ) ? '' : ' d-none' ?>">
							<?php

							revo_shine_get_admin_template_part( 'components/upload', [
								'id'    => 'header_logo',
								'label' => 'Upload Header Logo Here',
								'name'  => 'header_logo',
								'value' => $header_values['logo'],
							] );

							?>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="form-label mb-0">Hamburger Menu</div>
                                <button class="btn btn-outline-primary fs-12 lh-12 fw-600"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modal-hamburger-menu"
                                        style="height: var(--size-small)">
                                    Manage Menu
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<hr>
	<div class="admin-section-item gap-xlarge">
		<div class="admin-section-item-title">Hero Banner</div>
		<div class="admin-section-item-body">
            <div class="row">
                <div class="col-7">
                    <div class="d-flex flex-column gap-base section-container" style="max-width: 640px">
                        <div class="d-flex flex-column gap-small">
							<?php foreach ( $hero_banner as $key => $banner ) : ?>
                                <div class="d-flex align-items-start header-item mb-5">
                                    <input class="handle-on-change" type="radio" name="hero_banner_design"
                                           id="<?php echo $key ?>"
                                           value="<?php echo explode( '_', $key )[2] ?>" <?php echo $key === 'hero_banner_' . $hero_banner_value ? 'checked' : '' ?> >

                                    <div class="w-100 d-flex justify-content-between align-items-center gap-base">
										<label class="w-100 h-100" for="<?php echo $key ?>">
											<img style="border-radius: 12px;" class="w-100 h-100" src="<?php echo REVO_SHINE_ASSET_URL . 'images/builder/hero_banner/' . $key . '.webp' ?>"
												alt="<?php echo $key ?>">
										</label>

										<div class="d-flex flex-column align-items-center gap-small">
											<a class="btn btn-outline-primary btn-configure" href="<?php echo admin_url( "admin.php?page={$banner['redirect']}&revo_type={$banner['redirect_type']}", 'admin' ); ?>" target="_blank" style="font-size: 12px; font-weight:600">
												Configure
											</a>
										</div>
									</div>
                                </div>
							<?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</div>
    <hr>
    <div class="admin-section-item gap-xlarge">
        <div class="d-flex justify-content-between align-items-center" style="max-width: 640px">
            <div class="admin-section-item-title">Section</div>
            <select class=" selectric filter">
                <option disabled selected>Filter Section</option>
                <option value=".all">All Section</option>
                <option value=".on">Active Section</option>
                <option value=".banner">Banner</option>
                <option value=".additional-banner">Additional Banner</option>
                <option value=".categories">Categories</option>
                <option value=".additional-products">Additional Product Section</option>
                <option value=".other-section">Other Section</option>
            </select>
        </div>
        <div class="admin-section-item-body">
            <div class="section-app-container">
                <div class="row">
                    <div class="col-7">
                        <div class="d-flex flex-column gap-large section-container" id="homePageDesign">
							<?php foreach ( $sections as $section_key => $section_val ) : ?>
								<?php if ( $section_key === 'section_a' ) continue; ?>
                                <div class="d-flex justify-content-between align-items-center section-item<?php echo $section_val['is_active'] ? ' on' : '' ?> <?php echo $section_val['type'] ?>"
                                     data-section="<?php echo $section_key ?>">
                                    <div class="d-flex align-items-center gap-base">
                                        <div class="handle pointer">
                                            <svg width="24" height="25" viewBox="0 0 24 25" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 16.8926C12.1814 16.8923 12.3567 16.9579 12.4935 17.077C12.6303 17.1962 12.7193 17.3609 12.744 17.5406L12.751 17.6426V19.8306L13.471 19.1126C13.598 18.9856 13.7663 18.9083 13.9454 18.8949C14.1245 18.8815 14.3025 18.9329 14.447 19.0396L14.531 19.1126C14.6578 19.2397 14.7349 19.4081 14.7481 19.5872C14.7613 19.7663 14.7098 19.9442 14.603 20.0886L14.531 20.1726L12.531 22.1726L12.493 22.2086L12.421 22.2636L12.326 22.3186L12.239 22.3536L12.137 22.3796L12.052 22.3906H11.949L11.829 22.3726L11.761 22.3526L11.702 22.3306L11.632 22.2956L11.58 22.2636L11.549 22.2416C11.5215 22.22 11.4951 22.1969 11.47 22.1726L9.46998 20.1726C9.33578 20.0388 9.25723 19.8591 9.25011 19.6697C9.24299 19.4803 9.30783 19.2953 9.43159 19.1517C9.55535 19.0082 9.72886 18.9169 9.91723 18.8961C10.1056 18.8752 10.2949 18.9265 10.447 19.0396L10.531 19.1126L11.251 19.8316V17.6416C11.2512 17.4605 11.317 17.2857 11.4361 17.1493C11.5552 17.0129 11.7196 16.9242 11.899 16.8996L12 16.8926ZM12 9.39258C12.7956 9.39258 13.5587 9.70865 14.1213 10.2713C14.6839 10.8339 15 11.5969 15 12.3926C15 13.1882 14.6839 13.9513 14.1213 14.5139C13.5587 15.0765 12.7956 15.3926 12 15.3926C11.2043 15.3926 10.4413 15.0765 9.87866 14.5139C9.31605 13.9513 8.99998 13.1882 8.99998 12.3926C8.99998 11.5969 9.31605 10.8339 9.87866 10.2713C10.4413 9.70865 11.2043 9.39258 12 9.39258ZM18.72 9.86258C18.847 9.7353 19.0156 9.65791 19.1949 9.64451C19.3742 9.63111 19.5524 9.68259 19.697 9.78958L19.781 9.86158L21.781 11.8616L21.817 11.9006L21.872 11.9726L21.927 12.0676L21.962 12.1536L21.988 12.2566L21.998 12.3406V12.4436L21.981 12.5636L21.961 12.6316L21.939 12.6906L21.904 12.7606L21.872 12.8126L21.844 12.8506L21.781 12.9226L19.781 14.9226C19.647 15.0555 19.4678 15.1331 19.2791 15.1397C19.0905 15.1464 18.9062 15.0816 18.7632 14.9584C18.6202 14.8352 18.5289 14.6626 18.5075 14.4751C18.4862 14.2875 18.5363 14.0988 18.648 13.9466L18.72 13.8616L19.439 13.1416H17.25C17.0687 13.1418 16.8935 13.0765 16.7567 12.9575C16.6199 12.8386 16.5309 12.6741 16.506 12.4946L16.501 12.3926C16.501 12.2113 16.5666 12.0362 16.6857 11.8996C16.8049 11.7631 16.9694 11.6742 17.149 11.6496L17.251 11.6426H19.44L18.72 10.9226C18.5931 10.7955 18.5161 10.627 18.5029 10.4479C18.4896 10.2688 18.5411 10.0909 18.648 9.94658L18.72 9.86258ZM4.21998 9.86258C4.35363 9.72804 4.53329 9.64913 4.72279 9.64173C4.91229 9.63434 5.09756 9.699 5.2413 9.82271C5.38503 9.94642 5.47657 10.12 5.49748 10.3085C5.51838 10.497 5.46711 10.6864 5.35398 10.8386L5.28098 10.9226L4.56098 11.6426H6.75098C6.93221 11.6426 7.10732 11.7082 7.24391 11.8273C7.3805 11.9465 7.46933 12.111 7.49398 12.2906L7.49998 12.3926C7.49994 12.574 7.43416 12.7492 7.31483 12.8858C7.19551 13.0224 7.03071 13.1112 6.85098 13.1356L6.74998 13.1426H4.55998L5.27998 13.8626C5.40699 13.9896 5.48422 14.1579 5.49762 14.337C5.51102 14.5161 5.45969 14.6941 5.35298 14.8386L5.27998 14.9226C5.15293 15.0499 4.98438 15.1272 4.80504 15.1406C4.62571 15.154 4.44753 15.1026 4.30298 14.9956L4.21898 14.9226L2.21898 12.9226L2.12898 12.8126L2.07398 12.7176L2.03798 12.6316L2.01198 12.5286L2.00098 12.4386V12.3456L2.01898 12.2206L2.03898 12.1536L2.06098 12.0936L2.09598 12.0236L2.12798 11.9716L2.15098 11.9416C2.17223 11.9137 2.19492 11.887 2.21898 11.8616L4.21998 9.86258ZM12 10.8926C11.6022 10.8926 11.2206 11.0506 10.9393 11.3319C10.658 11.6132 10.5 11.9948 10.5 12.3926C10.5 12.7904 10.658 13.1719 10.9393 13.4532C11.2206 13.7345 11.6022 13.8926 12 13.8926C12.3978 13.8926 12.7793 13.7345 13.0606 13.4532C13.3419 13.1719 13.5 12.7904 13.5 12.3926C13.5 11.9948 13.3419 11.6132 13.0606 11.3319C12.7793 11.0506 12.3978 10.8926 12 10.8926ZM11.864 2.40458L11.931 2.39558L12.018 2.39258L12.078 2.39658L12.172 2.41258L12.24 2.43158L12.299 2.45358L12.369 2.48958L12.421 2.52158L12.459 2.54858L12.531 2.61158L14.531 4.61158C14.6669 4.74502 14.7469 4.92518 14.7549 5.11548C14.7628 5.30578 14.6981 5.49198 14.5737 5.63628C14.4494 5.78058 14.2748 5.87217 14.0855 5.89247C13.8961 5.91277 13.7061 5.86025 13.554 5.74558L13.47 5.67258L12.75 4.95258V7.14258C12.75 7.32382 12.6843 7.49892 12.5652 7.63551C12.4461 7.7721 12.2815 7.86093 12.102 7.88558L12.001 7.89258C11.8196 7.89281 11.6442 7.82729 11.5074 7.70814C11.3706 7.58899 11.2816 7.4243 11.257 7.24458L11.251 7.14258V4.95258L10.531 5.67258C10.4039 5.79986 10.2354 5.87725 10.056 5.89065C9.87671 5.90405 9.69853 5.85256 9.55398 5.74558L9.46998 5.67258C9.34311 5.54546 9.26607 5.37705 9.25285 5.19794C9.23964 5.01883 9.29113 4.84093 9.39798 4.69658L9.46998 4.61158L11.47 2.61158L11.58 2.52158L11.675 2.46658L11.762 2.43158L11.864 2.40458Z"
                                                      fill="#414346"/>
                                            </svg>
                                        </div>
                                        <img src="<?php echo REVO_SHINE_ASSET_URL . "images/builder/{$section_key}.webp" ?>"
                                             alt="<?php echo $section_key ?>">
                                    </div>
                                    <div class="d-flex flex-column align-items-center gap-small">
										<?php

										revo_shine_get_admin_template_part( 'components/switch', [
											'id'         => $section_key,
											'name'       => $section_key,
											'show_label' => false,
											'is_checked' => $section_val['is_active'],
											'value'      => $section_val['is_active'] ? 1 : 0
										] );

										if ( ! empty( $section_val['redirect'] ) ) {
											?>
                                            <a class="btn btn-outline-primary btn-configure"
                                               href="<?php echo admin_url( "admin.php?page={$section_val['redirect']}&revo_type={$section_val['redirect_type']}", 'admin' ); ?>"
                                               target="_blank">
                                                Configure
                                            </a>
											<?php
										}
										?>
                                    </div>
                                </div>
							<?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-5" style="position: relative">
                        <div style="transform: scale(0.6); position: fixed; top: 50px; right: 50px;">
                            <div class="marvel-device iphone-x">
                                <div class="notch">
                                    <div class="camera"></div>
                                    <div class="speaker"></div>
                                </div>
                                <div class="top-bar"></div>
                                <div class="sleep"></div>
                                <div class="bottom-bar"></div>
                                <div class="volume"></div>
                                <div class="overflow">
                                    <div class="shadow shadow--tr"></div>
                                    <div class="shadow shadow--tl"></div>
                                    <div class="shadow shadow--br"></div>
                                    <div class="shadow shadow--bl"></div>
                                </div>
                                <div class="inner-shadow"></div>
                                <div class="screen">
                                    <div class="d-flex justify-content-center align-items-center flex-column"
                                         id="loadingPreview" style="height: 100%; background-color: #f5f5f5;">
                                        <img src="<?php echo REVO_SHINE_ASSET_URL . "icon/loading.svg" ?>" width="130px"
                                             alt="">
                                        <div class="mt-5 pt-4" style="font-size: 28px">Loading Preview</div>
                                    </div>
                                    <div class="d-none" id="parentScreenContent">
                                        <div class="position-relative">
                                            <img src="<?php echo REVO_SHINE_ASSET_URL . "images/builder/hero_banner/hero_banner_v2.webp" ?>"
                                                 alt="section header" width="100%">
                                        </div>
                                        <div id="screenContent"></div>
                                        <div class="position-relative">
                                            <div style="position: fixed; bottom: 25px; left: 0; right: 0; z-index: 0">
                                                <img src="<?php echo REVO_SHINE_ASSET_URL . "images/builder/section_appbar.webp" ?>"
                                                     alt="section appbar" width="100%"
                                                     style="padding-left: 26px; padding-right: 26px; border-bottom-right-radius: 66px; border-bottom-left-radius: 66px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-none justify-content-end align-items-center btn-save-sections" id="save">
        <button type="button" class="btn btn-primary">Save Section</button>
    </div>
    <div id="js-data" data-siteUrl="<?php echo REVO_SHINE_URL; ?>"></div>
</div>

<div class="modal fade" id="modal-hamburger-menu" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Manage Menu</div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="#">
                <div class="modal-body">
                    <div class="d-flex flex-column gap-base menu-item-container">
						<?php foreach ( $header_values['menus'] as $key => $menu ) : ?>
                            <div class="row align-items-end">
                                <div class="col">
									<?php
									revo_shine_get_admin_template_part( 'components/input', [
										'id'    => 'title_' . $key,
										'name'  => 'titles[]',
										'label' => 'Title',
										'value' => $menu['title']
									] );
									?>
                                </div>
                                <div class="col">
									<?php
									revo_shine_get_admin_template_part( 'components/input', [
										'id'    => 'link' . $key,
										'name'  => 'links[]',
										'label' => 'Link',
										'value' => $menu['link']
									] );
									?>
                                </div>
                                <div class="col" style="max-width: 75px">
                                    <button class="btn btn-outline-danger btn-remove-menu-item" type="button"
                                            style="width:40px;height:40px">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
						<?php endforeach ?>
                    </div>

                    <button class="btn btn-outline-primary btn-add-menu-item w-100" type="button">
                        <i class="fas fa-plus"></i>
                        Add Menu
                    </button>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" value="header_menus">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
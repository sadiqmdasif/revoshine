<?php

global $submenus, $user_preferred_theme, $url_query_string;

$url_query_string = $_SERVER['QUERY_STRING'];

function render_menu_item( $tab_url, $tab, $url = '' ): void {

	global $url_query_string;

	$url = ! empty( $url ) ? $url : admin_url( 'admin.php?page=' . $tab_url );

	$is_tab_active = $_GET['page'] === $tab_url;

	if ( isset( $_GET['revo_type'] ) ) {
		$is_tab_active = str_contains( $url, $url_query_string );
	}

	?>

    <a class="admin-sidebar-menu-item<?php echo $is_tab_active ? ' active' : '' ?>"
       href="<?php echo $url ?>"
       data-tab="<?php echo $tab_url ?>">
		<?php

		// sidebar icon
		if ( file_exists( REVO_SHINE_TEMPLATE_PATH . "admin/components/icons/sidebar-{$tab['icon']}.php" ) ) {
			include_once REVO_SHINE_TEMPLATE_PATH . "admin/components/icons/sidebar-{$tab['icon']}.php";
		} else {
			include_once "{$tab['icon']}.php";
		}

		?>

        <span><?php echo $tab['title'] ?></span>
    </a>

	<?php
}

?>

<div class="admin-sidebar">
    <div class="d-flex flex-column">
        <div class="admin-sidebar-heading">
            <img src="<?php echo revo_shine_get_logo() ?>" alt="logo revo" width="64" height="64">
            <p class="text-default mb-0 fs-12 lh-16 fw-normal">Version <?php echo REVO_SHINE_PLUGIN_VERSION ?></p>
        </div>
        <div class="admin-sidebar-menus d-flex flex-column">
			<?php

			$submenus = array_merge( [
				'revo-apps-setting' => [
					'title'  => 'Dashboard',
					'view'   => 'view_dashboard.php',
					'status' => true,
					'icon'   => 'dashboard'
				]
			], $submenus );

			$additional_banner_submenus = apply_filters( 'revo_shine_register_additional_banner_submenus', [
				[
					'type'  => 'blog-banner',
					'title' => 'Blog Banner',
					'icon'  => 'blog-banner'
				],
				[
					'type'  => 'popup-promo',
					'title' => 'Popup Promotional Banner',
					'icon'  => 'popup-promo'
				]
			] );

			foreach ( $submenus as $slug => $menu ) {
				render_menu_item( $slug, $menu );

				if ( $slug === 'revo-mini-banner' ) {
					foreach ( $additional_banner_submenus as $additional_banner_menu ) {
						render_menu_item( $additional_banner_menu['type'], $additional_banner_menu, admin_url( "admin.php?page={$slug}&revo_type={$additional_banner_menu['type']}" ) );
					}
				}
			}

			?>
        </div>
    </div>
</div>
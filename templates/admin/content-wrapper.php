<div class="page-content-wrapper">
	<?php

	if ( file_exists( $this->page ) ) {
		include_once $this->page;
	} else {
		include_once "pages/view_dashboard_page.php";
	}

	include_once "parts/alert.php";

	?>
</div>
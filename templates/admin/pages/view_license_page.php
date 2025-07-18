<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {

	if ( ! is_string( $_POST['type'] ) || ! is_string( $_POST['code'] ) || strlen( $_POST['code'] ) < 19 ) {
		echo "<script>window.location.href='" . admin_url( '/admin.php?page=revo-apps-setting' ) . "';</script>";
		exit;
	}

	$update_at = array( 'update_at' => date( 'Y-m-d H:i:s' ), 'message' => 'coloumn update at is expired_at' );
	$data      = array(
		'title'       => $_POST['type'],
		'description' => $_POST['code'],
		'image'       => json_encode( $update_at ),
	);

	$cek_code = cek_license_code( $data );

	if ( $cek_code->status === 'success' ) {
		$data['description'] = json_encode( $cek_code->data );
		$data['update_at']   = $cek_code->data->expired_at;
		$update_status       = $wpdb->update( 'revo_mobile_variable', $data, [ 'slug' => 'license_code' ] );
		if ( $update_status !== false ) {
			$_SESSION["alert"] = array(
				'type'    => 'success',
				'title'   => 'Success !',
				'message' => 'License Successfully validate',
			);

			wp_redirect( admin_url( '/admin.php?page=revo-apps-setting' ) );
			exit;
		}
		echo "<script>window.location.href='" . admin_url( '/admin.php?page=revo-apps-setting' ) . "';</script>";
	} else {
		$alert = array(
			'type'    => 'error',
			'title'   => $cek_code->message,
			'message' => 'Try again later',
		);
	}

	$_SESSION["alert"] = $alert;
}

?>

<?php if ( isset( $_SESSION ) ) : ?>
    <div class="alert alert-warning">
        <div class="text-capitalize"><?php echo $_SESSION['alert']['message'] ?>.
            <strong><?php echo $_SESSION['alert']['title'] ?></strong></div>
    </div>
<?php endif ?>

<form action="#" method="post">
    <div class="admin-section-container">
        <div class="admin-section-item">
            <div class="admin-section-item-title">License Code</div>
            <div class="admin-section-item-body">
		        <?php

		        revo_shine_get_admin_template_part( 'components/select', [
			        'id'      => 'verifierType',
			        'name'    => 'type',
			        'label'   => 'Select For License',
			        'value'   => 'revo_server', // default value
			        'options' => array(
				        'revo_server' => 'Revo Server',
				        'envato'      => 'Envato'
			        )
		        ] );

		        revo_shine_get_admin_template_part( 'components/input', [
			        'id'          => 'license_code',
			        'name'        => 'code',
			        'label'       => 'Input License Code',
			        'placeholder' => '****-****-****-****'
		        ] );

		        ?>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary btn-save-changes" type="submit">Activate Now</button>
                </div>
            </div>
        </div>
    </div>
    <div class="license-alert-box">
        <div class="title">Can’t find what you need? Send us a help request.</div>
        <a class="btn rwt-btn-outline text-decoration-none" href="https://wa.me/+62811369000" target="_blank"
           role="button">
            Contact Us
        </a>
        <div class="text-weak">
            Or, you can
            <a class="fw-700 text-decoration-none text-primary" href="https://revoapps.net" target="_blank">
                visit our website
            </a>
            for quick questions
        </div>
    </div>
</form>

<script>
    jQuery(document).ready(function ($) {
        function licenseFormat(str, k) {
            str = str.trim().replace(/[^a-zA-Z0-9]/g, "").split("");

            let len = str.length;

            for (let i = len; i > 0; i = i - k) {
                if (i != len) {
                    str[i - 1] = str[i - 1] + "-";
                }
            }

            return str.join("");
        }

        $('#license_code').on('input', function (e) {

            const oldValue = $(this).val();

            $(this).val(licenseFormat(oldValue, 4))
        });
    });
</script>
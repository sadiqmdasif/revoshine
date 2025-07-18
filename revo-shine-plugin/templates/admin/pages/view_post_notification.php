<?php

defined( 'ABSPATH' ) || exit;

function notif() {
	global $wpdb;

	return $wpdb->get_row( "SELECT * FROM `revo_mobile_variable` WHERE slug = 'firebase_notification' limit 1", OBJECT );
}

global $wpdb;

list( $show_notification, $send_notif ) = [ false, false ];

$data_notif = notif();
$fire_key   = get_option( 'revo_shine_fire_key', null );
$dev_mode   = $wpdb->get_row( "SELECT id, slug, description FROM revo_mobile_variable WHERE slug = 'firebase_dev_mode' ORDER BY id DESC LIMIT 1" );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	// update firebase key
	if ( isset( $_FILES['firebase_key'] ) && $_FILES['firebase_key']['error'] == 0 ) {
		$file = $_FILES['firebase_key'];

		if ( $file['type'] === 'application/json' ) {
			$filename = md5( date( 'Y-m-d H:i:s' ) );

			move_uploaded_file( $file['tmp_name'], REVO_SHINE_ABSPATH . "/storage/{$filename}.json" );

			if ( ! is_null( $fire_key ) && file_exists( REVO_SHINE_ABSPATH . "/storage/{$fire_key}.json" ) ) {
				unlink( REVO_SHINE_ABSPATH . "/storage/{$fire_key}.json" );
			}

			$fire_key_file = file_get_contents( REVO_SHINE_ABSPATH . "/storage/{$filename}.json" );
			$fire_key_file = json_decode( $fire_key_file, true );

			update_option( 'revo_shine_fire_project_id', $fire_key_file['project_id'] );
			update_option( 'revo_shine_fire_key', $filename );

			$fire_key = get_option( 'revo_shine_fire_key', null );

			$alert = [
				'type'    => 'success',
				'title'   => 'Success !',
				'message' => 'the action was successful',
			];
		}
	}

	if ( isset( $_POST['type'] ) ) {
		// send notification
		if ( $_POST['type'] == 'firebase_notification' ) {
			$query_data = array(
				'slug'        => $_POST['type'],
				'title'       => json_encode( [ 'title' => $_POST['title'] ] ),
				'image'       => $_POST['image'],
				'description' => json_encode( [
					'description' => str_replace( array(
						"\r",
						"\n"
					), '', $_POST['description'] ),
					'link_to'     => $_POST['link_to']
				] ),
			);

			if ( empty( $data_notif ) ) {
				$insert_status = $wpdb->insert( 'revo_mobile_variable', $query_data );

				if ( $insert_status !== false ) {
					$send_notif = true;
					$alert      = array(
						'type'    => 'success',
						'title'   => 'Success !',
						'message' => 'Notification Successfully Sent',
					);
				}
			} else {
				$where         = [ 'id' => $data_notif->id ];
				$update_status = $wpdb->update( 'revo_mobile_variable', $query_data, $where );

				if ( $update_status !== false ) {
					$send_notif = true;
					$alert      = array(
						'type'    => 'success',
						'title'   => 'Success !',
						'message' => 'Notification Successfully Sent',
					);
				}
			}

			$data_notif = notif();
		}

		// setting dev mode
		if ( $_POST['type'] == 'dev_mode' ) {
			if ( empty( $dev_mode ) ) {
				$wpdb->insert( 'revo_mobile_variable', [
					'slug'        => 'firebase_dev_mode',
					'title'       => 'firebase_dev_mode',
					'description' => json_encode( [
						'status' => $_POST['dev_status'],
						'users'  => $_POST['dev_recipient_id']
					] )
				] );

				$dev_message = 'Dev Mode Insert Successfully';
			} else {
				$dev_data_update = [
					'description' => json_encode( [
						'status' => $_POST['dev_status'],
						'users'  => $_POST['dev_recipient_id']
					] )
				];

				$wpdb->update( 'revo_mobile_variable', $dev_data_update, [ 'id' => $dev_mode->id ] );

				$dev_message = 'Dev Mode Update Successfully';
			}

			$dev_mode = $wpdb->get_row( "SELECT id, slug, description FROM revo_mobile_variable WHERE slug = 'firebase_dev_mode' ORDER BY id DESC LIMIT 1" );

			$alert = [
				'type'    => 'success',
				'title'   => 'Success !',
				'message' => $dev_message
			];
		}
	}

	$_SESSION["alert"] = $alert ?? [];
}

$fire_key_exist = file_exists( REVO_SHINE_ABSPATH . "/storage/{$fire_key}.json" );

if ( $fire_key_exist ) {
	$show_notification = true;
}

if ( isset( $dev_mode ) ) {
	$dev_mode = json_decode( $dev_mode->description );
}

if ( isset( $data_notif->description ) ) {
	$description = json_decode( $data_notif->description );
}

if ( $send_notif ) {
	$wpdb->insert( 'revo_push_notification', [
		'type' => 'push_notif'
	] );

	$lastid_notif = $wpdb->insert_id;

	$notification = [
		'title' => stripslashes( json_decode( $data_notif->title )->title ),
		'body'  => isset( $description->description ) ? stripslashes( $description->description ) : '',
		'image' => isset( $data_notif->image ) ? $data_notif->image : revo_shine_get_logo()
	];

	$extend['id']           = "$lastid_notif";
	$extend['type']         = "all";
	$extend['click_action'] = isset( $description->link_to ) ? $description->link_to : '';

	list( $receivers_id, $receivers_token ) = array( [], [] );
	$get_user_token = get_user_token();

	if ( isset( $dev_mode ) && $dev_mode->status === 'on' ) {
		$dev_users_xplode = explode( ',', $dev_mode->users );
		$get_user_token   = get_user_token( "WHERE user_id IN (" . implode( ',', $dev_users_xplode ) . ")" );
	}

	foreach ( $get_user_token as $value ) {
		if ( ! in_array( $value->user_id, $receivers_id ) ) {
			$receivers_id[]    = $value->user_id;
			$receivers_token[] = $value->token;
		}
	}

	if ( ! empty( $receivers_token ) ) {
		if ( isset( $dev_users_xplode ) ) {
			foreach ( $receivers_token as $token ) {
				$status_send = revo_shine_fcm_api_v1( $notification, $extend, $token, false );
			}
		} else {
			$status_send = revo_shine_fcm_api_v1( $notification, $extend, '', true );
		}

		if ( $status_send === 'error' ) {
			$alert = array(
				'type'    => 'error',
				'title'   => 'Failed to Send Notification !',
				'message' => "Try Again Later",
			);
		}

		$_SESSION["alert"] = $alert;
	}

	$data_description = serialize( [
		'title'       => base64_encode( $_POST['title'] ),
		'link_to'     => $_POST['link_to'],
		'description' => base64_encode( str_replace( array(
			"\r",
			"\n"
		), '', $_POST['description'] ) ),
		'image'       => $notification['image']
	] );

	$date         = new DateTime( 'now', new DateTimeZone( wp_timezone()->getName() ) );
	$receivers_id = json_encode( [ "users" => $receivers_id ] );

	$wpdb->query(
		$wpdb->prepare(
			"UPDATE `revo_push_notification` SET `description` = %s, `user_id` = %s, `created_at` = %s WHERE `id` = %d",
			$data_description,
			$receivers_id,
			$date->format( 'Y-m-d H:i:s' ),
			$lastid_notif
		)
	);
}

?>

<form action="#" method="post">
    <div class="admin-section-container">
        <div class="admin-section-item">
            <div class="d-flex align-items-center justify-content-between">
                <div class="admin-section-item-title text-capitalize">
                    Push Notification
                </div>
                <div class="d-flex align-items-center gap-base">
					<?php if ( $show_notification ) : ?>
                        <button class="btn btn-dev-mode" type="button" data-bs-toggle="modal"
                                data-bs-target="#devModeModal">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.8 11L6.7 9.1C6.88333 8.91667 6.975 8.68333 6.975 8.4C6.975 8.11667 6.88333 7.88333 6.7 7.7C6.51667 7.51667 6.28333 7.425 6 7.425C5.71667 7.425 5.48333 7.51667 5.3 7.7L2.7 10.3C2.5 10.5 2.4 10.7333 2.4 11C2.4 11.2667 2.5 11.5 2.7 11.7L5.3 14.3C5.48333 14.4833 5.71667 14.575 6 14.575C6.28333 14.575 6.51667 14.4833 6.7 14.3C6.88333 14.1167 6.975 13.8833 6.975 13.6C6.975 13.3167 6.88333 13.0833 6.7 12.9L4.8 11ZM19.2 11L17.3 12.9C17.1167 13.0833 17.025 13.3167 17.025 13.6C17.025 13.8833 17.1167 14.1167 17.3 14.3C17.4833 14.4833 17.7167 14.575 18 14.575C18.2833 14.575 18.5167 14.4833 18.7 14.3L21.3 11.7C21.5 11.5 21.6 11.2667 21.6 11C21.6 10.7333 21.5 10.5 21.3 10.3L18.7 7.7C18.5167 7.51667 18.2833 7.425 18 7.425C17.7167 7.425 17.4833 7.51667 17.3 7.7C17.1167 7.88333 17.025 8.11667 17.025 8.4C17.025 8.68333 17.1167 8.91667 17.3 9.1L19.2 11ZM2 6V5C2 4.45 2.196 3.97933 2.588 3.588C2.98 3.19667 3.45067 3.00067 4 3H20C20.55 3 21.021 3.196 21.413 3.588C21.805 3.98 22.0007 4.45067 22 5V6C22 6.28333 21.904 6.521 21.712 6.713C21.52 6.905 21.2827 7.00067 21 7C20.7173 6.99933 20.48 6.90333 20.288 6.712C20.096 6.52067 20 6.28333 20 6V5H4V6C4 6.28333 3.904 6.521 3.712 6.713C3.52 6.905 3.28267 7.00067 3 7C2.71733 6.99933 2.48 6.90333 2.288 6.712C2.096 6.52067 2 6.28333 2 6ZM9 21C8.71667 21 8.47933 20.904 8.288 20.712C8.09667 20.52 8.00067 20.2827 8 20V19H4C3.45 19 2.97933 18.8043 2.588 18.413C2.19667 18.0217 2.00067 17.5507 2 17V16.025C2 15.7417 2.096 15.5083 2.288 15.325C2.48 15.1417 2.71733 15.05 3 15.05C3.28267 15.05 3.52033 15.1457 3.713 15.337C3.90567 15.5283 4.00133 15.766 4 16.05V17H20V16.025C20 15.7417 20.096 15.5083 20.288 15.325C20.48 15.1417 20.7173 15.05 21 15.05C21.2827 15.05 21.5203 15.1457 21.713 15.337C21.9057 15.5283 22.0013 15.766 22 16.05V17C22 17.55 21.8043 18.021 21.413 18.413C21.0217 18.805 20.5507 19.0007 20 19H16V20C16 20.2833 15.904 20.521 15.712 20.713C15.52 20.905 15.2827 21.0007 15 21H9Z"
                                      fill="#5258E4"/>
                            </svg>
                            <span>Dev Mode</span>
                        </button>
					<?php endif; ?>

                    <button class="btn btn-setting-firebase" type="button" data-bs-toggle="modal"
                            data-bs-target="#setFirebaseKey">
                        <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.5 3C1.5 2.44772 1.94772 2 2.5 2H22.5C23.0523 2 23.5 2.44772 23.5 3V9.25C23.5 9.80228 23.0523 10.25 22.5 10.25C21.9477 10.25 21.5 9.80228 21.5 9.25V5C21.5 4.44772 21.0523 4 20.5 4H4.5C3.94772 4 3.5 4.44772 3.5 5V15C3.5 15.5523 3.94772 16 4.5 16H11C11.5523 16 12 16.4477 12 17C12 17.5523 11.5523 18 11 18H2.5C1.94772 18 1.5 17.5523 1.5 17V3ZM3.5 21C3.5 20.4477 3.94772 20 4.5 20H11C11.5523 20 12 20.4477 12 21C12 21.5523 11.5523 22 11 22H4.5C3.94772 22 3.5 21.5523 3.5 21Z"
                                  fill="#414346"/>
                            <path d="M19.8 12C19.9105 12 20 12.0895 20 12.2V13.376C20.715 13.56 21.352 13.936 21.854 14.448L22.8738 13.859C22.9694 13.8038 23.0918 13.8366 23.147 13.9322L23.947 15.3178C24.0022 15.4134 23.9694 15.5358 23.8738 15.591L22.855 16.179C23.0497 16.8797 23.0497 17.6203 22.855 18.321L23.8738 18.909C23.9694 18.9642 24.0022 19.0866 23.947 19.1822L23.147 20.5678C23.0918 20.6634 22.9694 20.6962 22.8738 20.641L21.854 20.052C21.3449 20.5712 20.7039 20.9418 20 21.124V22.3C20 22.4105 19.9105 22.5 19.8 22.5H18.2C18.0895 22.5 18 22.4105 18 22.3V21.124C17.2961 20.9418 16.6551 20.5712 16.146 20.052L15.1262 20.641C15.0306 20.6962 14.9082 20.6634 14.853 20.5678L14.053 19.1822C13.9978 19.0866 14.0306 18.9642 14.1262 18.909L15.145 18.321C14.9503 17.6203 14.9503 16.8797 15.145 16.179L14.1262 15.591C14.0306 15.5358 13.9978 15.4134 14.053 15.3178L14.853 13.9321C14.9083 13.8365 15.0305 13.8037 15.1262 13.8589L16.146 14.447C16.6552 13.9282 17.2962 13.5579 18 13.376V12.2C18 12.0895 18.0895 12 18.2 12H19.8ZM17.249 16.283C17.0852 16.579 16.9992 16.9117 16.999 17.25C16.999 17.6 17.09 17.93 17.249 18.217L17.285 18.28C17.4627 18.5762 17.714 18.8213 18.0146 18.9914C18.3151 19.1616 18.6546 19.251 19 19.251C19.3454 19.251 19.6849 19.1616 19.9854 18.9914C20.286 18.8213 20.5373 18.5762 20.715 18.28L20.751 18.217C20.91 17.93 21 17.601 21 17.25C21 16.9 20.91 16.57 20.751 16.283L20.715 16.22C20.5373 15.9238 20.286 15.6787 19.9854 15.5086C19.6849 15.3384 19.3454 15.249 19 15.249C18.6546 15.249 18.3151 15.3384 18.0146 15.5086C17.714 15.6787 17.4627 15.9238 17.285 16.22L17.249 16.283Z"
                                  fill="#414346"/>
                        </svg>
                        <span>Setting Firebase Key</span>
                    </button>
                </div>
            </div>
            <div class="admin-section-item-body">
				<?php if ( ! $show_notification ) : ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
                        <strong>Cannot Send Notifications ! </strong> Please Input Firebase <strong>SERVER KEY</strong>
                        First
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
				<?php endif; ?>

				<?php

				$title_push_notif = json_decode( $data_notif->title ?? '' );

				revo_shine_get_admin_template_part( 'components/input', [
					'id'          => 'title',
					'label'       => 'Title',
					'name'        => 'title',
					'value'       => stripslashes( $title_push_notif->title ?? '' ),
					'placeholder' => 'Ex: Revo Apps - Native Woocommerce App',
					'is_disabled' => ! $show_notification
				] );

				revo_shine_get_admin_template_part( 'components/input', [
					'id'          => 'link_to',
					'label'       => 'Link To',
					'type'        => 'url',
					'name'        => 'link_to',
					'value'       => $description->link_to ?? '',
					'placeholder' => 'Ex: https://demoonlineshop.revoapps.id/shop/action-cam',
					'is_disabled' => ! $show_notification
				] );

				revo_shine_get_admin_template_part( 'components/textarea', [
					'id'          => 'description',
					'name'        => 'description',
					'label'       => 'Description',
					'placeholder' => 'Ex: Send Unlimited Push Notifications',
					'value'       => stripslashes( $description->description ?? '' ),
					'is_disabled' => ! $show_notification
				] );

				revo_shine_get_admin_template_part( 'components/upload', [
					'id'          => 'image',
					'label'       => 'Image',
					'name'        => 'image',
					'value'       => $data_notif->image ?? '',
					'accent_text' => 'Upload Photo Here',
					'helper_text' => 'Best Size : 100 X 100px, Max File Size : 2MB'
				] );

				?>

                <input type="hidden" name="type" value="firebase_notification" required>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary btn-save-changes send-notif"
                    type="submit"
                    data-dev="<?php echo $dev_mode->status ?? 'off' ?>"
				<?php echo ! $show_notification ? 'disabled' : '' ?>
            >
                Submit
            </button>
        </div>
    </div>
</form>

<div class="modal fade" id="devModeModal" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Dev Mode</div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="#">
                <div class="modal-body">
					<?php

					revo_shine_get_admin_template_part( 'components/select', [
						'id'      => 'dev-status',
						'name'    => 'dev_status',
						'label'   => 'Dev Status',
						'value'   => $dev_mode->status ?? 'off',
						'class'   => 'd-flex align-items-start gap-base',
						'options' => [
							'on'  => 'On',
							'off' => 'Off'
						],
					] );

					revo_shine_get_admin_template_part( 'components/input', [
						'id'           => 'dev-recipient-id',
						'name'         => 'dev_recipient_id',
						'label'        => 'Recipient ID',
						'label_helper' => '<span class="fs-12 fw-400 lh-16 text-danger">*separate with comma</span>',
						'class'        => 'd-flex align-items-start gap-base',
						'placeholder'  => 'Ex: 1,2,3,4',
						'value'        => $dev_mode->users ?? '',
						'is_required'  => true,
					] );

					?>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="type" value="dev_mode">
                    <button type="button" class="btn btn-secondary close" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="setFirebaseKey" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Upload Your Firebase Key</div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="#" enctype="multipart/form-data">
                <div class="modal-body">
					<?php

					$firebase_key_status = $show_notification ? 'text-success' : 'text-danger';
					$firebase_key_text   = $show_notification ? 'Ready' : 'Not Found';

					revo_shine_get_admin_template_part( 'components/input', [
						'id'           => 'firebase_key',
						'name'         => 'firebase_key',
						'type'         => 'file',
						'label'        => 'Firebase Key',
						'label_helper' => "<div class='fs-12 lh-16 fw-400 {$firebase_key_status}'>(File Status: {$firebase_key_text})</div>",
						'class'        => 'd-flex align-items-start gap-base',
						'accept'       => '.json',
						'is_required'  => true
					] );

					?>

                    <hr>
                    <div class="text-default">
                        <div class="fs-14 fw-700 lh-20">Configure your push notification by following these
                            steps:
                        </div>
                        <ol class="ms-0 ps-3 mt-small">
                            <li class="fs-14 lh-20 fw-400">
                                Open Firebase Console, Go to Settings >
                                <a href="https://console.firebase.google.com/project/_/settings/serviceaccounts/adminsdk?authuser=0&_gl=1*101j7hg*_ga*NTQ3ODg3MDYwLjE2NzIxMTIyODM.*_ga_CW55HF8NVT*MTY4ODU0NTY3MS44NC4xLjE2ODg1NDk2NDkuMC4wLjA."
                                   target="_blank">
                                    Service Accounts
                                </a>.
                            </li>
                            <li class="fs-14 lh-20 fw-400">
                                On the <strong>Firebase Admin SDK</strong> tab Generate a new key by pressing
                                <strong>Generate New Private Key</strong> Button.
                            </li>
                            <li class="fs-14 lh-20 fw-400">
                                Please enter the key that you created before in the form above, then press
                                submit button
                                to continue.
                            </li>
                        </ol>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    jQuery('body').on('click', '.send-notif', function (el) {
        const dev_status = jQuery(this).data('dev');

        if (dev_status === 'on') {
            el.preventDefault();
            Swal.fire({
                title: "Dev mode is running",
                text: "This push notification will only be sent to the user you have selected",
                icon: "info",
                showCancelButton: false,
                confirmButtonText: "Confirm",
            }).then((result) => {
                if (result.isConfirmed) {
                    jQuery(this).closest('form').submit();
                }
            });
        }
    });
</script>
<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

$get_status_variable_affiliate = $wpdb->get_row( "SELECT * FROM `revo_mobile_variable` WHERE slug = 'enable_affiliate_video' LIMIT 1" );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    if ( $_POST['action'] === 'video_setting' ) {
        $result = update_option( 'revo_video_setting', $_POST['video_size'] );

        $_SESSION["alert"] = array(
            'type'    => 'success',
            'title'   => 'Success!',
            'message' => 'Video settings successfully updated',
        );
    }

    if ( $_POST['action'] === 'add_video' ) {
        $data_user_raw = get_userdata(wp_get_current_user()->ID);
        $get_name_product = wc_get_product($_POST['id_product']);

        $post_args = array(
            'post_title'    => 'product ' .  $get_name_product->get_slug() . '-' . $data_user_raw->data->user_nicename,
            'post_content'  => 'Product ' .  $get_name_product->get_slug() . '-' . $data_user_raw->data->user_nicename,
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'video',
        );

        $post_id = wp_insert_post($post_args);

        if ($post_id) {
            $insert_result = $wpdb->insert('revo_video_affiliate', [
                'user_id'    => wp_get_current_user()->ID,
                'product_id' => $_POST['id_product'],
                'video_url'  => $_POST['video'],
                'video'      => $_POST['video_id'],
                'link'       => get_permalink($_POST['id_product']),
                'status'     => 1,
                'post_id'    => $post_id,
            ]);

            if (!$insert_result) {
                wp_delete_post($post_id, true);

                $alert = array(
                    'type'    => 'error',
                    'title'   => 'Error!',
                    'message' => 'Failed to add video. Error: ' . $wpdb->last_error,
                );
            } else {
                $alert = array(
                    'type'    => 'success',
                    'title'   => 'Success!',
                    'message' => 'Video has been added',
                );
            }
        } else {
            $alert = array(
                'type'    => 'error',
                'title'   => 'Error!',
                'message' => 'Failed to add video, please try again later',
            );
        }

        $_SESSION["alert"] = $alert;
    }

    if ( $_POST['action'] === 'approve_video' ) {
        $video_id = $_POST['video_id'];
        $status   = $_POST['btn_action'];

        $wpdb->update( 'revo_video_affiliate', [ 'status' => $status ], [ 'id' => $video_id ] );
        $status_message = ( $status == 1 ) ? 'Approved' : 'Rejected';

        $alert = array(
            'type'    => 'success',
            'title'   => 'Success!',
            'message' => "Video has been $status_message"
        );

        $_SESSION["alert"] = $alert;
    }

    if ( $_POST["action"] === 'destroy' ) {
        header( 'Content-type: application/json' );
        $video_id = $_POST['id'];

        if ( ! $get_status_variable_affiliate || $get_status_variable_affiliate->description === 'hide' ) {
            $redirect = home_url() . '/wp-admin/admin.php?page=revo-apps-additional-setting';

            $alert = array(
                'type'    => 'error',
                'title'   => 'Error!',
                'message' => "Video not active, please activate it in <a href=\"$redirect\">App Setting</a>"
            );
        }

        $check_video = $wpdb->get_row( "SELECT * FROM revo_video_affiliate WHERE id = $video_id" );

        if ( ! $check_video ) {
            $alert = array(
                'type'    => 'error',
                'title'   => 'Error!',
                'message' => "Video not found!"
            );
        }

        $wpdb->delete( 'revo_video_affiliate', array( 'id' => $check_video->id ) );

        $wpdb->delete( 'revo_video_affiliate_views', array( 'video_id' => $check_video->id ) );

        $alert = array(
            'type'    => 'success',
            'title'   => 'Success!',
            'message' => 'Successfully delete video!'
        );

        $_SESSION["alert"] = $alert;
    }

    if ( $_SESSION['alert']['type'] === 'success' ) {
        revo_shine_rebuild_cache( 'revo_home_data' );
    }
}

$get_data_video       = $wpdb->get_results( "SELECT * FROM `revo_video_affiliate` ORDER BY id DESC" );
$data_video_affiliate = [];

foreach ( $get_data_video as $data ) {
    $get_view_data = $wpdb->get_results( "SELECT * FROM revo_video_affiliate_views WHERE video_id = $data->id" );
    $viewCount     = "0";
    $clickCount    = "0";
    $sales         = "0";

    if ( $get_view_data ) {
        foreach ( $get_view_data as $data_views ) {
            if ( $data_views->type === "view" ) {
                $viewCount ++;
            } elseif ( $data_views->type === "click" ) {
                $clickCount ++;

                if ( ! empty( $data_views->information ) ) {
                    $information_array = json_decode( $data_views->information, true );

                    if ( ! empty( $information_array['order_id'] ) ) {
                        $order = wc_get_order( $information_array['order_id'] );

                        if ( $order && $order->get_status() === 'completed' ) {
                            $sales += $order->get_total();
                        }
                    }
                }
            }
        }
    }

    $data_video_affiliate[] = [
        'user_data'       => get_userdata( $data->user_id ),
        'product_data'    => get_post( $data->product_id ),
        'video_affiliate' => $data,
        'additional_data' => [
            'views'  => (string) $viewCount,
            'clicks' => (string) $clickCount,
            'sales'  => wc_price( $sales ),
        ]
    ];
}

?>

<div class="admin-section-container">
    <div class="admin-section-item">
        <div class="d-flex align-items-center justify-content-between">
            <div class="admin-section-item-title text-capitalize">
                Video Shopping
            </div>
            <input class="input-search" type="text" id="datatables-search" placeholder="Search..">
        </div>
        <div class="admin-section-item-body">
            <?php if ( ! $get_status_variable_affiliate || $get_status_variable_affiliate->description === 'hide' ) : ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Warning!</strong> You must activate video shopping first before using this feature.
                    <a href="<?php echo admin_url( 'admin.php?page=revo-apps-additional-setting' ) ?>">Enable it now</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif ?>

            <table class="table table-bordered" id="datatables" data-title="Add Video" data-button-modal="true"
                   data-page="video">
                <thead>
                <tr>
                    <th class="text-center">Sort</th>
                    <th class="text-center">Username</th>
                    <th class="text-center">Video</th>
                    <th class="text-center">Link Product</th>
                    <th class="text-center">Upload Date</th>
                    <th class="text-center">Views</th>
                    <th class="text-center">Clicks</th>
                    <th class="text-center">Sales</th>
                    <th class="text-center">Status</th>
                    <th class="text-center hidden-xs">Action</th>
                </tr>
                </thead>
                <tbody>
                <?php $i = 1; ?>
                <?php foreach ( $data_video_affiliate as $item ) : ?>
                    <tr>
                        <td class="align-top text-center"><?php echo $i ++; ?></td>
                        <td class="align-top text-center"><?php echo $item['user_data']->data->user_nicename; ?></td>
                        <td class="align-top text-center">
                            <video width="180px" controls>
                                <source src="<?php echo $item['video_affiliate']->video_url ?>" type="video/mp4">
                                <source src="<?php echo $item['video_affiliate']->video_url ?>"
                                        type="video/x-msvideo">
                                <source src="<?php echo $item['video_affiliate']->video_url ?>"
                                        type="video/x-matroska">
                                <source src="<?php echo $item['video_affiliate']->video_url ?>"
                                        type="video/quicktime">
                            </video>
                        </td>
                        <td class="align-top text-center">
                            <?php echo $item['video_affiliate']->link ? '<a class="text-decoration-none text-default" href="' . $item['video_affiliate']->link . '" target="_blank">' . $item['product_data']->post_title . '</a>' : ''; ?>
                        </td>
                        <td class="align-top text-center"><?php echo date( 'd F Y', strtotime( $item['video_affiliate']->created_at ) ); ?></td>
                        <td class="align-top text-center"><?php echo $item['additional_data']['views'] ?></td>
                        <td class="align-top text-center"><?php echo $item['additional_data']['clicks'] ?></td>
                        <td class="align-top text-center"><?php echo $item['additional_data']['sales'] ?></td>
                        <td class="align-top text-center">
                            <?php $status = $item['video_affiliate']->status === "1" ? "Active" : ( $item['video_affiliate']->status === "0" ? "Inactive" : "Rejected" ); ?>
                            <span class="badge badge-<?php echo $status === "Active" ? "success" : ( $status === "Inactive" ? "secondary" : "danger" ); ?> p-2"><?php echo $status; ?></span>

                        </td>
                        <td class="align-top text-center">
                            <div class="d-flex flex-column align-items-center gap-small">
                                <button class="btn w-100 btn-primary btn-update"
                                        data-id="<?php echo $item['video_affiliate']->id ?>" data-action="update"
                                        data-data="<?php echo base64_encode( json_encode( $item ) ) ?>">
                                    Action
                                </button>
                                <button class="btn w-100 btn-outline-danger btn-destroy"
                                        data-id="<?php echo $item['video_affiliate']->id ?>">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAction" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Add Video</div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="#">
                <div class="modal-body">
                    <?php

                    revo_shine_get_admin_template_part( 'components/upload', [
                        'id'          => 'video',
                        'label'       => 'Select Video',
                        'name'        => 'video',
                        'library'     => 'video',
                        'class'       => 'd-flex align-items-start gap-base',
                        'accent_text' => 'Upload Video Here',
                        'helper_text' => 'Only accept resolutions of (720x1080) or (1080x1920)',
                        'is_required' => true
                    ] );

                    revo_shine_get_admin_template_part( 'components/select', [
                        'id'          => 'id_product',
                        'name'        => 'id_product',
                        'label'       => 'Select Product',
                        'class'       => 'd-flex align-items-start gap-base',
                        'is_select2'  => true,
                        'is_required' => true,
                    ] );

                    ?>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="id">
                    <input type="hidden" name="action" value="add_video">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSetting" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Video Setting</div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="#">
                <div class="modal-body">
                    <?php

                    revo_shine_get_admin_template_part( 'components/radio', [
                        'id'          => 'video_size',
                        'name'        => 'video_size',
                        'label'       => 'Video File Size',
                        'value'       => get_option( 'revo_video_setting', 5 ),
                        'is_required' => true,
                        'class'       => 'd-flex align-items-start gap-base',
                        'options'     => [
                            '5'  => '5 MB',
                            '10' => '10 MB',
                            '25' => '25 MB',
                            '50' => '50 MB'
                        ]
                    ] );

                    ?>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" value="video_setting">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUpdateData" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Approved Video</div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="#">
                <div class="modal-body">
                    <?php

                    revo_shine_get_admin_template_part( 'components/input', [
                        'id'          => 'username',
                        'label'       => 'Username',
                        'name'        => 'username',
                        'class'       => 'd-flex align-items-start gap-base',
                        'is_disabled' => true
                    ] );

                    revo_shine_get_admin_template_part( 'components/input', [
                        'id'          => 'product_title',
                        'label'       => 'Product Name',
                        'name'        => 'product_title',
                        'class'       => 'd-flex align-items-start gap-base',
                        'is_disabled' => true
                    ] );

                    revo_shine_get_admin_template_part( 'components/input', [
                        'id'          => 'status',
                        'label'       => 'Status',
                        'name'        => 'status',
                        'class'       => 'd-flex align-items-start gap-base',
                        'is_disabled' => true
                    ] );

                    revo_shine_get_admin_template_part( 'components/upload', [
                        'id'                   => 'video',
                        'label'                => 'Video',
                        'name'                 => 'video',
                        'library'              => 'video',
                        'class'                => 'd-flex align-items-start gap-base',
                        'accent_text'          => 'Upload Video Here',
                        'value'                => '',
                        'helper_text'          => 'Only accept resolutions of (720x1080) or (1080x1920)',
                        'is_use_action_button' => false
                    ] );

                    ?>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="video_id">
                    <input type="hidden" name="action" value="approve_video">
                    <button class="btn btn-danger" type="submit" name="btn_action" value="2">Reject</button>
                    <button class="btn btn-primary" type="submit" name="btn_action" value="1">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php revo_shine_load_content_wrapper( 'modal_example' ); ?>

<script>
    jQuery(document).ready(function ($) {
        window.onload = function () {
            window.select2Builder('product', '#id_product');
        }

        const bodyEL = jQuery("body");

        jQuery("#btnShowAddModal").click(function () {
            jQuery("#modalAction").modal("show");
        });

        jQuery("#showSettingModal").click(function () {
            jQuery("#modalSetting").modal("show");
        });

        bodyEL.on("click", ".btn-update", function () {
            let _data = atob(jQuery(this).data("data"));
            let data = JSON.parse(_data);
            let statusLabel;

            jQuery("#modalUpdateData .modal-header .modal-title").html("Update Data");

            jQuery('#modalUpdateData input[name="username"]').val(data.user_data.data.user_nicename);
            jQuery('#modalUpdateData input[name="product_title"]').val(data.product_data.post_title);
            jQuery('#modalUpdateData input[name="video_id"]').val(data.video_affiliate.id);
            jQuery('#modalUpdateData input[name="status"]').val(data.video_affiliate.status);

            switch (data.video_affiliate.status) {
                case '0':
                    statusLabel = 'Inactive';
                    break;
                case '1':
                    statusLabel = 'Active';
                    break;
                case '2':
                    statusLabel = 'Reject';
                    break;
                default:
                    statusLabel = '';
            }

            jQuery('#modalUpdateData input[name="status"]').val(statusLabel);

            // video
            jQuery('#modalUpdateData .form-field-upload-container').addClass('file-attached');
            jQuery('#modalUpdateData .form-field-upload-container .form-field-file-preview img').addClass('d-none');
            jQuery('#modalUpdateData .form-field-upload-container .form-field-file-preview video').attr('src', data.video_affiliate.video_url);

            jQuery('#modalUpdateData input[name="id"]').val(data.video_affiliate.id);
            jQuery('#modalUpdateData input[name="action"]').val("approve_video");

            jQuery("#modalUpdateData").modal("show");
        });

        bodyEL.on("hidden.bs.modal", "#modalAction", function () {
            jQuery('#modalAction .form-field-upload-container').removeClass('file-attached');
        });
    });
</script>
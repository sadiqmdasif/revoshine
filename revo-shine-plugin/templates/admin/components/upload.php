<?php

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? [], [
	'id'                   => '',
	'name'                 => '',
	'label'                => 'Upload your image',
	'value'                => '',
	'helper_text'          => '',
	'library'              => 'image',
	'class'                => '',
	'file_upload_id'       => '',
	'file_upload_id_name'  => '',
	'preview_mode'         => '',
	'accent_text'          => 'Upload Photo Here',
	'is_multiple'          => false,
	'is_required'          => false,
	'is_use_action_button' => true
] );

?>

<div class="form-group<?php echo $args['class'] !== '' ? " {$args['class']}" : '' ?>">
    <label class="form-label<?php echo empty( $args['label'] ) ? ' invisible' : '' ?>" for="<?php echo $args['id'] ?>">
		<?php

		echo $args['label'];

		if ( $args['is_required'] ) {
			echo "<sup class='text-danger'>*</sup>";
		}

		?>
    </label>

    <div class="w-100">
        <div class="form-field-upload-container<?php echo $args['value'] !== '' ? ' file-attached' : '' ?>">
            <div class="form-field-upload btn-upload-file<?php echo $args['is_multiple'] ? ' multiple' : '' ?>"
                 data-target="<?php echo $args['name'] ?>"
                 data-multiple="<?php echo $args['is_multiple'] ?>"
                 data-library="<?php echo $args['library'] ?>"
            >
                <div class="form-field-accent">
                    <img src="<?php echo REVO_SHINE_ASSET_URL . '/images/upload-image.png' ?>" width="100"
                         height="100" alt="upload image">
                    <div class="fs-16 fw-700"><?php echo $args['accent_text'] ?></div>
                </div>

                <div class="form-field-file-preview w-100<?php echo $args['is_multiple'] ? ' multiple' : '' ?> <?php echo $args['preview_mode'] ?>">
					<?php if ( $args['is_multiple'] ) : ?>
						<?php

						$images = explode( ',', $args['value'] );

						foreach ( $images as $image ) : ?>
                            <img src="<?php echo $image ?>" alt="multiple preview">
						<?php endforeach;

						?>
					<?php else: ?>

                        <img src="<?php echo $args['value'] ?>" alt="preview">

						<?php if ( str_contains( $args['library'], 'video' ) ) : ?>
                            <video width="400" controls style="width: 100%; height: 120px"></video>
						<?php endif ?>

					<?php endif; ?>
                </div>

                <input type="hidden" name="<?php echo $args['name'] ?>" value="<?php echo $args['value'] ?>">
                <input type="hidden"
                       name="<?php echo $args['file_upload_id_name'] !== '' ? $args['file_upload_id_name'] : ( $args['name'] . '_id' ) ?>"
                       value="<?php echo $args['file_upload_id'] ?>">
            </div>
            <div class="form-field-upload-action<?php echo ! $args['is_use_action_button'] ? ' d-none' : '' ?>">
                <button class="btn rwt-btn-outline btn-upload-file"
                        data-target="<?php echo $args['name'] ?>"
                        data-library="<?php echo $args['library'] ?>"
                        data-multiple="<?php echo $args['is_multiple'] ?>" type="button">
                    Upload
                </button>
                <button class="btn rwt-btn-outline btn-remove-file" type="button">Remove</button>
            </div>
        </div>

		<?php if ( ! empty( $args['helper_text'] ) ) : ?>
            <div class="text-helper"><?php echo $args['helper_text'] ?></div>
		<?php endif; ?>
    </div>
</div>
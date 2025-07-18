<?php

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? [], [
	'id'          => '',
	'name'        => '',
	'label'       => 'Select an option',
	'value'       => '',
	'options'     => [],
	'helper_text' => '',
	'class'       => '',
	'is_required' => false
] );

?>

<div class="form-group<?php echo $args['class'] !== '' ? " {$args['class']}" : '' ?>">
	<?php if ( $args['label'] !== '' ) : ?>
        <label class="form-label" for="<?php echo $args['id'] ?>">
			<?php

			echo $args['label'];

			if ( $args['is_required'] ) {
				echo "<sup class='text-danger'>*</sup>";
			}

			?>
        </label>
	<?php endif; ?>

    <div class="form-radio-container d-flex align-items-center flex-wrap gap-base">
		<?php foreach ( $args['options'] as $key => $label ) : ?>
            <div class="form-check form-radio">
                <input class="form-check-input mt-0"
                       id="<?php echo $args['id'] . '-' . $key ?>"
                       type="radio"
                       name="<?php echo $args['name'] ?>"
                       value="<?php echo $key ?>"
					<?php echo $args['value'] == $key ? 'checked' : '' ?>
                >

                <label class="d-block" style="padding-top: 1px;"
                       for="<?php echo $args['id'] . '-' . $key ?>"><?php echo $label ?></label>
            </div>
		<?php endforeach; ?>
    </div>
</div>


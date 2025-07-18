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
	'toggle'      => '',
	'trigger'     => '',
	'is_select2'  => false,
	'is_multiple' => false,
	'is_required' => false
] );

?>

<div class="form-group<?php echo $args['class'] !== '' ? " {$args['class']}" : '' ?>" style="position: relative">
	<?php if ( $args['label'] !== '' ) : ?>
        <label class="form-label" for="<?php echo $args['id'] ?>">
			<?php

			echo $args['label'];

			if ( $args['is_required'] ) {
				echo "<sup class='text-danger'>*</sup>";
			}

			?>
        </label>
	<?php else: ?>
        <div class="form-label invisible">empty content</div>
	<?php endif; ?>

    <select class="form-select<?php echo $args['is_select2'] ? ' select2' : '' ?>"
            id="<?php echo $args['id'] ?>"
            name="<?php echo $args['name'] ?>"
		<?php echo $args['toggle'] !== '' ? " data-toggle='{$args['toggle']}'" : '' ?>
		<?php echo $args['trigger'] !== '' ? " data-trigger='{$args['trigger']}'" : '' ?>
		<?php echo $args['is_multiple'] ? 'multiple' : '' ?>
		<?php echo $args['is_required'] ? 'required' : '' ?>
    >
		<?php if ( ! $args['is_select2'] ) : ?>
            <option value="" selected disabled>Select an option</option>
		<?php endif; ?>

		<?php foreach ( $args['options'] as $key => $value ) : ?>
            <option value="<?php echo $key ?>" <?php echo $args['value'] == $key ? 'selected' : '' ?>><?php echo $value ?></option>
		<?php endforeach; ?>
    </select>

	<?php if ( ! empty( $args['helper_text'] ) ) : ?>
        <div class="text-helper"><?php echo $args['helper_text'] ?></div>
	<?php endif; ?>
</div>
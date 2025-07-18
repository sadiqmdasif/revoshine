<?php

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? [], [
	'id'          => '',
	'name'        => '',
	'label'       => 'Select an option',
	'placeholder' => '',
	'value'       => '',
	'helper_text' => '',
	'rows'        => '5',
	'class'       => '',
	'is_required' => false,
	'is_disabled' => false,
] );

?>

<div class="form-group<?php echo $args['class'] !== '' ? " {$args['class']}" : '' ?>">
    <label class="form-label" for="<?php echo $args['id'] ?>">
		<?php

		echo $args['label'];

		if ( $args['is_required'] ) {
			echo "<sup class='text-danger'>*</sup>";
		}

		?>
    </label>

    <textarea class="form-control"
              id="<?php echo $args['id'] ?>"
              name="<?php echo $args['name'] ?>"
              rows="<?php echo $args['rows'] ?>"
              placeholder="<?php echo $args['placeholder'] ?>"
              <?php echo $args['is_disabled'] ? 'disabled' : '' ?>
		<?php echo $args['is_required'] ? 'required' : '' ?>><?php echo $args['value'] ?></textarea>

	<?php if ( ! empty( $args['helper_text'] ) ) : ?>
        <div class="text-helper"><?php echo $args['helper_text'] ?></div>
	<?php endif; ?>
</div>
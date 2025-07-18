<?php

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? [], [
	'id'           => '',
	'name'         => '',
	'label'        => 'Select an option',
	'label_helper' => '',
	'type'         => 'text',
	'placeholder'  => '',
	'value'        => '',
	'helper_text'  => '',
	'min'          => '',
	'max'          => '',
	'class'        => '',
	'accept'       => '',
	'is_required'  => false,
	'is_disabled'  => false,
] );

?>

<div class="form-group<?php echo $args['class'] !== '' ? " {$args['class']}" : '' ?>">
    <div class="d-flex flex-column">
        <label class="form-label<?php echo empty( $args['label'] ) ? ' invisible' : '' ?>"
               for="<?php echo $args['id'] ?>">
			<?php

			echo $args['label'];

			if ( $args['is_required'] ) {
				echo "<sup class='text-danger'>*</sup>";
			}

			?>
        </label>

		<?php

		if ( ! empty( $args['label_helper'] ) ) {
			echo $args['label_helper'];
		}

		?>
    </div>

	<?php if ( $args['type'] === 'color' ) :
		echo '<div class="position-relative">';
	endif; ?>

    <input class="form-control"
           id="<?php echo $args['id'] ?>"
           type="<?php echo $args['type'] ?>"
           name="<?php echo $args['name'] ?>"
           placeholder="<?php echo $args['placeholder'] ?>"
           value="<?php echo $args['value'] ?>"
		<?php echo $args['is_required'] ? 'required' : '' ?>
		<?php echo $args['is_disabled'] ? 'disabled' : '' ?>
		<?php echo $args['min'] ? 'min="' . $args['min'] . '"' : '' ?>
		<?php echo $args['max'] ? 'max="' . $args['max'] . '"' : '' ?>
		<?php echo $args['accept'] ? 'accept="' . $args['accept'] . '"' : '' ?>
    >

	<?php if ( $args['type'] === 'color' ) :
		echo '<div class="position-absolute color-preview" style="right: 10px; bottom: 50%; transform: translateY(50%)">' . $args['value'] . '</div>';
		echo '</div>'; // end of position-relative
	endif; ?>

	<?php if ( ! empty( $args['helper_text'] ) ) : ?>
        <div class="text-helper"><?php echo $args['helper_text'] ?></div>
	<?php endif; ?>
</div>
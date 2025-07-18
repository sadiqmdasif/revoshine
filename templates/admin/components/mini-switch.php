<?php

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? [], [
	'id'          => '',
	'name'        => '',
	'label'       => 'Switch to enable',
	'show_label'  => true,
	'is_checked'  => false,
	'value'       => '',
	'input_class' => '',
] );

?>

<div class="d-flex gap-small">
	<?php if ( $args['show_label'] ): ?>
        <label class="form-check-" for="<?php echo "tmp_{$args['name']}" ?>">
			<?php echo $args['label'] ?>
        </label>
	<?php endif; ?>

    <div class="form-check form-switch mb-0">
        <input class="form-check-input mini tmp_checkbox <?php echo $args['input_class'] ?>" <?php echo $args['is_checked'] ? 'checked' : '' ?>
               id="<?php echo "tmp_{$args['name']}" ?>"
               name="<?php echo "tmp_{$args['name']}" ?>"
               data-inputname="<?php echo $args['name'] ?>"
               type="checkbox"
               role="switch">
        <input type="hidden" name="<?php echo $args['name'] ?>" value="<?php echo $args['value'] ?>">
    </div>
</div>
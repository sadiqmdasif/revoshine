<?php

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? [], [
	'id'           		=> '',
	'name'         		=> '',
	'label'        		=> 'Switch to enable',
	'label_helper' 		=> '',
	'show_label'   		=> true,
	'value'        		=> '',
	'switch_class' 		=> '',
	'toggle_on'    		=> '',
	'toggle_off'   		=> '',
	'is_checked'   		=> false,
	'customize_block'	=> false,
	'customize_block_data'	=> '',
] );

?>

<div class="form-group">
    <div class="d-flex justify-content-between align-items-center">
		<?php if ( $args['show_label'] ): ?>
            <div class="d-flex flex-column">
                <label class="form-label" for="<?php echo "tmp_{$args['name']}" ?>"><?php echo $args['label'] ?></label>

				<?php

				if ( ! empty( $args['label_helper'] ) ) {
					echo $args['label_helper'];
				}

				?>

				<?php do_action( 'revo_shine_toggle_switch_header_' . $args['name'] ) ?>
            </div>
		<?php endif ?>

        <div class="form-check form-switch mb-0">
            <input class="form-check-input tmp_checkbox" <?php echo $args['is_checked'] ? 'checked' : '' ?>
                   id="<?php echo "tmp_{$args['name']}" ?>"
                   name="<?php echo "tmp_{$args['name']}" ?>"
                   type="checkbox"
                   role="switch"
                   data-inputname="<?php echo $args['name'] ?>"
				<?php echo $args['toggle_on'] !== '' ? "data-toggle-on=\"{$args['toggle_on']}\"" : '' ?>
				<?php echo $args['toggle_off'] !== '' ? "data-toggle-off=\"{$args['toggle_off']}\"" : '' ?>
            >

            <input type="hidden" name="<?php echo $args['name'] ?>" value="<?php echo $args['value'] ?>">
        </div>
    </div>

	<?php
		if ( ! empty( $args['customize_block'] ) && $args['customize_block'] ) {
			echo $args['customize_block_data'];
		}
	?>
</div>
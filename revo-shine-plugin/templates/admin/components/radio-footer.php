<?php

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? [], [
	'id'          => '',
	'name'        => '',
	'label'       => 'Select an option',
	'value'       => '',
	'options'     => [],
	'helper_text' => '',
	'class'       => ''
] );

?>

<div class="form-group<?php echo $args['class'] !== '' ? " {$args['class']}" : '' ?>">
    <div class="form-label"><?php echo $args['label'] ?></div>

    <div class="d-flex align-items-center gap-base">
		<?php foreach ( $args['options'] as $key => $label ) : ?>
            <label class="radio-footer-item <?php echo $key ?>>" for="<?php echo $key ?>">
                <input class="mt-0"
                       id="<?php echo $key ?>"
                       type="radio"
                       name="footer-menu-layout"
                       value="<?php echo $key ?>"
					<?php echo $args['value'] === $key ? 'checked' : '' ?>
                >
                <div style="margin-top: -2px">
                    <span><?php echo $label ?></span>
                    <div class="mt-xsmall">
                        <img src="<?php echo REVO_SHINE_THEME_URI . "/assets/images/admin/{$key}.png" ?>"
                             alt="<?php echo $key ?>">
                    </div>
                </div>
            </label>
		<?php endforeach ?>
    </div>
</div>


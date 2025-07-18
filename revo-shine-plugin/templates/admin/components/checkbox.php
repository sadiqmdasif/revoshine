<?php

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? [], [
	'id'          => '',
	'name'        => '',
	'label'       => 'Select an option',
	'value'       => [],
	'options'     => [],
	'helper_text' => ''
] );

?>

<div class="form-group">
    <div class="form-label"><?php echo $args['label'] ?></div>

    <div class="form-checkbox-container">
		<?php foreach ( $args['options'] as $key => $label ) : ?>
            <div class="form-check form-checkbox">
                <input class="form-check-input mt-0"
                       id="<?php echo $args['id'] . '-' . $key ?>"
                       type="checkbox"
                       name="<?php echo $args['name'] ?>"
                       value="<?php echo $key ?>"
					<?php echo in_array( $key, $args['value'] ) ? 'checked' : '' ?>
                >

                <label class="d-block" style="padding-top: 1px;"
                       for="<?php echo $args['id'] . '-' . $key ?>"><?php echo $label ?></label>
            </div>
		<?php endforeach; ?>
    </div>
</div>

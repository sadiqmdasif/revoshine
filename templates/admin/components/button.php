<?php

$args = wp_parse_args( $args ?? [], [
	'class'       => '',
	'attributes'  => '',
	'is_outline'  => '',
	'text'        => 'Button',
	'icon'        => '',
	'fontawesome' => '',
	'type'        => 'button',
	'style'       => ''
] );

?>

<button class="btn<?php echo ! empty( $args['class'] ) ? " {$args['class']}" : '' ?>"
        type="<?php echo $args['type'] ?>"
	<?php echo ! empty( $args['style'] ) ? " style='{$args['style']}'" : '' ?>
	<?php echo $args['attributes'] ?>
>
    <div class="d-flex justify-content-center align-items-center gap-2">
		<?php

		if ( ! empty( $args['fontawesome'] ) ) {
			echo '<i class="' . $args['fontawesome'] . '"></i>';
		} elseif ( ! empty( $args['icon'] ) ) {
			echo $args['icon'];
		}

		echo $args['text']

		?>
    </div>
</button>
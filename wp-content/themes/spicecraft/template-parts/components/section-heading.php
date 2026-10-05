<?php
/**
 * SpiceCraft Reusable Component: Section Heading
 *
 * Parameters:
 * - eyebrow (string)
 * - heading (string)
 * - description (string)
 * - alignment ('left'|'center'|'right', default: 'center')
 * - tag ('h2'|'h3', default: 'h2')
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$eyebrow   = ! empty( $args['eyebrow'] ) ? $args['eyebrow'] : '';
$heading   = ! empty( $args['heading'] ) ? $args['heading'] : '';
$desc      = ! empty( $args['description'] ) ? $args['description'] : '';
$alignment = ! empty( $args['alignment'] ) ? $args['alignment'] : 'center';
$tag       = ! empty( $args['tag'] ) && in_array( $args['tag'], array( 'h1', 'h2', 'h3', 'h4' ), true ) ? $args['tag'] : 'h2';

if ( empty( $heading ) && empty( $eyebrow ) ) {
	return;
}
?>
<header class="sc-comp-section-header sc-comp-section-header--<?php echo esc_attr( $alignment ); ?>">
	<?php if ( $eyebrow ) : ?>
		<span class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
	<?php endif; ?>

	<?php if ( $heading ) : ?>
		<<?php echo esc_html( $tag ); ?> class="sc-section-title">
			<?php echo esc_html( $heading ); ?>
		</<?php echo esc_html( $tag ); ?>>
	<?php endif; ?>

	<?php if ( $desc ) : ?>
		<p class="sc-section-subtitle">
			<?php echo esc_html( $desc ); ?>
		</p>
	<?php endif; ?>
</header>

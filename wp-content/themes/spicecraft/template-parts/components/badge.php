<?php
/**
 * Reusable Badge Component
 *
 * Usage:
 * get_template_part( 'template-parts/components/badge', null, array(
 *     'type'  => 'pure|export|organic',
 *     'label' => '100% Pure',
 * ) );
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$type  = isset( $args['type'] ) ? sanitize_html_class( $args['type'] ) : 'pure';
$label = isset( $args['label'] ) ? sanitize_text_field( $args['label'] ) : '';

if ( empty( $label ) ) {
	return;
}
?>
<span class="sc-badge sc-badge--<?php echo esc_attr( $type ); ?>">
	<?php echo esc_html( $label ); ?>
</span>

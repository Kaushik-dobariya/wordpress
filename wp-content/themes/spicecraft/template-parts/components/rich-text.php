<?php
/**
 * SpiceCraft Reusable Component: Rich Text
 *
 * Parameters:
 * - heading (string)
 * - content (string HTML)
 * - cta_text (string)
 * - cta_url (string)
 * - width ('narrow'|'normal'|'wide', default: 'normal')
 * - background ('default'|'subtle'|'surface', default: 'default')
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading  = ! empty( $args['heading'] ) ? $args['heading'] : '';
$content  = ! empty( $args['content'] ) ? $args['content'] : '';
$cta_text = ! empty( $args['cta_text'] ) ? $args['cta_text'] : '';
$cta_url  = ! empty( $args['cta_url'] ) ? $args['cta_url'] : '';
$width    = ! empty( $args['width'] ) ? $args['width'] : 'normal';
$bg       = ! empty( $args['background'] ) ? $args['background'] : 'default';

$container_class = 'sc-container';
if ( 'narrow' === $width ) {
	$container_class .= ' sc-container--narrow';
} elseif ( 'wide' === $width ) {
	$container_class .= ' sc-container--wide';
}
?>
<section class="sc-comp-rich-text sc-comp-bg--<?php echo esc_attr( $bg ); ?>">
	<div class="<?php echo esc_attr( $container_class ); ?>">
		<?php if ( $heading ) : ?>
			<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>

		<?php if ( $content ) : ?>
			<div class="sc-comp-rich-text__body">
				<?php echo wp_kses_post( wpautop( $content ) ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $cta_text && $cta_url ) : ?>
			<div class="sc-comp-rich-text__action" style="margin-top: 1.5rem;">
				<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--primary">
					<?php echo esc_html( $cta_text ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>

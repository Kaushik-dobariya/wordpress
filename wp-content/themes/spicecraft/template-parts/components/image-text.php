<?php
/**
 * SpiceCraft Reusable Component: Image + Text (Two Column Editorial)
 *
 * Parameters:
 * - image_url (string)
 * - image_id (int)
 * - image_alt (string)
 * - eyebrow (string)
 * - heading (string)
 * - text (string)
 * - cta_text (string)
 * - cta_url (string)
 * - image_position ('left'|'right', default: 'left')
 * - background ('default'|'subtle'|'surface', default: 'default')
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$image_url  = ! empty( $args['image_url'] ) ? $args['image_url'] : '';
$image_id   = ! empty( $args['image_id'] ) ? absint( $args['image_id'] ) : 0;
$image_alt  = ! empty( $args['image_alt'] ) ? $args['image_alt'] : '';
$eyebrow    = ! empty( $args['eyebrow'] ) ? $args['eyebrow'] : '';
$heading    = ! empty( $args['heading'] ) ? $args['heading'] : '';
$text       = ! empty( $args['text'] ) ? $args['text'] : '';
$cta_text   = ! empty( $args['cta_text'] ) ? $args['cta_text'] : '';
$cta_url    = ! empty( $args['cta_url'] ) ? $args['cta_url'] : '';
$img_pos    = ! empty( $args['image_position'] ) && 'right' === $args['image_position'] ? 'right' : 'left';
$bg         = ! empty( $args['background'] ) ? $args['background'] : 'default';

if ( $image_id && empty( $image_url ) ) {
	$image_url = wp_get_attachment_image_url( $image_id, 'large' );
	if ( empty( $image_alt ) ) {
		$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	}
}
?>
<section class="sc-comp-image-text sc-comp-image-text--img-<?php echo esc_attr( $img_pos ); ?> sc-comp-bg--<?php echo esc_attr( $bg ); ?>">
	<div class="sc-container">
		<div class="sc-comp-image-text__grid">
			<div class="sc-comp-image-text__media">
				<?php if ( $image_url ) : ?>
					<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ?: $heading ); ?>" class="sc-comp-image-text__img" loading="lazy" />
				<?php else : ?>
					<div class="sc-comp-image-text__placeholder" style="background:var(--sc-color-bg-subtle); height:340px; border-radius:12px; display:flex; align-items:center; justify-content:center; color:var(--sc-color-primary-dark);">
						<span class="dashicons dashicons-format-image" style="font-size:48px; width:48px; height:48px;"></span>
					</div>
				<?php endif; ?>
			</div>

			<div class="sc-comp-image-text__content">
				<?php if ( $eyebrow ) : ?>
					<span class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<?php if ( $heading ) : ?>
					<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( $text ) : ?>
					<div class="sc-comp-image-text__desc">
						<?php echo wp_kses_post( wpautop( $text ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $cta_text && $cta_url ) : ?>
					<div class="sc-comp-image-text__action" style="margin-top: 1.5rem;">
						<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--primary">
							<?php echo esc_html( $cta_text ); ?> &rarr;
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<?php
/**
 * Quality & Sourcing Section: Quality & Origin Gallery
 *
 * Responsive media showcase of spice harvests, inspection lots, and analytical verification.
 * Reuses the native media attachment architecture without external libraries.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gal = function_exists( 'spicecraft_get_quality_section' )
	? spicecraft_get_quality_section( 'gallery' )
	: array();

if ( empty( $gal ) || empty( $gal['attachment_ids'] ) || ! is_array( $gal['attachment_ids'] ) ) {
	return;
}

$eyebrow     = $gal['eyebrow'] ?? '';
$heading     = $gal['heading'] ?? '';
$description = $gal['description'] ?? '';
$att_ids     = array_filter( array_map( 'absint', $gal['attachment_ids'] ) );

if ( empty( $att_ids ) ) {
	return;
}
?>

<section id="sc-quality-gallery" class="sc-quality-gallery sc-section" aria-label="<?php echo esc_attr( $heading ?: __( 'Quality & Origin Gallery', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<?php if ( ! empty( $heading ) || ! empty( $eyebrow ) ) : ?>
			<div class="sc-section-header text-center" style="max-width: 720px; margin: 0 auto var(--sc-space-10, 40px);">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $heading ) ) : ?>
					<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( ! empty( $description ) ) : ?>
					<p class="sc-section-desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="sc-quality-gallery__grid">
			<?php foreach ( $att_ids as $att_id ) :
				$thumb   = wp_get_attachment_image_url( $att_id, 'medium_large' );
				$full    = wp_get_attachment_image_url( $att_id, 'full' );
				$caption = wp_get_attachment_caption( $att_id );
				$alt     = get_post_meta( $att_id, '_wp_attachment_image_alt', true );
				if ( empty( $alt ) ) {
					$alt = $caption ?: get_the_title( $att_id );
				}
				if ( ! $thumb ) continue;
				?>
				<figure class="sc-quality-gallery__item">
					<a href="<?php echo esc_url( $full ); ?>" class="sc-quality-gallery__link" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $alt ); ?>">
						<?php echo wp_get_attachment_image( $att_id, 'medium_large', false, array( 'class' => 'sc-quality-gallery__img', 'loading' => 'lazy', 'alt' => esc_attr( $alt ) ) ); ?>
						<span class="sc-quality-gallery__overlay">
							<span class="dashicons dashicons-search"></span>
						</span>
					</a>
					<?php if ( ! empty( $caption ) ) : ?>
						<figcaption class="sc-quality-gallery__caption"><?php echo esc_html( $caption ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>

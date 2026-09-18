<?php
/**
 * About Section: Hero Banner
 *
 * Distinct editorial brand opening statement, high-priority LCP imagery,
 * and clear navigational conversion triggers.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hero = function_exists( 'spicecraft_get_about_section' )
	? spicecraft_get_about_section( 'hero' )
	: array();

if ( empty( $hero ) ) {
	return;
}

$eyebrow        = $hero['eyebrow'] ?? '';
$heading        = $hero['heading'] ?? '';
$highlight_text = $hero['highlight_text'] ?? '';
$description    = $hero['description'] ?? '';
$primary_label  = $hero['primary_cta_label'] ?? '';
$primary_url    = $hero['primary_cta_url'] ?? '';
$secondary_label= $hero['secondary_cta_label'] ?? '';
$secondary_url  = $hero['secondary_cta_url'] ?? '';
$desktop_img_id = absint( $hero['desktop_image_id'] ?? 0 );
$mobile_img_id  = absint( $hero['mobile_image_id'] ?? 0 );
$img_alt        = ! empty( $hero['image_alt'] ) ? $hero['image_alt'] : get_bloginfo( 'name' ) . ' About Us';

if ( empty( $heading ) && empty( $description ) && empty( $desktop_img_id ) ) {
	return;
}
?>

<section id="about-hero" class="sc-about-hero" aria-label="<?php echo esc_attr( ! empty( $heading ) ? $heading : __( 'About SpiceCraft', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-about-hero__grid">
			<!-- Narrative Column -->
			<div class="sc-about-hero__content">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-about-hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h1 class="sc-about-hero__title">
						<?php echo esc_html( $heading ); ?>
						<?php if ( ! empty( $highlight_text ) ) : ?>
							<span class="sc-about-hero__highlight"><?php echo esc_html( $highlight_text ); ?></span>
						<?php endif; ?>
					</h1>
				<?php endif; ?>

				<?php if ( ! empty( $description ) ) : ?>
					<p class="sc-about-hero__desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
				<?php endif; ?>

				<?php if ( ( ! empty( $primary_label ) && ! empty( $primary_url ) ) || ( ! empty( $secondary_label ) && ! empty( $secondary_url ) ) ) : ?>
					<div class="sc-about-hero__actions">
						<?php if ( ! empty( $primary_label ) && ! empty( $primary_url ) ) : ?>
							<a href="<?php echo esc_url( $primary_url ); ?>" class="sc-btn sc-btn--primary sc-btn--lg">
								<?php echo esc_html( $primary_label ); ?>
							</a>
						<?php endif; ?>

						<?php if ( ! empty( $secondary_label ) && ! empty( $secondary_url ) ) : ?>
							<a href="<?php echo esc_url( $secondary_url ); ?>" class="sc-btn sc-btn--outline sc-btn--lg">
								<?php echo esc_html( $secondary_label ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Imagery Column -->
			<?php if ( ! empty( $desktop_img_id ) ) : ?>
				<div class="sc-about-hero__media-col">
					<div class="sc-about-hero__media-frame">
						<?php
						$img_html = wp_get_attachment_image(
							$desktop_img_id,
							'large',
							false,
							array(
								'class'         => 'sc-about-hero__img',
								'alt'           => esc_attr( $img_alt ),
								'fetchpriority' => 'high',
								'loading'       => 'eager',
							)
						);
						echo $img_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
						<div class="sc-about-hero__media-accent" aria-hidden="true"></div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

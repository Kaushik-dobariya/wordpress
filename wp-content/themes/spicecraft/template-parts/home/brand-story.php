<?php
/**
 * Homepage Template Part: Brand Story & Heritage
 * Semantic ID: #brand-story
 *
 * Consumes structured brand narrative CMS settings.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$story = function_exists( 'spicecraft_get_homepage_section' )
	? spicecraft_get_homepage_section( 'brand_story' )
	: array();

$eyebrow       = ! empty( $story['eyebrow'] ) ? $story['eyebrow'] : '';
$heading       = ! empty( $story['heading'] ) ? $story['heading'] : '';
$description   = ! empty( $story['description'] ) ? $story['description'] : '';
$primary_img   = ! empty( $story['primary_image_id'] ) ? absint( $story['primary_image_id'] ) : 0;
$secondary_img = ! empty( $story['secondary_image_id'] ) ? absint( $story['secondary_image_id'] ) : 0;
$stat_label    = ! empty( $story['stat_label'] ) ? $story['stat_label'] : '';
$stat_value    = ! empty( $story['stat_value'] ) ? $story['stat_value'] : '';
$cta_label     = ! empty( $story['cta_label'] ) ? $story['cta_label'] : '';
$cta_url       = ! empty( $story['cta_url'] ) ? $story['cta_url'] : home_url( '/about/' );

if ( empty( $heading ) && empty( $description ) ) {
	return;
}
?>

<section id="brand-story" class="sc-home-section sc-home-story" aria-labelledby="sec-heading-story">
	<div class="sc-container">
		<div class="sc-story-grid">
			<div class="sc-story-visuals">
				<?php if ( $primary_img ) : ?>
					<div class="sc-story-media sc-story-media--primary">
						<?php
						echo function_exists( 'spicecraft_get_media_image' )
							? spicecraft_get_media_image( $primary_img, 'large', array( 'class' => 'sc-story-img', 'loading' => 'lazy' ) )
							: wp_get_attachment_image( $primary_img, 'large', false, array( 'class' => 'sc-story-img', 'loading' => 'lazy' ) );
						?>
					</div>
				<?php endif; ?>

				<?php if ( $secondary_img ) : ?>
					<div class="sc-story-media sc-story-media--secondary" aria-hidden="true">
						<?php
						echo function_exists( 'spicecraft_get_media_image' )
							? spicecraft_get_media_image( $secondary_img, 'medium', array( 'class' => 'sc-story-img-secondary', 'loading' => 'lazy' ) )
							: wp_get_attachment_image( $secondary_img, 'medium', false, array( 'class' => 'sc-story-img-secondary', 'loading' => 'lazy' ) );
						?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $stat_value ) && ! empty( $stat_label ) ) : ?>
					<div class="sc-story-floating-stat">
						<span class="sc-stat-number"><?php echo esc_html( $stat_value ); ?></span>
						<span class="sc-stat-caption"><?php echo esc_html( $stat_label ); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<div class="sc-story-content">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<p class="sc-eyebrow sc-eyebrow--accent"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 id="sec-heading-story" class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $description ) ) : ?>
					<div class="sc-story-text sc-prose">
						<?php echo wp_kses_post( wpautop( $description ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $cta_label ) ) : ?>
					<div class="sc-story-actions">
						<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--primary sc-btn--md">
							<span><?php echo esc_html( $cta_label ); ?></span>
							<svg class="sc-icon sc-icon-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

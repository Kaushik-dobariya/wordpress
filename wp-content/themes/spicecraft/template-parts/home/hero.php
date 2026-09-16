<?php
/**
 * Homepage Template Part: Hero Banner
 * Semantic ID: #home-hero
 *
 * Consumes structured hero CMS options. Adheres to single H1 accessibility rule
 * and provides responsive image markup with fallback handling.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hero = function_exists( 'spicecraft_get_homepage_section' )
	? spicecraft_get_homepage_section( 'hero' )
	: array();

$eyebrow        = ! empty( $hero['eyebrow'] ) ? $hero['eyebrow'] : '';
$heading        = ! empty( $hero['heading'] ) ? $hero['heading'] : get_bloginfo( 'name' );
$highlight_text = ! empty( $hero['highlight_text'] ) ? $hero['highlight_text'] : '';
$description    = ! empty( $hero['description'] ) ? $hero['description'] : get_bloginfo( 'description' );
$badge_text     = ! empty( $hero['badge_text'] ) ? $hero['badge_text'] : '';
$primary_label  = ! empty( $hero['primary_cta_label'] ) ? $hero['primary_cta_label'] : '';
$primary_url    = ! empty( $hero['primary_cta_url'] ) ? $hero['primary_cta_url'] : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/#catalog' ) );
$secondary_label= ! empty( $hero['secondary_cta_label'] ) ? $hero['secondary_cta_label'] : '';
$secondary_url  = ! empty( $hero['secondary_cta_url'] ) ? $hero['secondary_cta_url'] : home_url( '/#business-enquiry' );
$desktop_img_id = ! empty( $hero['desktop_image_id'] ) ? absint( $hero['desktop_image_id'] ) : 0;
$mobile_img_id  = ! empty( $hero['mobile_image_id'] ) ? absint( $hero['mobile_image_id'] ) : 0;
$image_alt      = ! empty( $hero['image_alt'] ) ? $hero['image_alt'] : get_bloginfo( 'name' );
$bg_treatment   = ! empty( $hero['bg_treatment'] ) ? $hero['bg_treatment'] : 'gradient';

// If heading and description are completely absent, suppress section
if ( empty( $heading ) && empty( $description ) ) {
	return;
}

// Highlight accent text in heading
$formatted_heading = esc_html( $heading );
if ( ! empty( $highlight_text ) && false !== stripos( $heading, $highlight_text ) ) {
	$pos = stripos( $heading, $highlight_text );
	$len = strlen( $highlight_text );
	$match = substr( $heading, $pos, $len );
	$formatted_heading = esc_html( substr( $heading, 0, $pos ) ) .
		'<span class="sc-hero-highlight">' . esc_html( $match ) . '</span>' .
		esc_html( substr( $heading, $pos + $len ) );
}
?>

<section id="home-hero" class="sc-home-section sc-home-hero sc-home-hero--<?php echo esc_attr( $bg_treatment ); ?>" aria-label="<?php esc_attr_e( 'Introduction & Featured Showcase', 'spicecraft' ); ?>">
	<!-- Subtle Botanical Watermark Accent -->
	<div class="sc-hero-bg-accent" aria-hidden="true">
		<svg width="480" height="480" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="sc-hero-watermark">
			<circle cx="100" cy="100" r="90" stroke="currentColor" stroke-width="0.75" stroke-dasharray="3 3"/>
			<circle cx="100" cy="100" r="65" stroke="currentColor" stroke-width="0.5"/>
			<path d="M100 20 C105 60 140 95 180 100 C140 105 105 140 100 180 C95 140 60 105 20 100 C60 95 95 60 100 20 Z" stroke="currentColor" stroke-width="0.75"/>
		</svg>
	</div>

	<div class="sc-container sc-hero-inner">
		<div class="sc-hero-content">
			<?php if ( ! empty( $badge_text ) ) : ?>
				<div class="sc-hero-badge">
					<span class="sc-badge sc-badge--pure">
						<svg class="sc-badge-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
							<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
						</svg>
						<?php echo esc_html( $badge_text ); ?>
					</span>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $eyebrow ) ) : ?>
				<p class="sc-eyebrow sc-hero-eyebrow">
					<?php echo esc_html( $eyebrow ); ?>
				</p>
			<?php endif; ?>

			<h1 class="sc-hero-heading">
				<?php echo wp_kses( $formatted_heading, array( 'span' => array( 'class' => array() ) ) ); ?>
			</h1>

			<?php if ( ! empty( $description ) ) : ?>
				<p class="sc-hero-description">
					<?php echo esc_html( $description ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $primary_label ) || ! empty( $secondary_label ) ) : ?>
				<div class="sc-hero-actions">
					<?php if ( ! empty( $primary_label ) ) : ?>
						<a href="<?php echo esc_url( $primary_url ); ?>" class="sc-btn sc-btn--primary sc-btn--lg">
							<span><?php echo esc_html( $primary_label ); ?></span>
							<svg class="sc-icon sc-icon-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
						</a>
					<?php endif; ?>

					<?php if ( ! empty( $secondary_label ) ) : ?>
						<a href="<?php echo esc_url( $secondary_url ); ?>" class="sc-btn sc-btn--outline sc-btn--lg">
							<span><?php echo esc_html( $secondary_label ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="sc-hero-trust-bar">
				<div class="sc-hero-trust-item">
					<svg class="sc-trust-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
					<span><?php esc_html_e( 'Single Origin Lot Tested', 'spicecraft' ); ?></span>
				</div>
				<div class="sc-hero-trust-item">
					<svg class="sc-trust-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
					<span><?php esc_html_e( 'Cryogenic Cold Milled', 'spicecraft' ); ?></span>
				</div>
				<div class="sc-hero-trust-item">
					<svg class="sc-trust-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
					<span><?php esc_html_e( 'Export Certified Facility', 'spicecraft' ); ?></span>
				</div>
			</div>
		</div>

		<?php if ( $desktop_img_id ) : ?>
			<div class="sc-hero-visual">
				<div class="sc-hero-media-wrapper">
					<div class="sc-hero-media-frame">
						<?php
						$img_attr = array(
							'class'   => 'sc-hero-media',
							'alt'     => $image_alt,
							'loading' => 'eager',
							'sizes'   => '(max-width: 991px) 100vw, 50vw',
						);
						echo function_exists( 'spicecraft_get_media_image' )
							? spicecraft_get_media_image( $desktop_img_id, 'large', $img_attr, $mobile_img_id )
							: wp_get_attachment_image( $desktop_img_id, 'large', false, $img_attr );
						?>
					</div>
					<!-- Subtle floating accent card -->
					<div class="sc-hero-floating-badge" aria-hidden="true">
						<span class="sc-floating-label"><?php esc_html_e( 'Artisanal Purity', 'spicecraft' ); ?></span>
						<span class="sc-floating-sub"><?php esc_html_e( 'Zero Artificial Colors or Fillers', 'spicecraft' ); ?></span>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
/**
 * Homepage Template Part: Manufacturing Plant & Infrastructure
 * Semantic ID: #manufacturing
 *
 * Consumes manufacturing facility CMS data. Clean industrial FMCG layout.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mfg = function_exists( 'spicecraft_get_homepage_section' )
	? spicecraft_get_homepage_section( 'manufacturing' )
	: array();

$eyebrow     = ! empty( $mfg['eyebrow'] ) ? $mfg['eyebrow'] : '';
$heading     = ! empty( $mfg['heading'] ) ? $mfg['heading'] : '';
$description = ! empty( $mfg['description'] ) ? $mfg['description'] : '';
$main_img    = ! empty( $mfg['main_image_id'] ) ? absint( $mfg['main_image_id'] ) : 0;
$support_img = ! empty( $mfg['support_image_id'] ) ? absint( $mfg['support_image_id'] ) : 0;
$video_url   = ! empty( $mfg['video_url'] ) ? esc_url( $mfg['video_url'] ) : '';
$stats       = ! empty( $mfg['stats'] ) && is_array( $mfg['stats'] ) ? $mfg['stats'] : array();
$cta_label   = ! empty( $mfg['cta_label'] ) ? $mfg['cta_label'] : '';
$cta_url     = ! empty( $mfg['cta_url'] ) ? $mfg['cta_url'] : home_url( '/infrastructure/' );

if ( empty( $heading ) && empty( $description ) && empty( $stats ) ) {
	return;
}
?>

<section id="manufacturing" class="sc-home-section sc-home-manufacturing sc-surface-warm" aria-labelledby="sec-heading-mfg">
	<div class="sc-container">
		<header class="sc-section-header sc-section-header--center">
			<?php if ( ! empty( $eyebrow ) ) : ?>
				<p class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $heading ) ) : ?>
				<h2 id="sec-heading-mfg" class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $description ) ) : ?>
				<div class="sc-section-subtitle sc-prose">
					<?php echo wp_kses_post( wpautop( $description ) ); ?>
				</div>
			<?php endif; ?>
		</header>

		<?php if ( ! empty( $stats ) ) : ?>
			<div class="sc-mfg-stats-strip">
				<?php foreach ( $stats as $st ) :
					$st_label = ! empty( $st['label'] ) ? $st['label'] : '';
					$st_value = ! empty( $st['value'] ) ? $st['value'] : '';
					if ( empty( $st_label ) && empty( $st_value ) ) {
						continue;
					}
					?>
					<div class="sc-mfg-stat-item">
						<span class="sc-mfg-stat-value"><?php echo esc_html( $st_value ); ?></span>
						<span class="sc-mfg-stat-label"><?php echo esc_html( $st_label ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $main_img || $support_img || $video_url ) : ?>
			<div class="sc-mfg-stage">
				<?php if ( $main_img ) : ?>
					<div class="sc-mfg-main-frame">
						<?php
						echo function_exists( 'spicecraft_get_media_image' )
							? spicecraft_get_media_image( $main_img, 'large', array( 'class' => 'sc-mfg-stage-img', 'loading' => 'lazy' ) )
							: wp_get_attachment_image( $main_img, 'large', false, array( 'class' => 'sc-mfg-stage-img', 'loading' => 'lazy' ) );
						?>
						<div class="sc-mfg-stage-badge">
							<span class="sc-mfg-badge-dot" aria-hidden="true"></span>
							<span><?php esc_html_e( 'Cryogenic Pulverization Plant', 'spicecraft' ); ?></span>
						</div>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $cta_label ) || ! empty( $video_url ) ) : ?>
			<div class="sc-mfg-actions">
				<?php if ( ! empty( $cta_label ) ) : ?>
					<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--primary sc-btn--md">
						<span><?php echo esc_html( $cta_label ); ?></span>
						<svg class="sc-icon sc-icon-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
					</a>
				<?php endif; ?>

				<?php if ( ! empty( $video_url ) ) : ?>
					<a href="<?php echo esc_url( $video_url ); ?>" target="_blank" rel="noopener noreferrer" class="sc-btn sc-btn--outline sc-btn--md">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
						<span><?php esc_html_e( 'Watch Facility Tour', 'spicecraft' ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

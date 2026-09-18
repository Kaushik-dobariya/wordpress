<?php
/**
 * About Section: Manufacturing Philosophy
 *
 * Concise brand-level introduction to modern processing plant capabilities,
 * hygienic packaging, and cold-milling technology without fabricating operational metrics.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mfg = function_exists( 'spicecraft_get_about_section' )
	? spicecraft_get_about_section( 'manufacturing' )
	: array();

if ( empty( $mfg ) ) {
	return;
}

$eyebrow     = $mfg['eyebrow'] ?? '';
$heading     = $mfg['heading'] ?? '';
$description = $mfg['description'] ?? '';
$image_id    = absint( $mfg['main_image_id'] ?? 0 );
$video_url   = $mfg['video_url'] ?? '';
$highlights  = $mfg['highlights'] ?? array();
$cta_label   = $mfg['cta_label'] ?? '';
$cta_url     = $mfg['cta_url'] ?? '';

if ( empty( $heading ) && empty( $description ) && empty( $highlights ) ) {
	return;
}
?>

<section id="manufacturing-philosophy" class="sc-about-mfg" aria-label="<?php echo esc_attr( ! empty( $heading ) ? $heading : __( 'Manufacturing Philosophy', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-about-mfg__grid <?php echo empty( $image_id ) ? 'sc-about-mfg__grid--no-media' : ''; ?>">
			<!-- Text Content & Highlights -->
			<div class="sc-about-mfg__content">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-section-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 class="sc-about-mfg__heading"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $description ) ) : ?>
					<p class="sc-about-mfg__desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $highlights ) && is_array( $highlights ) ) : ?>
					<div class="sc-about-mfg__highlights">
						<?php
						foreach ( $highlights as $hl ) :
							$hl_title = is_array( $hl ) ? ( $hl['title'] ?? '' ) : (string) $hl;
							$hl_text  = is_array( $hl ) ? ( $hl['text'] ?? '' ) : '';
							if ( empty( $hl_title ) && empty( $hl_text ) ) {
								continue;
							}
							?>
							<div class="sc-about-mfg__hl-card">
								<?php if ( ! empty( $hl_title ) ) : ?>
									<h3 class="sc-about-mfg__hl-title"><?php echo esc_html( $hl_title ); ?></h3>
								<?php endif; ?>
								<?php if ( ! empty( $hl_text ) ) : ?>
									<p class="sc-about-mfg__hl-text"><?php echo esc_html( $hl_text ); ?></p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="sc-about-mfg__actions">
					<?php if ( ! empty( $cta_label ) && ! empty( $cta_url ) ) : ?>
						<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--primary sc-btn--md">
							<?php echo esc_html( $cta_label ); ?>
						</a>
					<?php endif; ?>

					<?php if ( ! empty( $video_url ) ) : ?>
						<a href="<?php echo esc_url( $video_url ); ?>" target="_blank" rel="noopener noreferrer" class="sc-btn sc-btn--outline sc-btn--md sc-about-mfg__video-btn">
							<span class="dashicons dashicons-controls-play" style="margin-top: -2px;"></span>
							<?php esc_html_e( 'Watch Facility Video', 'spicecraft' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<!-- Plant Media -->
			<?php if ( ! empty( $image_id ) ) : ?>
				<div class="sc-about-mfg__media">
					<div class="sc-about-mfg__frame">
						<?php
						echo wp_get_attachment_image(
							$image_id,
							'large',
							false,
							array(
								'class'   => 'sc-about-mfg__img',
								'loading' => 'lazy',
							)
						);
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

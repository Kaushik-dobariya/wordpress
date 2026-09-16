<?php
/**
 * Homepage Template Part: Quality & Sourcing
 * Semantic ID: #quality-sourcing
 *
 * Consumes structured sourcing & quality assurance CMS data.
 * Rendered on a rich dark brand background for visual rhythm.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$qs = function_exists( 'spicecraft_get_homepage_section' )
	? spicecraft_get_homepage_section( 'quality_sourcing' )
	: array();

$eyebrow     = ! empty( $qs['eyebrow'] ) ? $qs['eyebrow'] : '';
$heading     = ! empty( $qs['heading'] ) ? $qs['heading'] : '';
$description = ! empty( $qs['description'] ) ? $qs['description'] : '';
$main_img    = ! empty( $qs['main_image_id'] ) ? absint( $qs['main_image_id'] ) : 0;
$support_img = ! empty( $qs['support_image_id'] ) ? absint( $qs['support_image_id'] ) : 0;
$points      = ! empty( $qs['points'] ) && is_array( $qs['points'] ) ? $qs['points'] : array();
$cta_label   = ! empty( $qs['cta_label'] ) ? $qs['cta_label'] : '';
$cta_url     = ! empty( $qs['cta_url'] ) ? $qs['cta_url'] : home_url( '/quality/' );

if ( empty( $heading ) && empty( $description ) && empty( $points ) ) {
	return;
}
?>

<section id="quality-sourcing" class="sc-home-section sc-home-quality sc-surface-dark" aria-labelledby="sec-heading-quality">
	<div class="sc-container">
		<div class="sc-quality-grid">
			<div class="sc-quality-visuals">
				<?php if ( $main_img ) : ?>
					<div class="sc-quality-media-main">
						<?php
						echo function_exists( 'spicecraft_get_media_image' )
							? spicecraft_get_media_image( $main_img, 'large', array( 'class' => 'sc-quality-img', 'loading' => 'lazy' ) )
							: wp_get_attachment_image( $main_img, 'large', false, array( 'class' => 'sc-quality-img', 'loading' => 'lazy' ) );
						?>
						<div class="sc-quality-badge-overlay">
							<span class="sc-quality-badge-text"><?php esc_html_e( 'Direct Plantation Harvest', 'spicecraft' ); ?></span>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $support_img ) : ?>
					<div class="sc-quality-media-support" aria-hidden="true">
						<?php
						echo function_exists( 'spicecraft_get_media_image' )
							? spicecraft_get_media_image( $support_img, 'medium', array( 'class' => 'sc-quality-img-support', 'loading' => 'lazy' ) )
							: wp_get_attachment_image( $support_img, 'medium', false, array( 'class' => 'sc-quality-img-support', 'loading' => 'lazy' ) );
						?>
					</div>
				<?php endif; ?>
			</div>

			<div class="sc-quality-content">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<p class="sc-eyebrow sc-eyebrow--accent"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 id="sec-heading-quality" class="sc-section-title sc-title--white"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $description ) ) : ?>
					<div class="sc-section-subtitle sc-subtitle--light sc-prose">
						<?php echo wp_kses_post( wpautop( $description ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $points ) ) : ?>
					<div class="sc-quality-checkpoints">
						<?php
						$pt_num = 1;
						foreach ( $points as $pt ) :
							$pt_title = ! empty( $pt['title'] ) ? $pt['title'] : '';
							$pt_text  = ! empty( $pt['text'] ) ? $pt['text'] : '';
							if ( empty( $pt_title ) && empty( $pt_text ) ) {
								continue;
							}
							?>
							<div class="sc-checkpoint-item">
								<div class="sc-checkpoint-marker">
									<span class="sc-checkpoint-number"><?php echo esc_html( $pt_num ); ?></span>
								</div>
								<div class="sc-checkpoint-body">
									<?php if ( ! empty( $pt_title ) ) : ?>
										<h3 class="sc-checkpoint-title"><?php echo esc_html( $pt_title ); ?></h3>
									<?php endif; ?>
									<?php if ( ! empty( $pt_text ) ) : ?>
										<p class="sc-checkpoint-desc"><?php echo esc_html( $pt_text ); ?></p>
									<?php endif; ?>
								</div>
							</div>
							<?php
							$pt_num++;
						endforeach;
						?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $cta_label ) ) : ?>
					<div class="sc-quality-actions">
						<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--outline-white sc-btn--md">
							<span><?php echo esc_html( $cta_label ); ?></span>
							<svg class="sc-icon sc-icon-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

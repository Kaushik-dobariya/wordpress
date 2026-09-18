<?php
/**
 * About Section: Quality Philosophy
 *
 * Concise brand-level presentation of food safety protocols, laboratory testing,
 * and cold grinding standards.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$quality = function_exists( 'spicecraft_get_about_section' )
	? spicecraft_get_about_section( 'quality' )
	: array();

if ( empty( $quality ) ) {
	return;
}

$eyebrow     = $quality['eyebrow'] ?? '';
$heading     = $quality['heading'] ?? '';
$description = $quality['description'] ?? '';
$image_id    = absint( $quality['image_id'] ?? 0 );
$points      = $quality['points'] ?? array();
$cta_label   = $quality['cta_label'] ?? '';
$cta_url     = $quality['cta_url'] ?? '';

if ( empty( $heading ) && empty( $description ) && empty( $points ) ) {
	return;
}
?>

<section id="quality-philosophy" class="sc-about-quality" aria-label="<?php echo esc_attr( ! empty( $heading ) ? $heading : __( 'Quality Philosophy', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-about-quality__grid <?php echo empty( $image_id ) ? 'sc-about-quality__grid--no-media' : ''; ?>">
			<!-- Text & Protocols -->
			<div class="sc-about-quality__content">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-section-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 class="sc-about-quality__heading"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $description ) ) : ?>
					<p class="sc-about-quality__desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $points ) && is_array( $points ) ) : ?>
					<div class="sc-about-quality__protocols">
						<?php
						foreach ( $points as $pt ) :
							$pt_title = is_array( $pt ) ? ( $pt['title'] ?? '' ) : (string) $pt;
							$pt_text  = is_array( $pt ) ? ( $pt['text'] ?? '' ) : '';
							if ( empty( $pt_title ) && empty( $pt_text ) ) {
								continue;
							}
							?>
							<div class="sc-about-quality__point">
								<span class="sc-about-quality__point-bullet" aria-hidden="true">&check;</span>
								<div>
									<?php if ( ! empty( $pt_title ) ) : ?>
										<h3 class="sc-about-quality__point-title"><?php echo esc_html( $pt_title ); ?></h3>
									<?php endif; ?>
									<?php if ( ! empty( $pt_text ) ) : ?>
										<p class="sc-about-quality__point-text"><?php echo esc_html( $pt_text ); ?></p>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $cta_label ) && ! empty( $cta_url ) ) : ?>
					<div class="sc-about-quality__cta">
						<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--secondary sc-btn--md">
							<?php echo esc_html( $cta_label ); ?> &rarr;
						</a>
					</div>
				<?php endif; ?>
			</div>

			<!-- Image Media -->
			<?php if ( ! empty( $image_id ) ) : ?>
				<div class="sc-about-quality__media">
					<div class="sc-about-quality__frame">
						<?php
						echo wp_get_attachment_image(
							$image_id,
							'large',
							false,
							array(
								'class'   => 'sc-about-quality__img',
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

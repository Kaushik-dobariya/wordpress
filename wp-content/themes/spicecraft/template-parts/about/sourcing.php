<?php
/**
 * About Section: Sourcing Philosophy
 *
 * Concise brand-level presentation of farm origins, direct grower partnerships,
 * and single-origin raw spice integrity.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sourcing = function_exists( 'spicecraft_get_about_section' )
	? spicecraft_get_about_section( 'sourcing' )
	: array();

if ( empty( $sourcing ) ) {
	return;
}

$eyebrow       = $sourcing['eyebrow'] ?? '';
$heading       = $sourcing['heading'] ?? '';
$description   = $sourcing['description'] ?? '';
$image_id      = absint( $sourcing['image_id'] ?? 0 );
$support_img_id= absint( $sourcing['support_image_id'] ?? 0 );
$points        = $sourcing['points'] ?? array();
$cta_label     = $sourcing['cta_label'] ?? '';
$cta_url       = $sourcing['cta_url'] ?? '';

if ( empty( $heading ) && empty( $description ) && empty( $points ) ) {
	return;
}
?>

<section id="sourcing-philosophy" class="sc-about-sourcing" aria-label="<?php echo esc_attr( ! empty( $heading ) ? $heading : __( 'Sourcing Philosophy', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-about-sourcing__grid <?php echo empty( $image_id ) ? 'sc-about-sourcing__grid--no-media' : ''; ?>">
			<!-- Imagery Column (Left on desktop for alternating rhythm) -->
			<?php if ( ! empty( $image_id ) ) : ?>
				<div class="sc-about-sourcing__media">
					<div class="sc-about-sourcing__frame">
						<?php
						echo wp_get_attachment_image(
							$image_id,
							'large',
							false,
							array(
								'class'   => 'sc-about-sourcing__img sc-about-sourcing__img--main',
								'loading' => 'lazy',
							)
						);
						?>
						<?php if ( ! empty( $support_img_id ) ) : ?>
							<div class="sc-about-sourcing__inset">
								<?php
								echo wp_get_attachment_image(
									$support_img_id,
									'medium',
									false,
									array(
										'class'   => 'sc-about-sourcing__inset-img',
										'loading' => 'lazy',
									)
								);
								?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Narrative & Sourcing Points -->
			<div class="sc-about-sourcing__content">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-section-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 class="sc-about-sourcing__heading"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $description ) ) : ?>
					<p class="sc-about-sourcing__desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $points ) && is_array( $points ) ) : ?>
					<div class="sc-about-sourcing__points">
						<?php
						foreach ( $points as $pt ) :
							$pt_title = is_array( $pt ) ? ( $pt['title'] ?? '' ) : (string) $pt;
							$pt_text  = is_array( $pt ) ? ( $pt['text'] ?? '' ) : '';
							if ( empty( $pt_title ) && empty( $pt_text ) ) {
								continue;
							}
							?>
							<div class="sc-about-sourcing__point-item">
								<div class="sc-about-sourcing__point-icon">&bull;</div>
								<div>
									<?php if ( ! empty( $pt_title ) ) : ?>
										<h3 class="sc-about-sourcing__point-title"><?php echo esc_html( $pt_title ); ?></h3>
									<?php endif; ?>
									<?php if ( ! empty( $pt_text ) ) : ?>
										<p class="sc-about-sourcing__point-text"><?php echo esc_html( $pt_text ); ?></p>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $cta_label ) && ! empty( $cta_url ) ) : ?>
					<div class="sc-about-sourcing__cta">
						<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--secondary sc-btn--md">
							<?php echo esc_html( $cta_label ); ?> &rarr;
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

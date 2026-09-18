<?php
/**
 * Manufacturing Section: Hero Banner
 *
 * Industrial precision visual opening, single H1 heading, high-priority LCP imagery,
 * and prominent B2B inquiry actions.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hero = function_exists( 'spicecraft_get_manufacturing_section' )
	? spicecraft_get_manufacturing_section( 'hero' )
	: array();

if ( empty( $hero ) || empty( $hero['heading'] ) ) {
	return;
}

$eyebrow        = $hero['eyebrow'] ?? '';
$heading        = $hero['heading'] ?? '';
$highlight_text = $hero['heading_highlight'] ?? '';
$intro          = $hero['intro'] ?? '';
$primary_label  = $hero['cta_primary_label'] ?? '';
$primary_url    = $hero['cta_primary_url'] ?? '';
$secondary_label= $hero['cta_secondary_label'] ?? '';
$secondary_url  = $hero['cta_secondary_url'] ?? '';
$desktop_img_id = absint( $hero['desktop_image_id'] ?? 0 );
$mobile_img_id  = absint( $hero['mobile_image_id'] ?? 0 );
$img_alt        = ! empty( $hero['image_alt'] ) ? $hero['image_alt'] : get_bloginfo( 'name' ) . ' Manufacturing Facility';
?>

<section id="sc-mfg-hero" class="sc-mfg-hero" aria-label="<?php echo esc_attr( $heading ); ?>">
	<div class="sc-container">
		<div class="sc-mfg-hero__grid">
			<!-- Narrative Column -->
			<div class="sc-mfg-hero__content">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-mfg-hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<h1 class="sc-mfg-hero__title">
					<?php echo esc_html( $heading ); ?>
					<?php if ( ! empty( $highlight_text ) ) : ?>
						<span class="sc-mfg-hero__highlight"><?php echo esc_html( $highlight_text ); ?></span>
					<?php endif; ?>
				</h1>

				<?php if ( ! empty( $intro ) ) : ?>
					<p class="sc-mfg-hero__desc"><?php echo nl2br( esc_html( $intro ) ); ?></p>
				<?php endif; ?>

				<?php if ( ( ! empty( $primary_label ) && ! empty( $primary_url ) ) || ( ! empty( $secondary_label ) && ! empty( $secondary_url ) ) ) : ?>
					<div class="sc-mfg-hero__actions">
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

			<!-- Media Column -->
			<div class="sc-mfg-hero__media">
				<?php if ( $desktop_img_id ) : ?>
					<div class="sc-mfg-hero__img-frame">
						<picture>
							<?php if ( $mobile_img_id ) :
								$mob_src = wp_get_attachment_image_url( $mobile_img_id, 'medium_large' );
								if ( $mob_src ) : ?>
									<source media="(max-width: 767px)" srcset="<?php echo esc_url( $mob_src ); ?>">
								<?php endif;
							endif; ?>
							<?php
							echo wp_get_attachment_image(
								$desktop_img_id,
								'large',
								false,
								array(
									'class'         => 'sc-mfg-hero__img',
									'alt'           => esc_attr( $img_alt ),
									'loading'       => 'eager',
									'fetchpriority' => 'high',
									'decoding'      => 'async',
								)
							);
							?>
						</picture>
					</div>
				<?php else : ?>
					<div class="sc-mfg-hero__placeholder-badge">
						<span class="dashicons dashicons-hammer"></span>
						<p><?php esc_html_e( 'Manufacturing Excellence & Processing Integrity', 'spicecraft' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<?php
/**
 * Certifications Section: Archive Hero Banner
 *
 * CMS-driven typography and trust narrative for the central certifications archive.
 * Displays clean typography or optional responsive picture banner without fabricated logos.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = function_exists( 'spicecraft_get_certification_settings' ) ? spicecraft_get_certification_settings() : array();

$eyebrow        = ! empty( $settings['eyebrow'] ) ? $settings['eyebrow'] : ( ! empty( $settings['archive_eyebrow'] ) ? $settings['archive_eyebrow'] : __( 'Verified Quality & Standards', 'spicecraft' ) );
$heading        = ! empty( $settings['heading'] ) ? $settings['heading'] : ( ! empty( $settings['archive_heading'] ) ? $settings['archive_heading'] : __( 'Official Certifications & Accreditations', 'spicecraft' ) );
$intro          = ! empty( $settings['intro'] ) ? $settings['intro'] : ( ! empty( $settings['archive_intro'] ) ? $settings['archive_intro'] : __( 'Every batch of SpiceCraft spices is produced under rigorous global food safety standards. Review our verified regulatory accreditations and facility audit records below.', 'spicecraft' ) );
$desktop_img_id = absint( $settings['desktop_image_id'] ?? ( $settings['archive_hero_image_id'] ?? 0 ) );
$mobile_img_id  = absint( $settings['mobile_image_id'] ?? ( $settings['archive_mobile_image_id'] ?? 0 ) );
$has_image      = $desktop_img_id > 0;
?>

<section class="sc-cert-hero <?php echo $has_image ? 'sc-cert-hero--has-image' : 'sc-cert-hero--typography'; ?>" aria-label="<?php echo esc_attr( $heading ); ?>">
	<div class="sc-container">
		<div class="sc-cert-hero__layout">
			<div class="sc-cert-hero__content">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-cert-hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<h1 class="sc-cert-hero__title"><?php echo esc_html( $heading ); ?></h1>

				<?php if ( ! empty( $intro ) ) : ?>
					<p class="sc-cert-hero__desc"><?php echo nl2br( esc_html( $intro ) ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $has_image ) : ?>
				<div class="sc-cert-hero__media">
					<picture>
						<?php if ( $mobile_img_id ) : ?>
							<source media="(max-width: 767px)" srcset="<?php echo esc_url( wp_get_attachment_image_url( $mobile_img_id, 'large' ) ); ?>">
						<?php endif; ?>
						<?php
						echo wp_get_attachment_image(
							$desktop_img_id,
							'full',
							false,
							array(
								'class'         => 'sc-cert-hero__img',
								'loading'       => 'eager',
								'fetchpriority' => 'high',
								'alt'           => esc_attr( $heading ),
							)
						);
						?>
					</picture>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

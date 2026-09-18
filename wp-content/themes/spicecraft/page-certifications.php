<?php
/**
 * Template Name: Certifications
 *
 * Dedicated public archive and discovery experience for SpiceCraft official certifications.
 * Consumes centralized certification taxonomy data and global certification display settings.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$settings        = function_exists( 'spicecraft_get_certification_settings' ) ? spicecraft_get_certification_settings() : array();
$archive_enabled = ! empty( $settings['archive_enabled'] );

// If archive is disabled globally and visitor cannot manage options, display polite maintenance state
if ( ! $archive_enabled && ! current_user_can( 'manage_options' ) ) :
	?>
	<main id="primary" class="site-main sc-cert-archive-disabled">
		<div class="sc-container" style="padding: var(--sc-space-16, 64px) var(--sc-space-4, 16px); text-align: center;">
			<div class="sc-cert-empty-box" style="max-width: 560px; margin: 0 auto; padding: var(--sc-space-8, 32px); background: #ffffff; border: 1px solid var(--sc-color-border, #e5e5e5); border-radius: 8px;">
				<h1 style="font-size: 1.5rem; margin-bottom: 12px; color: var(--sc-color-text, #1c1917);"><?php esc_html_e( 'Certifications & Compliance', 'spicecraft' ); ?></h1>
				<p style="color: var(--sc-color-text-muted, #78716c); margin: 0; line-height: 1.6;"><?php esc_html_e( 'Certification and regulatory compliance information is currently being updated. Please check back shortly or contact our compliance desk for verified audit documentation.', 'spicecraft' ); ?></p>
			</div>
		</div>
	</main>
	<?php
	get_footer();
	return;
endif;
?>

<main id="primary" class="site-main sc-cert-archive-main">
	<div class="sc-cert-page-wrapper">
		<?php
		// 1. Hero Section
		get_template_part( 'template-parts/certifications/archive-hero' );

		// 2. Filters Section (rendered conditionally if enough records and enabled)
		get_template_part( 'template-parts/certifications/archive-filters' );

		// 3. Grid & Cards (or Neutral Empty State)
		get_template_part( 'template-parts/certifications/archive-grid' );

		// 4. Final Commercial / Compliance CTA
		get_template_part( 'template-parts/certifications/final-cta' );
		?>
	</div>
</main>

<?php
get_footer();

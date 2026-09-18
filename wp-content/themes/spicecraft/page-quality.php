<?php
/**
 * Template Name: Quality & Sourcing
 *
 * Dynamic section orchestrator for the SpiceCraft Quality & Sourcing experience.
 * Consumes structured CMS options from SpiceCraft Core.
 * Reads enabled sections in their configured priority order and delegates
 * rendering to modular template parts in template-parts/quality/.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$active_sections = function_exists( 'spicecraft_get_quality_active_sections' )
	? spicecraft_get_quality_active_sections()
	: array();

// Map section key to template part slug
$section_template_map = array(
	'hero'           => 'hero',
	'introduction'   => 'introduction',
	'principles'     => 'principles',
	'process'        => 'process',
	'testing'        => 'testing',
	'sourcing'       => 'sourcing',
	'regions'        => 'regions',
	'raw_materials'  => 'raw-materials',
	'traceability'   => 'traceability',
	'food_safety'    => 'food-safety',
	'certifications' => 'certifications',
	'statistics'     => 'statistics',
	'gallery'        => 'gallery',
	'products'       => 'products',
	'b2b_cta'        => 'b2b-cta',
	'final_cta'      => 'final-cta',
);

if ( ! empty( $active_sections ) ) {
	echo '<div class="sc-quality-page sc-quality-sections">';
	foreach ( $active_sections as $sec_key ) {
		$template_slug = isset( $section_template_map[ $sec_key ] ) ? $section_template_map[ $sec_key ] : $sec_key;
		get_template_part( 'template-parts/quality/' . $template_slug );
	}
	echo '</div>';
} else {
	// Clean empty state with helpful admin guidance
	?>
	<div class="sc-quality-empty">
		<div class="sc-container" style="padding: var(--sc-space-16, 64px) 0; text-align: center;">
			<div class="sc-card" style="max-width: 600px; margin: 0 auto; padding: var(--sc-space-8, 32px);">
				<h1 style="font-size: 1.75rem; margin-bottom: 12px;"><?php esc_html_e( 'Quality & Sourcing Setup in Progress', 'spicecraft' ); ?></h1>
				<p style="color: var(--sc-color-text-muted); margin-bottom: var(--sc-space-6, 24px);">
					<?php esc_html_e( 'No Quality & Sourcing sections are currently enabled with published data. Configure and enable sections in the WordPress administrator panel.', 'spicecraft' ); ?>
				</p>
				<?php if ( current_user_can( 'manage_options' ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=spicecraft-quality' ) ); ?>" class="sc-btn sc-btn--primary">
						<?php esc_html_e( 'Configure Quality Settings', 'spicecraft' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}

get_footer();

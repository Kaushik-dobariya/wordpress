<?php
/**
 * Template Name: Manufacturing
 *
 * Dynamic section orchestrator for the SpiceCraft Manufacturing experience.
 * Consumes structured CMS options from SpiceCraft Core.
 * Reads enabled sections in their configured priority order and delegates
 * rendering to modular template parts in template-parts/manufacturing/.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$active_sections = function_exists( 'spicecraft_get_manufacturing_active_sections' )
	? spicecraft_get_manufacturing_active_sections()
	: array();

// Map section key to template part slug
$section_template_map = array(
	'hero'           => 'hero',
	'introduction'   => 'introduction',
	'facility'       => 'facility',
	'process'        => 'process',
	'capabilities'   => 'capabilities',
	'equipment'      => 'equipment',
	'hygiene'        => 'hygiene',
	'packaging'      => 'packaging',
	'warehousing'    => 'warehousing',
	'statistics'     => 'statistics',
	'gallery'        => 'gallery',
	'certifications' => 'certifications',
	'products'       => 'products',
	'b2b_cta'        => 'b2b-cta',
	'final_cta'      => 'final-cta',
);

if ( ! empty( $active_sections ) ) {
	echo '<div class="sc-mfg-page sc-mfg-sections">';
	foreach ( $active_sections as $sec_key ) {
		$template_slug = isset( $section_template_map[ $sec_key ] ) ? $section_template_map[ $sec_key ] : $sec_key;
		get_template_part( 'template-parts/manufacturing/' . $template_slug );
	}
	echo '</div>';
} else {
	// Clean empty state with helpful admin guidance
	?>
	<div class="sc-mfg-empty">
		<div class="sc-container" style="padding: var(--sc-space-16, 64px) 0; text-align: center;">
			<div class="sc-card" style="max-width: 600px; margin: 0 auto; padding: var(--sc-space-8, 32px);">
				<h1 style="font-size: 1.75rem; margin-bottom: 12px;"><?php esc_html_e( 'Manufacturing Page Setup in Progress', 'spicecraft' ); ?></h1>
				<p style="color: var(--sc-color-text-muted); margin-bottom: var(--sc-space-6, 24px);">
					<?php esc_html_e( 'No Manufacturing sections are currently enabled with published data. Configure and enable sections in the WordPress administrator panel.', 'spicecraft' ); ?>
				</p>
				<?php if ( current_user_can( 'manage_options' ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=spicecraft-manufacturing' ) ); ?>" class="sc-btn sc-btn--primary">
						<?php esc_html_e( 'Configure Manufacturing Settings', 'spicecraft' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}

get_footer();

<?php
/**
 * The template for displaying the front page
 *
 * Provides the architectural foundation for the SpiceCraft brand experience.
 * Phase 1 Step 1 delivers the structural scaffold; interactive homepage
 * sections and discovery widgets are scheduled for subsequent phases.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$whatsapp_url = function_exists( 'spicecraft_get_whatsapp_enquiry_url' ) ? spicecraft_get_whatsapp_enquiry_url() : '';
?>

<div class="sc-front-page-scaffold">
	<!-- Hero Section Foundation -->
	<section class="sc-hero-scaffold" style="background: linear-gradient(135deg, var(--sc-color-primary-dark) 0%, var(--sc-color-primary) 100%); color: #ffffff; padding: var(--sc-space-16) 0; border-bottom: 4px solid var(--sc-color-accent);">
		<div class="sc-container" style="text-align: center; max-width: 900px;">
			<span class="sc-badge sc-badge--pure" style="margin-bottom: var(--sc-space-4); background-color: rgba(255,255,255,0.15); color: #ffffff; border: 1px solid rgba(255,255,255,0.25);">
				<?php esc_html_e( 'Pure Indian Spice Heritage &bull; Export Grade', 'spicecraft' ); ?>
			</span>
			<h1 style="color: #ffffff; font-size: clamp(2.5rem, 5vw, 4rem); line-height: 1.15; margin-bottom: var(--sc-space-4);">
				<?php bloginfo( 'name' ); ?>
			</h1>
			<p style="font-size: clamp(1.1rem, 2vw, 1.35rem); line-height: 1.6; color: #f3ede2; margin-bottom: var(--sc-space-8);">
				<?php bloginfo( 'description' ); ?>
			</p>

			<div style="display: flex; gap: var(--sc-space-4); justify-content: center; flex-wrap: wrap;">
				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sc-btn sc-btn--secondary sc-btn--lg">
						<?php esc_html_e( 'Explore Spice Catalog', 'spicecraft' ); ?> &rarr;
					</a>
				<?php endif; ?>

				<?php if ( ! empty( $whatsapp_url ) ) : ?>
					<a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" class="sc-btn sc-btn--whatsapp sc-btn--lg">
						<svg class="sc-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
							<path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
						</svg>
						<span><?php esc_html_e( 'Direct WhatsApp Enquiry', 'spicecraft' ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- Architecture Highlights Overview -->
	<section style="padding: var(--sc-space-12) 0;">
		<div class="sc-container">
			<div class="sc-grid sc-grid--3">
				<div class="sc-card" style="padding: var(--sc-space-6);">
					<div style="font-size: 2rem; margin-bottom: var(--sc-space-2);">🌿</div>
					<h3><?php esc_html_e( 'Authentic Sourcing', 'spicecraft' ); ?></h3>
					<p style="color: var(--sc-color-text-muted); font-size: 0.95rem;">
						<?php esc_html_e( 'Origin-verified spices harvested from pristine agro-climatic belts across India, processed to retain essential volatile oils.', 'spicecraft' ); ?>
					</p>
				</div>

				<div class="sc-card" style="padding: var(--sc-space-6);">
					<div style="font-size: 2rem; margin-bottom: var(--sc-space-2);">🔬</div>
					<h3><?php esc_html_e( 'Quality & Standards', 'spicecraft' ); ?></h3>
					<p style="color: var(--sc-color-text-muted); font-size: 0.95rem;">
						<?php esc_html_e( 'Modern processing lines compliant with global food safety standards including FSSAI, ISO 22000, and US FDA requirements.', 'spicecraft' ); ?>
					</p>
				</div>

				<div class="sc-card" style="padding: var(--sc-space-6);">
					<div style="font-size: 2rem; margin-bottom: var(--sc-space-2);">🌍</div>
					<h3><?php esc_html_e( 'Global Trade & Export', 'spicecraft' ); ?></h3>
					<p style="color: var(--sc-color-text-muted); font-size: 0.95rem;">
						<?php esc_html_e( 'Export-grade packaging, bulk supply contracts, customized grind specifications, and institutional food service supply.', 'spicecraft' ); ?>
					</p>
				</div>
			</div>
		</div>
	</section>

	<?php
	// If the front page is set to a static page with content, display it cleanly
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			if ( get_the_content() ) :
				?>
				<section style="padding-bottom: var(--sc-space-12);">
					<div class="sc-container sc-container--narrow sc-card" style="padding: var(--sc-space-8);">
						<?php the_content(); ?>
					</div>
				</section>
				<?php
			endif;
		endwhile;
	endif;
	?>
</div>

<?php
get_footer();

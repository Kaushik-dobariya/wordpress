<?php
/**
 * The template for displaying the Testimonials & Client Reviews archive (/testimonials/).
 *
 * Provides a dedicated showcase of commercial partnerships, chef endorsements,
 * and bulk spice buyer reviews with trust metrics, featured spotlight, and responsive grid.
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

// Query all published testimonials ordered by custom order then date
$testimonials_query = new WP_Query(
	array(
		'post_type'      => 'sc_testimonial',
		'post_status'    => 'publish',
		'posts_per_page' => 12,
		'paged'          => $paged,
		'meta_key'       => '_sc_testimonial_order',
		'orderby'        => array(
			'meta_value_num' => 'ASC',
			'date'           => 'DESC',
		),
	)
);

// Featured testimonials for spotlight (first page only)
$featured_testimonials = array();
if ( 1 === (int) $paged && function_exists( 'spicecraft_get_featured_testimonials' ) ) {
	$featured_testimonials = spicecraft_get_featured_testimonials( 3 );
}
?>

<main id="primary" class="site-main sc-testimonials-archive-page">
	<!-- 1. Hero & Trust Introduction -->
	<header class="sc-testimonials-hero">
		<div class="sc-container">
			<div class="sc-testimonials-hero-content">
				<span class="sc-badge sc-badge--accent"><?php esc_html_e( 'Verified Client Stories', 'spicecraft' ); ?></span>
				<h1 class="sc-testimonials-hero-title"><?php esc_html_e( 'Customer Endorsements & Partner Stories', 'spicecraft' ); ?></h1>
				<p class="sc-testimonials-hero-subtitle">
					<?php esc_html_e( 'Discover how Michelin-starred kitchens, industrial food processors, artisanal bakeries, and global seasoning formulators rely on SpiceCraft for uncompromising purity, batch consistency, and certified origin spices.', 'spicecraft' ); ?>
				</p>
			</div>

			<!-- Trust Metrics Bar -->
			<div class="sc-testimonials-trust-bar" role="region" aria-label="<?php esc_attr_e( 'Purity and Quality Metrics', 'spicecraft' ); ?>">
				<div class="sc-trust-metric-item">
					<div class="sc-trust-metric-val">4.9<span class="sc-trust-metric-sub">/5</span></div>
					<div class="sc-trust-metric-label"><?php esc_html_e( 'Commercial Client Rating', 'spicecraft' ); ?></div>
				</div>
				<div class="sc-trust-metric-divider" aria-hidden="true"></div>
				<div class="sc-trust-metric-item">
					<div class="sc-trust-metric-val">500+</div>
					<div class="sc-trust-metric-label"><?php esc_html_e( 'Commercial Kitchens & Food Brands', 'spicecraft' ); ?></div>
				</div>
				<div class="sc-trust-metric-divider" aria-hidden="true"></div>
				<div class="sc-trust-metric-item">
					<div class="sc-trust-metric-val">99.8%</div>
					<div class="sc-trust-metric-label"><?php esc_html_e( 'Batch Purity & Consistency', 'spicecraft' ); ?></div>
				</div>
				<div class="sc-trust-metric-divider" aria-hidden="true"></div>
				<div class="sc-trust-metric-item">
					<div class="sc-trust-metric-val">100%</div>
					<div class="sc-trust-metric-label"><?php esc_html_e( 'Direct Single-Origin Traceability', 'spicecraft' ); ?></div>
				</div>
			</div>
		</div>
	</header>

	<?php if ( ! empty( $featured_testimonials ) && 1 === (int) $paged ) : ?>
		<!-- 2. Featured Spotlight Section -->
		<section class="sc-testimonials-spotlight-section" aria-labelledby="featured-spotlight-heading">
			<div class="sc-container">
				<div class="sc-section-header sc-section-header--center">
					<span class="sc-badge sc-badge--secondary"><?php esc_html_e( 'Partner Spotlight', 'spicecraft' ); ?></span>
					<h2 id="featured-spotlight-heading" class="sc-section-title"><?php esc_html_e( 'Featured Endorsements', 'spicecraft' ); ?></h2>
					<p class="sc-section-subtitle">
						<?php esc_html_e( 'Key partners who have transformed their culinary creations and supply chain resilience with our freshly milled spices.', 'spicecraft' ); ?>
					</p>
				</div>

				<div class="sc-testimonials-spotlight-grid">
					<?php
					foreach ( $featured_testimonials as $featured_post ) :
						get_template_part(
							'template-parts/content/testimonial-card',
							null,
							array(
								'post_id' => $featured_post->ID,
								'class'   => 'sc-testimonial-card--spotlight',
							)
						);
					endforeach;
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- 3. All Testimonials Grid -->
	<section class="sc-testimonials-listing-section" aria-labelledby="all-testimonials-heading">
		<div class="sc-container">
			<div class="sc-section-header sc-section-header--center">
				<h2 id="all-testimonials-heading" class="sc-section-title"><?php esc_html_e( 'All Client Reviews & Testimonials', 'spicecraft' ); ?></h2>
				<p class="sc-section-subtitle">
					<?php esc_html_e( 'Authentic feedback from head chefs, procurement directors, and seasoning specialists.', 'spicecraft' ); ?>
				</p>
			</div>

			<?php if ( $testimonials_query->have_posts() ) : ?>
				<div class="sc-testimonials-grid">
					<?php
					while ( $testimonials_query->have_posts() ) :
						$testimonials_query->the_post();
						get_template_part(
							'template-parts/content/testimonial-card',
							null,
							array(
								'post_id' => get_the_ID(),
							)
						);
					endwhile;
					wp_reset_postdata();
					?>
				</div>

				<!-- Pagination -->
				<?php if ( $testimonials_query->max_num_pages > 1 ) : ?>
					<nav class="sc-pagination" aria-label="<?php esc_attr_e( 'Testimonials pagination', 'spicecraft' ); ?>">
						<?php
						echo paginate_links(
							array(
								'total'     => $testimonials_query->max_num_pages,
								'current'   => $paged,
								'prev_text' => '&larr; ' . esc_html__( 'Previous', 'spicecraft' ),
								'next_text' => esc_html__( 'Next', 'spicecraft' ) . ' &rarr;',
							)
						);
						?>
					</nav>
				<?php endif; ?>

			<?php else : ?>
				<!-- Empty State -->
				<div class="sc-testimonials-empty-state sc-text-center">
					<div class="sc-empty-icon" aria-hidden="true">
						<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
							<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
						</svg>
					</div>
					<h3 class="sc-empty-title"><?php esc_html_e( 'No Client Stories Published Yet', 'spicecraft' ); ?></h3>
					<p class="sc-empty-desc"><?php esc_html_e( 'Our client testimonials and chef case studies are currently being curated. Please check back soon.', 'spicecraft' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>" class="sc-btn sc-btn--primary">
						<?php esc_html_e( 'Explore Our Spices Catalog', 'spicecraft' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- 4. Bottom B2B Call to Action -->
	<section class="sc-testimonials-cta-section sc-surface-warm" aria-labelledby="cta-heading-testimonials">
		<div class="sc-container">
			<div class="sc-testimonials-cta-card">
				<div class="sc-testimonials-cta-content">
					<span class="sc-badge sc-badge--accent"><?php esc_html_e( 'Partner With SpiceCraft', 'spicecraft' ); ?></span>
					<h2 id="cta-heading-testimonials" class="sc-testimonials-cta-title">
						<?php esc_html_e( 'Elevate Your Kitchen or Product Line with Guaranteed Purity', 'spicecraft' ); ?>
					</h2>
					<p class="sc-testimonials-cta-desc">
						<?php esc_html_e( 'Whether you require custom mesh grinds, bulk whole spices, or private label seasoning blends with full laboratory COA reports, our trade experts are ready to collaborate.', 'spicecraft' ); ?>
					</p>
					<div class="sc-testimonials-cta-actions">
						<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="sc-btn sc-btn--primary sc-btn--lg">
							<?php esc_html_e( 'Request Commercial Sample Kit', 'spicecraft' ); ?>
						</a>
						<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>" class="sc-btn sc-btn--outline sc-btn--lg">
							<?php esc_html_e( 'View Spices Catalog', 'spicecraft' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();

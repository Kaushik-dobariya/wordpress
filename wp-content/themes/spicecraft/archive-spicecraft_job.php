<?php
/**
 * The template for displaying Careers / Job Openings archive (/careers/).
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$settings = function_exists( 'spicecraft_get_careers_settings' ) ? spicecraft_get_careers_settings() : array();
$show_closed = ! empty( $settings['show_closed_jobs'] );

// Handle search/filters via query parameters if provided
$search_kw  = isset( $_GET['job_search'] ) ? sanitize_text_field( wp_unslash( $_GET['job_search'] ) ) : '';
$filter_dep = isset( $_GET['department'] ) ? sanitize_text_field( wp_unslash( $_GET['department'] ) ) : '';
$filter_loc = isset( $_GET['location'] ) ? sanitize_text_field( wp_unslash( $_GET['location'] ) ) : '';
$filter_typ = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';

$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

$args = array(
	'post_type'      => 'spicecraft_job',
	'post_status'    => 'publish',
	'posts_per_page' => 20,
	'paged'          => $paged,
	'orderby'        => 'menu_order date',
	'order'          => 'DESC',
);

if ( ! empty( $search_kw ) ) {
	$args['s'] = $search_kw;
}

if ( ! empty( $filter_dep ) ) {
	$args['tax_query'] = array(
		array(
			'taxonomy' => 'spicecraft_department',
			'field'    => 'slug',
			'terms'    => $filter_dep,
		),
	);
}

$meta_query = array();
if ( ! empty( $filter_loc ) ) {
	$meta_query[] = array(
		'key'     => '_sc_job_location',
		'value'   => $filter_loc,
		'compare' => 'LIKE',
	);
}
if ( ! empty( $filter_typ ) ) {
	$meta_query[] = array(
		'key'     => '_sc_job_type',
		'value'   => $filter_typ,
		'compare' => '=',
	);
}

// If show_closed_jobs is false, only show active positions
if ( ! $show_closed ) {
	$meta_query[] = array(
		'relation' => 'OR',
		array(
			'key'     => '_sc_job_status',
			'compare' => 'NOT EXISTS',
		),
		array(
			'key'     => '_sc_job_status',
			'value'   => 'closed',
			'compare' => '!=',
		),
	);
}

if ( ! empty( $meta_query ) ) {
	$args['meta_query'] = $meta_query;
}

$jobs_query = new WP_Query( $args );
?>

<main id="primary" class="site-main sc-careers-page">
	<!-- 1. Careers Hero Section -->
	<?php get_template_part( 'template-parts/careers/hero' ); ?>

	<!-- 2. Why Join Us / Culture Section -->
	<?php get_template_part( 'template-parts/careers/why-join' ); ?>

	<!-- 3. Open Positions Section -->
	<section id="open-positions" class="sc-careers-positions" aria-labelledby="careers-positions-heading">
		<div class="sc-container">
			<div class="sc-section-header sc-section-header--center">
				<span class="sc-badge sc-badge--accent"><?php esc_html_e( 'Current Opportunities', 'spicecraft' ); ?></span>
				<h2 id="careers-positions-heading" class="sc-section-title">
					<?php esc_html_e( 'Explore Open Positions', 'spicecraft' ); ?>
				</h2>
				<p class="sc-section-subtitle">
					<?php esc_html_e( 'Discover roles tailored to your craftsmanship, food science knowledge, and culinary passion.', 'spicecraft' ); ?>
				</p>
			</div>

			<!-- Search & Filter Controls -->
			<?php get_template_part( 'template-parts/careers/filters' ); ?>

			<!-- Jobs Listing Grid -->
			<div class="sc-jobs-grid" id="sc-jobs-listings" role="feed" aria-busy="false" aria-label="<?php esc_attr_e( 'Job openings listing', 'spicecraft' ); ?>">
				<?php
				if ( $jobs_query->have_posts() ) :
					while ( $jobs_query->have_posts() ) :
						$jobs_query->the_post();
						get_template_part( 'template-parts/careers/job-card' );
					endwhile;
				else :
					get_template_part( 'template-parts/careers/empty-state' );
				endif;
				wp_reset_postdata();
				?>
			</div>

			<!-- Pagination if needed -->
			<?php if ( $jobs_query->max_num_pages > 1 ) : ?>
				<nav class="sc-pagination" aria-label="<?php esc_attr_e( 'Jobs Pagination', 'spicecraft' ); ?>">
					<?php
					echo paginate_links( array(
						'total'     => $jobs_query->max_num_pages,
						'current'   => $paged,
						'prev_text' => '&larr; ' . esc_html__( 'Previous', 'spicecraft' ),
						'next_text' => esc_html__( 'Next', 'spicecraft' ) . ' &rarr;',
					) );
					?>
				</nav>
			<?php endif; ?>
		</div>
	</section>

	<!-- 4. General Application / Talent Pool CTA -->
	<?php get_template_part( 'template-parts/careers/general-cta' ); ?>

	<!-- 5. Final Bottom CTA -->
	<?php get_template_part( 'template-parts/careers/final-cta' ); ?>
</main>

<?php
get_footer();

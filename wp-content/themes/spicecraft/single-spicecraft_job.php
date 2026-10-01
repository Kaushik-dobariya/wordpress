<?php
/**
 * The template for displaying a single Job Opening (/careers/{slug}/).
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$job_id    = get_the_ID();
	$meta      = function_exists( 'spicecraft_get_job_meta' ) ? spicecraft_get_job_meta( $job_id ) : array();
	$is_closed = ! empty( $meta['is_closed'] );

	// Output Schema.org JobPosting structured data ONLY for published, genuine, active jobs
	if ( ! $is_closed && 'publish' === get_post_status() ) {
		$schema_type_map = array(
			'full_time'  => 'FULL_TIME',
			'part_time'  => 'PART_TIME',
			'contract'   => 'CONTRACTOR',
			'internship' => 'INTERN',
			'temporary'  => 'TEMPORARY',
		);
		$schema_type = $schema_type_map[ $meta['employment_type'] ?? 'full_time' ] ?? 'FULL_TIME';

		$company_name = function_exists( 'spicecraft_get_setting' ) ? spicecraft_get_setting( 'company_name', get_bloginfo( 'name' ) ) : get_bloginfo( 'name' );

		$schema_data = array(
			'@context'           => 'https://schema.org',
			'@type'              => 'JobPosting',
			'title'              => get_the_title(),
			'description'        => wp_strip_all_tags( get_the_content() ),
			'datePosted'         => get_the_date( 'c' ),
			'employmentType'     => $schema_type,
			'hiringOrganization'=> array(
				'@type' => 'Organization',
				'name'  => $company_name,
				'sameAs'=> home_url( '/' ),
			),
			'jobLocation'        => array(
				'@type'   => 'Place',
				'address' => array(
					'@type'          => 'PostalAddress',
					'addressLocality'=> ! empty( $meta['location'] ) ? $meta['location'] : 'Ahmedabad',
					'addressRegion'  => 'Gujarat',
					'addressCountry' => 'IN',
				),
			),
		);

		if ( ! empty( $meta['deadline'] ) ) {
			$schema_data['validThrough'] = gmdate( 'c', strtotime( $meta['deadline'] . ' 23:59:59' ) );
		}

		echo "\n<!-- Schema.org JobPosting Structured Data -->\n";
		echo '<script type="application/ld+json">' . wp_json_encode( $schema_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "</script>\n";
	}
?>

<main id="primary" class="site-main sc-single-job-page">

		<!-- 1. Job Header / Hero -->
		<?php get_template_part( 'template-parts/careers/job-header' ); ?>

		<!-- 2. Job Content & Application Form Grid -->
		<div class="sc-single-job-body">
			<div class="sc-container">
				<div class="sc-single-job-layout">
					<!-- Main Column: Detailed Role Specifications -->
					<div class="sc-single-job-main">
						<?php get_template_part( 'template-parts/careers/job-details' ); ?>
					</div>

					<!-- Sidebar Column: Summary & Application Form -->
					<aside class="sc-single-job-sidebar" aria-label="<?php esc_attr_e( 'Application Sidebar', 'spicecraft' ); ?>">
						<!-- Position Fast-Facts Card -->
						<div class="sc-position-facts-card">
							<h3 class="sc-position-facts__title"><?php esc_html_e( 'Role Summary', 'spicecraft' ); ?></h3>
							<ul class="sc-position-facts__list" role="list">
								<?php if ( ! empty( $meta['department'] ) ) : ?>
									<li>
										<span class="sc-fact-label"><?php esc_html_e( 'Department', 'spicecraft' ); ?></span>
										<strong class="sc-fact-val"><?php echo esc_html( $meta['department'] ); ?></strong>
									</li>
								<?php endif; ?>
								<?php if ( ! empty( $meta['location'] ) ) : ?>
									<li>
										<span class="sc-fact-label"><?php esc_html_e( 'Location', 'spicecraft' ); ?></span>
										<strong class="sc-fact-val"><?php echo esc_html( $meta['location'] ); ?></strong>
									</li>
								<?php endif; ?>
								<?php if ( ! empty( $meta['employment_type_label'] ) ) : ?>
									<li>
										<span class="sc-fact-label"><?php esc_html_e( 'Employment', 'spicecraft' ); ?></span>
										<strong class="sc-fact-val"><?php echo esc_html( $meta['employment_type_label'] ); ?></strong>
									</li>
								<?php endif; ?>
								<?php if ( ! empty( $meta['experience'] ) ) : ?>
									<li>
										<span class="sc-fact-label"><?php esc_html_e( 'Experience', 'spicecraft' ); ?></span>
										<strong class="sc-fact-val"><?php echo esc_html( $meta['experience'] ); ?></strong>
									</li>
								<?php endif; ?>
								<?php if ( ! empty( $meta['openings'] ) ) : ?>
									<li>
										<span class="sc-fact-label"><?php esc_html_e( 'Total Openings', 'spicecraft' ); ?></span>
										<strong class="sc-fact-val"><?php echo esc_html( $meta['openings'] ); ?></strong>
									</li>
								<?php endif; ?>
								<li>
									<span class="sc-fact-label"><?php esc_html_e( 'Status', 'spicecraft' ); ?></span>
									<?php if ( $is_closed ) : ?>
										<strong class="sc-fact-val sc-status--closed"><?php esc_html_e( 'Closed', 'spicecraft' ); ?></strong>
									<?php else : ?>
										<strong class="sc-fact-val sc-status--open"><?php esc_html_e( 'Actively Hiring', 'spicecraft' ); ?></strong>
									<?php endif; ?>
								</li>
								<?php if ( ! empty( $meta['deadline_formatted'] ) ) : ?>
									<li>
										<span class="sc-fact-label"><?php esc_html_e( 'Deadline', 'spicecraft' ); ?></span>
										<strong class="sc-fact-val <?php echo $is_closed ? 'sc-status--closed' : ''; ?>"><?php echo esc_html( $meta['deadline_formatted'] ); ?></strong>
									</li>
								<?php endif; ?>
							</ul>
						</div>

						<!-- Application Form Card -->
						<?php get_template_part( 'template-parts/careers/application-form' ); ?>
					</aside>
				</div>
			</div>
		</div>
	<?php endwhile; ?>
</main>

<?php
get_footer();

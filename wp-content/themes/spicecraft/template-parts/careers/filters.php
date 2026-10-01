<?php
/**
 * Template part: Careers Job Search & Filtering Controls
 *
 * Lightweight, accessible, and fast filtering controls for Open Positions.
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Fetch all available departments
$departments = get_terms( array(
	'taxonomy'   => 'spicecraft_department',
	'hide_empty' => false,
) );

// Collect unique locations from existing jobs
$locations_query = new WP_Query( array(
	'post_type'      => 'spicecraft_job',
	'post_status'    => 'publish',
	'posts_per_page' => 100,
	'fields'         => 'ids',
) );
$unique_locations = array();
if ( $locations_query->have_posts() ) {
	foreach ( $locations_query->posts as $jid ) {
		$loc = get_post_meta( $jid, '_sc_job_location', true );
		if ( ! empty( $loc ) && ! in_array( $loc, $unique_locations, true ) ) {
			$unique_locations[] = $loc;
		}
	}
}

// Employment Types
$emp_types = function_exists( 'spicecraft_get_employment_types' ) ? spicecraft_get_employment_types() : array();

// Read active filters from URL query
$search_query   = isset( $_GET['job_search'] ) ? sanitize_text_field( wp_unslash( $_GET['job_search'] ) ) : '';
$active_dept    = isset( $_GET['department'] ) ? sanitize_text_field( wp_unslash( $_GET['department'] ) ) : '';
$active_loc     = isset( $_GET['location'] ) ? sanitize_text_field( wp_unslash( $_GET['location'] ) ) : '';
$active_type    = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';
?>

<div class="sc-careers-filters-wrap" id="job-filters-bar">
	<form method="get" action="<?php echo esc_url( spicecraft_get_careers_url() ); ?>#open-positions" class="sc-careers-filters-form" id="sc-careers-filter-form" role="search" aria-label="<?php esc_attr_e( 'Filter Job Openings', 'spicecraft' ); ?>">
		<div class="sc-careers-filters-grid">
			<!-- 1. Search Keywords -->
			<div class="sc-filter-field sc-filter-field--search">
				<label for="sc-job-search" class="screen-reader-text"><?php esc_html_e( 'Search by job title or skills', 'spicecraft' ); ?></label>
				<div class="sc-input-icon-wrap">
					<svg class="sc-input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
					<input type="text" id="sc-job-search" name="job_search" value="<?php echo esc_attr( $search_query ); ?>" placeholder="<?php esc_attr_e( 'Search positions, skills, keywords...', 'spicecraft' ); ?>" class="sc-filter-input" />
				</div>
			</div>

			<!-- 2. Department Dropdown -->
			<div class="sc-filter-field">
				<label for="sc-job-dept" class="screen-reader-text"><?php esc_html_e( 'Department', 'spicecraft' ); ?></label>
				<select id="sc-job-dept" name="department" class="sc-filter-select">
					<option value=""><?php esc_html_e( 'All Departments', 'spicecraft' ); ?></option>
					<?php if ( ! empty( $departments ) && ! is_wp_error( $departments ) ) : ?>
						<?php foreach ( $departments as $dept ) : ?>
							<option value="<?php echo esc_attr( $dept->slug ); ?>" <?php selected( $active_dept, $dept->slug ); ?>>
								<?php echo esc_html( $dept->name ); ?>
							</option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>

			<!-- 3. Location Dropdown -->
			<div class="sc-filter-field">
				<label for="sc-job-loc" class="screen-reader-text"><?php esc_html_e( 'Location', 'spicecraft' ); ?></label>
				<select id="sc-job-loc" name="location" class="sc-filter-select">
					<option value=""><?php esc_html_e( 'All Locations', 'spicecraft' ); ?></option>
					<?php foreach ( $unique_locations as $uloc ) : ?>
						<option value="<?php echo esc_attr( $uloc ); ?>" <?php selected( $active_loc, $uloc ); ?>>
							<?php echo esc_html( $uloc ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- 4. Employment Type Dropdown -->
			<div class="sc-filter-field">
				<label for="sc-job-type" class="screen-reader-text"><?php esc_html_e( 'Employment Type', 'spicecraft' ); ?></label>
				<select id="sc-job-type" name="type" class="sc-filter-select">
					<option value=""><?php esc_html_e( 'All Employment Types', 'spicecraft' ); ?></option>
					<?php foreach ( $emp_types as $k => $label ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $active_type, $k ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- 5. Actions / Reset -->
			<div class="sc-filter-field sc-filter-field--actions">
				<button type="submit" class="sc-btn sc-btn--primary sc-btn--full">
					<?php esc_html_e( 'Search', 'spicecraft' ); ?>
				</button>
				<?php if ( ! empty( $search_query ) || ! empty( $active_dept ) || ! empty( $active_loc ) || ! empty( $active_type ) ) : ?>
					<a href="<?php echo esc_url( spicecraft_get_careers_url() ); ?>#open-positions" class="sc-btn sc-btn--outline sc-btn--sm sc-filter-reset-btn" title="<?php esc_attr_e( 'Clear All Filters', 'spicecraft' ); ?>">
						<?php esc_html_e( 'Reset', 'spicecraft' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</form>

	<!-- Live Filter Status bar -->
	<div class="sc-careers-filter-meta">
		<span class="sc-careers-count-text" id="sc-jobs-count-text" aria-live="polite">
			<!-- Injected or rendered dynamically -->
		</span>
	</div>
</div>

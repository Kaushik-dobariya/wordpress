<?php
/**
 * Certifications Section: Archive Filtering and Sorting Controls
 *
 * Provides lightweight status, category, featured, and sorting controls.
 * Hides gracefully when filters are disabled in global settings or when
 * fewer than 2 public certifications exist.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings     = function_exists( 'spicecraft_get_certification_settings' ) ? spicecraft_get_certification_settings() : array();
$show_filters = ! empty( $settings['show_filters'] );

if ( ! $show_filters ) {
	return;
}

// Check total public certifications available
$all_public = function_exists( 'spicecraft_get_public_certifications' ) ? spicecraft_get_public_certifications() : array();
if ( count( $all_public ) < 2 ) {
	// Not enough records to justify filter UI
	return;
}

// Current query states
$current_status   = isset( $_GET['cert_status'] ) ? sanitize_key( $_GET['cert_status'] ) : '';
$current_cat      = isset( $_GET['cert_cat'] ) ? absint( $_GET['cert_cat'] ) : 0;
$current_featured = ! empty( $_GET['cert_featured'] ) ? '1' : '';
$current_sort     = isset( $_GET['cert_sort'] ) ? sanitize_key( $_GET['cert_sort'] ) : ( $settings['default_sort'] ?? 'order' );

// Gather unique statuses present in public records
$available_statuses = array();
$related_cat_ids    = array();

foreach ( $all_public as $term ) {
	$effective_st = function_exists( 'spicecraft_get_certification_effective_status' )
		? spicecraft_get_certification_effective_status( $term->term_id )
		: 'active';
	$available_statuses[ $effective_st ] = true;

	$meta = function_exists( 'spicecraft_get_certification_meta' ) ? spicecraft_get_certification_meta( $term->term_id ) : array();
	if ( ! empty( $meta['related_categories'] ) && is_array( $meta['related_categories'] ) ) {
		foreach ( $meta['related_categories'] as $cid ) {
			$related_cat_ids[ absint( $cid ) ] = true;
		}
	}
}

$archive_page = get_page_by_path( 'certifications' );
$action_url   = $archive_page ? get_permalink( $archive_page->ID ) : home_url( '/certifications/' );
?>

<section class="sc-cert-filters-section" aria-label="<?php esc_attr_e( 'Filter and Sort Certifications', 'spicecraft' ); ?>">
	<div class="sc-container">
		<form method="get" action="<?php echo esc_url( $action_url ); ?>" class="sc-cert-filters-form">
			<div class="sc-cert-filters-grid">
				<!-- Status Filter -->
				<?php if ( ! empty( $settings['show_status'] ) && count( $available_statuses ) > 1 ) : ?>
					<div class="sc-cert-filter-group">
						<label for="sc-cert-filter-status" class="sc-cert-filter-label"><?php esc_html_e( 'Status', 'spicecraft' ); ?></label>
						<select name="cert_status" id="sc-cert-filter-status" class="sc-cert-select">
							<option value=""><?php esc_html_e( 'All Statuses', 'spicecraft' ); ?></option>
							<?php
							$status_options = function_exists( 'spicecraft_get_certification_status_options' ) ? spicecraft_get_certification_status_options() : array();
							foreach ( $status_options as $s_key => $s_label ) :
								if ( ! isset( $available_statuses[ $s_key ] ) ) {
									continue;
								}
								?>
								<option value="<?php echo esc_attr( $s_key ); ?>" <?php selected( $current_status, $s_key ); ?>>
									<?php echo esc_html( $s_label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>

				<!-- Category Filter -->
				<?php if ( ! empty( $related_cat_ids ) ) : ?>
					<div class="sc-cert-filter-group">
						<label for="sc-cert-filter-cat" class="sc-cert-filter-label"><?php esc_html_e( 'Product Line', 'spicecraft' ); ?></label>
						<select name="cert_cat" id="sc-cert-filter-cat" class="sc-cert-select">
							<option value=""><?php esc_html_e( 'All Product Lines', 'spicecraft' ); ?></option>
							<?php
							foreach ( array_keys( $related_cat_ids ) as $cat_id ) :
								$cat_term = get_term( $cat_id, 'product_cat' );
								if ( ! $cat_term || is_wp_error( $cat_term ) ) {
									continue;
								}
								?>
								<option value="<?php echo esc_attr( $cat_term->term_id ); ?>" <?php selected( $current_cat, $cat_term->term_id ); ?>>
									<?php echo esc_html( $cat_term->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>

				<!-- Sort Order -->
				<div class="sc-cert-filter-group">
					<label for="sc-cert-filter-sort" class="sc-cert-filter-label"><?php esc_html_e( 'Sort By', 'spicecraft' ); ?></label>
					<select name="cert_sort" id="sc-cert-filter-sort" class="sc-cert-select">
						<option value="order" <?php selected( $current_sort, 'order' ); ?>><?php esc_html_e( 'Standard Order', 'spicecraft' ); ?></option>
						<option value="title_asc" <?php selected( $current_sort, 'title_asc' ); ?>><?php esc_html_e( 'Name (A to Z)', 'spicecraft' ); ?></option>
						<option value="issue_date_desc" <?php selected( $current_sort, 'issue_date_desc' ); ?>><?php esc_html_e( 'Newest Issued', 'spicecraft' ); ?></option>
						<option value="expiry_date_asc" <?php selected( $current_sort, 'expiry_date_asc' ); ?>><?php esc_html_e( 'Expiry Date', 'spicecraft' ); ?></option>
					</select>
				</div>

				<!-- Actions -->
				<div class="sc-cert-filter-actions">
					<button type="submit" class="sc-btn sc-btn--secondary sc-btn--sm">
						<?php esc_html_e( 'Apply', 'spicecraft' ); ?>
					</button>
					<?php if ( ! empty( $current_status ) || ! empty( $current_cat ) || ! empty( $current_featured ) || ( ! empty( $current_sort ) && $current_sort !== ( $settings['default_sort'] ?? 'order' ) ) ) : ?>
						<a href="<?php echo esc_url( $action_url ); ?>" class="sc-cert-reset-link">
							<?php esc_html_e( 'Reset', 'spicecraft' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</form>
	</div>
</section>

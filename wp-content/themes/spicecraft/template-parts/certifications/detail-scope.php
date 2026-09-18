<?php
/**
 * Certifications Section: Detail Scope & Facilities
 *
 * Detailed breakdown of authorized certification scope and verified facility locations.
 * Renders only when genuine scope details are configured.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term           = $args['term'] ?? get_queried_object();
$meta           = $args['meta'] ?? array();
$scope          = ! empty( $meta['scope'] ) ? trim( $meta['scope'] ) : '';
$facility_scope = ! empty( $meta['facility_scope'] ) ? trim( $meta['facility_scope'] ) : '';

if ( empty( $scope ) && empty( $facility_scope ) ) {
	return;
}
?>

<section class="sc-cert-content-block sc-cert-scope-block" aria-labelledby="sc-cert-scope-title">
	<h2 id="sc-cert-scope-title" class="sc-cert-section-heading">
		<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
		<?php esc_html_e( 'Audit Scope & Facility Coverage', 'spicecraft' ); ?>
	</h2>

	<?php if ( ! empty( $scope ) ) : ?>
		<div class="sc-cert-scope-item">
			<h3 class="sc-cert-scope-subheading"><?php esc_html_e( 'Certified Operational Scope', 'spicecraft' ); ?></h3>
			<div class="sc-cert-scope-desc">
				<p><?php echo nl2br( esc_html( $scope ) ); ?></p>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $facility_scope ) ) : ?>
		<div class="sc-cert-scope-item">
			<h3 class="sc-cert-scope-subheading"><?php esc_html_e( 'Covered Facilities & Units', 'spicecraft' ); ?></h3>
			<div class="sc-cert-facility-badge-wrap">
				<div class="sc-cert-facility-item">
					<span class="dashicons dashicons-location" aria-hidden="true"></span>
					<span><?php echo esc_html( $facility_scope ); ?></span>
				</div>
			</div>
		</div>
	<?php endif; ?>
</section>

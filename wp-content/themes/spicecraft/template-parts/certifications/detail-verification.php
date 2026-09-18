<?php
/**
 * Certifications Section: Detail Verification Link
 *
 * Renders verified external link to the registrar or government registry portal.
 * Only displays when an official verification URL is explicitly configured.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term       = $args['term'] ?? get_queried_object();
$meta       = $args['meta'] ?? array();
$verify_url = ! empty( $meta['verification_url'] ) ? esc_url_raw( $meta['verification_url'] ) : '';
$authority  = ! empty( $meta['issuing_authority'] ) ? $meta['issuing_authority'] : __( 'Issuing Authority', 'spicecraft' );

if ( empty( $verify_url ) ) {
	return;
}
?>

<div class="sc-cert-sidebar-card sc-cert-verification-card">
	<h3 class="sc-cert-card-title"><?php esc_html_e( 'Third-Party Verification', 'spicecraft' ); ?></h3>
	<p class="sc-cert-verify-desc">
		<?php
		printf(
			esc_html__( 'Validate this certificate directly on the official registrar portal maintained by %s.', 'spicecraft' ),
			'<strong>' . esc_html( $authority ) . '</strong>'
		);
		?>
	</p>
	<a href="<?php echo esc_url( $verify_url ); ?>" target="_blank" rel="noopener noreferrer" class="sc-btn sc-btn--secondary sc-btn--full sc-cert-verify-btn" aria-label="<?php echo esc_attr( sprintf( __( 'Verify %s on issuing authority portal (opens in new tab)', 'spicecraft' ), $term->name ) ); ?>">
		<span class="dashicons dashicons-external" aria-hidden="true"></span>
		<span><?php esc_html_e( 'Verify with Issuing Authority', 'spicecraft' ); ?></span>
	</a>
</div>

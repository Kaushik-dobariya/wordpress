<?php
/**
 * Certifications Section: Detail Overview
 *
 * Renders structured registry specifications and validity metrics for the certification.
 * Only displays rows that have actual configured values. Never outputs empty table rows.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term         = $args['term'] ?? get_queried_object();
$meta         = $args['meta'] ?? array();
$status       = $args['status'] ?? ( $meta['status'] ?? 'active' );
$status_label = $args['status_label'] ?? ucfirst( $status );

$cert_number = ! empty( $meta['certificate_number'] ) ? $meta['certificate_number'] : '';
$authority   = ! empty( $meta['issuing_authority'] ) ? $meta['issuing_authority'] : '';
$body        = ! empty( $meta['accreditation_body'] ) ? $meta['accreditation_body'] : '';
$issue_date  = ! empty( $meta['issue_date'] ) ? $meta['issue_date'] : '';
$valid_from  = ! empty( $meta['valid_from'] ) ? $meta['valid_from'] : '';
$expiry_date = ! empty( $meta['expiry_date'] ) ? $meta['expiry_date'] : '';

// Build rows array of non-empty items
$spec_rows = array();

if ( ! empty( $cert_number ) ) {
	$spec_rows[] = array(
		'label' => __( 'Certificate / Reg. Number', 'spicecraft' ),
		'value' => '<code>' . esc_html( $cert_number ) . '</code>',
		'is_code' => true,
	);
}

$spec_rows[] = array(
	'label' => __( 'Compliance Status', 'spicecraft' ),
	'value' => '<span class="sc-cert-badge sc-cert-badge--sm sc-cert-badge--' . esc_attr( $status ) . '"><span class="sc-cert-badge__dot" aria-hidden="true"></span> ' . esc_html( $status_label ) . '</span>',
	'is_html' => true,
);

if ( ! empty( $authority ) ) {
	$spec_rows[] = array(
		'label' => __( 'Issuing Registrar', 'spicecraft' ),
		'value' => esc_html( $authority ),
	);
}

if ( ! empty( $body ) ) {
	$spec_rows[] = array(
		'label' => __( 'Accreditation Board', 'spicecraft' ),
		'value' => esc_html( $body ),
	);
}

if ( ! empty( $issue_date ) ) {
	$spec_rows[] = array(
		'label' => __( 'Initial Issue Date', 'spicecraft' ),
		'value' => esc_html( date_i18n( get_option( 'date_format' ), strtotime( $issue_date ) ) ),
	);
}

if ( ! empty( $valid_from ) ) {
	$spec_rows[] = array(
		'label' => __( 'Current Cycle Valid From', 'spicecraft' ),
		'value' => esc_html( date_i18n( get_option( 'date_format' ), strtotime( $valid_from ) ) ),
	);
}

if ( ! empty( $expiry_date ) ) {
	$spec_rows[] = array(
		'label' => __( 'Expiry / Renewal Date', 'spicecraft' ),
		'value' => esc_html( date_i18n( get_option( 'date_format' ), strtotime( $expiry_date ) ) ),
	);
}
?>

<div class="sc-cert-sidebar-card sc-cert-overview-card">
	<h3 class="sc-cert-card-title"><?php esc_html_e( 'Audit & Registry Information', 'spicecraft' ); ?></h3>

	<dl class="sc-cert-spec-list">
		<?php foreach ( $spec_rows as $row ) : ?>
			<div class="sc-cert-spec-row">
				<dt class="sc-cert-spec-dt"><?php echo esc_html( $row['label'] ); ?></dt>
				<dd class="sc-cert-spec-dd">
					<?php
					if ( ! empty( $row['is_code'] ) || ! empty( $row['is_html'] ) ) {
						echo wp_kses_post( $row['value'] );
					} else {
						echo esc_html( $row['value'] );
					}
					?>
				</dd>
			</div>
		<?php endforeach; ?>
	</dl>
</div>

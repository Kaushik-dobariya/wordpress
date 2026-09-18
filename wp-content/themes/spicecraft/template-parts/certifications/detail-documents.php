<?php
/**
 * Certifications Section: Detail Documents & Certificate Image
 *
 * Displays scanned certificate preview image and public document download button.
 * Strictly enforces Document Visibility: if marked private, suppresses all file links.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term    = $args['term'] ?? get_queried_object();
$meta    = $args['meta'] ?? array();
$term_id = $term->term_id ?? 0;

$image_id = absint( $meta['image_id'] ?? 0 );
$doc_url  = function_exists( 'spicecraft_get_certification_public_document_url' )
	? spicecraft_get_certification_public_document_url( $term_id )
	: '';

if ( empty( $image_id ) && empty( $doc_url ) ) {
	return;
}
?>

<div class="sc-cert-sidebar-card sc-cert-docs-card">
	<h3 class="sc-cert-card-title"><?php esc_html_e( 'Official Documentation', 'spicecraft' ); ?></h3>

	<?php if ( $image_id ) : ?>
		<div class="sc-cert-doc-preview-wrap">
			<a href="<?php echo esc_url( wp_get_attachment_url( $image_id ) ); ?>" target="_blank" rel="noopener noreferrer" class="sc-cert-doc-img-link" title="<?php esc_attr_e( 'Click to view full certificate scan in a new tab', 'spicecraft' ); ?>">
				<?php
				echo wp_get_attachment_image(
					$image_id,
					'large',
					false,
					array(
						'class'   => 'sc-cert-doc-preview-img',
						'loading' => 'lazy',
						'alt'     => sprintf( esc_attr__( 'Official Certificate for %s', 'spicecraft' ), $term->name ),
					)
				);
				?>
				<span class="sc-cert-doc-img-hint">
					<span class="dashicons dashicons-search" aria-hidden="true"></span>
					<?php esc_html_e( 'View Full Certificate Scan', 'spicecraft' ); ?>
				</span>
			</a>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $doc_url ) ) : ?>
		<div class="sc-cert-doc-action-wrap">
			<a href="<?php echo esc_url( $doc_url ); ?>" target="_blank" rel="noopener noreferrer" class="sc-btn sc-btn--primary sc-btn--full sc-cert-download-btn">
				<span class="dashicons dashicons-pdf" aria-hidden="true"></span>
				<span><?php esc_html_e( 'Download Certificate (PDF)', 'spicecraft' ); ?></span>
			</a>
			<p class="sc-cert-doc-notice"><?php esc_html_e( 'Verified digital copy provided for compliance & procurement audits.', 'spicecraft' ); ?></p>
		</div>
	<?php endif; ?>
</div>

<?php
/**
 * Certifications Section: Archive Cards Grid
 *
 * Renders verified public certification cards in responsive layout.
 * Respects all global display settings, detail page toggles, and document visibility.
 * Implements a neutral, non-misleading empty state when no public records exist.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = function_exists( 'spicecraft_get_certification_settings' ) ? spicecraft_get_certification_settings() : array();

// Read query params from URL
$status_filter   = isset( $_GET['cert_status'] ) ? sanitize_key( $_GET['cert_status'] ) : '';
$cat_filter      = isset( $_GET['cert_cat'] ) ? absint( $_GET['cert_cat'] ) : 0;
$featured_filter = ! empty( $_GET['cert_featured'] );
$sort_order      = isset( $_GET['cert_sort'] ) ? sanitize_key( $_GET['cert_sort'] ) : ( $settings['default_sort'] ?? 'order' );

$query_args = array(
	'status'      => $status_filter,
	'category_id' => $cat_filter,
	'featured'    => $featured_filter,
	'orderby'     => $sort_order,
);

$certifications = function_exists( 'spicecraft_get_public_certifications' )
	? spicecraft_get_public_certifications( $query_args )
	: array();
?>

<section class="sc-cert-grid-section" aria-label="<?php esc_attr_e( 'Certifications List', 'spicecraft' ); ?>">
	<div class="sc-container">
		<?php if ( empty( $certifications ) ) : ?>
			<div class="sc-cert-empty-state">
				<div class="sc-cert-empty-inner">
					<span class="dashicons dashicons-shield-alt sc-cert-empty-icon" aria-hidden="true"></span>
					<h2 class="sc-cert-empty-title"><?php esc_html_e( 'Certification Information Being Updated', 'spicecraft' ); ?></h2>
					<p class="sc-cert-empty-text">
						<?php
						if ( ! empty( $status_filter ) || ! empty( $cat_filter ) ) {
							esc_html_e( 'No public certification records match your current filter criteria. Try resetting your filters to view all accreditations.', 'spicecraft' );
						} else {
							esc_html_e( 'Our official regulatory audit records and compliance accreditations are currently being updated in this portal. Please contact our quality desk directly for verified audit certificates.', 'spicecraft' );
						}
						?>
					</p>
					<?php if ( ! empty( $status_filter ) || ! empty( $cat_filter ) ) : ?>
						<?php
						$archive_page = get_page_by_path( 'certifications' );
						$reset_url    = $archive_page ? get_permalink( $archive_page->ID ) : home_url( '/certifications/' );
						?>
						<a href="<?php echo esc_url( $reset_url ); ?>" class="sc-btn sc-btn--secondary sc-btn--sm">
							<?php esc_html_e( 'View All Certifications', 'spicecraft' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		<?php else : ?>
			<div class="sc-cert-grid">
				<?php
				foreach ( $certifications as $term ) :
					$term_id          = $term->term_id;
					$meta             = function_exists( 'spicecraft_get_certification_meta' ) ? spicecraft_get_certification_meta( $term_id ) : array();
					$effective_status = function_exists( 'spicecraft_get_certification_effective_status' ) ? spicecraft_get_certification_effective_status( $term_id ) : ( $meta['status'] ?? 'active' );
					$status_label     = function_exists( 'spicecraft_get_certification_status_label' ) ? spicecraft_get_certification_status_label( $effective_status ) : ucfirst( $effective_status );
					$has_detail       = function_exists( 'spicecraft_has_certification_public_detail' ) ? spicecraft_has_certification_public_detail( $term_id ) : true;
					$term_url         = get_term_link( $term );
					$logo_id          = absint( $meta['logo_id'] ?? 0 );
					$short_name       = ! empty( $meta['short_name'] ) ? $meta['short_name'] : '';
					$authority        = ! empty( $meta['issuing_authority'] ) ? $meta['issuing_authority'] : '';
					$cert_number      = ! empty( $meta['certificate_number'] ) ? $meta['certificate_number'] : '';
					$expiry_date      = ! empty( $meta['expiry_date'] ) ? $meta['expiry_date'] : '';
					$issue_date       = ! empty( $meta['issue_date'] ) ? $meta['issue_date'] : '';
					$doc_url          = function_exists( 'spicecraft_get_certification_public_document_url' ) ? spicecraft_get_certification_public_document_url( $term_id ) : '';
					$verify_url       = ! empty( $meta['verification_url'] ) ? $meta['verification_url'] : '';
					?>
					<article class="sc-cert-card sc-cert-card--<?php echo esc_attr( $effective_status ); ?>" id="cert-<?php echo esc_attr( $term_id ); ?>">
						<!-- Card Top Header -->
						<div class="sc-cert-card__header">
							<div class="sc-cert-card__logo-wrap">
								<?php if ( $logo_id ) : ?>
									<?php
									echo wp_get_attachment_image(
										$logo_id,
										'medium',
										false,
										array(
											'class'   => 'sc-cert-card__logo',
											'loading' => 'lazy',
											'alt'     => sprintf( esc_attr__( '%s Certification Logo', 'spicecraft' ), $term->name ),
										)
									);
									?>
								<?php else : ?>
									<div class="sc-cert-card__logo-fallback" aria-hidden="true">
										<span class="dashicons dashicons-awards"></span>
									</div>
								<?php endif; ?>
							</div>

							<?php if ( ! empty( $settings['show_status'] ) ) : ?>
								<span class="sc-cert-badge sc-cert-badge--<?php echo esc_attr( $effective_status ); ?>" title="<?php echo esc_attr( sprintf( __( 'Status: %s', 'spicecraft' ), $status_label ) ); ?>">
									<span class="sc-cert-badge__dot" aria-hidden="true"></span>
									<span class="sc-cert-badge__text"><?php echo esc_html( $status_label ); ?></span>
								</span>
							<?php endif; ?>
						</div>

						<!-- Card Title & Acronym -->
						<div class="sc-cert-card__body">
							<h2 class="sc-cert-card__title">
								<?php if ( $has_detail && ! is_wp_error( $term_url ) ) : ?>
									<a href="<?php echo esc_url( $term_url ); ?>" class="sc-cert-card__title-link">
										<?php echo esc_html( $term->name ); ?>
									</a>
								<?php else : ?>
									<span><?php echo esc_html( $term->name ); ?></span>
								<?php endif; ?>
							</h2>

							<?php if ( ! empty( $short_name ) ) : ?>
								<span class="sc-cert-card__shortname"><?php echo esc_html( $short_name ); ?></span>
							<?php endif; ?>

							<!-- Concise Meta Information -->
							<dl class="sc-cert-card__meta-list">
								<?php if ( ! empty( $settings['show_authority'] ) && ! empty( $authority ) ) : ?>
									<div class="sc-cert-card__meta-row">
										<dt><?php esc_html_e( 'Authority:', 'spicecraft' ); ?></dt>
										<dd><?php echo esc_html( $authority ); ?></dd>
									</div>
								<?php endif; ?>

								<?php if ( ( ! empty( $settings['show_cert_number'] ) || ! empty( $settings['show_number'] ) ) && ! empty( $cert_number ) ) : ?>
									<div class="sc-cert-card__meta-row">
										<dt><?php esc_html_e( 'Registration:', 'spicecraft' ); ?></dt>
										<dd><code><?php echo esc_html( $cert_number ); ?></code></dd>
									</div>
								<?php endif; ?>

								<?php if ( ! empty( $settings['show_validity'] ) ) : ?>
									<?php if ( ! empty( $expiry_date ) ) : ?>
										<div class="sc-cert-card__meta-row">
											<dt><?php esc_html_e( 'Valid Until:', 'spicecraft' ); ?></dt>
											<dd><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $expiry_date ) ) ); ?></dd>
										</div>
									<?php elseif ( ! empty( $issue_date ) ) : ?>
										<div class="sc-cert-card__meta-row">
											<dt><?php esc_html_e( 'Registered:', 'spicecraft' ); ?></dt>
											<dd><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $issue_date ) ) ); ?></dd>
										</div>
									<?php endif; ?>
								<?php endif; ?>
							</dl>

							<?php if ( ! empty( $term->description ) ) : ?>
								<p class="sc-cert-card__desc">
									<?php echo esc_html( wp_trim_words( $term->description, 20, '...' ) ); ?>
								</p>
							<?php endif; ?>
						</div>

						<!-- Card Footer Actions -->
						<div class="sc-cert-card__footer">
							<?php if ( $has_detail && ! is_wp_error( $term_url ) ) : ?>
								<a href="<?php echo esc_url( $term_url ); ?>" class="sc-btn sc-btn--secondary sc-btn--sm sc-cert-card__action-btn">
									<?php esc_html_e( 'View Details & Scope', 'spicecraft' ); ?>
									<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
								</a>
							<?php endif; ?>

							<div class="sc-cert-card__secondary-links">
								<?php if ( ! empty( $settings['show_documents'] ) && ! empty( $doc_url ) ) : ?>
									<a href="<?php echo esc_url( $doc_url ); ?>" class="sc-cert-icon-link" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e( 'Download / View Public Certificate Document (PDF)', 'spicecraft' ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Download certificate for %s (PDF, opens in new tab)', 'spicecraft' ), $term->name ) ); ?>">
										<span class="dashicons dashicons-media-document" aria-hidden="true"></span>
										<span class="sc-cert-icon-link__text"><?php esc_html_e( 'Certificate PDF', 'spicecraft' ); ?></span>
									</a>
								<?php endif; ?>

								<?php if ( ( ! empty( $settings['show_verification_link'] ) || ! empty( $settings['show_verification'] ) ) && ! empty( $verify_url ) ) : ?>
									<a href="<?php echo esc_url( $verify_url ); ?>" class="sc-cert-icon-link" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e( 'Verify on Official Registry Portal', 'spicecraft' ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Verify %s on official registrar portal (opens in new tab)', 'spicecraft' ), $term->name ) ); ?>">
										<span class="dashicons dashicons-external" aria-hidden="true"></span>
										<span class="sc-cert-icon-link__text"><?php esc_html_e( 'Verify', 'spicecraft' ); ?></span>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

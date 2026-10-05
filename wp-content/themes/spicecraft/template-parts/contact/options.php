<?php
/**
 * SpiceCraft Contact Page - Contact / Enquiry Options (B2B Departments)
 *
 * Categorized cards for targeted business routing:
 * - Bulk Supply & Wholesale Sourcing
 * - Export & International Trade
 * - Private Label / Custom OEM Blending
 * - General Corporate Enquiries
 * - Careers & Talent Acquisition
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$departments = function_exists( 'spicecraft_get_contact_departments' )
	? spicecraft_get_contact_departments()
	: array();

if ( empty( $departments ) ) {
	return;
}
?>

<section class="sc-contact-departments-section" id="contact-departments" aria-labelledby="contact-dept-heading">
	<div class="sc-container">
		<header class="sc-section-header sc-section-header--center">
			<span class="sc-eyebrow"><?php esc_html_e( 'Targeted Commercial Routing', 'spicecraft' ); ?></span>
			<h2 id="contact-dept-heading" class="sc-section-title"><?php esc_html_e( 'Select Your Business Inquiry Channel', 'spicecraft' ); ?></h2>
			<p class="sc-section-subtitle">
				<?php esc_html_e( 'Choose the dedicated trade desk aligned with your commercial requirements for expedited specialist handling.', 'spicecraft' ); ?>
			</p>
		</header>

		<div class="sc-contact-departments-grid">
			<?php foreach ( $departments as $dept_key => $dept ) : ?>
				<div class="sc-dept-card sc-dept-card--<?php echo esc_attr( $dept_key ); ?>">
					<div class="sc-dept-card__top">
						<span class="sc-dept-card__badge"><?php echo esc_html( $dept['badge'] ); ?></span>
						<h3 class="sc-dept-card__title"><?php echo esc_html( $dept['title'] ); ?></h3>
					</div>

					<p class="sc-dept-card__desc"><?php echo esc_html( $dept['description'] ); ?></p>

					<div class="sc-dept-card__channels">
						<?php if ( ! empty( $dept['email'] ) ) : ?>
							<a href="mailto:<?php echo esc_attr( $dept['email'] ); ?>" class="sc-dept-card__action-link">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
								<span><?php echo esc_html( $dept['email'] ); ?></span>
							</a>
						<?php endif; ?>

						<?php if ( ! empty( $dept['phone'] ) ) : ?>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $dept['phone'] ) ); ?>" class="sc-dept-card__action-link">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
								<span><?php echo esc_html( $dept['phone'] ); ?></span>
							</a>
						<?php endif; ?>
					</div>

					<div class="sc-dept-card__footer">
						<?php if ( ! empty( $dept['link'] ) ) : ?>
							<a href="<?php echo esc_url( $dept['link'] ); ?>" class="sc-btn sc-btn--outline sc-btn--sm">
								<?php esc_html_e( 'View Open Positions &rarr;', 'spicecraft' ); ?>
							</a>
						<?php else : ?>
							<a href="#contact-form-section" class="sc-btn sc-btn--outline sc-btn--sm sc-dept-select-btn" data-enquiry-type="<?php echo esc_attr( $dept['slug'] ); ?>">
								<?php esc_html_e( 'Route Enquiry Here &darr;', 'spicecraft' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

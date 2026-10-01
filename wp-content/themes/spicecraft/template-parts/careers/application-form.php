<?php
/**
 * Template part: Dedicated Candidate Application Form
 *
 * Renders the accessible application form on single job detail pages.
 * Handles open vs closed states gracefully.
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$job_id    = get_the_ID();
$meta      = function_exists( 'spicecraft_get_job_meta' ) ? spicecraft_get_job_meta( $job_id ) : array();
$is_closed = ! empty( $meta['is_closed'] );
$settings  = function_exists( 'spicecraft_get_careers_settings' ) ? spicecraft_get_careers_settings() : array();

$form_title   = ! empty( $settings['application_title'] ) ? $settings['application_title'] : __( 'Apply for This Position', 'spicecraft' );
$form_intro   = ! empty( $settings['application_intro'] ) ? $settings['application_intro'] : __( 'Please complete the form below and attach your CV/resume. Our talent acquisition team reviews every profile with strict confidentiality.', 'spicecraft' );
$privacy_text = ! empty( $settings['privacy_text'] ) ? $settings['privacy_text'] : __( 'I agree that the information provided may be used for recruitment and employment-related communication.', 'spicecraft' );
$job_title    = get_the_title();

$careers_url = function_exists( 'spicecraft_get_careers_url' ) ? spicecraft_get_careers_url() : home_url( '/careers/' );
?>

<div id="apply-now" class="sc-application-box">
	<?php if ( $is_closed ) : ?>
		<!-- Position Closed Notice -->
		<div class="sc-application-closed" role="alert">
			<div class="sc-application-closed__icon" aria-hidden="true">
				<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
			</div>
			<h3 class="sc-application-closed__title">
				<?php esc_html_e( 'Applications Closed for This Position', 'spicecraft' ); ?>
			</h3>
			<p class="sc-application-closed__desc">
				<?php
				printf(
					/* translators: %s: Job Title */
					esc_html__( 'The application window for "%s" has concluded. Historical details remain accessible for your reference.', 'spicecraft' ),
					esc_html( $job_title )
				);
				?>
			</p>
			<div class="sc-application-closed__actions">
				<a href="<?php echo esc_url( $careers_url ); ?>#open-positions" class="sc-btn sc-btn--primary">
					<?php esc_html_e( 'View Other Active Positions', 'spicecraft' ); ?> &rarr;
				</a>
			</div>
		</div>
	<?php else : ?>
		<!-- Active Application Form -->
		<div class="sc-application-form-card" id="sc-application-container">
			<div class="sc-application-form-header">
				<h2 class="sc-application-form__title"><?php echo esc_html( $form_title ); ?></h2>
				<p class="sc-application-form__intro"><?php echo nl2br( esc_html( $form_intro ) ); ?></p>
			</div>

			<!-- Success State Box (Hidden by default, shown on successful submission) -->
			<div class="sc-form-alert sc-form-alert--success" id="sc-app-success-box" style="display: none;" role="status" aria-live="polite">
				<div class="sc-form-alert__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
				</div>
				<div class="sc-form-alert__body">
					<h3 class="sc-form-alert__title"><?php esc_html_e( 'Application Submitted Successfully', 'spicecraft' ); ?></h3>
					<p class="sc-form-alert__message" id="sc-app-success-message">
						<?php
						printf(
							/* translators: %s: Job Title */
							esc_html__( 'Thank you for your interest in joining our team. We have received your application for: %s. Our team will review your profile and contact you if there is a suitable opportunity.', 'spicecraft' ),
							'<strong>' . esc_html( $job_title ) . '</strong>'
						);
						?>
					</p>
				</div>
			</div>

			<!-- Error Alert Box -->
			<div class="sc-form-alert sc-form-alert--error" id="sc-app-error-box" style="display: none;" role="alert" aria-live="assertive">
				<div class="sc-form-alert__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
				</div>
				<div class="sc-form-alert__body">
					<p class="sc-form-alert__message" id="sc-app-error-message"></p>
				</div>
			</div>

			<!-- Form Element -->
			<form id="sc-careers-application-form" class="sc-app-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" enctype="multipart/form-data" novalidate>
				<input type="hidden" name="action" value="spicecraft_submit_application" />
				<input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />
				<?php wp_nonce_field( 'spicecraft_careers_nonce', 'careers_nonce' ); ?>

				<!-- Honeypot Field for Spam Protection (Hidden from legitimate users) -->
				<div style="display: none !important;" aria-hidden="true">
					<label for="sc_hp_website"><?php esc_html_e( 'Leave this field blank', 'spicecraft' ); ?></label>
					<input type="text" id="sc_hp_website" name="sc_hp_website" value="" tabindex="-1" autocomplete="off" />
				</div>

				<!-- Section 1: Personal Details -->
				<fieldset class="sc-form-section">
					<legend class="sc-form-section__legend"><?php esc_html_e( '1. Personal Details', 'spicecraft' ); ?></legend>
					
					<div class="sc-form-row sc-form-row--2col">
						<div class="sc-form-field">
							<label for="sc_app_fullname" class="sc-form-label">
								<?php esc_html_e( 'Full Name', 'spicecraft' ); ?> <span class="sc-required" aria-hidden="true">*</span>
							</label>
							<input type="text" id="sc_app_fullname" name="full_name" required class="sc-form-input" autocomplete="name" placeholder="<?php esc_attr_e( 'e.g. Ramesh Patel', 'spicecraft' ); ?>" />
						</div>

						<div class="sc-form-field">
							<label for="sc_app_email" class="sc-form-label">
								<?php esc_html_e( 'Email Address', 'spicecraft' ); ?> <span class="sc-required" aria-hidden="true">*</span>
							</label>
							<input type="email" id="sc_app_email" name="email" required class="sc-form-input" autocomplete="email" placeholder="<?php esc_attr_e( 'e.g. ramesh.patel@example.com', 'spicecraft' ); ?>" />
						</div>
					</div>

					<div class="sc-form-row sc-form-row--2col">
						<div class="sc-form-field">
							<label for="sc_app_phone" class="sc-form-label">
								<?php esc_html_e( 'Phone Number', 'spicecraft' ); ?> <span class="sc-required" aria-hidden="true">*</span>
							</label>
							<input type="tel" id="sc_app_phone" name="phone" required class="sc-form-input" autocomplete="tel" placeholder="<?php esc_attr_e( '+91 98765 43210', 'spicecraft' ); ?>" />
						</div>

						<div class="sc-form-field">
							<label for="sc_app_location" class="sc-form-label">
								<?php esc_html_e( 'Current City / Location', 'spicecraft' ); ?>
							</label>
							<input type="text" id="sc_app_location" name="location" class="sc-form-input" placeholder="<?php esc_attr_e( 'e.g. Ahmedabad, Gujarat', 'spicecraft' ); ?>" />
						</div>
					</div>
				</fieldset>

				<!-- Section 2: Professional Details -->
				<fieldset class="sc-form-section">
					<legend class="sc-form-section__legend"><?php esc_html_e( '2. Professional Experience', 'spicecraft' ); ?></legend>

					<div class="sc-form-row sc-form-row--2col">
						<div class="sc-form-field">
							<label for="sc_app_company" class="sc-form-label">
								<?php esc_html_e( 'Current Company', 'spicecraft' ); ?>
							</label>
							<input type="text" id="sc_app_company" name="company" class="sc-form-input" placeholder="<?php esc_attr_e( 'e.g. Heritage Foods Ltd.', 'spicecraft' ); ?>" />
						</div>

						<div class="sc-form-field">
							<label for="sc_app_designation" class="sc-form-label">
								<?php esc_html_e( 'Current Designation', 'spicecraft' ); ?>
							</label>
							<input type="text" id="sc_app_designation" name="designation" class="sc-form-input" placeholder="<?php esc_attr_e( 'e.g. Quality Control Executive', 'spicecraft' ); ?>" />
						</div>
					</div>

					<div class="sc-form-row sc-form-row--3col">
						<div class="sc-form-field">
							<label for="sc_app_experience" class="sc-form-label">
								<?php esc_html_e( 'Total Experience', 'spicecraft' ); ?>
							</label>
							<input type="text" id="sc_app_experience" name="experience" class="sc-form-input" placeholder="<?php esc_attr_e( 'e.g. 4.5 Years', 'spicecraft' ); ?>" />
						</div>

						<div class="sc-form-field">
							<label for="sc_app_linkedin" class="sc-form-label">
								<?php esc_html_e( 'LinkedIn Profile URL', 'spicecraft' ); ?>
							</label>
							<input type="url" id="sc_app_linkedin" name="linkedin" class="sc-form-input" placeholder="<?php esc_attr_e( 'https://linkedin.com/in/...', 'spicecraft' ); ?>" />
						</div>

						<div class="sc-form-field">
							<label for="sc_app_portfolio" class="sc-form-label">
								<?php esc_html_e( 'Portfolio / Website URL', 'spicecraft' ); ?>
							</label>
							<input type="url" id="sc_app_portfolio" name="portfolio" class="sc-form-input" placeholder="<?php esc_attr_e( 'https://...', 'spicecraft' ); ?>" />
						</div>
					</div>
				</fieldset>

				<!-- Section 3: Resume & Cover Message -->
				<fieldset class="sc-form-section">
					<legend class="sc-form-section__legend"><?php esc_html_e( '3. Application & Resume', 'spicecraft' ); ?></legend>

					<!-- Resume Upload Dropzone -->
					<div class="sc-form-field">
						<label for="sc_app_resume" class="sc-form-label">
							<?php esc_html_e( 'Attach Resume / CV', 'spicecraft' ); ?> <span class="sc-required" aria-hidden="true">*</span>
						</label>
						<div class="sc-file-dropzone" id="sc-resume-dropzone">
							<div class="sc-file-dropzone__inner">
								<svg class="sc-file-dropzone__icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
								<div class="sc-file-dropzone__prompt">
									<span class="sc-file-dropzone__btn"><?php esc_html_e( 'Browse Document', 'spicecraft' ); ?></span>
									<span class="sc-file-dropzone__text"><?php esc_html_e( 'or drag and drop here', 'spicecraft' ); ?></span>
								</div>
								<p class="sc-file-dropzone__hint">
									<?php esc_html_e( 'Accepted formats: PDF, DOC, DOCX. Max file size: 10MB.', 'spicecraft' ); ?>
								</p>
								<input type="file" id="sc_app_resume" name="resume" required accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="sc-file-input" />
							</div>
							<div class="sc-file-selected" id="sc-resume-selected" style="display: none;">
								<span class="dashicons dashicons-media-document sc-file-icon"></span>
								<div class="sc-file-details">
									<strong class="sc-file-name" id="sc-file-name-display"></strong>
									<span class="sc-file-size" id="sc-file-size-display"></span>
								</div>
								<button type="button" class="sc-file-clear-btn" id="sc-file-clear-btn" title="<?php esc_attr_e( 'Remove and change file', 'spicecraft' ); ?>">&times;</button>
							</div>
						</div>
					</div>

					<!-- Cover Message -->
					<div class="sc-form-field">
						<label for="sc_app_cover" class="sc-form-label">
							<?php esc_html_e( 'Cover Message / Summary', 'spicecraft' ); ?> <span class="sc-required" aria-hidden="true">*</span>
						</label>
						<textarea id="sc_app_cover" name="cover_message" required rows="4" class="sc-form-textarea" placeholder="<?php esc_attr_e( 'Tell us about your background, key culinary or technical accomplishments, and why you wish to join SpiceCraft...', 'spicecraft' ); ?>"></textarea>
					</div>
				</fieldset>

				<!-- Section 4: Privacy & Consent -->
				<div class="sc-form-consent">
					<label class="sc-checkbox-label" for="sc_app_consent">
						<input type="checkbox" id="sc_app_consent" name="consent" value="1" required class="sc-checkbox-input" />
						<span class="sc-checkbox-text">
							<?php echo esc_html( $privacy_text ); ?> <span class="sc-required" aria-hidden="true">*</span>
						</span>
					</label>
				</div>

				<!-- Submit Button Row -->
				<div class="sc-form-submit-row">
					<button type="submit" id="sc-app-submit-btn" class="sc-btn sc-btn--primary sc-btn--lg sc-btn--full">
						<span class="sc-btn__text"><?php esc_html_e( 'Submit Application', 'spicecraft' ); ?></span>
						<span class="sc-btn__spinner" aria-hidden="true" style="display: none;">
							<svg class="sc-spinner" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-linecap="round"/></svg>
						</span>
					</button>
					<p class="sc-form-privacy-assurance">
						<svg class="sc-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
						<?php esc_html_e( 'Your candidate data is private and encrypted. Never shared with third parties.', 'spicecraft' ); ?>
					</p>
				</div>
			</form>
		</div>
	<?php endif; ?>
</div>

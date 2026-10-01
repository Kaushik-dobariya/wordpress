<?php
/**
 * SpiceCraft Core - Careers Meta Boxes Engine
 *
 * Implements structured admin meta box interfaces for:
 * 1. 'spicecraft_job' (Position details, deadlines, responsibilities, qualifications, skills, benefits)
 * 2. 'spicecraft_application' (Confidential candidate review, resume download, application status workflow)
 *
 * @package SpiceCraft_Core
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SpiceCraft_Careers_Meta {

	/**
	 * Nonce action keys
	 */
	const JOB_NONCE_ACTION = 'spicecraft_job_meta_save';
	const JOB_NONCE_NAME   = 'spicecraft_job_meta_nonce';

	const APP_NONCE_ACTION = 'spicecraft_app_meta_save';
	const APP_NONCE_NAME   = 'spicecraft_app_meta_nonce';

	/**
	 * Singleton Instance.
	 *
	 * @var SpiceCraft_Careers_Meta|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance.
	 *
	 * @return SpiceCraft_Careers_Meta
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post_' . SpiceCraft_Careers_CPT::JOB_CPT, array( $this, 'save_job_meta' ), 10, 2 );
		add_action( 'save_post_' . SpiceCraft_Careers_CPT::APPLICATION_CPT, array( $this, 'save_application_meta' ), 10, 2 );
	}

	/**
	 * Register Meta Boxes.
	 */
	public function register_meta_boxes() {
		// 1. Job Opening Meta Box
		add_meta_box(
			'spicecraft_job_structured_data',
			__( 'Job Opening Specifications & Recruitment Data', 'spicecraft-core' ),
			array( $this, 'render_job_meta_box' ),
			SpiceCraft_Careers_CPT::JOB_CPT,
			'normal',
			'high'
		);

		// 2. Candidate Application Meta Box
		add_meta_box(
			'spicecraft_app_review_data',
			__( 'Candidate Profile & Application Details', 'spicecraft-core' ),
			array( $this, 'render_application_meta_box' ),
			SpiceCraft_Careers_CPT::APPLICATION_CPT,
			'normal',
			'high'
		);
	}

	/**
	 * Render Job Opening Meta Box with Tabbed Interface.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_job_meta_box( $post ) {
		wp_nonce_field( self::JOB_NONCE_ACTION, self::JOB_NONCE_NAME );

		$meta         = spicecraft_get_job_meta( $post->ID );
		$emp_types    = spicecraft_get_employment_types();
		?>
		<div class="sc-metabox-wrapper">
			<!-- Tabs Navigation -->
			<div class="sc-metabox-tabs" role="tablist">
				<button type="button" class="sc-metabox-tab-btn is-active" data-tab="sc-job-tab-details">
					<span class="dashicons dashicons-id"></span> <?php esc_html_e( 'Position Details', 'spicecraft-core' ); ?>
				</button>
				<button type="button" class="sc-metabox-tab-btn" data-tab="sc-job-tab-recruitment">
					<span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e( 'Recruitment & Status', 'spicecraft-core' ); ?>
				</button>
				<button type="button" class="sc-metabox-tab-btn" data-tab="sc-job-tab-responsibilities">
					<span class="dashicons dashicons-list-view"></span> <?php esc_html_e( 'Responsibilities', 'spicecraft-core' ); ?>
				</button>
				<button type="button" class="sc-metabox-tab-btn" data-tab="sc-job-tab-qualifications">
					<span class="dashicons dashicons-welcome-learn-more"></span> <?php esc_html_e( 'Qualifications & Skills', 'spicecraft-core' ); ?>
				</button>
				<button type="button" class="sc-metabox-tab-btn" data-tab="sc-job-tab-benefits">
					<span class="dashicons dashicons-awards"></span> <?php esc_html_e( 'Benefits & Perks', 'spicecraft-core' ); ?>
				</button>
			</div>

			<!-- TAB 1: Position Details -->
			<div class="sc-metabox-panel" id="sc-job-tab-details" style="display: block;">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="sc_job_dept"><?php esc_html_e( 'Department / Division', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" id="sc_job_dept" name="_sc_job_department" value="<?php echo esc_attr( $meta['department'] ); ?>" class="regular-text" placeholder="e.g. Cryo-Milling & Processing" />
							<p class="description"><?php esc_html_e( 'E.g. Production, Quality Assurance, Food Science & Blending, Supply Chain & Logistics, Sales & Exports.', 'spicecraft-core' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_job_location"><?php esc_html_e( 'Primary Location / Plant', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" id="sc_job_location" name="_sc_job_location" value="<?php echo esc_attr( $meta['location'] ); ?>" class="regular-text" placeholder="e.g. Ahmedabad, Gujarat (Corporate Plant)" />
							<p class="description"><?php esc_html_e( 'Enter facility or plant location (e.g. Ahmedabad, Kochi, Remote / Field).', 'spicecraft-core' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_job_type"><?php esc_html_e( 'Employment Type', 'spicecraft-core' ); ?></label></th>
						<td>
							<select id="sc_job_type" name="_sc_job_type">
								<?php foreach ( $emp_types as $type_key => $type_label ) : ?>
									<option value="<?php echo esc_attr( $type_key ); ?>" <?php selected( $meta['employment_type'], $type_key ); ?>>
										<?php echo esc_html( $type_label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_job_exp"><?php esc_html_e( 'Experience Required', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" id="sc_job_exp" name="_sc_job_experience" value="<?php echo esc_attr( $meta['experience'] ); ?>" class="regular-text" placeholder="e.g. 3–5 Years in FMCG / Spice Manufacturing" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_job_openings"><?php esc_html_e( 'Number of Openings', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="number" id="sc_job_openings" name="_sc_job_openings" value="<?php echo esc_attr( $meta['openings'] ); ?>" min="1" max="99" class="small-text" />
							<span class="description"><?php esc_html_e( 'Number of open positions for this role.', 'spicecraft-core' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_job_salary"><?php esc_html_e( 'Compensation / Remuneration (Optional)', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="text" id="sc_job_salary" name="_sc_job_salary" value="<?php echo esc_attr( $meta['salary'] ); ?>" class="regular-text" placeholder="e.g. Competitive / Commensurate with experience" />
							<p class="description"><?php esc_html_e( 'Visible on frontend only if "Show Salary" is enabled in Careers Settings.', 'spicecraft-core' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<!-- TAB 2: Recruitment & Deadline -->
			<div class="sc-metabox-panel" id="sc-job-tab-recruitment" style="display: none;">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="sc_job_status"><?php esc_html_e( 'Recruitment Status', 'spicecraft-core' ); ?></label></th>
						<td>
							<select id="sc_job_status" name="_sc_job_status">
								<option value="published" <?php selected( $meta['status'], 'published' ); ?>><?php esc_html_e( 'Published & Active (Accepting Applications)', 'spicecraft-core' ); ?></option>
								<option value="closed" <?php selected( $meta['status'], 'closed' ); ?>><?php esc_html_e( 'Closed (Applications Disabled)', 'spicecraft-core' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Closing a job immediately prevents candidates from applying on the frontend and backend.', 'spicecraft-core' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sc_job_deadline"><?php esc_html_e( 'Application Deadline', 'spicecraft-core' ); ?></label></th>
						<td>
							<input type="date" id="sc_job_deadline" name="_sc_job_deadline" value="<?php echo esc_attr( $meta['deadline'] ); ?>" />
							<p class="description"><?php esc_html_e( 'If set, new applications are automatically rejected after 23:59:59 on this date.', 'spicecraft-core' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Featured Position', 'spicecraft-core' ); ?></th>
						<td>
							<label for="sc_job_featured">
								<input type="checkbox" id="sc_job_featured" name="_sc_job_featured" value="1" <?php checked( $meta['is_featured'] ); ?> />
								<?php esc_html_e( 'Mark as Featured Job (Highlights on Careers page)', 'spicecraft-core' ); ?>
							</label>
						</td>
					</tr>
				</table>
			</div>

			<!-- TAB 3: Responsibilities (Repeatable) -->
			<div class="sc-metabox-panel" id="sc-job-tab-responsibilities" style="display: none;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
					<div>
						<h3 style="margin: 0;"><?php esc_html_e( 'Key Responsibilities', 'spicecraft-core' ); ?></h3>
						<p class="description"><?php esc_html_e( 'Add specific responsibilities and daily operational expectations for this role.', 'spicecraft-core' ); ?></p>
					</div>
					<button type="button" class="button button-primary" id="sc-add-responsibility-btn">
						<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span> <?php esc_html_e( 'Add Responsibility', 'spicecraft-core' ); ?>
					</button>
				</div>

				<div id="sc-responsibilities-container">
					<?php
					$resp_items = ! empty( $meta['responsibilities'] ) ? $meta['responsibilities'] : array( '' );
					foreach ( $resp_items as $r_idx => $resp_text ) :
						?>
						<div class="sc-repeatable-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px;">
							<span class="dashicons dashicons-menu sc-drag-handle" style="color: #8c8f94; cursor: grab;"></span>
							<input type="text" name="_sc_job_responsibilities[]" value="<?php echo esc_attr( $resp_text ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Oversee precision milling and cryo-grinding lines to ensure particle consistency.', 'spicecraft-core' ); ?>" />
							<button type="button" class="button sc-remove-row-btn" title="<?php esc_attr_e( 'Remove', 'spicecraft-core' ); ?>">&times;</button>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- TAB 4: Qualifications & Skills -->
			<div class="sc-metabox-panel" id="sc-job-tab-qualifications" style="display: none;">
				<!-- Required Qualifications -->
				<div style="margin-bottom: 24px;">
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
						<h4 style="margin: 0; font-size: 14px;"><?php esc_html_e( 'Required Qualifications', 'spicecraft-core' ); ?></h4>
						<button type="button" class="button button-secondary" id="sc-add-qualification-btn">
							<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span> <?php esc_html_e( 'Add Required Qualification', 'spicecraft-core' ); ?>
						</button>
					</div>
					<div id="sc-qualifications-container">
						<?php
						$qual_items = ! empty( $meta['qualifications'] ) ? $meta['qualifications'] : array( '' );
						foreach ( $qual_items as $q_idx => $qual_text ) :
							?>
							<div class="sc-repeatable-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px;">
								<span class="dashicons dashicons-menu sc-drag-handle" style="color: #8c8f94; cursor: grab;"></span>
								<input type="text" name="_sc_job_qualifications[]" value="<?php echo esc_attr( $qual_text ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Bachelor\'s degree in Food Science, Chemical Engineering, or related discipline.', 'spicecraft-core' ); ?>" />
								<button type="button" class="button sc-remove-row-btn">&times;</button>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Preferred Qualifications -->
				<div style="margin-bottom: 24px;">
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
						<h4 style="margin: 0; font-size: 14px;"><?php esc_html_e( 'Preferred Qualifications (Optional)', 'spicecraft-core' ); ?></h4>
						<button type="button" class="button button-secondary" id="sc-add-preferred-qualification-btn">
							<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span> <?php esc_html_e( 'Add Preferred Qualification', 'spicecraft-core' ); ?>
						</button>
					</div>
					<div id="sc-pref-qualifications-container">
						<?php
						$pref_items = ! empty( $meta['preferred_qualifications'] ) ? $meta['preferred_qualifications'] : array();
						foreach ( $pref_items as $p_idx => $pref_text ) :
							?>
							<div class="sc-repeatable-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px;">
								<span class="dashicons dashicons-menu sc-drag-handle" style="color: #8c8f94; cursor: grab;"></span>
								<input type="text" name="_sc_job_preferred_qualifications[]" value="<?php echo esc_attr( $pref_text ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Prior experience with US FDA, ISO 22000, or BRCGS spice audit standards.', 'spicecraft-core' ); ?>" />
								<button type="button" class="button sc-remove-row-btn">&times;</button>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Required Skills -->
				<div>
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
						<h4 style="margin: 0; font-size: 14px;"><?php esc_html_e( 'Key Skills / Competencies', 'spicecraft-core' ); ?></h4>
						<button type="button" class="button button-secondary" id="sc-add-skill-btn">
							<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span> <?php esc_html_e( 'Add Skill Chip', 'spicecraft-core' ); ?>
						</button>
					</div>
					<div id="sc-skills-container">
						<?php
						$skill_items = ! empty( $meta['skills'] ) ? $meta['skills'] : array( '' );
						foreach ( $skill_items as $s_idx => $skill_text ) :
							?>
							<div class="sc-repeatable-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px;">
								<span class="dashicons dashicons-menu sc-drag-handle" style="color: #8c8f94; cursor: grab;"></span>
								<input type="text" name="_sc_job_skills[]" value="<?php echo esc_attr( $skill_text ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Cryo-Milling, HACCP, Sensory Analysis, Spice Blending, Steam Sterilization', 'spicecraft-core' ); ?>" />
								<button type="button" class="button sc-remove-row-btn">&times;</button>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<!-- TAB 5: Benefits & Perks -->
			<div class="sc-metabox-panel" id="sc-job-tab-benefits" style="display: none;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
					<div>
						<h3 style="margin: 0;"><?php esc_html_e( 'Employee Benefits & Perks', 'spicecraft-core' ); ?></h3>
						<p class="description"><?php esc_html_e( 'Specify perks offered for this role (e.g. health insurance, continuing education, meal subsidies).', 'spicecraft-core' ); ?></p>
					</div>
					<button type="button" class="button button-primary" id="sc-add-benefit-btn">
						<span class="dashicons dashicons-plus-alt2" style="margin-top: -2px;"></span> <?php esc_html_e( 'Add Benefit', 'spicecraft-core' ); ?>
					</button>
				</div>

				<div id="sc-benefits-container">
					<?php
					$benefit_items = ! empty( $meta['benefits'] ) ? $meta['benefits'] : array(
						'Comprehensive Family Health Insurance',
						'Performance & Annual Production Bonuses',
						'Professional Training & Industry Certifications',
						'Subsidized Organic Meals at Factory Cafeteria',
					);
					foreach ( $benefit_items as $b_idx => $benefit_text ) :
						?>
						<div class="sc-repeatable-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px;">
							<span class="dashicons dashicons-menu sc-drag-handle" style="color: #8c8f94; cursor: grab;"></span>
							<input type="text" name="_sc_job_benefits[]" value="<?php echo esc_attr( $benefit_text ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Comprehensive Family Medical Cover', 'spicecraft-core' ); ?>" />
							<button type="button" class="button sc-remove-row-btn">&times;</button>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Application Review Meta Box.
	 *
	 * @param WP_Post $post Current application post.
	 */
	public function render_application_meta_box( $post ) {
		wp_nonce_field( self::APP_NONCE_ACTION, self::APP_NONCE_NAME );

		$job_id      = get_post_meta( $post->ID, '_sc_app_job_id', true );
		$job_title   = get_post_meta( $post->ID, '_sc_app_job_title', true );
		$email       = get_post_meta( $post->ID, '_sc_app_email', true );
		$phone       = get_post_meta( $post->ID, '_sc_app_phone', true );
		$location    = get_post_meta( $post->ID, '_sc_app_location', true );
		$company     = get_post_meta( $post->ID, '_sc_app_company', true );
		$designation = get_post_meta( $post->ID, '_sc_app_designation', true );
		$experience  = get_post_meta( $post->ID, '_sc_app_experience', true );
		$linkedin    = get_post_meta( $post->ID, '_sc_app_linkedin', true );
		$portfolio   = get_post_meta( $post->ID, '_sc_app_portfolio', true );
		$cover_msg   = get_post_meta( $post->ID, '_sc_app_cover_message', true );
		$resume_url  = get_post_meta( $post->ID, '_sc_app_resume_url', true );
		$resume_name = get_post_meta( $post->ID, '_sc_app_resume_name', true );
		$status      = get_post_meta( $post->ID, '_sc_app_status', true ) ?: 'new';
		$admin_notes = get_post_meta( $post->ID, '_sc_app_admin_notes', true );
		$statuses    = spicecraft_get_application_statuses();

		$job_obj = $job_id ? get_post( $job_id ) : null;
		?>
		<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
			<!-- Main Column: Candidate Dossier -->
			<div>
				<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 20px;">
					<h3 style="margin-top: 0; color: #1e293b;"><?php esc_html_e( 'Target Position', 'spicecraft-core' ); ?></h3>
					<p style="font-size: 16px; margin: 0;">
						<?php if ( $job_obj ) : ?>
							<strong><a href="<?php echo esc_url( get_edit_post_link( $job_id ) ); ?>"><?php echo esc_html( $job_obj->post_title ); ?></a></strong>
							<span style="color: #64748b;">(ID: <?php echo esc_html( $job_id ); ?>)</span>
						<?php elseif ( ! empty( $job_title ) ) : ?>
							<strong><?php echo esc_html( $job_title ); ?></strong>
						<?php else : ?>
							<em><?php esc_html_e( 'General Talent Profile Submission', 'spicecraft-core' ); ?></em>
						<?php endif; ?>
					</p>
				</div>

				<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 20px;">
					<h3 style="margin-top: 0; color: #1e293b;"><?php esc_html_e( 'Applicant Personal Details', 'spicecraft-core' ); ?></h3>
					<table class="form-table" role="presentation" style="margin-top: 0;">
						<tr>
							<th style="width: 140px;"><?php esc_html_e( 'Full Name', 'spicecraft-core' ); ?></th>
							<td><strong><?php echo esc_html( $post->post_title ); ?></strong></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Email', 'spicecraft-core' ); ?></th>
							<td><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Phone', 'spicecraft-core' ); ?></th>
							<td><a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Location', 'spicecraft-core' ); ?></th>
							<td><?php echo ! empty( $location ) ? esc_html( $location ) : '<span style="color: #94a3b8;">&mdash;</span>'; ?></td>
						</tr>
					</table>
				</div>

				<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 20px;">
					<h3 style="margin-top: 0; color: #1e293b;"><?php esc_html_e( 'Professional Experience', 'spicecraft-core' ); ?></h3>
					<table class="form-table" role="presentation" style="margin-top: 0;">
						<tr>
							<th style="width: 140px;"><?php esc_html_e( 'Current Company', 'spicecraft-core' ); ?></th>
							<td><?php echo ! empty( $company ) ? esc_html( $company ) : '<span style="color: #94a3b8;">&mdash;</span>'; ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Designation', 'spicecraft-core' ); ?></th>
							<td><?php echo ! empty( $designation ) ? esc_html( $designation ) : '<span style="color: #94a3b8;">&mdash;</span>'; ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Experience', 'spicecraft-core' ); ?></th>
							<td><?php echo ! empty( $experience ) ? esc_html( $experience ) : '<span style="color: #94a3b8;">&mdash;</span>'; ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'LinkedIn', 'spicecraft-core' ); ?></th>
							<td>
								<?php if ( ! empty( $linkedin ) ) : ?>
									<a href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $linkedin ); ?> &nearr;</a>
								<?php else : ?>
									<span style="color: #94a3b8;">&mdash;</span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Portfolio / Work', 'spicecraft-core' ); ?></th>
							<td>
								<?php if ( ! empty( $portfolio ) ) : ?>
									<a href="<?php echo esc_url( $portfolio ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $portfolio ); ?> &nearr;</a>
								<?php else : ?>
									<span style="color: #94a3b8;">&mdash;</span>
								<?php endif; ?>
							</td>
						</tr>
					</table>
				</div>

				<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px;">
					<h3 style="margin-top: 0; color: #1e293b;"><?php esc_html_e( 'Cover Message', 'spicecraft-core' ); ?></h3>
					<div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 12px; font-size: 14px; line-height: 1.6; white-space: pre-wrap;">
						<?php echo esc_html( $cover_msg ?: $post->post_content ); ?>
					</div>
				</div>
			</div>

			<!-- Sidebar: Recruitment Controls & Resume Download -->
			<div>
				<!-- Resume Download Card -->
				<div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 16px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
					<h4 style="margin-top: 0; color: #0f172a; font-size: 14px;"><?php esc_html_e( 'Candidate Resume / CV', 'spicecraft-core' ); ?></h4>
					<?php if ( ! empty( $resume_url ) ) :
						$download_url = wp_nonce_url(
							admin_url( 'admin-post.php?action=spicecraft_download_resume&app_id=' . $post->ID ),
							'spicecraft_download_resume_' . $post->ID
						);
						?>
						<p style="font-size: 13px; color: #475569; word-break: break-all; margin-bottom: 12px;">
							<span class="dashicons dashicons-media-document" style="color: #b32d2e;"></span>
							<strong><?php echo esc_html( $resume_name ?: basename( $resume_url ) ); ?></strong>
						</p>
						<a href="<?php echo esc_url( $download_url ); ?>" class="button button-primary" style="display: block; text-align: center; font-weight: 600; padding: 6px 12px; height: auto;">
							<span class="dashicons dashicons-download" style="vertical-align: middle;"></span> <?php esc_html_e( 'Secure Download Resume', 'spicecraft-core' ); ?>
						</a>
						<p class="description" style="margin-top: 8px; font-size: 11px;"><?php esc_html_e( 'Protected access. Available exclusively to authorized administrators.', 'spicecraft-core' ); ?></p>
					<?php else : ?>
						<p style="color: #64748b; font-size: 13px;"><?php esc_html_e( 'No resume attachment was uploaded.', 'spicecraft-core' ); ?></p>
					<?php endif; ?>
				</div>

				<!-- Email Notification Status Card -->
				<?php
				$email_sent  = get_post_meta( $post->ID, '_sc_app_email_sent', true );
				$email_to    = get_post_meta( $post->ID, '_sc_app_email_to', true ) ?: spicecraft_get_careers_profile_email();
				$email_err   = get_post_meta( $post->ID, '_sc_app_email_error', true );
				$email_time  = get_post_meta( $post->ID, '_sc_app_email_time', true );
				?>
				<div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 16px; margin-bottom: 20px;">
					<h4 style="margin-top: 0; color: #0f172a; font-size: 14px; display: flex; align-items: center; justify-content: space-between;">
						<span><span class="dashicons dashicons-email-alt" style="vertical-align: middle;"></span> <?php esc_html_e( 'Email Notification', 'spicecraft-core' ); ?></span>
						<?php if ( '1' === (string) $email_sent ) : ?>
							<span style="background: #dcfce7; color: #166534; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 12px;"><?php esc_html_e( 'Dispatched', 'spicecraft-core' ); ?></span>
						<?php else : ?>
							<span style="background: #fee2e2; color: #991b1b; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 12px;"><?php esc_html_e( 'Pending / Local', 'spicecraft-core' ); ?></span>
						<?php endif; ?>
					</h4>
					<p style="margin: 6px 0; font-size: 13px; color: #334155;">
						<strong><?php esc_html_e( 'Target Address:', 'spicecraft-core' ); ?></strong> <code><?php echo esc_html( $email_to ); ?></code>
					</p>
					<?php if ( ! empty( $email_time ) ) : ?>
						<p style="margin: 4px 0; font-size: 12px; color: #64748b;">
							<strong><?php esc_html_e( 'Attempted:', 'spicecraft-core' ); ?></strong> <?php echo esc_html( $email_time ); ?>
						</p>
					<?php endif; ?>
					<?php if ( ! empty( $email_err ) && '1' !== (string) $email_sent ) : ?>
						<div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 4px; padding: 8px 10px; margin: 10px 0; font-size: 12px; color: #991b1b;">
							<strong><?php esc_html_e( 'Notice:', 'spicecraft-core' ); ?></strong> <?php echo esc_html( $email_err ); ?>
						</div>
					<?php endif; ?>
					<p style="margin: 12px 0 0;">
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=spicecraft_resend_application_email&app_id=' . $post->ID ), 'spicecraft_resend_email_' . $post->ID ) ); ?>" class="button button-secondary" style="width: 100%; text-align: center;">
							<span class="dashicons dashicons-update" style="vertical-align: middle;"></span> <?php esc_html_e( 'Resend Notification Email', 'spicecraft-core' ); ?>
						</a>
					</p>
				</div>

				<!-- Application Status & Notes Card -->
				<div style="background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 16px; margin-bottom: 20px;">
					<h4 style="margin-top: 0; color: #0f172a; font-size: 14px;"><?php esc_html_e( 'Recruitment Pipeline Status', 'spicecraft-core' ); ?></h4>
					<p>
						<label for="sc_app_status" class="screen-reader-text"><?php esc_html_e( 'Application Status', 'spicecraft-core' ); ?></label>
						<select id="sc_app_status" name="_sc_app_status" style="width: 100%; font-weight: 600; padding: 6px;">
							<?php foreach ( $statuses as $k => $st ) : ?>
								<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $status, $k ); ?>>
									<?php echo esc_html( $st['label'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</p>

					<p style="margin-top: 14px;">
						<label for="sc_app_admin_notes" style="font-weight: 600; font-size: 12px; display: block; margin-bottom: 4px;"><?php esc_html_e( 'Internal HR / Interview Notes:', 'spicecraft-core' ); ?></label>
						<textarea id="sc_app_admin_notes" name="_sc_app_admin_notes" rows="6" class="widefat" placeholder="<?php esc_attr_e( 'Notes on screening, interview schedules, candidate feedback...', 'spicecraft-core' ); ?>"><?php echo esc_textarea( $admin_notes ); ?></textarea>
					</p>
				</div>

				<!-- Submission Metadata -->
				<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; font-size: 12px; color: #64748b;">
					<p style="margin: 0 0 6px;"><strong><?php esc_html_e( 'Submitted:', 'spicecraft-core' ); ?></strong> <?php echo esc_html( get_the_date( 'Y-m-d H:i:s', $post->ID ) ); ?></p>
					<p style="margin: 0;"><strong><?php esc_html_e( 'Candidate IP:', 'spicecraft-core' ); ?></strong> <?php echo esc_html( get_post_meta( $post->ID, '_sc_app_submission_ip', true ) ?: 'N/A' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Save Job Opening metadata.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_job_meta( $post_id, $post ) {
		// Verify nonce
		if ( ! isset( $_POST[ self::JOB_NONCE_NAME ] ) || ! wp_verify_nonce( $_POST[ self::JOB_NONCE_NAME ], self::JOB_NONCE_ACTION ) ) {
			return;
		}

		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Capability check
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save Single Fields
		$department  = isset( $_POST['_sc_job_department'] ) ? sanitize_text_field( wp_unslash( $_POST['_sc_job_department'] ) ) : '';
		$location    = isset( $_POST['_sc_job_location'] ) ? sanitize_text_field( wp_unslash( $_POST['_sc_job_location'] ) ) : '';
		$emp_type    = isset( $_POST['_sc_job_type'] ) ? sanitize_text_field( wp_unslash( $_POST['_sc_job_type'] ) ) : 'full_time';
		$experience  = isset( $_POST['_sc_job_experience'] ) ? sanitize_text_field( wp_unslash( $_POST['_sc_job_experience'] ) ) : '';
		$openings    = isset( $_POST['_sc_job_openings'] ) ? absint( $_POST['_sc_job_openings'] ) : 1;
		$salary      = isset( $_POST['_sc_job_salary'] ) ? sanitize_text_field( wp_unslash( $_POST['_sc_job_salary'] ) ) : '';
		$status      = isset( $_POST['_sc_job_status'] ) ? sanitize_text_field( wp_unslash( $_POST['_sc_job_status'] ) ) : 'published';
		$deadline    = isset( $_POST['_sc_job_deadline'] ) ? sanitize_text_field( wp_unslash( $_POST['_sc_job_deadline'] ) ) : '';
		$is_featured = ! empty( $_POST['_sc_job_featured'] ) ? 1 : 0;

		update_post_meta( $post_id, '_sc_job_department', $department );
		update_post_meta( $post_id, '_sc_job_location', $location );
		update_post_meta( $post_id, '_sc_job_type', $emp_type );
		update_post_meta( $post_id, '_sc_job_experience', $experience );
		update_post_meta( $post_id, '_sc_job_openings', $openings );
		update_post_meta( $post_id, '_sc_job_salary', $salary );
		update_post_meta( $post_id, '_sc_job_status', $status );
		update_post_meta( $post_id, '_sc_job_deadline', $deadline );
		update_post_meta( $post_id, '_sc_job_featured', $is_featured );

		// Automatically link or create taxonomy term for department
		if ( ! empty( $department ) ) {
			wp_set_object_terms( $post_id, $department, SpiceCraft_Careers_CPT::DEPT_TAX, false );
		}

		// Save Repeatable Lists
		$responsibilities = isset( $_POST['_sc_job_responsibilities'] ) && is_array( $_POST['_sc_job_responsibilities'] )
			? array_values( array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['_sc_job_responsibilities'] ) ) ) )
			: array();
		update_post_meta( $post_id, '_sc_job_responsibilities', $responsibilities );

		$qualifications = isset( $_POST['_sc_job_qualifications'] ) && is_array( $_POST['_sc_job_qualifications'] )
			? array_values( array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['_sc_job_qualifications'] ) ) ) )
			: array();
		update_post_meta( $post_id, '_sc_job_qualifications', $qualifications );

		$preferred_qual = isset( $_POST['_sc_job_preferred_qualifications'] ) && is_array( $_POST['_sc_job_preferred_qualifications'] )
			? array_values( array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['_sc_job_preferred_qualifications'] ) ) ) )
			: array();
		update_post_meta( $post_id, '_sc_job_preferred_qualifications', $preferred_qual );

		$skills = isset( $_POST['_sc_job_skills'] ) && is_array( $_POST['_sc_job_skills'] )
			? array_values( array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['_sc_job_skills'] ) ) ) )
			: array();
		update_post_meta( $post_id, '_sc_job_skills', $skills );

		$benefits = isset( $_POST['_sc_job_benefits'] ) && is_array( $_POST['_sc_job_benefits'] )
			? array_values( array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['_sc_job_benefits'] ) ) ) )
			: array();
		update_post_meta( $post_id, '_sc_job_benefits', $benefits );
	}

	/**
	 * Save Application status and admin notes.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_application_meta( $post_id, $post ) {
		// Verify nonce
		if ( ! isset( $_POST[ self::APP_NONCE_NAME ] ) || ! wp_verify_nonce( $_POST[ self::APP_NONCE_NAME ], self::APP_NONCE_ACTION ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['_sc_app_status'] ) ) {
			$status_key = sanitize_key( $_POST['_sc_app_status'] );
			$statuses   = spicecraft_get_application_statuses();
			if ( isset( $statuses[ $status_key ] ) ) {
				update_post_meta( $post_id, '_sc_app_status', $status_key );
			}
		}

		if ( isset( $_POST['_sc_app_admin_notes'] ) ) {
			$notes = sanitize_textarea_field( wp_unslash( $_POST['_sc_app_admin_notes'] ) );
			update_post_meta( $post_id, '_sc_app_admin_notes', $notes );
		}
	}
}

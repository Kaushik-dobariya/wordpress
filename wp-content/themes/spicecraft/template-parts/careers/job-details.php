<?php
/**
 * Template part: Job Specifications & Content Body
 *
 * Displays detailed job description, responsibilities, qualifications,
 * competencies, and company benefits.
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$job_id = get_the_ID();
$meta   = function_exists( 'spicecraft_get_job_meta' ) ? spicecraft_get_job_meta( $job_id ) : array();

$responsibilities = ! empty( $meta['responsibilities'] ) && is_array( $meta['responsibilities'] ) ? $meta['responsibilities'] : array();
$qualifications   = ! empty( $meta['qualifications'] ) && is_array( $meta['qualifications'] ) ? $meta['qualifications'] : array();
$pref_qual        = ! empty( $meta['preferred_qualifications'] ) && is_array( $meta['preferred_qualifications'] ) ? $meta['preferred_qualifications'] : array();
$skills           = ! empty( $meta['skills'] ) && is_array( $meta['skills'] ) ? $meta['skills'] : array();
$benefits         = ! empty( $meta['benefits'] ) && is_array( $meta['benefits'] ) ? $meta['benefits'] : array();
?>

<div class="sc-job-details">
	<!-- 1. Role Overview / About the Role -->
	<section class="sc-job-section" aria-labelledby="section-about-role">
		<h2 id="section-about-role" class="sc-job-section__title">
			<svg class="sc-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
			<?php esc_html_e( 'About the Role', 'spicecraft' ); ?>
		</h2>
		<div class="sc-job-content sc-prose">
			<?php the_content(); ?>
		</div>
	</section>

	<!-- 2. Key Responsibilities -->
	<?php if ( ! empty( $responsibilities ) ) : ?>
		<section class="sc-job-section" aria-labelledby="section-responsibilities">
			<h2 id="section-responsibilities" class="sc-job-section__title">
				<svg class="sc-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
				<?php esc_html_e( 'Key Responsibilities', 'spicecraft' ); ?>
			</h2>
			<ul class="sc-checklist" role="list">
				<?php foreach ( $responsibilities as $resp ) : ?>
					<li class="sc-checklist__item" role="listitem">
						<span class="sc-checklist__icon" aria-hidden="true">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
						</span>
						<span class="sc-checklist__text"><?php echo esc_html( $resp ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<!-- 3. Qualifications & Requirements -->
	<?php if ( ! empty( $qualifications ) ) : ?>
		<section class="sc-job-section" aria-labelledby="section-qualifications">
			<h2 id="section-qualifications" class="sc-job-section__title">
				<svg class="sc-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
				<?php esc_html_e( 'Required Qualifications', 'spicecraft' ); ?>
			</h2>
			<ul class="sc-checklist" role="list">
				<?php foreach ( $qualifications as $qual ) : ?>
					<li class="sc-checklist__item" role="listitem">
						<span class="sc-checklist__icon" aria-hidden="true">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
						</span>
						<span class="sc-checklist__text"><?php echo esc_html( $qual ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<!-- 4. Preferred Qualifications (if specified) -->
	<?php if ( ! empty( $pref_qual ) ) : ?>
		<section class="sc-job-section" aria-labelledby="section-pref-qualifications">
			<h2 id="section-pref-qualifications" class="sc-job-section__title">
				<svg class="sc-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
				<?php esc_html_e( 'Preferred Qualifications', 'spicecraft' ); ?>
			</h2>
			<ul class="sc-checklist" role="list">
				<?php foreach ( $pref_qual as $pqual ) : ?>
					<li class="sc-checklist__item" role="listitem">
						<span class="sc-checklist__icon sc-checklist__icon--accent" aria-hidden="true">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
						</span>
						<span class="sc-checklist__text"><?php echo esc_html( $pqual ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<!-- 5. Key Skills Chips -->
	<?php if ( ! empty( $skills ) ) : ?>
		<section class="sc-job-section" aria-labelledby="section-skills">
			<h2 id="section-skills" class="sc-job-section__title">
				<svg class="sc-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
				<?php esc_html_e( 'Key Skills & Competencies', 'spicecraft' ); ?>
			</h2>
			<div class="sc-skills-cloud" aria-label="<?php esc_attr_e( 'Skill tags', 'spicecraft' ); ?>">
				<?php foreach ( $skills as $sk ) : ?>
					<span class="sc-skill-tag"><?php echo esc_html( $sk ); ?></span>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- 6. Benefits & Perks -->
	<?php if ( ! empty( $benefits ) ) : ?>
		<section class="sc-job-section" aria-labelledby="section-benefits">
			<h2 id="section-benefits" class="sc-job-section__title">
				<svg class="sc-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
				<?php esc_html_e( 'What We Offer', 'spicecraft' ); ?>
			</h2>
			<div class="sc-benefits-grid" role="list">
				<?php foreach ( $benefits as $ben ) : ?>
					<div class="sc-benefit-card" role="listitem">
						<span class="sc-benefit-card__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
						</span>
						<span class="sc-benefit-card__text"><?php echo esc_html( $ben ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>
</div>

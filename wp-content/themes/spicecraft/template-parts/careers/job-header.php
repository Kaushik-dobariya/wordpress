<?php
/**
 * Template part: Job Detail Header & Hero
 *
 * Displays single job title, meta badges, deadline, and quick apply action.
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$job_id      = get_the_ID();
$meta        = function_exists( 'spicecraft_get_job_meta' ) ? spicecraft_get_job_meta( $job_id ) : array();
$careers_url = function_exists( 'spicecraft_get_careers_url' ) ? spicecraft_get_careers_url() : home_url( '/careers/' );

$is_closed    = ! empty( $meta['is_closed'] );
$department   = ! empty( $meta['department'] ) ? $meta['department'] : '';
$location     = ! empty( $meta['location'] ) ? $meta['location'] : '';
$emp_label    = ! empty( $meta['employment_type_label'] ) ? $meta['employment_type_label'] : '';
$experience   = ! empty( $meta['experience'] ) ? $meta['experience'] : '';
$openings     = ! empty( $meta['openings'] ) ? $meta['openings'] : 1;
$deadline     = ! empty( $meta['deadline_formatted'] ) ? $meta['deadline_formatted'] : '';
$salary       = ! empty( $meta['salary'] ) ? $meta['salary'] : '';

$settings    = function_exists( 'spicecraft_get_careers_settings' ) ? spicecraft_get_careers_settings() : array();
$show_salary = ! empty( $settings['show_salary'] );
?>

<div class="sc-job-hero">
	<div class="sc-container">
		<!-- Accessible Breadcrumb Navigation -->
		<nav class="sc-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'spicecraft' ); ?>">
			<ol class="sc-breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">
				<li class="sc-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" itemprop="item">
						<span itemprop="name"><?php esc_html_e( 'Home', 'spicecraft' ); ?></span>
					</a>
					<meta itemprop="position" content="1" />
				</li>
				<li class="sc-breadcrumbs__separator" aria-hidden="true">&rsaquo;</li>
				<li class="sc-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<a href="<?php echo esc_url( $careers_url ); ?>" itemprop="item">
						<span itemprop="name"><?php esc_html_e( 'Careers', 'spicecraft' ); ?></span>
					</a>
					<meta itemprop="position" content="2" />
				</li>
				<li class="sc-breadcrumbs__separator" aria-hidden="true">&rsaquo;</li>
				<li class="sc-breadcrumbs__item sc-breadcrumbs__item--current" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">
					<span itemprop="name"><?php the_title(); ?></span>
					<meta itemprop="position" content="3" />
				</li>
			</ol>
		</nav>

		<!-- Job Header Summary -->
		<div class="sc-job-hero__inner">
			<div class="sc-job-hero__content">
				<div class="sc-job-hero__tags">
					<?php if ( ! empty( $department ) ) : ?>
						<span class="sc-badge sc-badge--secondary"><?php echo esc_html( $department ); ?></span>
					<?php endif; ?>

					<?php if ( $is_closed ) : ?>
						<span class="sc-badge sc-badge--muted sc-badge--closed">
							<?php esc_html_e( 'Position Closed', 'spicecraft' ); ?>
						</span>
					<?php else : ?>
						<span class="sc-badge sc-badge--success">
							<span class="sc-status-dot"></span>
							<?php esc_html_e( 'Actively Recruiting', 'spicecraft' ); ?>
						</span>
					<?php endif; ?>
				</div>

				<h1 class="sc-job-hero__title"><?php the_title(); ?></h1>

				<!-- Meta Bar -->
				<div class="sc-job-hero__meta">
					<?php if ( ! empty( $location ) ) : ?>
						<div class="sc-job-hero__meta-item">
							<svg class="sc-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
							<span><strong><?php esc_html_e( 'Location:', 'spicecraft' ); ?></strong> <?php echo esc_html( $location ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $emp_label ) ) : ?>
						<div class="sc-job-hero__meta-item">
							<svg class="sc-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
							<span><strong><?php esc_html_e( 'Type:', 'spicecraft' ); ?></strong> <?php echo esc_html( $emp_label ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $experience ) ) : ?>
						<div class="sc-job-hero__meta-item">
							<svg class="sc-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
							<span><strong><?php esc_html_e( 'Experience:', 'spicecraft' ); ?></strong> <?php echo esc_html( $experience ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $openings ) && $openings > 1 ) : ?>
						<div class="sc-job-hero__meta-item">
							<svg class="sc-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
							<span><strong><?php esc_html_e( 'Openings:', 'spicecraft' ); ?></strong> <?php echo esc_html( $openings ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( $show_salary && ! empty( $salary ) ) : ?>
						<div class="sc-job-hero__meta-item">
							<svg class="sc-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
							<span><strong><?php esc_html_e( 'Compensation:', 'spicecraft' ); ?></strong> <?php echo esc_html( $salary ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $deadline ) ) : ?>
						<div class="sc-job-hero__meta-item">
							<svg class="sc-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
							<span><strong><?php esc_html_e( 'Deadline:', 'spicecraft' ); ?></strong> <span class="<?php echo $is_closed ? 'sc-deadline--expired' : ''; ?>"><?php echo esc_html( $deadline ); ?></span></span>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Quick Action / Status CTA -->
			<div class="sc-job-hero__cta">
				<?php if ( $is_closed ) : ?>
					<div class="sc-closed-pill" role="status">
						<svg class="sc-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
						<span><?php esc_html_e( 'Position Closed', 'spicecraft' ); ?></span>
					</div>
					<p class="sc-closed-subtext"><?php esc_html_e( 'This opening is no longer accepting new applications.', 'spicecraft' ); ?></p>
				<?php else : ?>
					<a href="#apply-now" class="sc-btn sc-btn--primary sc-btn--lg sc-scroll-trigger">
						<?php esc_html_e( 'Apply for This Position', 'spicecraft' ); ?> &darr;
					</a>
					<p class="sc-apply-subtext"><?php esc_html_e( 'Takes ~3 minutes &bull; Direct HR review', 'spicecraft' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

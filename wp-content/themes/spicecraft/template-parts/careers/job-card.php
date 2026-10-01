<?php
/**
 * Template part: Job Opening Card
 *
 * Displays a single job opening card on the Careers archive and listings.
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$job_id    = get_the_ID();
$meta      = function_exists( 'spicecraft_get_job_meta' ) ? spicecraft_get_job_meta( $job_id ) : array();
$settings  = function_exists( 'spicecraft_get_careers_settings' ) ? spicecraft_get_careers_settings() : array();
$permalink = get_permalink( $job_id );

$is_closed    = ! empty( $meta['is_closed'] );
$department   = ! empty( $meta['department'] ) ? $meta['department'] : '';
$location     = ! empty( $meta['location'] ) ? $meta['location'] : '';
$emp_label    = ! empty( $meta['employment_type_label'] ) ? $meta['employment_type_label'] : '';
$experience   = ! empty( $meta['experience'] ) ? $meta['experience'] : '';
$openings     = ! empty( $meta['openings'] ) ? $meta['openings'] : 1;
$deadline     = ! empty( $meta['deadline_formatted'] ) ? $meta['deadline_formatted'] : '';
$is_featured  = ! empty( $meta['is_featured'] );

// Check settings toggles
$show_dept = ! empty( $settings['show_department'] );
$show_loc  = ! empty( $settings['show_location'] );
$show_emp  = ! empty( $settings['show_employment_type'] );
$show_exp  = ! empty( $settings['show_experience'] );

$card_classes = array( 'sc-job-card' );
if ( $is_closed ) {
	$card_classes[] = 'sc-job-card--closed';
}
if ( $is_featured ) {
	$card_classes[] = 'sc-job-card--featured';
}

// Data attributes for client-side instant filtering
$data_dept = esc_attr( strtolower( $department ) );
$data_loc  = esc_attr( strtolower( $location ) );
$data_type = esc_attr( strtolower( $meta['employment_type'] ?? '' ) );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( implode( ' ', $card_classes ) ); ?> data-department="<?php echo $data_dept; ?>" data-location="<?php echo $data_loc; ?>" data-type="<?php echo $data_type; ?>" data-status="<?php echo $is_closed ? 'closed' : 'active'; ?>">
	<div class="sc-job-card__header">
		<div class="sc-job-card__tags">
			<?php if ( $is_featured && ! $is_closed ) : ?>
				<span class="sc-badge sc-badge--accent sc-job-badge--featured">
					<span class="dashicons dashicons-star-filled" style="font-size: 13px; width: 13px; height: 13px; margin-top: -2px;"></span>
					<?php esc_html_e( 'Featured', 'spicecraft' ); ?>
				</span>
			<?php endif; ?>

			<?php if ( $show_dept && ! empty( $department ) ) : ?>
				<span class="sc-badge sc-badge--secondary sc-job-badge--dept">
					<?php echo esc_html( $department ); ?>
				</span>
			<?php endif; ?>

			<?php if ( $is_closed ) : ?>
				<span class="sc-badge sc-badge--muted sc-job-badge--closed">
					<?php esc_html_e( 'Position Closed', 'spicecraft' ); ?>
				</span>
			<?php else : ?>
				<span class="sc-badge sc-badge--success sc-job-badge--active">
					<span class="sc-status-dot"></span>
					<?php esc_html_e( 'Actively Hiring', 'spicecraft' ); ?>
				</span>
			<?php endif; ?>
		</div>

		<h3 class="sc-job-card__title">
			<a href="<?php echo esc_url( $permalink ); ?>" rel="bookmark">
				<?php the_title(); ?>
			</a>
		</h3>
	</div>

	<!-- Meta Attributes Row -->
	<div class="sc-job-card__meta">
		<?php if ( $show_loc && ! empty( $location ) ) : ?>
			<span class="sc-job-card__meta-item" title="<?php esc_attr_e( 'Location', 'spicecraft' ); ?>">
				<svg class="sc-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
				<span><?php echo esc_html( $location ); ?></span>
			</span>
		<?php endif; ?>

		<?php if ( $show_emp && ! empty( $emp_label ) ) : ?>
			<span class="sc-job-card__meta-item" title="<?php esc_attr_e( 'Employment Type', 'spicecraft' ); ?>">
				<svg class="sc-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
				<span><?php echo esc_html( $emp_label ); ?></span>
			</span>
		<?php endif; ?>

		<?php if ( $show_exp && ! empty( $experience ) ) : ?>
			<span class="sc-job-card__meta-item" title="<?php esc_attr_e( 'Experience Required', 'spicecraft' ); ?>">
				<svg class="sc-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
				<span><?php echo esc_html( $experience ); ?></span>
			</span>
		<?php endif; ?>

		<?php if ( ! empty( $openings ) && $openings > 1 ) : ?>
			<span class="sc-job-card__meta-item" title="<?php esc_attr_e( 'Number of Openings', 'spicecraft' ); ?>">
				<svg class="sc-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
				<span><?php printf( esc_html__( '%d Openings', 'spicecraft' ), $openings ); ?></span>
			</span>
		<?php endif; ?>
	</div>

	<!-- Short Summary -->
	<div class="sc-job-card__excerpt">
		<?php
		$excerpt = get_the_excerpt();
		if ( ! empty( $excerpt ) ) {
			echo wp_kses_post( wp_trim_words( $excerpt, 28, '&hellip;' ) );
		} else {
			echo wp_kses_post( wp_trim_words( get_the_content(), 28, '&hellip;' ) );
		}
		?>
	</div>

	<!-- Key Skills Chips (top 3) -->
	<?php if ( ! empty( $meta['skills'] ) && is_array( $meta['skills'] ) ) : ?>
		<div class="sc-job-card__skills" aria-label="<?php esc_attr_e( 'Key Skills', 'spicecraft' ); ?>">
			<?php
			$display_skills = array_slice( $meta['skills'], 0, 4 );
			foreach ( $display_skills as $sk ) :
				?>
				<span class="sc-skill-chip"><?php echo esc_html( $sk ); ?></span>
			<?php endforeach; ?>
			<?php if ( count( $meta['skills'] ) > 4 ) : ?>
				<span class="sc-skill-chip sc-skill-chip--more">+<?php echo count( $meta['skills'] ) - 4; ?></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<!-- Card Footer -->
	<div class="sc-job-card__footer">
		<div class="sc-job-card__deadline">
			<?php if ( ! empty( $deadline ) ) : ?>
				<span class="sc-deadline-label"><?php esc_html_e( 'Deadline:', 'spicecraft' ); ?></span>
				<span class="sc-deadline-date <?php echo $is_closed ? 'sc-deadline--expired' : ''; ?>">
					<?php echo esc_html( $deadline ); ?>
				</span>
			<?php else : ?>
				<span class="sc-deadline-open"><?php esc_html_e( 'Applications Open', 'spicecraft' ); ?></span>
			<?php endif; ?>
		</div>

		<div class="sc-job-card__action">
			<?php if ( $is_closed ) : ?>
				<a href="<?php echo esc_url( $permalink ); ?>" class="sc-btn sc-btn--outline sc-btn--sm sc-btn--muted">
					<?php esc_html_e( 'View Details', 'spicecraft' ); ?> &rarr;
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( $permalink ); ?>" class="sc-btn sc-btn--primary sc-btn--sm">
					<?php esc_html_e( 'View Position & Apply', 'spicecraft' ); ?> &rarr;
				</a>
			<?php endif; ?>
		</div>
	</div>
</article>

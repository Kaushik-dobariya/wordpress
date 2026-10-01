<?php
/**
 * Template part: Careers Empty State
 *
 * Rendered when no open positions are found or when search/filters return zero results.
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = function_exists( 'spicecraft_get_careers_settings' ) ? spicecraft_get_careers_settings() : array();
$hr_email = ! empty( $settings['contact_hr_email'] ) ? $settings['contact_hr_email'] : spicecraft_get_careers_profile_email();
?>

<div class="sc-careers-empty-state" id="sc-careers-no-results">
	<div class="sc-careers-empty-state__icon" aria-hidden="true">
		<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
			<circle cx="11" cy="11" r="8"/>
			<line x1="21" y1="21" x2="16.65" y2="16.65"/>
			<line x1="8" y1="11" x2="14" y2="11"/>
		</svg>
	</div>
	<h3 class="sc-careers-empty-state__title">
		<?php esc_html_e( 'No Matching Positions Found', 'spicecraft' ); ?>
	</h3>
	<p class="sc-careers-empty-state__desc">
		<?php esc_html_e( 'We couldn\'t find any openings matching your selected criteria. Try adjusting your filters or send your profile directly to our talent acquisition team.', 'spicecraft' ); ?>
	</p>
	<div class="sc-careers-empty-state__actions">
		<a href="<?php echo esc_url( spicecraft_get_careers_url() ); ?>#open-positions" class="sc-btn sc-btn--outline sc-btn--sm">
			<?php esc_html_e( 'Clear All Filters', 'spicecraft' ); ?>
		</a>
		<a href="mailto:<?php echo esc_attr( $hr_email ); ?>?subject=<?php echo esc_attr( rawurlencode( 'General Application / Talent Profile' ) ); ?>" class="sc-btn sc-btn--primary sc-btn--sm">
			<?php esc_html_e( 'Email Your Resume to HR', 'spicecraft' ); ?> &rarr;
		</a>
	</div>
</div>

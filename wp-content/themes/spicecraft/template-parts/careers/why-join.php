<?php
/**
 * Template part: Careers Why Join Us / Culture Section
 *
 * Displays culture pillars, company values, and employment benefits.
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = function_exists( 'spicecraft_get_careers_settings' ) ? spicecraft_get_careers_settings() : array();
$title    = ! empty( $settings['why_join_title'] ) ? $settings['why_join_title'] : __( 'Why Build Your Career at SpiceCraft?', 'spicecraft' );
$subtitle = ! empty( $settings['why_join_subtitle'] ) ? $settings['why_join_subtitle'] : __( 'We unite four decades of traditional Indian spice craftsmanship with cutting-edge cryo-milling facilities, ethical farm partnerships, and an uncompromising commitment to purity.', 'spicecraft' );
$points   = ! empty( $settings['culture_points'] ) && is_array( $settings['culture_points'] ) ? $settings['culture_points'] : array();

if ( empty( $points ) ) {
	return;
}
?>

<section id="why-join-us" class="sc-careers-culture" aria-labelledby="careers-culture-heading">
	<div class="sc-container">
		<div class="sc-section-header sc-section-header--center">
			<span class="sc-badge sc-badge--secondary"><?php esc_html_e( 'Our Culture & People', 'spicecraft' ); ?></span>
			<h2 id="careers-culture-heading" class="sc-section-title"><?php echo esc_html( $title ); ?></h2>
			<?php if ( ! empty( $subtitle ) ) : ?>
				<p class="sc-section-subtitle"><?php echo nl2br( esc_html( $subtitle ) ); ?></p>
			<?php endif; ?>
		</div>

		<div class="sc-culture-grid">
			<?php foreach ( $points as $idx => $pt ) :
				$pt_title = ! empty( $pt['title'] ) ? $pt['title'] : '';
				$pt_desc  = ! empty( $pt['description'] ) ? $pt['description'] : '';
				$pt_icon  = ! empty( $pt['icon'] ) ? $pt['icon'] : 'star';
				if ( empty( $pt_title ) && empty( $pt_desc ) ) {
					continue;
				}
				?>
				<div class="sc-culture-card">
					<div class="sc-culture-card__icon" aria-hidden="true">
						<?php
						switch ( $pt_icon ) {
							case 'shield':
								echo '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>';
								break;
							case 'leaf':
								echo '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>';
								break;
							case 'award':
								echo '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>';
								break;
							case 'heart':
								echo '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>';
								break;
							default:
								echo '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
								break;
						}
						?>
					</div>
					<h3 class="sc-culture-card__title"><?php echo esc_html( $pt_title ); ?></h3>
					<p class="sc-culture-card__desc"><?php echo nl2br( esc_html( $pt_desc ) ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

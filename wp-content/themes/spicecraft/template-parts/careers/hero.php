<?php
/**
 * Template part: Careers Hero Section
 *
 * Displays rich hero with badge, H1, description, key stats, and optional hero image.
 *
 * @package SpiceCraft
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings   = function_exists( 'spicecraft_get_careers_settings' ) ? spicecraft_get_careers_settings() : array();
$badge      = ! empty( $settings['hero_badge'] ) ? $settings['hero_badge'] : __( 'We Are Hiring', 'spicecraft' );
$title      = ! empty( $settings['hero_title'] ) ? $settings['hero_title'] : __( 'Craft Your Career With Heritage & Innovation', 'spicecraft' );
$subtitle   = ! empty( $settings['hero_subtitle'] ) ? $settings['hero_subtitle'] : __( 'Join India\'s premier artisanal spice manufacturer. Explore opportunities across blending, production, food science, supply chain, and global exports.', 'spicecraft' );
$image_id   = ! empty( $settings['hero_image_id'] ) ? absint( $settings['hero_image_id'] ) : 0;
$image_url  = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
?>

<section class="sc-careers-hero" aria-labelledby="careers-hero-title">
	<div class="sc-container">
		<div class="sc-careers-hero__inner">
			<div class="sc-careers-hero__content">
				<?php if ( ! empty( $badge ) ) : ?>
					<div class="sc-careers-hero__badge-wrap">
						<span class="sc-badge sc-badge--accent sc-careers-hero__badge">
							<span class="sc-pulse-dot" aria-hidden="true"></span>
							<?php echo esc_html( $badge ); ?>
						</span>
					</div>
				<?php endif; ?>

				<h1 id="careers-hero-title" class="sc-careers-hero__title">
					<?php echo esc_html( $title ); ?>
				</h1>

				<p class="sc-careers-hero__subtitle">
					<?php echo nl2br( esc_html( $subtitle ) ); ?>
				</p>

				<div class="sc-careers-hero__actions">
					<a href="#open-positions" class="sc-btn sc-btn--primary sc-btn--lg">
						<?php esc_html_e( 'Explore Open Positions', 'spicecraft' ); ?> &darr;
					</a>
					<a href="#why-join-us" class="sc-btn sc-btn--outline sc-btn--lg">
						<?php esc_html_e( 'Our Culture & Values', 'spicecraft' ); ?>
					</a>
				</div>

				<!-- Quick Highlights Bar -->
				<div class="sc-careers-hero__stats" role="list">
					<div class="sc-stat-pill" role="listitem">
						<span class="sc-stat-pill__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
						</span>
						<div class="sc-stat-pill__text">
							<strong><?php esc_html_e( 'Cryo-Milling', 'spicecraft' ); ?></strong>
							<span><?php esc_html_e( 'World-Class Facility', 'spicecraft' ); ?></span>
						</div>
					</div>
					<div class="sc-stat-pill" role="listitem">
						<span class="sc-stat-pill__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
						</span>
						<div class="sc-stat-pill__text">
							<strong><?php esc_html_e( '25+ Export Markets', 'spicecraft' ); ?></strong>
							<span><?php esc_html_e( 'Global Career Scope', 'spicecraft' ); ?></span>
						</div>
					</div>
					<div class="sc-stat-pill" role="listitem">
						<span class="sc-stat-pill__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
						</span>
						<div class="sc-stat-pill__text">
							<strong><?php esc_html_e( 'Equal Opportunity', 'spicecraft' ); ?></strong>
							<span><?php esc_html_e( 'Inclusive & Supportive', 'spicecraft' ); ?></span>
						</div>
					</div>
				</div>
			</div>

			<?php if ( $image_url ) : ?>
				<div class="sc-careers-hero__media">
					<div class="sc-careers-hero__image-card">
						<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="sc-careers-hero__img" loading="eager" />
						<div class="sc-careers-hero__image-tag">
							<span class="dashicons dashicons-location"></span>
							<span><?php esc_html_e( 'Ahmedabad & Kochi Facilities', 'spicecraft' ); ?></span>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

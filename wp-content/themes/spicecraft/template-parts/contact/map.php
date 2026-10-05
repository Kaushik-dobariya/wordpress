<?php
/**
 * SpiceCraft Contact Page - Interactive Location & Map Section
 *
 * Displays a responsive, secure map container using configured CMS embed URL
 * or latitude/longitude coordinates, alongside verified campus addresses and
 * directions buttons. Zero API key exposure.
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$map_data = function_exists( 'spicecraft_get_contact_map_data' )
	? spicecraft_get_contact_map_data()
	: array(
		'embed_url'       => '',
		'latitude'        => '10.0159',
		'longitude'       => '76.3419',
		'zoom'            => '14',
		'title'           => __( 'SpiceCraft Campus', 'spicecraft' ),
		'address_primary' => '',
		'address_factory' => '',
		'directions_url'  => 'https://maps.google.com',
	);

$embed_src = '';
if ( ! empty( $map_data['embed_url'] ) ) {
	$embed_src = $map_data['embed_url'];
} elseif ( ! empty( $map_data['latitude'] ) && ! empty( $map_data['longitude'] ) ) {
	// Secure OpenStreetMap / Google search embed with zero exposed API keys
	$lat = rawurlencode( $map_data['latitude'] );
	$lng = rawurlencode( $map_data['longitude'] );
	$zoom = absint( $map_data['zoom'] ?: 14 );
	$embed_src = 'https://maps.google.com/maps?q=' . $lat . ',' . $lng . '&hl=en&z=' . $zoom . '&output=embed';
}
?>

<section class="sc-contact-map-section" id="contact-map" aria-labelledby="contact-map-heading">
	<div class="sc-container">
		<header class="sc-section-header sc-section-header--center">
			<span class="sc-eyebrow"><?php esc_html_e( 'Geographic Presence', 'spicecraft' ); ?></span>
			<h2 id="contact-map-heading" class="sc-section-title"><?php esc_html_e( 'Our Location & Infrastructure', 'spicecraft' ); ?></h2>
			<p class="sc-section-subtitle">
				<?php esc_html_e( 'Strategically situated with immediate access to major spice growing tracts, container terminals, and international shipping corridors.', 'spicecraft' ); ?>
			</p>
		</header>

		<div class="sc-contact-map-wrapper">
			<!-- Interactive Map Frame -->
			<div class="sc-contact-map__frame-wrap">
				<?php if ( ! empty( $embed_src ) ) : ?>
					<iframe 
						class="sc-contact-map__iframe"
						src="<?php echo esc_url( $embed_src ); ?>" 
						width="100%" 
						height="460" 
						style="border:0;" 
						allowfullscreen="" 
						loading="lazy" 
						referrerpolicy="no-referrer-when-downgrade"
						title="<?php echo esc_attr( $map_data['title'] ); ?>">
					</iframe>
				<?php else : ?>
					<div class="sc-contact-map__placeholder">
						<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
							<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
							<circle cx="12" cy="10" r="3"></circle>
						</svg>
						<p><?php esc_html_e( 'Interactive Map View', 'spicecraft' ); ?></p>
					</div>
				<?php endif; ?>
			</div>

			<!-- Location Details Card Alongside Map -->
			<div class="sc-contact-map__details-card">
				<div class="sc-contact-map__details-header">
					<span class="sc-badge sc-badge--pure"><?php esc_html_e( 'Manufacturing Hub', 'spicecraft' ); ?></span>
					<h3 class="sc-contact-map__details-title"><?php echo esc_html( $map_data['title'] ); ?></h3>
				</div>

				<?php if ( ! empty( $map_data['address_primary'] ) ) : ?>
					<div class="sc-contact-map__detail-block">
						<span class="sc-contact-map__detail-label"><?php esc_html_e( 'Headquarters Address', 'spicecraft' ); ?></span>
						<p class="sc-contact-map__detail-text"><?php echo nl2br( esc_html( $map_data['address_primary'] ) ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $map_data['address_factory'] ) ) : ?>
					<div class="sc-contact-map__detail-block">
						<span class="sc-contact-map__detail-label"><?php esc_html_e( 'Plant & Processing Address', 'spicecraft' ); ?></span>
						<p class="sc-contact-map__detail-text"><?php echo nl2br( esc_html( $map_data['address_factory'] ) ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $map_data['latitude'] ) && ! empty( $map_data['longitude'] ) ) : ?>
					<div class="sc-contact-map__coords">
						<span class="sc-contact-map__coord-item">
							<strong><?php esc_html_e( 'Lat:', 'spicecraft' ); ?></strong> <?php echo esc_html( $map_data['latitude'] ); ?>
						</span>
						<span class="sc-contact-map__coord-item">
							<strong><?php esc_html_e( 'Long:', 'spicecraft' ); ?></strong> <?php echo esc_html( $map_data['longitude'] ); ?>
						</span>
					</div>
				<?php endif; ?>

				<div class="sc-contact-map__actions">
					<a href="<?php echo esc_url( $map_data['directions_url'] ); ?>" class="sc-btn sc-btn--primary sc-btn--full" target="_blank" rel="noopener noreferrer">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
						</svg>
						<span><?php esc_html_e( 'Get Directions on Google Maps', 'spicecraft' ); ?> &rarr;</span>
					</a>
				</div>
			</div>
		</div>
	</div>
</section>

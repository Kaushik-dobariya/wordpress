<?php
/**
 * Homepage Template Part: Product Certifications
 * Semantic ID: #certifications
 *
 * Consumes legitimate terms from the `spicecraft_certification` taxonomy.
 * Suppresses itself cleanly if no certifications exist in the database.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cert_settings = function_exists( 'spicecraft_get_homepage_section' )
	? spicecraft_get_homepage_section( 'certifications' )
	: array();

$eyebrow      = ! empty( $cert_settings['eyebrow'] ) ? $cert_settings['eyebrow'] : '';
$heading      = ! empty( $cert_settings['heading'] ) ? $cert_settings['heading'] : '';
$description  = ! empty( $cert_settings['description'] ) ? $cert_settings['description'] : '';
$limit        = ! empty( $cert_settings['limit'] ) ? absint( $cert_settings['limit'] ) : 6;
$selected_ids = ! empty( $cert_settings['selected_ids'] ) ? array_map( 'absint', (array) $cert_settings['selected_ids'] ) : array();

$term_args = array(
	'taxonomy'   => 'spicecraft_certification',
	'hide_empty' => false,
	'number'     => $limit,
);

if ( ! empty( $selected_ids ) ) {
	$term_args['include'] = $selected_ids;
	unset( $term_args['number'] );
}

$certs = get_terms( $term_args );

// Graceful empty state: If no terms exist, omit section entirely
if ( empty( $certs ) || is_wp_error( $certs ) ) {
	return;
}
?>

<section id="certifications" class="sc-home-section sc-home-certifications" aria-labelledby="sec-heading-certs">
	<div class="sc-container">
		<header class="sc-section-header sc-section-header--center">
			<?php if ( ! empty( $eyebrow ) ) : ?>
				<p class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $heading ) ) : ?>
				<h2 id="sec-heading-certs" class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
			<?php else : ?>
				<h2 id="sec-heading-certs" class="sc-section-title"><?php esc_html_e( 'Statutory & Quality Accreditations', 'spicecraft' ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $description ) ) : ?>
				<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</header>

		<div class="sc-certs-grid">
			<?php foreach ( $certs as $cert_term ) :
				$badge_id    = get_term_meta( $cert_term->term_id, '_sc_cert_badge_id', true );
				$cert_number = get_term_meta( $cert_term->term_id, '_sc_cert_number', true );
				?>
				<div class="sc-cert-item">
					<div class="sc-cert-icon-box">
						<?php if ( ! empty( $badge_id ) ) : ?>
							<?php echo wp_get_attachment_image( absint( $badge_id ), 'thumbnail', false, array( 'class' => 'sc-cert-badge-img', 'alt' => $cert_term->name ) ); ?>
						<?php else : ?>
							<svg class="sc-cert-seal-svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
								<circle cx="12" cy="8" r="7"/>
								<polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>
							</svg>
						<?php endif; ?>
					</div>
					<div class="sc-cert-info">
						<h3 class="sc-cert-name"><?php echo esc_html( $cert_term->name ); ?></h3>
						<?php if ( ! empty( $cert_number ) ) : ?>
							<span class="sc-cert-number"><?php echo esc_html( $cert_number ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

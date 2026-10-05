<?php
/**
 * SpiceCraft Reusable Component: Certifications & Statutory Standards Strip
 *
 * Parameters:
 * - heading (string)
 * - eyebrow (string)
 * - description (string)
 * - limit (int, default: 6)
 * - include (array of term IDs)
 * - theme ('light'|'dark', default: 'light')
 * - show_number (bool, default: true)
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading     = ! empty( $args['heading'] ) ? $args['heading'] : '';
$eyebrow     = ! empty( $args['eyebrow'] ) ? $args['eyebrow'] : '';
$description = ! empty( $args['description'] ) ? $args['description'] : '';
$limit       = ! empty( $args['limit'] ) ? absint( $args['limit'] ) : 6;
$include     = ! empty( $args['include'] ) && is_array( $args['include'] ) ? array_map( 'absint', $args['include'] ) : array();
$theme       = ! empty( $args['theme'] ) && 'dark' === $args['theme'] ? 'dark' : 'light';
$show_number = ! isset( $args['show_number'] ) || ! empty( $args['show_number'] );

$custom_items = ! empty( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();

$query_args = array(
	'number' => $limit,
);

if ( ! empty( $include ) ) {
	$query_args['include'] = $include;
	unset( $query_args['number'] );
}

$certs = function_exists( 'spicecraft_get_public_certifications' )
	? spicecraft_get_public_certifications( $query_args )
	: array();

if ( empty( $certs ) && empty( $custom_items ) ) {
	return;
}
?>
<section class="sc-comp-cert-strip sc-comp-cert-strip--<?php echo esc_attr( $theme ); ?>">
	<div class="sc-container">
		<?php if ( $heading || $eyebrow || $description ) : ?>
			<header class="sc-section-header sc-section-header--center">
				<?php if ( $eyebrow ) : ?>
					<p class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( $description ) : ?>
					<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</header>
		<?php endif; ?>

		<div class="sc-certs-grid">
			<?php if ( ! empty( $certs ) ) : ?>
				<?php foreach ( $certs as $cert_term ) :
					$cert_meta   = function_exists( 'spicecraft_get_certification_meta' ) ? spicecraft_get_certification_meta( $cert_term->term_id ) : array();
					$badge_id    = absint( $cert_meta['logo_id'] ?? 0 );
					$cert_number = ! empty( $cert_meta['certificate_number'] ) ? $cert_meta['certificate_number'] : '';
					$has_detail  = function_exists( 'spicecraft_has_certification_public_detail' ) ? spicecraft_has_certification_public_detail( $cert_term->term_id ) : true;
					$term_url    = get_term_link( $cert_term );
					?>
					<div class="sc-cert-item">
						<div class="sc-cert-icon-box">
							<?php if ( ! empty( $badge_id ) ) : ?>
								<?php echo wp_get_attachment_image( $badge_id, 'thumbnail', false, array( 'class' => 'sc-cert-badge-img', 'alt' => esc_attr( $cert_term->name ) ) ); ?>
							<?php else : ?>
								<svg class="sc-cert-seal-svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
									<circle cx="12" cy="8" r="7"/>
									<polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>
								</svg>
							<?php endif; ?>
						</div>
						<div class="sc-cert-info">
							<h3 class="sc-cert-name">
								<?php if ( $has_detail && ! is_wp_error( $term_url ) ) : ?>
									<a href="<?php echo esc_url( $term_url ); ?>" class="sc-cert-name__link">
										<?php echo esc_html( $cert_term->name ); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html( $cert_term->name ); ?>
								<?php endif; ?>
							</h3>
							<?php if ( $show_number && ! empty( $cert_number ) ) : ?>
								<span class="sc-cert-number"><?php echo esc_html( $cert_number ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php elseif ( ! empty( $custom_items ) ) : ?>
				<?php foreach ( $custom_items as $item ) :
					$i_name   = ! empty( $item['name'] ) ? $item['name'] : ( ! empty( $item['title'] ) ? $item['title'] : '' );
					$i_number = ! empty( $item['number'] ) ? $item['number'] : '';
					$i_image  = ! empty( $item['image'] ) ? $item['image'] : '';
					?>
					<div class="sc-cert-item">
						<div class="sc-cert-icon-box">
							<?php if ( $i_image ) : ?>
								<img src="<?php echo esc_url( $i_image ); ?>" class="sc-cert-badge-img" alt="<?php echo esc_attr( $i_name ); ?>" loading="lazy" />
							<?php else : ?>
								<svg class="sc-cert-seal-svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
									<circle cx="12" cy="8" r="7"/>
									<polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>
								</svg>
							<?php endif; ?>
						</div>
						<div class="sc-cert-info">
							<h3 class="sc-cert-name"><?php echo esc_html( $i_name ); ?></h3>
							<?php if ( $show_number && $i_number ) : ?>
								<span class="sc-cert-number"><?php echo esc_html( $i_number ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</section>

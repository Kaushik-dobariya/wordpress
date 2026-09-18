<?php
/**
 * Certifications Section: Detail Hero
 *
 * Primary identity header for an individual certification.
 * Features the official name, short acronym, issuing authority,
 * accessible status badge, and official logo mark.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term         = $args['term'] ?? get_queried_object();
$meta         = $args['meta'] ?? array();
$status       = $args['status'] ?? ( $meta['status'] ?? 'active' );
$status_label = $args['status_label'] ?? ucfirst( $status );

if ( ! ( $term instanceof WP_Term ) ) {
	return;
}

$logo_id    = absint( $meta['logo_id'] ?? 0 );
$short_name = ! empty( $meta['short_name'] ) ? $meta['short_name'] : '';
$authority  = ! empty( $meta['issuing_authority'] ) ? $meta['issuing_authority'] : '';
$body       = ! empty( $meta['accreditation_body'] ) ? $meta['accreditation_body'] : '';
?>

<section class="sc-cert-detail-hero" aria-label="<?php echo esc_attr( $term->name ); ?>">
	<div class="sc-container">
		<div class="sc-cert-detail-hero__inner">
			<div class="sc-cert-detail-hero__content">
				<div class="sc-cert-detail-hero__badges">
					<span class="sc-cert-badge sc-cert-badge--lg sc-cert-badge--<?php echo esc_attr( $status ); ?>">
						<span class="sc-cert-badge__dot" aria-hidden="true"></span>
						<span class="sc-cert-badge__text"><?php echo esc_html( $status_label ); ?></span>
					</span>

					<?php if ( ! empty( $short_name ) ) : ?>
						<span class="sc-cert-acronym-pill"><?php echo esc_html( $short_name ); ?></span>
					<?php endif; ?>
				</div>

				<h1 class="sc-cert-detail-hero__title"><?php echo esc_html( $term->name ); ?></h1>

				<?php if ( ! empty( $authority ) || ! empty( $body ) ) : ?>
					<p class="sc-cert-detail-hero__authority">
						<?php if ( ! empty( $authority ) ) : ?>
							<span><?php esc_html_e( 'Issued by:', 'spicecraft' ); ?> <strong><?php echo esc_html( $authority ); ?></strong></span>
						<?php endif; ?>
						<?php if ( ! empty( $body ) ) : ?>
							<span class="sc-cert-detail-hero__sep" aria-hidden="true">•</span>
							<span><?php esc_html_e( 'Accredited by:', 'spicecraft' ); ?> <strong><?php echo esc_html( $body ); ?></strong></span>
						<?php endif; ?>
					</p>
				<?php endif; ?>

				<?php if ( ! empty( $term->description ) ) : ?>
					<div class="sc-cert-detail-hero__description">
						<p><?php echo nl2br( esc_html( $term->description ) ); ?></p>
					</div>
				<?php endif; ?>
			</div>

			<div class="sc-cert-detail-hero__logo-box">
				<?php if ( $logo_id ) : ?>
					<?php
					echo wp_get_attachment_image(
						$logo_id,
						'medium',
						false,
						array(
							'class'         => 'sc-cert-detail-hero__logo',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'alt'           => sprintf( esc_attr__( '%s Certification Logo', 'spicecraft' ), $term->name ),
						)
					);
					?>
				<?php else : ?>
					<div class="sc-cert-detail-hero__logo-fallback" aria-hidden="true">
						<span class="dashicons dashicons-awards"></span>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

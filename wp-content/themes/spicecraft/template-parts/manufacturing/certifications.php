<?php
/**
 * Manufacturing Section: Certifications & Standards
 *
 * Consumes statutory food safety credentials from spicecraft_certification taxonomy.
 * Reuses existing certification architecture without duplication.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cert_sec = function_exists( 'spicecraft_get_manufacturing_section' )
	? spicecraft_get_manufacturing_section( 'certifications' )
	: array();

if ( empty( $cert_sec ) ) {
	return;
}

$eyebrow      = $cert_sec['eyebrow'] ?? '';
$heading      = $cert_sec['heading'] ?? '';
$description  = $cert_sec['description'] ?? '';
$selected_ids = $cert_sec['selected_ids'] ?? array();
$limit        = absint( $cert_sec['limit'] ?? 6 );
$cta_label    = $cert_sec['cta_label'] ?? '';
$cta_url      = $cert_sec['cta_url'] ?? '';

$query_args = array(
	'number' => $limit > 0 ? $limit : 6,
);

if ( ! empty( $selected_ids ) && is_array( $selected_ids ) ) {
	$query_args['include'] = array_filter( array_map( 'absint', $selected_ids ) );
	unset( $query_args['number'] );
}

$terms = function_exists( 'spicecraft_get_public_certifications' )
	? spicecraft_get_public_certifications( $query_args )
	: array();

if ( empty( $terms ) ) {
	return;
}
?>

<section id="sc-mfg-certifications" class="sc-mfg-certs sc-section" aria-label="<?php echo esc_attr( $heading ?: __( 'Manufacturing Accreditations & Standards', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<?php if ( ! empty( $eyebrow ) || ! empty( $heading ) || ! empty( $description ) ) : ?>
			<div class="sc-section-header text-center" style="max-width: 720px; margin: 0 auto var(--sc-space-10, 40px);">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $heading ) ) : ?>
					<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( ! empty( $description ) ) : ?>
					<p class="sc-section-desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="sc-certs-grid">
			<?php foreach ( $terms as $term ) :
				$cert_meta  = function_exists( 'spicecraft_get_certification_meta' ) ? spicecraft_get_certification_meta( $term->term_id ) : array();
				$logo_id    = absint( $cert_meta['logo_id'] ?? 0 );
				$desc       = term_description( $term->term_id, 'spicecraft_certification' );
				$has_detail = function_exists( 'spicecraft_has_certification_public_detail' ) ? spicecraft_has_certification_public_detail( $term->term_id ) : true;
				$term_url   = get_term_link( $term );
				?>
				<div class="sc-cert-badge sc-card text-center">
					<?php if ( ! empty( $logo_id ) ) : ?>
						<div class="sc-cert-badge__logo-wrap" style="margin-bottom: 12px;">
							<?php echo wp_get_attachment_image( $logo_id, 'thumbnail', false, array( 'class' => 'sc-cert-badge__logo', 'loading' => 'lazy', 'alt' => esc_attr( $term->name ) ) ); ?>
						</div>
					<?php else : ?>
						<div class="sc-cert-badge__icon-wrap" aria-hidden="true" style="margin-bottom: 12px;">
							<span class="dashicons dashicons-awards" style="font-size: 32px; color: var(--sc-color-secondary, #d97706);"></span>
						</div>
					<?php endif; ?>

					<h3 class="sc-cert-badge__name" style="font-size: 1.1rem; margin-bottom: 6px;">
						<?php if ( $has_detail && ! is_wp_error( $term_url ) ) : ?>
							<a href="<?php echo esc_url( $term_url ); ?>" class="sc-cert-name__link">
								<?php echo esc_html( $term->name ); ?>
							</a>
						<?php else : ?>
							<?php echo esc_html( $term->name ); ?>
						<?php endif; ?>
					</h3>

					<?php if ( ! empty( $desc ) ) : ?>
						<p class="sc-cert-badge__desc" style="font-size: 0.875rem; color: var(--sc-color-text-muted);"><?php echo esc_html( wp_strip_all_tags( $desc ) ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $cta_label ) && ! empty( $cta_url ) ) : ?>
			<div class="text-center" style="margin-top: var(--sc-space-8, 32px);">
				<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--secondary">
					<?php echo esc_html( $cta_label ); ?> &rarr;
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>

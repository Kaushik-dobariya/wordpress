<?php
/**
 * Manufacturing Section: Technology & Equipment
 *
 * Configured processing machinery cards with technical specification tables.
 * Stacked gracefully on mobile devices.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$equipment = function_exists( 'spicecraft_get_manufacturing_section' )
	? spicecraft_get_manufacturing_section( 'equipment' )
	: array();

if ( empty( $equipment ) || empty( $equipment['items'] ) || ! is_array( $equipment['items'] ) ) {
	return;
}

$eyebrow     = $equipment['eyebrow'] ?? '';
$heading     = $equipment['heading'] ?? '';
$description = $equipment['description'] ?? '';
$items       = $equipment['items'];
?>

<section id="sc-mfg-equipment" class="sc-mfg-equipment sc-section" aria-label="<?php echo esc_attr( $heading ?: __( 'Technology & Equipment', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-section-header text-center" style="max-width: 740px; margin: 0 auto var(--sc-space-12, 48px);">
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

		<div class="sc-mfg-equipment__grid">
			<?php foreach ( $items as $eq ) :
				$eq_name = $eq['name'] ?? '';
				$eq_desc = $eq['description'] ?? '';
				$eq_img  = absint( $eq['image_id'] ?? 0 );
				$specs   = $eq['specs'] ?? array();
				if ( empty( $eq_name ) && empty( $eq_desc ) ) continue;
				?>
				<div class="sc-mfg-equipment__card sc-card">
					<?php if ( $eq_img ) : ?>
						<div class="sc-mfg-equipment__card-media">
							<?php echo wp_get_attachment_image( $eq_img, 'medium_large', false, array( 'class' => 'sc-img-fluid sc-rounded', 'loading' => 'lazy' ) ); ?>
						</div>
					<?php endif; ?>

					<div class="sc-mfg-equipment__card-content">
						<?php if ( ! empty( $eq_name ) ) : ?>
							<h3 class="sc-mfg-equipment__card-title"><?php echo esc_html( $eq_name ); ?></h3>
						<?php endif; ?>

						<?php if ( ! empty( $eq_desc ) ) : ?>
							<p class="sc-mfg-equipment__card-desc"><?php echo nl2br( esc_html( $eq_desc ) ); ?></p>
						<?php endif; ?>

						<?php if ( ! empty( $specs ) && is_array( $specs ) ) : ?>
							<div class="sc-mfg-equipment__specs">
								<table class="sc-mfg-equipment__specs-table" role="presentation">
									<tbody>
										<?php foreach ( $specs as $sp ) :
											$sp_lbl = $sp['label'] ?? '';
											$sp_val = $sp['value'] ?? '';
											if ( empty( $sp_lbl ) && empty( $sp_val ) ) continue;
											?>
											<tr>
												<th scope="row" class="sc-mfg-equipment__spec-lbl"><?php echo esc_html( $sp_lbl ); ?></th>
												<td class="sc-mfg-equipment__spec-val"><?php echo esc_html( $sp_val ); ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

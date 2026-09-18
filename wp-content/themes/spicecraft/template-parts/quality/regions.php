<?php
/**
 * Quality & Sourcing Section: Sourcing Regions
 *
 * Editorial visual cards showcasing verified agricultural origins and agro-climatic zones.
 * No map dependencies; responsive cards with rich origin metadata.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$reg = function_exists( 'spicecraft_get_quality_section' )
	? spicecraft_get_quality_section( 'regions' )
	: array();

if ( empty( $reg ) || empty( $reg['items'] ) || ! is_array( $reg['items'] ) ) {
	return;
}

$eyebrow     = $reg['eyebrow'] ?? '';
$heading     = $reg['heading'] ?? '';
$description = $reg['description'] ?? '';
$items       = $reg['items'];
?>

<section id="sc-quality-regions" class="sc-quality-regions sc-section sc-section--alt" aria-label="<?php echo esc_attr( $heading ?: __( 'Sourcing Origins & Regions', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-section-header text-center" style="max-width: 760px; margin: 0 auto var(--sc-space-12, 48px);">
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

		<div class="sc-quality-regions__grid">
			<?php foreach ( $items as $r ) :
				$name    = $r['name'] ?? '';
				$state   = $r['state'] ?? '';
				$country = $r['country'] ?? '';
				$spice   = $r['ingredient'] ?? '';
				$desc    = $r['description'] ?? '';
				$img_id  = absint( $r['image_id'] ?? 0 );
				if ( empty( $name ) && empty( $spice ) ) continue;

				$location_parts = array_filter( array( $state, $country ) );
				$location_str   = implode( ', ', $location_parts );
				?>
				<div class="sc-quality-regions__card sc-card">
					<?php if ( $img_id ) : ?>
						<div class="sc-quality-regions__card-media">
							<?php echo wp_get_attachment_image( $img_id, 'medium_large', false, array( 'class' => 'sc-img-fluid sc-rounded', 'loading' => 'lazy' ) ); ?>
						</div>
					<?php endif; ?>

					<div class="sc-quality-regions__card-body">
						<div class="sc-quality-regions__card-header">
							<?php if ( ! empty( $spice ) ) : ?>
								<span class="sc-quality-regions__spice-tag"><?php echo esc_html( $spice ); ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $name ) ) : ?>
								<h3 class="sc-quality-regions__region-name"><?php echo esc_html( $name ); ?></h3>
							<?php endif; ?>
							<?php if ( ! empty( $location_str ) ) : ?>
								<p class="sc-quality-regions__location">
									<span class="dashicons dashicons-location" style="font-size: 14px; margin-top: -2px;"></span>
									<?php echo esc_html( $location_str ); ?>
								</p>
							<?php endif; ?>
						</div>

						<?php if ( ! empty( $desc ) ) : ?>
							<p class="sc-quality-regions__card-desc"><?php echo nl2br( esc_html( $desc ) ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

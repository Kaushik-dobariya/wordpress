<?php
/**
 * Quality & Sourcing Section: Food Safety Governance
 *
 * Food safety practices, allergen segregation, and regulatory hygiene.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$safety = function_exists( 'spicecraft_get_quality_section' )
	? spicecraft_get_quality_section( 'food_safety' )
	: array();

if ( empty( $safety ) || ( empty( $safety['heading'] ) && empty( $safety['description'] ) && empty( $safety['practices'] ) ) ) {
	return;
}

$eyebrow     = $safety['eyebrow'] ?? '';
$heading     = $safety['heading'] ?? '';
$description = $safety['description'] ?? '';
$image_id    = absint( $safety['image_id'] ?? 0 );
$practices   = $safety['practices'] ?? array();
?>

<section id="sc-quality-food-safety" class="sc-quality-food-safety sc-section" aria-label="<?php echo esc_attr( $heading ?: __( 'Food Safety Governance', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-quality-food-safety__grid">
			<div class="sc-quality-food-safety__content">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $description ) ) : ?>
					<p class="sc-section-desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $practices ) && is_array( $practices ) ) : ?>
					<div class="sc-quality-food-safety__practices" style="margin-top: var(--sc-space-6, 24px);">
						<?php foreach ( $practices as $pr ) :
							$pr_t = $pr['title'] ?? '';
							$pr_d = $pr['description'] ?? '';
							if ( empty( $pr_t ) && empty( $pr_d ) ) continue;
							?>
							<div class="sc-quality-food-safety__practice-card sc-card">
								<?php if ( ! empty( $pr_t ) ) : ?>
									<h3 class="sc-quality-food-safety__practice-title"><?php echo esc_html( $pr_t ); ?></h3>
								<?php endif; ?>
								<?php if ( ! empty( $pr_d ) ) : ?>
									<p class="sc-quality-food-safety__practice-desc"><?php echo esc_html( $pr_d ); ?></p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $image_id ) : ?>
				<div class="sc-quality-food-safety__media">
					<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'sc-img-fluid sc-rounded', 'loading' => 'lazy' ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

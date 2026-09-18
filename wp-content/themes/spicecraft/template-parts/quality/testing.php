<?php
/**
 * Quality & Sourcing Section: Testing & Laboratory Standards
 *
 * Analytical testing protocols and parameters.
 * Strict testing context badge displayed only when explicitly configured by admin.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$testing = function_exists( 'spicecraft_get_quality_section' )
	? spicecraft_get_quality_section( 'testing' )
	: array();

if ( empty( $testing ) || empty( $testing['items'] ) || ! is_array( $testing['items'] ) ) {
	return;
}

$eyebrow       = $testing['eyebrow'] ?? '';
$heading       = $testing['heading'] ?? '';
$description   = $testing['description'] ?? '';
$image_id      = absint( $testing['image_id'] ?? 0 );
$context_key   = $testing['testing_context'] ?? 'not_specified';
$context_label = function_exists( 'spicecraft_get_testing_context_label' )
	? spicecraft_get_testing_context_label( $context_key )
	: '';
$items         = $testing['items'];
?>

<section id="sc-quality-testing" class="sc-quality-testing sc-section sc-section--alt" aria-label="<?php echo esc_attr( $heading ?: __( 'Testing Protocols & Standards', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-section-header text-center" style="max-width: 760px; margin: 0 auto var(--sc-space-10, 40px);">
			<?php if ( ! empty( $eyebrow ) ) : ?>
				<span class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>

			<?php if ( ! empty( $heading ) ) : ?>
				<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $context_label ) ) : ?>
				<div class="sc-quality-testing__context-badge" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: rgba(217, 119, 6, 0.12); color: var(--sc-color-secondary, #d97706); border-radius: 20px; font-size: 0.875rem; font-weight: 600; margin: 8px 0 12px;">
					<span class="dashicons dashicons-clipboard"></span>
					<?php echo esc_html( $context_label ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $description ) ) : ?>
				<p class="sc-section-desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $image_id ) : ?>
			<div class="sc-quality-testing__featured-img text-center" style="margin-bottom: var(--sc-space-8, 32px);">
				<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'sc-img-fluid sc-rounded', 'loading' => 'lazy' ) ); ?>
			</div>
		<?php endif; ?>

		<div class="sc-quality-testing__grid">
			<?php foreach ( $items as $it ) :
				$name     = $it['name'] ?? '';
				$desc     = $it['description'] ?? '';
				$method   = $it['method'] ?? '';
				$standard = $it['standard'] ?? '';
				if ( empty( $name ) && empty( $desc ) ) continue;
				?>
				<div class="sc-quality-testing__card sc-card">
					<div class="sc-quality-testing__card-header">
						<?php if ( ! empty( $name ) ) : ?>
							<h3 class="sc-quality-testing__card-title"><?php echo esc_html( $name ); ?></h3>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $desc ) ) : ?>
						<p class="sc-quality-testing__card-desc"><?php echo nl2br( esc_html( $desc ) ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $method ) || ! empty( $standard ) ) : ?>
						<div class="sc-quality-testing__meta" style="margin-top: 12px; padding-top: 10px; border-top: 1px solid var(--sc-color-border, #e5e7eb); font-size: 0.85rem;">
							<?php if ( ! empty( $method ) ) : ?>
								<p style="margin: 0 0 4px; color: var(--sc-color-text-muted);">
									<strong><?php esc_html_e( 'Method:', 'spicecraft' ); ?></strong> <?php echo esc_html( $method ); ?>
								</p>
							<?php endif; ?>
							<?php if ( ! empty( $standard ) ) : ?>
								<p style="margin: 0; color: var(--sc-color-text-muted);">
									<strong><?php esc_html_e( 'Acceptance Standard:', 'spicecraft' ); ?></strong> <?php echo esc_html( $standard ); ?>
								</p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

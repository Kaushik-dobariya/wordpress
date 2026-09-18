<?php
/**
 * Manufacturing Section: Process Flow
 *
 * Premium structured step-by-step visual manufacturing flow.
 * Alternating/sequential layout on desktop, clean vertical rail on mobile.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$process = function_exists( 'spicecraft_get_manufacturing_section' )
	? spicecraft_get_manufacturing_section( 'process' )
	: array();

if ( empty( $process ) || empty( $process['items'] ) || ! is_array( $process['items'] ) ) {
	return;
}

$eyebrow     = $process['eyebrow'] ?? '';
$heading     = $process['heading'] ?? '';
$description = $process['description'] ?? '';
$items       = $process['items'];
?>

<section id="sc-mfg-process" class="sc-mfg-process sc-section" aria-label="<?php echo esc_attr( $heading ?: __( 'Manufacturing Process Flow', 'spicecraft' ) ); ?>">
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

		<div class="sc-mfg-process__flow">
			<?php foreach ( $items as $idx => $step ) :
				$step_num = ! empty( $step['step_number'] ) ? $step['step_number'] : sprintf( '%02d', $idx + 1 );
				$title    = $step['title'] ?? '';
				$desc     = $step['description'] ?? '';
				$img_id   = absint( $step['image_id'] ?? 0 );
				$is_even  = 0 === ( $idx % 2 );
				?>
				<div class="sc-mfg-process__step <?php echo $is_even ? 'sc-mfg-process__step--even' : 'sc-mfg-process__step--odd'; ?>">
					<div class="sc-mfg-process__node">
						<span class="sc-mfg-process__badge"><?php echo esc_html( $step_num ); ?></span>
						<div class="sc-mfg-process__line" aria-hidden="true"></div>
					</div>

					<div class="sc-mfg-process__card sc-card">
						<div class="sc-mfg-process__card-header">
							<span class="sc-mfg-process__step-tag"><?php printf( esc_html__( 'Stage %s', 'spicecraft' ), esc_html( $step_num ) ); ?></span>
							<?php if ( ! empty( $title ) ) : ?>
								<h3 class="sc-mfg-process__card-title"><?php echo esc_html( $title ); ?></h3>
							<?php endif; ?>
						</div>

						<?php if ( ! empty( $desc ) ) : ?>
							<p class="sc-mfg-process__card-desc"><?php echo nl2br( esc_html( $desc ) ); ?></p>
						<?php endif; ?>

						<?php if ( $img_id ) : ?>
							<div class="sc-mfg-process__card-media" style="margin-top: var(--sc-space-4, 16px);">
								<?php echo wp_get_attachment_image( $img_id, 'medium_large', false, array( 'class' => 'sc-img-fluid sc-rounded', 'loading' => 'lazy' ) ); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

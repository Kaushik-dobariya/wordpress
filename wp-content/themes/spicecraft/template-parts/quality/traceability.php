<?php
/**
 * Quality & Sourcing Section: Traceability Framework
 *
 * Batch tracking, lot coding, and record keeping.
 * Only rendered when explicitly configured by administrator.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$trace = function_exists( 'spicecraft_get_quality_section' )
	? spicecraft_get_quality_section( 'traceability' )
	: array();

if ( empty( $trace ) || ( empty( $trace['heading'] ) && empty( $trace['description'] ) && empty( $trace['steps'] ) ) ) {
	return;
}

$eyebrow     = $trace['eyebrow'] ?? '';
$heading     = $trace['heading'] ?? '';
$description = $trace['description'] ?? '';
$image_id    = absint( $trace['image_id'] ?? 0 );
$steps       = $trace['steps'] ?? array();
?>

<section id="sc-quality-traceability" class="sc-quality-traceability sc-section sc-section--alt" aria-label="<?php echo esc_attr( $heading ?: __( 'Traceability Framework', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-quality-traceability__grid">
			<?php if ( $image_id ) : ?>
				<div class="sc-quality-traceability__media">
					<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'sc-img-fluid sc-rounded', 'loading' => 'lazy' ) ); ?>
				</div>
			<?php endif; ?>

			<div class="sc-quality-traceability__content">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $description ) ) : ?>
					<p class="sc-section-desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $steps ) && is_array( $steps ) ) : ?>
					<div class="sc-quality-traceability__steps" style="margin-top: var(--sc-space-6, 24px);">
						<?php foreach ( $steps as $idx => $st ) :
							$step_num = ! empty( $st['step_number'] ) ? $st['step_number'] : sprintf( '%02d', $idx + 1 );
							$st_title = $st['title'] ?? '';
							$st_desc  = $st['description'] ?? '';
							if ( empty( $st_title ) && empty( $st_desc ) ) continue;
							?>
							<div class="sc-quality-traceability__step-item">
								<span class="sc-quality-traceability__step-num"><?php echo esc_html( $step_num ); ?></span>
								<div>
									<?php if ( ! empty( $st_title ) ) : ?>
										<h3 class="sc-quality-traceability__step-title"><?php echo esc_html( $st_title ); ?></h3>
									<?php endif; ?>
									<?php if ( ! empty( $st_desc ) ) : ?>
										<p class="sc-quality-traceability__step-desc"><?php echo esc_html( $st_desc ); ?></p>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

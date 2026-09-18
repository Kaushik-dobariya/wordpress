<?php
/**
 * About Section: Journey & Milestones
 *
 * Responsive, CSS-driven semantic timeline. Alternating on desktop, vertical on mobile.
 * Uses no external libraries.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ms_sec = function_exists( 'spicecraft_get_about_section' )
	? spicecraft_get_about_section( 'milestones' )
	: array();

if ( empty( $ms_sec ) ) {
	return;
}

$items = function_exists( 'spicecraft_get_about_milestones' )
	? spicecraft_get_about_milestones()
	: array();

if ( empty( $items ) ) {
	return;
}

$eyebrow     = $ms_sec['eyebrow'] ?? '';
$heading     = $ms_sec['heading'] ?? '';
$description = $ms_sec['description'] ?? '';
?>

<section id="journey-milestones" class="sc-about-milestones" aria-label="<?php echo esc_attr( ! empty( $heading ) ? $heading : __( 'Our Journey & Milestones', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<?php if ( ! empty( $eyebrow ) || ! empty( $heading ) || ! empty( $description ) ) : ?>
			<div class="sc-section-header sc-section-header--center" style="margin-bottom: var(--sc-space-16);">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-section-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $description ) ) : ?>
					<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="sc-timeline" role="list">
			<div class="sc-timeline__spine" aria-hidden="true"></div>
			<?php
			$counter = 0;
			foreach ( $items as $ms ) :
				$counter++;
				$is_even = 0 === $counter % 2;
				?>
				<div class="sc-timeline__node <?php echo $is_even ? 'sc-timeline__node--right' : 'sc-timeline__node--left'; ?>" role="listitem">
					<div class="sc-timeline__marker" aria-hidden="true">
						<span class="sc-timeline__dot"></span>
					</div>
					<div class="sc-timeline__content">
						<?php if ( ! empty( $ms['date_label'] ) ) : ?>
							<span class="sc-timeline__date"><?php echo esc_html( $ms['date_label'] ); ?></span>
						<?php endif; ?>

						<h3 class="sc-timeline__title"><?php echo esc_html( $ms['title'] ); ?></h3>

						<?php if ( ! empty( $ms['description'] ) ) : ?>
							<p class="sc-timeline__text"><?php echo nl2br( esc_html( $ms['description'] ) ); ?></p>
						<?php endif; ?>

						<?php if ( ! empty( $ms['image_id'] ) ) : ?>
							<div class="sc-timeline__media">
								<?php
								echo wp_get_attachment_image(
									$ms['image_id'],
									'medium',
									false,
									array(
										'class'   => 'sc-timeline__img',
										'loading' => 'lazy',
									)
								);
								?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

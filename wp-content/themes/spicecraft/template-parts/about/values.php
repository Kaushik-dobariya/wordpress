<?php
/**
 * About Section: Core Values
 *
 * Distinct numbered value progression (01, 02, 03...) featuring fine typographic
 * separators and generous whitespace rather than generic boxy cards.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$val_sec = function_exists( 'spicecraft_get_about_section' )
	? spicecraft_get_about_section( 'values' )
	: array();

if ( empty( $val_sec ) ) {
	return;
}

$items = function_exists( 'spicecraft_get_about_values' )
	? spicecraft_get_about_values()
	: array();

if ( empty( $items ) ) {
	return;
}

$eyebrow     = $val_sec['eyebrow'] ?? '';
$heading     = $val_sec['heading'] ?? '';
$description = $val_sec['description'] ?? '';
?>

<section id="core-values" class="sc-about-values" aria-label="<?php echo esc_attr( ! empty( $heading ) ? $heading : __( 'Core Values', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<?php if ( ! empty( $eyebrow ) || ! empty( $heading ) || ! empty( $description ) ) : ?>
			<div class="sc-section-header sc-section-header--center" style="margin-bottom: var(--sc-space-12);">
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

		<div class="sc-about-values__grid">
			<?php
			$count = 1;
			foreach ( $items as $item ) :
				$num_str = sprintf( '%02d', $count++ );
				?>
				<div class="sc-about-values__item">
					<div class="sc-about-values__top">
						<span class="sc-about-values__num"><?php echo esc_html( $num_str ); ?></span>
						<?php if ( ! empty( $item['icon'] ) ) : ?>
							<span class="sc-about-values__icon dashicons dashicons-<?php echo esc_attr( $item['icon'] ); ?>" aria-hidden="true"></span>
						<?php endif; ?>
					</div>
					<h3 class="sc-about-values__item-title"><?php echo esc_html( $item['title'] ); ?></h3>
					<?php if ( ! empty( $item['description'] ) ) : ?>
						<p class="sc-about-values__item-desc"><?php echo nl2br( esc_html( $item['description'] ) ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

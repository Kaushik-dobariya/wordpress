<?php
/**
 * Homepage Template Part: Why Choose Us
 * Semantic ID: #why-choose-us
 *
 * Consumes repeatable differentiators. Suppresses if no items configured.
 * Implements an editorial numbered strip with refined separator lines.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wcu = function_exists( 'spicecraft_get_homepage_section' )
	? spicecraft_get_homepage_section( 'why_choose_us' )
	: array();

$items = ! empty( $wcu['items'] ) && is_array( $wcu['items'] ) ? $wcu['items'] : array();

if ( empty( $items ) ) {
	return;
}

// Sort items by order
usort( $items, function ( $a, $b ) {
	$ord_a = isset( $a['order'] ) ? absint( $a['order'] ) : 10;
	$ord_b = isset( $b['order'] ) ? absint( $b['order'] ) : 10;
	return $ord_a <=> $ord_b;
} );

$eyebrow     = ! empty( $wcu['eyebrow'] ) ? $wcu['eyebrow'] : '';
$heading     = ! empty( $wcu['heading'] ) ? $wcu['heading'] : __( 'Why Choose SpiceCraft', 'spicecraft' );
$description = ! empty( $wcu['description'] ) ? $wcu['description'] : '';
?>

<section id="why-choose-us" class="sc-home-section sc-home-wcu" aria-labelledby="sec-heading-wcu">
	<div class="sc-container">
		<header class="sc-section-header sc-section-header--center">
			<?php if ( ! empty( $eyebrow ) ) : ?>
				<p class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<h2 id="sec-heading-wcu" class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>

			<?php if ( ! empty( $description ) ) : ?>
				<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</header>

		<div class="sc-wcu-strip">
			<?php
			$idx = 1;
			foreach ( $items as $item ) :
				$icon_slug = ! empty( $item['icon'] ) ? $item['icon'] : 'leaf';
				$title     = ! empty( $item['title'] ) ? $item['title'] : '';
				$desc      = ! empty( $item['description'] ) ? $item['description'] : '';
				if ( empty( $title ) && empty( $desc ) ) {
					continue;
				}
				$number_str = sprintf( '%02d', $idx );
				?>
				<article class="sc-wcu-item">
					<div class="sc-wcu-item__top">
						<span class="sc-wcu-number" aria-hidden="true"><?php echo esc_html( $number_str ); ?></span>
					</div>
					<div class="sc-wcu-item__content">
						<h3 class="sc-wcu-title"><?php echo esc_html( $title ); ?></h3>
						<?php if ( ! empty( $desc ) ) : ?>
							<p class="sc-wcu-desc"><?php echo esc_html( $desc ); ?></p>
						<?php endif; ?>
					</div>
				</article>
				<?php
				$idx++;
			endforeach;
			?>
		</div>
	</div>
</section>

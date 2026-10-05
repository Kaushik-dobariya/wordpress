<?php
/**
 * SpiceCraft Reusable Component: Hero
 *
 * Parameters:
 * - title (string)
 * - subtitle (string)
 * - eyebrow (string)
 * - badge (string)
 * - bg_image (string URL)
 * - cta_text (string)
 * - cta_url (string)
 * - cta_secondary_text (string)
 * - cta_secondary_url (string)
 * - alignment ('left'|'center', default: 'left')
 * - theme ('light'|'dark', default: 'light')
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title          = ! empty( $args['title'] ) ? $args['title'] : get_the_title();
$subtitle       = ! empty( $args['subtitle'] ) ? $args['subtitle'] : '';
$eyebrow        = ! empty( $args['eyebrow'] ) ? $args['eyebrow'] : '';
$badge          = ! empty( $args['badge'] ) ? $args['badge'] : '';
$bg_image       = ! empty( $args['bg_image'] ) ? $args['bg_image'] : '';
$cta_text       = ! empty( $args['cta_text'] ) ? $args['cta_text'] : '';
$cta_url        = ! empty( $args['cta_url'] ) ? $args['cta_url'] : '';
$cta_sec_text   = ! empty( $args['cta_secondary_text'] ) ? $args['cta_secondary_text'] : '';
$cta_sec_url    = ! empty( $args['cta_secondary_url'] ) ? $args['cta_secondary_url'] : '';
$alignment      = ! empty( $args['alignment'] ) && 'center' === $args['alignment'] ? 'center' : 'left';
$theme          = ! empty( $args['theme'] ) && 'dark' === $args['theme'] ? 'dark' : 'light';

$style_attr = '';
if ( $bg_image ) {
	$style_attr = 'style="background-image: linear-gradient(' . ( 'dark' === $theme ? 'rgba(31,29,29,0.85), rgba(31,29,29,0.85)' : 'rgba(250,247,242,0.92), rgba(250,247,242,0.92)' ) . '), url(' . esc_url( $bg_image ) . '); background-size: cover; background-position: center;"';
}
?>
<section class="sc-comp-hero sc-comp-hero--<?php echo esc_attr( $alignment ); ?> sc-comp-hero--<?php echo esc_attr( $theme ); ?>" <?php echo $style_attr; // phpcs:ignore ?>>
	<div class="sc-container">
		<div class="sc-comp-hero__content">
			<?php if ( $eyebrow || $badge ) : ?>
				<div class="sc-comp-hero__eyebrow-wrap">
					<?php if ( $eyebrow ) : ?>
						<span class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
					<?php endif; ?>
					<?php if ( $badge ) : ?>
						<span class="sc-comp-hero__badge"><?php echo esc_html( $badge ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<h1 class="sc-comp-hero__title"><?php echo esc_html( $title ); ?></h1>

			<?php if ( $subtitle ) : ?>
				<p class="sc-comp-hero__subtitle"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>

			<?php if ( $cta_text || $cta_sec_text ) : ?>
				<div class="sc-comp-hero__actions">
					<?php if ( $cta_text && $cta_url ) : ?>
						<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--primary">
							<?php echo esc_html( $cta_text ); ?>
						</a>
					<?php endif; ?>
					<?php if ( $cta_sec_text && $cta_sec_url ) : ?>
						<a href="<?php echo esc_url( $cta_sec_url ); ?>" class="sc-btn sc-btn--outline">
							<?php echo esc_html( $cta_sec_text ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

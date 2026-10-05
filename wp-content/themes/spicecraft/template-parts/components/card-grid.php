<?php
/**
 * SpiceCraft Reusable Component: Card Grid
 *
 * Parameters:
 * - heading (string)
 * - eyebrow (string)
 * - description (string)
 * - cards (array)
 *     - title (string)
 *     - text (string)
 *     - icon (string svg or dashicon)
 *     - image (string url)
 *     - badge (string)
 *     - link_url (string)
 *     - link_text (string)
 * - columns (int: 2, 3, 4 - default 3)
 * - theme ('light'|'dark' - default 'light')
 * - alignment ('left'|'center' - default 'center')
 * - cta_text (string)
 * - cta_url (string)
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading     = ! empty( $args['heading'] ) ? $args['heading'] : '';
$eyebrow     = ! empty( $args['eyebrow'] ) ? $args['eyebrow'] : '';
$description = ! empty( $args['description'] ) ? $args['description'] : '';
$cards       = ! empty( $args['cards'] ) && is_array( $args['cards'] ) ? $args['cards'] : array();
$columns     = ! empty( $args['columns'] ) && in_array( (int) $args['columns'], array( 2, 3, 4 ), true ) ? (int) $args['columns'] : 3;
$theme       = ! empty( $args['theme'] ) && 'dark' === $args['theme'] ? 'dark' : 'light';
$alignment   = ! empty( $args['alignment'] ) && 'left' === $args['alignment'] ? 'left' : 'center';
$cta_text    = ! empty( $args['cta_text'] ) ? $args['cta_text'] : '';
$cta_url     = ! empty( $args['cta_url'] ) ? $args['cta_url'] : '';

if ( empty( $cards ) && empty( $heading ) ) {
	return;
}
?>
<section class="sc-comp-card-grid sc-comp-card-grid--<?php echo esc_attr( $theme ); ?> sc-comp-card-grid--cols-<?php echo esc_attr( $columns ); ?>">
	<div class="sc-container">
		<?php if ( $heading || $eyebrow || $description ) : ?>
			<header class="sc-section-header sc-section-header--<?php echo esc_attr( $alignment ); ?>">
				<?php if ( $eyebrow ) : ?>
					<p class="sc-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h2 class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( $description ) : ?>
					<p class="sc-section-subtitle"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</header>
		<?php endif; ?>

		<?php if ( ! empty( $cards ) ) : ?>
			<div class="sc-card-grid-inner sc-grid-cols-<?php echo esc_attr( $columns ); ?>">
				<?php foreach ( $cards as $card ) :
					$c_title = ! empty( $card['title'] ) ? $card['title'] : '';
					$c_text  = ! empty( $card['text'] ) ? $card['text'] : '';
					$c_icon  = ! empty( $card['icon'] ) ? $card['icon'] : '';
					$c_image = ! empty( $card['image'] ) ? $card['image'] : '';
					$c_badge = ! empty( $card['badge'] ) ? $card['badge'] : '';
					$c_url   = ! empty( $card['link_url'] ) ? $card['link_url'] : '';
					$c_btn   = ! empty( $card['link_text'] ) ? $card['link_text'] : __( 'Learn More', 'spicecraft' );
					?>
					<article class="sc-card-grid__item">
						<?php if ( $c_image ) : ?>
							<div class="sc-card-grid__media">
								<img src="<?php echo esc_url( $c_image ); ?>" alt="<?php echo esc_attr( $c_title ); ?>" loading="lazy" />
								<?php if ( $c_badge ) : ?>
									<span class="sc-badge sc-badge--card"><?php echo esc_html( $c_badge ); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<div class="sc-card-grid__body">
							<?php if ( $c_icon && empty( $c_image ) ) : ?>
								<div class="sc-card-grid__icon" aria-hidden="true">
									<?php
									if ( 0 === strpos( trim( $c_icon ), '<svg' ) ) {
										echo $c_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									} else {
										echo '<span class="dashicons ' . esc_attr( $c_icon ) . '"></span>';
									}
									?>
								</div>
							<?php endif; ?>

							<?php if ( $c_badge && empty( $c_image ) ) : ?>
								<span class="sc-badge sc-badge--card"><?php echo esc_html( $c_badge ); ?></span>
							<?php endif; ?>

							<?php if ( $c_title ) : ?>
								<h3 class="sc-card-grid__title">
									<?php if ( $c_url ) : ?>
										<a href="<?php echo esc_url( $c_url ); ?>"><?php echo esc_html( $c_title ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $c_title ); ?>
									<?php endif; ?>
								</h3>
							<?php endif; ?>

							<?php if ( $c_text ) : ?>
								<p class="sc-card-grid__desc"><?php echo esc_html( $c_text ); ?></p>
							<?php endif; ?>

							<?php if ( $c_url ) : ?>
								<div class="sc-card-grid__action">
									<a href="<?php echo esc_url( $c_url ); ?>" class="sc-link-arrow">
										<span><?php echo esc_html( $c_btn ); ?></span>
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
									</a>
								</div>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $cta_text && $cta_url ) : ?>
			<div class="sc-card-grid__footer sc-text-center">
				<a href="<?php echo esc_url( $cta_url ); ?>" class="sc-btn sc-btn--primary">
					<?php echo esc_html( $cta_text ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>

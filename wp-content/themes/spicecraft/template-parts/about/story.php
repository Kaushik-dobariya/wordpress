<?php
/**
 * About Section: Our Story & Heritage
 *
 * Emotionally rich narrative of brand origins, spice heritage, and founder philosophy
 * with delicate typographic quotation treatment.
 *
 * @package SpiceCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$story = function_exists( 'spicecraft_get_about_section' )
	? spicecraft_get_about_section( 'story' )
	: array();

if ( empty( $story ) ) {
	return;
}

$eyebrow       = $story['eyebrow'] ?? '';
$heading       = $story['heading'] ?? '';
$content       = $story['content'] ?? '';
$story_img     = absint( $story['story_image_id'] ?? 0 );
$secondary_img = absint( $story['secondary_image_id'] ?? 0 );
$quote_text    = $story['quote_text'] ?? '';
$quote_attr    = $story['quote_attribution'] ?? '';

if ( empty( $heading ) && empty( $content ) && empty( $quote_text ) ) {
	return;
}
?>

<section id="our-story" class="sc-about-story" aria-label="<?php echo esc_attr( ! empty( $heading ) ? $heading : __( 'Our Story', 'spicecraft' ) ); ?>">
	<div class="sc-container">
		<div class="sc-about-story__layout">
			<!-- Header / Intro Banner -->
			<div class="sc-about-story__header">
				<?php if ( ! empty( $eyebrow ) ) : ?>
					<span class="sc-section-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) ) : ?>
					<h2 class="sc-about-story__title"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
			</div>

			<!-- Grid Composition -->
			<div class="sc-about-story__grid <?php echo empty( $story_img ) ? 'sc-about-story__grid--full' : ''; ?>">
				<!-- Narrative & Quote -->
				<div class="sc-about-story__narrative">
					<?php if ( ! empty( $content ) ) : ?>
						<div class="sc-about-story__body entry-content">
							<?php echo wp_kses_post( wpautop( $content ) ); ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $quote_text ) ) : ?>
						<blockquote class="sc-about-story__quote">
							<p class="sc-about-story__quote-text">&ldquo;<?php echo esc_html( $quote_text ); ?>&rdquo;</p>
							<?php if ( ! empty( $quote_attr ) ) : ?>
								<cite class="sc-about-story__quote-author">&mdash; <?php echo esc_html( $quote_attr ); ?></cite>
							<?php endif; ?>
						</blockquote>
					<?php endif; ?>
				</div>

				<!-- Imagery Column -->
				<?php if ( ! empty( $story_img ) ) : ?>
					<div class="sc-about-story__media">
						<div class="sc-about-story__frame">
							<?php
							echo wp_get_attachment_image(
								$story_img,
								'large',
								false,
								array(
									'class'   => 'sc-about-story__img',
									'loading' => 'lazy',
								)
							);
							?>
							<?php if ( ! empty( $secondary_img ) ) : ?>
								<div class="sc-about-story__inset">
									<?php
									echo wp_get_attachment_image(
										$secondary_img,
										'medium',
										false,
										array(
											'class'   => 'sc-about-story__inset-img',
											'loading' => 'lazy',
										)
									);
									?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

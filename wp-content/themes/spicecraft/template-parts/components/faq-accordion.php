<?php
/**
 * SpiceCraft Reusable Component: FAQ Accordion
 *
 * Parameters:
 * - heading (string)
 * - eyebrow (string)
 * - description (string)
 * - faqs (array of array('q'|'question' => string, 'a'|'answer' => string))
 * - alignment ('left'|'center' - default 'center')
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
$alignment   = ! empty( $args['alignment'] ) && 'left' === $args['alignment'] ? 'left' : 'center';

if ( ! empty( $args['faqs'] ) && is_array( $args['faqs'] ) ) {
	$faqs = $args['faqs'];
} elseif ( function_exists( 'spicecraft_get_contact_faqs' ) ) {
	$faqs = spicecraft_get_contact_faqs();
} else {
	$faqs = array();
}

if ( empty( $faqs ) ) {
	return;
}
?>
<section class="sc-comp-faq sc-surface-warm">
	<div class="sc-container sc-container--narrow">
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

		<div class="sc-accordion sc-faq-list" role="region" aria-label="<?php echo esc_attr( $heading ? $heading : __( 'Frequently Asked Questions', 'spicecraft' ) ); ?>">
			<?php foreach ( $faqs as $idx => $faq ) :
				$question = ! empty( $faq['q'] ) ? $faq['q'] : ( ! empty( $faq['question'] ) ? $faq['question'] : '' );
				$answer   = ! empty( $faq['a'] ) ? $faq['a'] : ( ! empty( $faq['answer'] ) ? $faq['answer'] : '' );
				if ( ! $question || ! $answer ) {
					continue;
				}
				?>
				<details class="sc-accordion__item sc-faq-item" <?php echo 0 === $idx ? 'open' : ''; ?>>
					<summary class="sc-accordion__trigger sc-faq-trigger">
						<span class="sc-faq-question"><?php echo esc_html( $question ); ?></span>
						<span class="sc-accordion__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
						</span>
					</summary>
					<div class="sc-accordion__content sc-faq-answer">
						<p><?php echo wp_kses_post( $answer ); ?></p>
					</div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

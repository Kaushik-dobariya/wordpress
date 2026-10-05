<?php
/**
 * SpiceCraft Contact Page - Trade FAQ Section
 *
 * Fully accessible accordion using semantic HTML5 <details> and <summary>
 * elements. Content is CMS editable via Global Settings -> Contact Page.
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$contact_settings = function_exists( 'spicecraft_get_contact_settings' )
	? spicecraft_get_contact_settings()
	: array();

$faqs = function_exists( 'spicecraft_get_contact_faqs' )
	? spicecraft_get_contact_faqs()
	: array();

if ( empty( $faqs ) ) {
	return;
}

$heading  = ! empty( $contact_settings['faq_heading'] ) ? $contact_settings['faq_heading'] : __( 'Frequently Asked Trade Questions', 'spicecraft' );
$subtitle = ! empty( $contact_settings['faq_subtitle'] ) ? $contact_settings['faq_subtitle'] : __( 'Clear answers on minimum orders, international phytosanitary certificates, private label packaging, and sample dispatches.', 'spicecraft' );
?>

<section class="sc-contact-faq-section" id="contact-faq" aria-labelledby="contact-faq-heading">
	<div class="sc-container sc-container--narrow">
		<header class="sc-section-header sc-section-header--center">
			<span class="sc-eyebrow"><?php esc_html_e( 'Commercial FAQs', 'spicecraft' ); ?></span>
			<h2 id="contact-faq-heading" class="sc-section-title"><?php echo esc_html( $heading ); ?></h2>
			<?php if ( ! empty( $subtitle ) ) : ?>
				<p class="sc-section-subtitle"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>
		</header>

		<div class="sc-faq-accordion" role="region" aria-label="<?php esc_attr_e( 'Trade FAQs', 'spicecraft' ); ?>">
			<?php foreach ( $faqs as $index => $item ) : ?>
				<?php
				$q = ! empty( $item['q'] ) ? $item['q'] : '';
				$a = ! empty( $item['a'] ) ? $item['a'] : '';
				if ( empty( $q ) || empty( $a ) ) {
					continue;
				}
				?>
				<details class="sc-faq-item" <?php echo 0 === $index ? 'open' : ''; ?>>
					<summary class="sc-faq-summary">
						<span class="sc-faq-question-text"><?php echo esc_html( $q ); ?></span>
						<span class="sc-faq-icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<polyline points="6 9 12 15 18 9"></polyline>
							</svg>
						</span>
					</summary>
					<div class="sc-faq-answer">
						<p><?php echo nl2br( esc_html( $a ) ); ?></p>
					</div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

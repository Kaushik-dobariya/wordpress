<?php
/**
 * SpiceCraft Global Enquiry Modal Component
 *
 * Accessible modal dialog that hosts the enquiry form.
 * Can be opened dynamically from any product card, single product page, or general contact CTA.
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="sc-enquiry-modal" 
	class="sc-enquiry-modal" 
	role="dialog" 
	aria-modal="true" 
	aria-labelledby="sc-enquiry-modal-title" 
	aria-describedby="sc-enquiry-modal-desc" 
	aria-hidden="true" 
	style="display: none;">

	<!-- Modal Backdrop Overlay -->
	<div class="sc-enquiry-modal__overlay" tabindex="-1"></div>

	<!-- Modal Container Dialog -->
	<div class="sc-enquiry-modal__dialog" role="document">

		<!-- Modal Close Button -->
		<button type="button" 
			class="sc-enquiry-modal__close" 
			id="sc-enquiry-modal-close" 
			aria-label="<?php esc_attr_e( 'Close enquiry modal', 'spicecraft' ); ?>">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<line x1="18" y1="6" x2="6" y2="18"></line>
				<line x1="6" y1="6" x2="18" y2="18"></line>
			</svg>
		</button>

		<!-- Modal Header -->
		<div class="sc-enquiry-modal__header">
			<span class="sc-enquiry-modal__eyebrow"><?php esc_html_e( 'Direct Manufacturer Trade Desk', 'spicecraft' ); ?></span>
			<h3 id="sc-enquiry-modal-title" class="sc-enquiry-modal__title">
				<?php esc_html_e( 'Enquire About This Product', 'spicecraft' ); ?>
			</h3>
			<p id="sc-enquiry-modal-desc" class="sc-enquiry-modal__desc">
				<?php esc_html_e( 'Connect directly with our spice specialists for institutional bulk supply, custom packaging, private label specifications, or export pricing.', 'spicecraft' ); ?>
			</p>
		</div>

		<!-- Modal Body: Form Injection -->
		<div class="sc-enquiry-modal__body">
			<?php
			get_template_part(
				'template-parts/components/enquiry-form',
				null,
				array(
					'form_id'  => 'sc-modal-enquiry-form',
					'is_modal' => true,
				)
			);
			?>
		</div>

	</div><!-- .sc-enquiry-modal__dialog -->
</div><!-- #sc-enquiry-modal -->

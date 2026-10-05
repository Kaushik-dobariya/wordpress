<?php
/**
 * Template Name: Contact Us
 *
 * Dedicated B2B Contact Us page integrated with the SpiceCraft enquiry/lead management system.
 *
 * Page Structure:
 * 1. Hero with Breadcrumb & 24-Hour Response Badge
 * 2. Company Contact Information (HQ, Plant, Operating Hours, Direct Desks, Statutory Strip)
 * 3. Targeted Commercial Routing (Departments: Bulk, Export, Private Label, General, Careers)
 * 4. Contact & Procurement Form (Pre-selected product support & optional catalog selector)
 * 5. Interactive Location & Campus Map (Embed & Directions)
 * 6. Quick Communication CTA Band (WhatsApp Business & Direct Trade Email)
 * 7. Frequently Asked Trade Questions (Accessible Accordion)
 * 8. Final B2B Conversion CTA Banner
 *
 * @package SpiceCraft
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="sc-contact-page">
	<?php
	// 1. Hero Section
	get_template_part( 'template-parts/contact/hero' );

	// 2. Company Contact Information & Statutory Credentials
	get_template_part( 'template-parts/contact/company-info' );

	// 3. Contact / Enquiry Options (B2B Departments)
	get_template_part( 'template-parts/contact/options' );

	// 4. Contact Form Section
	get_template_part( 'template-parts/contact/form' );

	// 5. Location & Map Section
	get_template_part( 'template-parts/contact/map' );

	// 6. Direct WhatsApp & Email CTA Band
	get_template_part( 'template-parts/contact/cta-band' );

	// 7. Frequently Asked Trade Questions (FAQs)
	get_template_part( 'template-parts/contact/faq' );

	// 8. Final Conversion CTA Banner
	get_template_part( 'template-parts/contact/final-cta' );
	?>
</div>

<?php
get_footer();

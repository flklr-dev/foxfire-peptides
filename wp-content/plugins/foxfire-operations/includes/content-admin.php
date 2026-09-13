<?php
/**
 * Secure public-content controls for routine store operators.
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return the supported public-content field definitions.
 *
 * Copy values are plain text by design. Image fields store validated WordPress
 * attachment IDs so operators can use the Media Library without exposing raw
 * HTML or arbitrary remote image URLs.
 *
 * @return array<string, array{section:string,label:string,type:string,max:int,default:string,description:string}>
 */
function foxfire_operations_public_content_schema(): array {
	return array(
		'seo_home_title' => array(
			'section' => 'seo', 'label' => __( 'Homepage search title', 'foxfire-operations' ), 'type' => 'text', 'max' => 70,
			'default' => __( 'Research Peptides & Batch COA Access', 'foxfire-operations' ),
			'description' => __( 'Title before the Foxfire Peptides brand suffix. Keep it concise and describe the page accurately.', 'foxfire-operations' ),
		),
		'seo_home_description' => array(
			'section' => 'seo', 'label' => __( 'Homepage search description', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 180,
			'default' => __( 'Browse laboratory research peptides with clear product information and access to available batch and Certificate of Analysis documentation.', 'foxfire-operations' ),
			'description' => __( 'Unique summary for search results and social previews. The storefront safely limits rendered metadata to 160 characters.', 'foxfire-operations' ),
		),
		'seo_shop_title' => array(
			'section' => 'seo', 'label' => __( 'Shop search title', 'foxfire-operations' ), 'type' => 'text', 'max' => 70,
			'default' => __( 'Shop Research Peptides', 'foxfire-operations' ),
			'description' => __( 'Shop title before the Foxfire Peptides brand suffix.', 'foxfire-operations' ),
		),
		'seo_shop_description' => array(
			'section' => 'seo', 'label' => __( 'Shop search description', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 180,
			'default' => __( 'Browse Foxfire Peptides research compounds, compare available vial quantities, and review batch and testing information before ordering.', 'foxfire-operations' ),
			'description' => __( 'Unique summary for the main product catalog.', 'foxfire-operations' ),
		),
		'seo_testing_title' => array(
			'section' => 'seo', 'label' => __( 'Testing/COA search title', 'foxfire-operations' ), 'type' => 'text', 'max' => 70,
			'default' => __( 'Testing & Certificate of Analysis', 'foxfire-operations' ),
			'description' => __( 'Testing directory title before the Foxfire Peptides brand suffix.', 'foxfire-operations' ),
		),
		'seo_testing_description' => array(
			'section' => 'seo', 'label' => __( 'Testing/COA search description', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 180,
			'default' => __( 'Look up Foxfire Peptides batch and lot numbers to access available testing records and Certificate of Analysis documentation.', 'foxfire-operations' ),
			'description' => __( 'Unique summary for the public testing directory.', 'foxfire-operations' ),
		),
		'seo_about_title' => array(
			'section' => 'seo', 'label' => __( 'About search title', 'foxfire-operations' ), 'type' => 'text', 'max' => 70,
			'default' => __( 'About', 'foxfire-operations' ),
			'description' => __( 'About-page title before the Foxfire Peptides brand suffix.', 'foxfire-operations' ),
		),
		'seo_about_description' => array(
			'section' => 'seo', 'label' => __( 'About search description', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 180,
			'default' => __( 'Learn about Foxfire Peptides and our approach to transparent product information, accessible testing documentation, and dependable service.', 'foxfire-operations' ),
			'description' => __( 'Unique summary for the About page.', 'foxfire-operations' ),
		),
		'seo_contact_title' => array(
			'section' => 'seo', 'label' => __( 'Contact search title', 'foxfire-operations' ), 'type' => 'text', 'max' => 70,
			'default' => __( 'Contact', 'foxfire-operations' ),
			'description' => __( 'Contact-page title before the Foxfire Peptides brand suffix.', 'foxfire-operations' ),
		),
		'seo_contact_description' => array(
			'section' => 'seo', 'label' => __( 'Contact search description', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 180,
			'default' => __( 'Contact Foxfire Peptides for help with products, batch documentation, existing orders, or general research customer questions.', 'foxfire-operations' ),
			'description' => __( 'Unique summary for the Contact page.', 'foxfire-operations' ),
		),
		'home_hero_eyebrow' => array(
			'section' => 'homepage', 'label' => __( 'Hero eyebrow', 'foxfire-operations' ), 'type' => 'text', 'max' => 80,
			'default' => __( 'Analytical Research Standards', 'foxfire-operations' ),
			'description' => __( 'Short label above the main homepage heading.', 'foxfire-operations' ),
		),
		'home_hero_title' => array(
			'section' => 'homepage', 'label' => __( 'Hero heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 140,
			'default' => __( 'Research Compounds. Transparent Testing. Real Accountability.', 'foxfire-operations' ),
			'description' => __( 'Primary homepage heading.', 'foxfire-operations' ),
		),
		'home_hero_lead' => array(
			'section' => 'homepage', 'label' => __( 'Hero description', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 320,
			'default' => __( 'Third-party testing, clear batch documentation, and straightforward access to the information behind every Foxfire product.', 'foxfire-operations' ),
			'description' => __( 'Supporting text below the homepage heading.', 'foxfire-operations' ),
		),
		'home_primary_cta' => array(
			'section' => 'homepage', 'label' => __( 'Primary button label', 'foxfire-operations' ), 'type' => 'text', 'max' => 40,
			'default' => __( 'SHOP RESEARCH COMPOUNDS', 'foxfire-operations' ),
			'description' => __( 'The destination remains the WooCommerce shop.', 'foxfire-operations' ),
		),
		'home_secondary_cta' => array(
			'section' => 'homepage', 'label' => __( 'Secondary button label', 'foxfire-operations' ), 'type' => 'text', 'max' => 60,
			'default' => __( 'VIEW TESTING & COAs', 'foxfire-operations' ),
			'description' => __( 'The destination remains the Testing/COA page.', 'foxfire-operations' ),
		),
		'home_coa_title' => array(
			'section' => 'homepage', 'label' => __( 'COA callout heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 140,
			'default' => __( "Know What's Behind Every Vial.", 'foxfire-operations' ),
			'description' => __( 'Heading in the homepage testing callout.', 'foxfire-operations' ),
		),
		'home_coa_description' => array(
			'section' => 'homepage', 'label' => __( 'COA callout description', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 280,
			'default' => __( 'Access available third-party testing and batch-specific documentation for Foxfire research compounds. Search by product, batch, or lot number to find the available COA.', 'foxfire-operations' ),
			'description' => __( 'Supporting text in the homepage testing callout.', 'foxfire-operations' ),
		),
		'home_closing_title' => array(
			'section' => 'homepage', 'label' => __( 'Closing call-to-action heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 140,
			'default' => __( 'Ready to Explore Foxfire?', 'foxfire-operations' ),
			'description' => __( 'Heading above the final homepage buttons.', 'foxfire-operations' ),
		),
		'home_closing_description' => array(
			'section' => 'homepage', 'label' => __( 'Closing call-to-action description', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 280,
			'default' => __( 'Browse our research compounds, review available testing documentation, and find the products that fit your research needs.', 'foxfire-operations' ),
			'description' => __( 'Supporting text above the final homepage buttons.', 'foxfire-operations' ),
		),
		'about_hero_eyebrow' => array(
			'section' => 'about', 'label' => __( 'About page label', 'foxfire-operations' ), 'type' => 'text', 'max' => 50,
			'default' => __( 'About Foxfire', 'foxfire-operations' ),
			'description' => __( 'Short label above the main About page heading.', 'foxfire-operations' ),
		),
		'about_hero_title' => array(
			'section' => 'about', 'label' => __( 'About page heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 140,
			'default' => __( 'The Road Is Better Together.', 'foxfire-operations' ),
			'description' => __( 'Purpose-led primary heading at the top of the About page. Keep it concise for mobile.', 'foxfire-operations' ),
		),
		'about_hero_intro' => array(
			'section' => 'about', 'label' => __( 'About page introduction', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 420,
			'default' => __( 'Foxfire Peptides is being built as a focused, human-led peptide company—with clear product information, a recognizable founder, and genuine relationships at its heart.', 'foxfire-operations' ),
			'description' => __( 'Introductory paragraph below the About page heading.', 'foxfire-operations' ),
		),
		'about_story_title' => array(
			'section' => 'about', 'label' => __( 'Story heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 100,
			'default' => __( 'A Focused, More Personal Approach', 'foxfire-operations' ),
			'description' => __( 'Heading above the main About story.', 'foxfire-operations' ),
		),
		'about_story_one' => array(
			'section' => 'about', 'label' => __( 'Story paragraph one', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 650,
			'default' => __( 'Foxfire is designed around a focused catalog rather than an overwhelming warehouse of options. The goal is to make it simple to find a product, understand the available strengths, and move through checkout without unnecessary friction.', 'foxfire-operations' ),
			'description' => __( 'First paragraph in the About story.', 'foxfire-operations' ),
		),
		'about_story_two' => array(
			'section' => 'about', 'label' => __( 'Story paragraph two', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 650,
			'default' => __( 'As the company grows, the commitment stays the same: communicate clearly, make available documentation easy to find, and create an experience customers can navigate with confidence.', 'foxfire-operations' ),
			'description' => __( 'Second paragraph in the About story.', 'foxfire-operations' ),
		),
		'about_story_callout_title' => array(
			'section' => 'about', 'label' => __( 'Story callout heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 80,
			'default' => __( 'Focused by design', 'foxfire-operations' ),
			'description' => __( 'Short heading beside the main About story.', 'foxfire-operations' ),
		),
		'about_story_callout_text' => array(
			'section' => 'about', 'label' => __( 'Story callout text', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 300,
			'default' => __( 'A smaller, intentional catalog keeps the experience straightforward—from product discovery to testing information and account support.', 'foxfire-operations' ),
			'description' => __( 'Supporting copy in the story callout.', 'foxfire-operations' ),
		),
		'about_founder_enabled' => array(
			'section' => 'about', 'label' => __( 'Display founder section', 'foxfire-operations' ), 'type' => 'checkbox', 'max' => 3,
			'default' => 'yes',
			'description' => __( 'Show or hide the complete founder section on the public About page.', 'foxfire-operations' ),
		),
		'about_founder_image' => array(
			'section' => 'about', 'label' => __( 'Founder image', 'foxfire-operations' ), 'type' => 'image', 'max' => 20,
			'default' => '',
			'description' => __( 'Choose an approved founder portrait from the Media Library. A template portrait of Jay appears while no custom image is selected.', 'foxfire-operations' ),
		),
		'about_founder_image_alt' => array(
			'section' => 'about', 'label' => __( 'Founder image description', 'foxfire-operations' ), 'type' => 'text', 'max' => 160,
			'default' => __( 'Jay, founder of Foxfire Peptides', 'foxfire-operations' ),
			'description' => __( 'Briefly describe the approved portrait for visitors using assistive technology.', 'foxfire-operations' ),
		),
		'about_founder_eyebrow' => array(
			'section' => 'about', 'label' => __( 'Founder section label', 'foxfire-operations' ), 'type' => 'text', 'max' => 50,
			'default' => __( 'Meet the Founder', 'foxfire-operations' ),
			'description' => __( 'Short label above the founder heading.', 'foxfire-operations' ),
		),
		'about_founder_name' => array(
			'section' => 'about', 'label' => __( 'Founder heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 100,
			'default' => __( 'Meet Jay', 'foxfire-operations' ),
			'description' => __( 'Public founder heading.', 'foxfire-operations' ),
		),
		'about_founder_role' => array(
			'section' => 'about', 'label' => __( 'Founder role', 'foxfire-operations' ), 'type' => 'text', 'max' => 100,
			'default' => __( 'Founder of Foxfire Peptides', 'foxfire-operations' ),
			'description' => __( 'Role displayed below the founder heading.', 'foxfire-operations' ),
		),
		'about_founder_story_one' => array(
			'section' => 'about', 'label' => __( 'Founder story paragraph one', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 650,
			'default' => __( 'Foxfire is being built as a human-led company rather than another anonymous peptide storefront. Jay plans to be publicly connected to the brand and accountable for the experience it creates.', 'foxfire-operations' ),
			'description' => __( 'First paragraph in the founder section. Use only approved biographical information.', 'foxfire-operations' ),
		),
		'about_founder_story_two' => array(
			'section' => 'about', 'label' => __( 'Founder story paragraph two', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 650,
			'default' => __( 'The aim is straightforward: keep products easy to shop, make available information easy to find, and build lasting relationships through clear communication and dependable service.', 'foxfire-operations' ),
			'description' => __( 'Second paragraph in the founder section. Use only approved biographical information.', 'foxfire-operations' ),
		),
		'about_founder_quote' => array(
			'section' => 'about', 'label' => __( 'Founder statement', 'foxfire-operations' ), 'type' => 'text', 'max' => 140,
			'default' => __( 'The Road Is Better Together.', 'foxfire-operations' ),
			'description' => __( 'Short highlighted statement in the founder section.', 'foxfire-operations' ),
		),
		'about_values_title' => array(
			'section' => 'about', 'label' => __( 'Values heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 100,
			'default' => __( 'Built Around Three Commitments', 'foxfire-operations' ),
			'description' => __( 'Heading above the three brand-pillar cards.', 'foxfire-operations' ),
		),
		'about_values_intro' => array(
			'section' => 'about', 'label' => __( 'Values introduction', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 320,
			'default' => __( 'Three principles shape the store, the information we share, and the relationships we want to build.', 'foxfire-operations' ),
			'description' => __( 'Supporting text above the value cards.', 'foxfire-operations' ),
		),
		'about_value_quality_title' => array(
			'section' => 'about', 'label' => __( 'Quality pillar heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 60,
			'default' => __( 'Quality', 'foxfire-operations' ),
			'description' => __( 'Heading for the first brand pillar.', 'foxfire-operations' ),
		),
		'about_value_quality_text' => array(
			'section' => 'about', 'label' => __( 'Quality pillar text', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 260,
			'default' => __( 'A focused catalog, carefully presented product information, and a commitment to a dependable customer experience.', 'foxfire-operations' ),
			'description' => __( 'Supporting copy for the Quality pillar.', 'foxfire-operations' ),
		),
		'about_value_transparency_title' => array(
			'section' => 'about', 'label' => __( 'Transparency pillar heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 60,
			'default' => __( 'Transparency', 'foxfire-operations' ),
			'description' => __( 'Heading for the second brand pillar.', 'foxfire-operations' ),
		),
		'about_value_transparency_text' => array(
			'section' => 'about', 'label' => __( 'Transparency pillar text', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 260,
			'default' => __( 'Available batch and testing information should be easy to locate and straightforward to review.', 'foxfire-operations' ),
			'description' => __( 'Supporting copy for the Transparency pillar.', 'foxfire-operations' ),
		),
		'about_value_community_title' => array(
			'section' => 'about', 'label' => __( 'Community pillar heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 60,
			'default' => __( 'Community', 'foxfire-operations' ),
			'description' => __( 'Heading for the third brand pillar.', 'foxfire-operations' ),
		),
		'about_value_community_text' => array(
			'section' => 'about', 'label' => __( 'Community pillar text', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 260,
			'default' => __( 'Foxfire is intended to grow through genuine relationships, responsive support, and shared trust.', 'foxfire-operations' ),
			'description' => __( 'Supporting copy for the Community pillar.', 'foxfire-operations' ),
		),
		'about_coa_eyebrow' => array(
			'section' => 'about', 'label' => __( 'Testing callout label', 'foxfire-operations' ), 'type' => 'text', 'max' => 60,
			'default' => __( 'Testing & Documentation', 'foxfire-operations' ),
			'description' => __( 'Short label above the Testing/COA callout.', 'foxfire-operations' ),
		),
		'about_coa_title' => array(
			'section' => 'about', 'label' => __( 'Testing callout heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 120,
			'default' => __( 'Know What’s Behind the Vial.', 'foxfire-operations' ),
			'description' => __( 'Heading in the About page Testing/COA callout.', 'foxfire-operations' ),
		),
		'about_coa_description' => array(
			'section' => 'about', 'label' => __( 'Testing callout description', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 320,
			'default' => __( 'Review available testing status, batch and lot information, and COA documents in one clear directory.', 'foxfire-operations' ),
			'description' => __( 'Supporting copy in the Testing/COA callout.', 'foxfire-operations' ),
		),
		'about_community_title' => array(
			'section' => 'about', 'label' => __( 'Community statement heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 120,
			'default' => __( 'The Road Is Better Together.', 'foxfire-operations' ),
			'description' => __( 'Heading in the closing community statement.', 'foxfire-operations' ),
		),
		'about_community_description' => array(
			'section' => 'about', 'label' => __( 'Community statement text', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 320,
			'default' => __( 'Foxfire is more than a catalog. It is a brand being built in the open—with a founder customers can recognize and a community that helps shape what comes next.', 'foxfire-operations' ),
			'description' => __( 'Supporting copy in the closing community statement.', 'foxfire-operations' ),
		),
		'about_cta_title' => array(
			'section' => 'about', 'label' => __( 'Closing heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 100,
			'default' => __( 'Start With What Matters Most', 'foxfire-operations' ),
			'description' => __( 'Heading in the About page call to action.', 'foxfire-operations' ),
		),
		'about_cta_description' => array(
			'section' => 'about', 'label' => __( 'Closing description', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 320,
			'default' => __( 'Explore the focused catalog or review available testing documentation.', 'foxfire-operations' ),
			'description' => __( 'Supporting text in the About page call to action.', 'foxfire-operations' ),
		),
		'about_primary_cta' => array(
			'section' => 'about', 'label' => __( 'Shop button label', 'foxfire-operations' ), 'type' => 'text', 'max' => 40,
			'default' => __( 'Shop Products', 'foxfire-operations' ),
			'description' => __( 'The destination remains the WooCommerce shop.', 'foxfire-operations' ),
		),
		'about_secondary_cta' => array(
			'section' => 'about', 'label' => __( 'Testing button label', 'foxfire-operations' ), 'type' => 'text', 'max' => 60,
			'default' => __( 'View Testing & COAs', 'foxfire-operations' ),
			'description' => __( 'The destination remains the Testing/COA page.', 'foxfire-operations' ),
		),
		'contact_eyebrow' => array(
			'section' => 'contact', 'label' => __( 'Contact page eyebrow', 'foxfire-operations' ), 'type' => 'text', 'max' => 80,
			'default' => __( 'Customer Care & Inquiries', 'foxfire-operations' ),
			'description' => __( 'Short label above the Contact Us heading.', 'foxfire-operations' ),
		),
		'contact_title' => array(
			'section' => 'contact', 'label' => __( 'Contact page heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 100,
			'default' => __( 'Contact Us', 'foxfire-operations' ),
			'description' => __( 'Primary heading at the top of the Contact page.', 'foxfire-operations' ),
		),
		'contact_intro' => array(
			'section' => 'contact', 'label' => __( 'Contact page introduction', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 300,
			'default' => __( 'Have questions about products, batch documentation, or your order? We are here to help.', 'foxfire-operations' ),
			'description' => __( 'Introduction shown above the contact information and form.', 'foxfire-operations' ),
		),
		'contact_direct_title' => array(
			'section' => 'contact', 'label' => __( 'Direct-contact heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 100,
			'default' => __( 'How to Reach Us', 'foxfire-operations' ),
			'description' => __( 'Heading above the public contact details.', 'foxfire-operations' ),
		),
		'contact_direct_description' => array(
			'section' => 'contact', 'label' => __( 'Direct-contact description', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 300,
			'default' => __( 'Our team is available Monday through Friday to assist with inquiries, documentation, and orders.', 'foxfire-operations' ),
			'description' => __( 'Text above the support email and hours.', 'foxfire-operations' ),
		),
		'support_email' => array(
			'section' => 'contact', 'label' => __( 'Public support email', 'foxfire-operations' ), 'type' => 'email', 'max' => 254,
			'default' => 'support@foxfirepeptides.com',
			'description' => __( 'Displayed publicly and used as the contact-form recipient. This is not an SMTP credential.', 'foxfire-operations' ),
		),
		'contact_response_time' => array(
			'section' => 'contact', 'label' => __( 'Response time', 'foxfire-operations' ), 'type' => 'text', 'max' => 80,
			'default' => __( '12–24 business hours', 'foxfire-operations' ),
			'description' => __( 'Customer-facing response estimate.', 'foxfire-operations' ),
		),
		'contact_hours' => array(
			'section' => 'contact', 'label' => __( 'Support hours', 'foxfire-operations' ), 'type' => 'text', 'max' => 100,
			'default' => __( 'Mon – Fri, 9 AM – 5 PM EST', 'foxfire-operations' ),
			'description' => __( 'Public support availability. Include the timezone.', 'foxfire-operations' ),
		),
		'contact_trust_note' => array(
			'section' => 'contact', 'label' => __( 'Contact trust note', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 220,
			'default' => __( 'Every inquiry is received and handled directly by the Foxfire team.', 'foxfire-operations' ),
			'description' => __( 'Short reassurance below the contact details.', 'foxfire-operations' ),
		),
		'contact_form_title' => array(
			'section' => 'contact', 'label' => __( 'Contact form heading', 'foxfire-operations' ), 'type' => 'text', 'max' => 100,
			'default' => __( 'Send Us a Message', 'foxfire-operations' ),
			'description' => __( 'Heading above the secure contact form.', 'foxfire-operations' ),
		),
		'contact_form_description' => array(
			'section' => 'contact', 'label' => __( 'Contact form description', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 260,
			'default' => __( 'Fill in the details below and we will get back to you as soon as possible.', 'foxfire-operations' ),
			'description' => __( 'Supporting text above the secure contact form.', 'foxfire-operations' ),
		),
		'support_phone' => array(
			'section' => 'contact', 'label' => __( 'Public support phone', 'foxfire-operations' ), 'type' => 'text', 'max' => 40,
			'default' => '',
			'description' => __( 'Optional. Displayed on the Contact page and footer when provided.', 'foxfire-operations' ),
		),
		'faq_question_1' => array(
			'section' => 'faq', 'label' => __( 'Question 1', 'foxfire-operations' ), 'type' => 'text', 'max' => 180, 'default' => __( 'How do I place an order?', 'foxfire-operations' ),
			'description' => __( 'Leave both the question and answer blank to hide this FAQ item.', 'foxfire-operations' ),
		),
		'faq_answer_1' => array(
			'section' => 'faq', 'label' => __( 'Answer 1', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 700, 'default' => __( 'Browse the shop, choose an available product and quantity option, add it to your cart, and complete checkout using one of the currently enabled payment methods.', 'foxfire-operations' ),
			'description' => __( 'Plain-text answer for question 1.', 'foxfire-operations' ),
		),
		'faq_question_2' => array(
			'section' => 'faq', 'label' => __( 'Question 2', 'foxfire-operations' ), 'type' => 'text', 'max' => 180, 'default' => __( 'Can I order more than one vial?', 'foxfire-operations' ),
			'description' => __( 'Leave both fields blank to hide this FAQ item.', 'foxfire-operations' ),
		),
		'faq_answer_2' => array(
			'section' => 'faq', 'label' => __( 'Answer 2', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 700, 'default' => __( 'Yes. Multiple-vial quantities are available on select products. Available quantity options and pricing are shown directly on each product page.', 'foxfire-operations' ),
			'description' => __( 'Plain-text answer for question 2.', 'foxfire-operations' ),
		),
		'faq_question_3' => array(
			'section' => 'faq', 'label' => __( 'Question 3', 'foxfire-operations' ), 'type' => 'text', 'max' => 180, 'default' => __( 'Where can I find testing and COA documents?', 'foxfire-operations' ),
			'description' => __( 'Leave both fields blank to hide this FAQ item.', 'foxfire-operations' ),
		),
		'faq_answer_3' => array(
			'section' => 'faq', 'label' => __( 'Answer 3', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 700, 'default' => __( 'Use the Testing/COA directory and search for the batch or lot number. A report link appears only when a document has been attached for that batch.', 'foxfire-operations' ),
			'description' => __( 'Plain-text answer for question 3.', 'foxfire-operations' ),
		),
		'faq_question_4' => array(
			'section' => 'faq', 'label' => __( 'Question 4', 'foxfire-operations' ), 'type' => 'text', 'max' => 180, 'default' => __( 'Where can I find the batch or lot number?', 'foxfire-operations' ),
			'description' => __( 'Leave both fields blank to hide this FAQ item.', 'foxfire-operations' ),
		),
		'faq_answer_4' => array(
			'section' => 'faq', 'label' => __( 'Answer 4', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 700, 'default' => __( 'Check the product label or packaging for the batch or lot number, then use it in the Testing/COA directory.', 'foxfire-operations' ),
			'description' => __( 'Plain-text answer for question 4.', 'foxfire-operations' ),
		),
		'faq_question_5' => array(
			'section' => 'faq', 'label' => __( 'Question 5', 'foxfire-operations' ), 'type' => 'text', 'max' => 180, 'default' => __( 'Which payment methods can I use?', 'foxfire-operations' ),
			'description' => __( 'Leave both fields blank to hide this FAQ item.', 'foxfire-operations' ),
		),
		'faq_answer_5' => array(
			'section' => 'faq', 'label' => __( 'Answer 5', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 700, 'default' => __( 'Checkout displays the payment methods currently enabled by Foxfire Peptides. Available methods may change after the client approves the production payment provider.', 'foxfire-operations' ),
			'description' => __( 'Plain-text answer for question 5.', 'foxfire-operations' ),
		),
		'faq_question_6' => array(
			'section' => 'faq', 'label' => __( 'Question 6', 'foxfire-operations' ), 'type' => 'text', 'max' => 180, 'default' => __( 'Are these products intended for human consumption?', 'foxfire-operations' ),
			'description' => __( 'Leave both fields blank to hide this FAQ item.', 'foxfire-operations' ),
		),
		'faq_answer_6' => array(
			'section' => 'faq', 'label' => __( 'Answer 6', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 700, 'default' => __( 'No. Products are offered for laboratory research use only and are not for human consumption.', 'foxfire-operations' ),
			'description' => __( 'Plain-text answer for question 6.', 'foxfire-operations' ),
		),
		'faq_question_7' => array(
			'section' => 'faq', 'label' => __( 'Question 7', 'foxfire-operations' ), 'type' => 'text', 'max' => 180, 'default' => __( 'How is shipping calculated?', 'foxfire-operations' ),
			'description' => __( 'Leave both fields blank to hide this FAQ item.', 'foxfire-operations' ),
		),
		'faq_answer_7' => array(
			'section' => 'faq', 'label' => __( 'Answer 7', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 700, 'default' => __( 'Shipping is calculated at checkout based on your delivery address and order total. If your order qualifies for free shipping, it will be applied automatically.', 'foxfire-operations' ),
			'description' => __( 'Plain-text answer for question 7.', 'foxfire-operations' ),
		),
		'faq_question_8' => array(
			'section' => 'faq', 'label' => __( 'Question 8', 'foxfire-operations' ), 'type' => 'text', 'max' => 180, 'default' => __( 'Is an account required to order?', 'foxfire-operations' ),
			'description' => __( 'Dedicated FAQ page only; this account question is excluded from the homepage. Leave both fields blank to hide it on the FAQ page too.', 'foxfire-operations' ),
		),
		'faq_answer_8' => array(
			'section' => 'faq', 'label' => __( 'Answer 8', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 700, 'default' => __( 'You can browse products without an account. The checkout page reflects the current guest-checkout setting, while an account provides access to saved addresses, orders, tracking, and available COA documents.', 'foxfire-operations' ),
			'description' => __( 'Plain-text answer for question 8.', 'foxfire-operations' ),
		),
		'footer_tagline' => array(
			'section' => 'footer', 'label' => __( 'Footer tagline', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 220,
			'default' => __( 'Research compounds with transparent testing, clear documentation, and straightforward ordering.', 'foxfire-operations' ),
			'description' => __( 'Short brand statement in the footer.', 'foxfire-operations' ),
		),
		'footer_research_notice' => array(
			'section' => 'footer', 'label' => __( 'Footer research notice', 'foxfire-operations' ), 'type' => 'textarea', 'max' => 220,
			'default' => __( 'For laboratory research use only. Not for human consumption.', 'foxfire-operations' ),
			'description' => __( 'Sitewide footer notice. Obtain legal approval before changing it.', 'foxfire-operations' ),
		),
	);
}

/**
 * Read a saved public-content value with a caller-provided fallback.
 *
 * @param string $key      Registered field key.
 * @param string $fallback Value used when the field is blank or unknown.
 * @return string
 */
function foxfire_operations_get_public_content( string $key, string $fallback = '' ): string {
	$schema = foxfire_operations_public_content_schema();
	if ( ! isset( $schema[ $key ] ) ) {
		return $fallback;
	}

	$saved = get_option( 'foxfire_public_content', array() );
	$value = is_array( $saved ) && isset( $saved[ $key ] ) && is_string( $saved[ $key ] ) ? trim( $saved[ $key ] ) : '';

	return '' !== $value ? $value : $fallback;
}

/**
 * Ensure the managed FAQ has a public route once for new and existing installs.
 *
 * Existing FAQ content is never overwritten. An existing draft is published only
 * when it already uses the Foxfire-managed FAQ template.
 */
function foxfire_operations_maybe_install_content(): void {
	if ( FOXFIRE_OPERATIONS_CONTENT_VERSION === get_option( 'foxfire_operations_content_version' ) ) {
		return;
	}

	$faq_page = get_page_by_path( 'faq', OBJECT, 'page' );

	if ( ! $faq_page ) {
		$page_id = wp_insert_post(
			array(
				'post_title'   => __( 'Frequently Asked Questions', 'foxfire-operations' ),
				'post_name'    => 'faq',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			),
			true
		);

		if ( ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', 'page-faq.php' );
		}
	} elseif ( 'publish' !== $faq_page->post_status && 'page-faq.php' === get_page_template_slug( $faq_page->ID ) ) {
		wp_update_post(
			array(
				'ID'          => $faq_page->ID,
				'post_status' => 'publish',
			)
		);
	}

	update_option( 'foxfire_operations_content_version', FOXFIRE_OPERATIONS_CONTENT_VERSION, false );
}
add_action( 'init', 'foxfire_operations_maybe_install_content', 30 );

/** Return templates that may explicitly replace draft copy with editor content. */
function foxfire_operations_editor_content_templates(): array {
	return array(
		'page-privacy-policy.php',
		'page-refund-and-returns.php',
		'page-shipping-policy.php',
		'page-terms-and-conditions.php',
	);
}

/** Add an explicit display switch to approved policy pages. */
function foxfire_operations_add_page_content_metabox( WP_Post $post ): void {
	if ( ! foxfire_operations_can_manage_public_content() || ! in_array( get_page_template_slug( $post->ID ), foxfire_operations_editor_content_templates(), true ) ) {
		return;
	}

	add_meta_box(
		'foxfire-page-content-display',
		__( 'Foxfire Page Display', 'foxfire-operations' ),
		'foxfire_operations_render_page_content_metabox',
		'page',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes_page', 'foxfire_operations_add_page_content_metabox' );

/** Render the policy-page editor-content switch. */
function foxfire_operations_render_page_content_metabox( WP_Post $post ): void {
	wp_nonce_field( 'foxfire_save_page_content_display_' . $post->ID, 'foxfire_page_content_display_nonce' );
	$enabled = 'yes' === get_post_meta( $post->ID, '_foxfire_use_editor_content', true );
	?>
	<p><label><input type="checkbox" name="foxfire_use_editor_content" value="yes" <?php checked( $enabled ); ?>> <?php esc_html_e( 'Use the WordPress editor content on the public page', 'foxfire-operations' ); ?></label></p>
	<p class="description"><?php esc_html_e( 'Enable only after the complete policy has client/legal approval. When disabled, the designed built-in draft remains the fallback. WordPress revisions can restore earlier editor content.', 'foxfire-operations' ); ?></p>
	<?php
}

/** Save the explicit policy-page display switch. */
function foxfire_operations_save_page_content_metabox( int $post_id, WP_Post $post ): void {
	if ( wp_is_post_revision( $post_id ) || ! foxfire_operations_can_manage_public_content() || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( ! in_array( get_page_template_slug( $post_id ), foxfire_operations_editor_content_templates(), true ) ) {
		return;
	}

	$nonce = isset( $_POST['foxfire_page_content_display_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['foxfire_page_content_display_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'foxfire_save_page_content_display_' . $post_id ) ) {
		return;
	}

	$enabled = isset( $_POST['foxfire_use_editor_content'] ) && 'yes' === sanitize_key( wp_unslash( $_POST['foxfire_use_editor_content'] ) );
	update_post_meta( $post_id, '_foxfire_use_editor_content', $enabled ? 'yes' : 'no' );
}
add_action( 'save_post_page', 'foxfire_operations_save_page_content_metabox', 30, 2 );

/** Whether an approved policy page should render its revisioned editor content. */
function foxfire_operations_should_use_editor_content( int $post_id ): bool {
	if ( 'yes' !== get_post_meta( $post_id, '_foxfire_use_editor_content', true ) ) {
		return false;
	}

	$content = get_post_field( 'post_content', $post_id );
	return is_string( $content ) && '' !== trim( wp_strip_all_tags( $content ) );
}

/** Register the scoped content page below Foxfire Operations. */
function foxfire_operations_register_content_page(): void {
	add_submenu_page(
		'foxfire-operations',
		__( 'Site Content', 'foxfire-operations' ),
		__( 'Site Content', 'foxfire-operations' ),
		FOXFIRE_OPERATIONS_CAPABILITY,
		'foxfire-site-content',
		'foxfire_operations_render_content_page'
	);
}
add_action( 'admin_menu', 'foxfire_operations_register_content_page', 40 );

/** Determine whether the current account may manage approved public content. */
function foxfire_operations_can_manage_public_content(): bool {
	return current_user_can( FOXFIRE_OPERATIONS_CAPABILITY );
}

/**
 * Determine whether a page belongs to the deliberately approved policy set.
 */
function foxfire_operations_is_approved_policy_page( int $post_id ): bool {
	return 'page' === get_post_type( $post_id )
		&& in_array( get_page_template_slug( $post_id ), foxfire_operations_editor_content_templates(), true );
}

/**
 * Let a restricted Shop Manager edit only approved Foxfire policy pages.
 *
 * Routine staff do not receive WordPress's broad post/page capabilities. This
 * narrowly maps edit access for existing Foxfire policy pages to the operations
 * capability while keeping page creation, deletion, publishing, settings, and
 * all unrelated posts/pages unavailable.
 *
 * @param string[] $caps    Primitive capabilities WordPress requires.
 * @param string   $cap     Requested meta capability.
 * @param int      $user_id User being checked.
 * @param mixed[]  $args    Meta capability arguments.
 * @return string[]
 */
function foxfire_operations_scope_policy_page_edit( array $caps, string $cap, int $user_id, array $args ): array {
	if ( ! in_array( $cap, array( 'edit_post', 'edit_page' ), true ) || empty( $args[0] ) ) {
		return $caps;
	}

	$post_id = absint( $args[0] );
	if ( $post_id <= 0 || ! foxfire_operations_is_approved_policy_page( $post_id ) ) {
		return $caps;
	}

	$user = get_userdata( $user_id );
	$role = get_role( 'shop_manager' );
	if (
		! $user instanceof WP_User
		|| ! $role instanceof WP_Role
		|| ! in_array( 'shop_manager', (array) $user->roles, true )
		|| in_array( 'administrator', (array) $user->roles, true )
		|| ! $role->has_cap( FOXFIRE_OPERATIONS_CAPABILITY )
	) {
		return $caps;
	}

	return array( FOXFIRE_OPERATIONS_CAPABILITY );
}
add_filter( 'map_meta_cap', 'foxfire_operations_scope_policy_page_edit', 20, 4 );

/**
 * Truncate sanitized content without splitting multibyte characters when possible.
 *
 * @param string $value Sanitized content.
 * @param int    $max   Maximum character count.
 * @return string
 */
function foxfire_operations_limit_content_length( string $value, int $max ): string {
	return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $max ) : substr( $value, 0, $max );
}

/** Save approved public content through a nonce-protected admin-post action. */
function foxfire_operations_save_public_content(): void {
	if ( ! foxfire_operations_can_manage_public_content() ) {
		wp_die( esc_html__( 'You do not have permission to edit public site content.', 'foxfire-operations' ), esc_html__( 'Access denied', 'foxfire-operations' ), array( 'response' => 403 ) );
	}

	check_admin_referer( 'foxfire_save_public_content', 'foxfire_public_content_nonce' );

	$schema = foxfire_operations_public_content_schema();
	$posted = isset( $_POST['foxfire_public_content'] ) && is_array( $_POST['foxfire_public_content'] ) ? wp_unslash( $_POST['foxfire_public_content'] ) : array();
	$clean  = array();
	$status = 'updated';

	foreach ( $schema as $key => $field ) {
		$raw = isset( $posted[ $key ] ) && is_string( $posted[ $key ] ) ? trim( $posted[ $key ] ) : '';

		if ( 'checkbox' === $field['type'] ) {
			$value = 'yes' === sanitize_key( $raw ) ? 'yes' : 'no';
		} elseif ( 'image' === $field['type'] ) {
			$attachment_id = absint( $raw );
			if ( $attachment_id > 0 && ( ! ( get_post( $attachment_id ) instanceof WP_Post ) || ! wp_attachment_is_image( $attachment_id ) ) ) {
				$status = 'invalid-image';
				break;
			}
			$value = (string) $attachment_id;
		} elseif ( 'email' === $field['type'] ) {
			$value = sanitize_email( $raw );
			if ( '' !== $raw && ( '' === $value || ! is_email( $value ) ) ) {
				$status = 'invalid-email';
				break;
			}
		} elseif ( 'textarea' === $field['type'] ) {
			$value = sanitize_textarea_field( $raw );
		} else {
			$value = sanitize_text_field( $raw );
		}

		$clean[ $key ] = foxfire_operations_limit_content_length( $value, $field['max'] );
	}

	if ( 'updated' === $status ) {
		update_option( 'foxfire_public_content', $clean, false );
		update_option( 'foxfire_public_content_audit', array( 'user_id' => get_current_user_id(), 'updated_gmt' => gmdate( 'Y-m-d H:i:s' ) ), false );
	}

	wp_safe_redirect( add_query_arg( 'foxfire_content_status', $status, admin_url( 'admin.php?page=foxfire-site-content' ) ) );
	exit;
}
add_action( 'admin_post_foxfire_save_public_content', 'foxfire_operations_save_public_content' );

/** Render the scoped public-content editor. */
function foxfire_operations_render_content_page(): void {
	if ( ! foxfire_operations_can_manage_public_content() ) {
		wp_die( esc_html__( 'You do not have permission to edit public site content.', 'foxfire-operations' ), esc_html__( 'Access denied', 'foxfire-operations' ), array( 'response' => 403 ) );
	}

	$schema   = foxfire_operations_public_content_schema();
	$saved_content = get_option( 'foxfire_public_content', array() );
	$sections = array(
		'seo'      => __( 'Search & social previews', 'foxfire-operations' ),
		'homepage' => __( 'Homepage', 'foxfire-operations' ),
		'about'    => __( 'About', 'foxfire-operations' ),
		'contact'  => __( 'Contact', 'foxfire-operations' ),
		'faq'      => __( 'Frequently Asked Questions', 'foxfire-operations' ),
		'footer'   => __( 'Footer', 'foxfire-operations' ),
	);
	// Read-only redirect status does not authorize or mutate anything.
	$status = isset( $_GET['foxfire_content_status'] ) ? sanitize_key( wp_unslash( $_GET['foxfire_content_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$audit  = get_option( 'foxfire_public_content_audit', array() );
	$policy_pages = array(
		'privacy-policy'           => __( 'Privacy Policy', 'foxfire-operations' ),
		'terms-and-conditions'      => __( 'Terms and Conditions', 'foxfire-operations' ),
		'refund-and-returns-policy' => __( 'Refund and Returns Policy', 'foxfire-operations' ),
		'shipping-policy'           => __( 'Shipping Policy', 'foxfire-operations' ),
	);
	?>
	<div class="wrap ff-ops-wrap ff-content-editor">
		<h1><?php esc_html_e( 'Site Content', 'foxfire-operations' ); ?></h1>
		<p class="ff-ops-intro"><?php esc_html_e( 'Update approved storefront wording without editing theme files. Plain text only; layout, links, forms, and security controls cannot be changed here. Policy bodies use their WordPress page editor and explicit approval switch.', 'foxfire-operations' ); ?></p>

		<?php if ( 'updated' === $status ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Site content saved.', 'foxfire-operations' ); ?></p></div>
		<?php elseif ( 'invalid-email' === $status ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Nothing was saved because the public support email was invalid.', 'foxfire-operations' ); ?></p></div>
		<?php elseif ( 'invalid-image' === $status ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Nothing was saved because the selected founder image was not a valid Media Library image.', 'foxfire-operations' ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ff-content-form">
			<input type="hidden" name="action" value="foxfire_save_public_content">
			<?php wp_nonce_field( 'foxfire_save_public_content', 'foxfire_public_content_nonce' ); ?>

			<?php foreach ( $sections as $section_key => $section_label ) : ?>
				<section class="ff-content-section" aria-labelledby="foxfire-content-<?php echo esc_attr( $section_key ); ?>">
					<h2 id="foxfire-content-<?php echo esc_attr( $section_key ); ?>"><?php echo esc_html( $section_label ); ?></h2>
					<table class="form-table" role="presentation"><tbody>
						<?php foreach ( $schema as $key => $field ) : ?>
							<?php if ( $section_key !== $field['section'] ) { continue; } ?>
							<?php
							$value = 'faq' === $field['section'] && is_array( $saved_content ) && array_key_exists( $key, $saved_content )
								? ( is_string( $saved_content[ $key ] ) ? $saved_content[ $key ] : '' )
								: foxfire_operations_get_public_content( $key, $field['default'] );
							?>
							<tr>
								<th scope="row"><label for="foxfire-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
								<td>
									<?php if ( 'textarea' === $field['type'] ) : ?>
										<textarea class="large-text" rows="3" id="foxfire-<?php echo esc_attr( $key ); ?>" name="foxfire_public_content[<?php echo esc_attr( $key ); ?>]" maxlength="<?php echo esc_attr( (string) $field['max'] ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
									<?php elseif ( 'checkbox' === $field['type'] ) : ?>
										<input type="hidden" name="foxfire_public_content[<?php echo esc_attr( $key ); ?>]" value="no">
										<label for="foxfire-<?php echo esc_attr( $key ); ?>">
											<input type="checkbox" id="foxfire-<?php echo esc_attr( $key ); ?>" name="foxfire_public_content[<?php echo esc_attr( $key ); ?>]" value="yes" <?php checked( 'yes', $value ); ?>>
											<?php esc_html_e( 'Display this section', 'foxfire-operations' ); ?>
										</label>
									<?php elseif ( 'image' === $field['type'] ) : ?>
										<?php
										$attachment_id = absint( $value );
										$preview_url  = $attachment_id > 0 ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : false;
										?>
										<div class="ff-content-image-field" data-choose-label="<?php echo esc_attr__( 'Use this image', 'foxfire-operations' ); ?>" data-dialog-title="<?php echo esc_attr__( 'Choose founder image', 'foxfire-operations' ); ?>" data-empty-label="<?php echo esc_attr__( 'Choose image', 'foxfire-operations' ); ?>" data-replace-label="<?php echo esc_attr__( 'Replace image', 'foxfire-operations' ); ?>">
											<input type="hidden" id="foxfire-<?php echo esc_attr( $key ); ?>" name="foxfire_public_content[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) $attachment_id ); ?>">
											<div class="ff-content-image-field__preview" <?php echo $preview_url ? '' : 'hidden'; ?>>
												<img src="<?php echo esc_url( $preview_url ? $preview_url : '' ); ?>" alt="">
											</div>
											<div class="ff-content-image-field__actions">
												<button type="button" class="button ff-content-image-field__choose"><?php echo esc_html( $preview_url ? __( 'Replace image', 'foxfire-operations' ) : __( 'Choose image', 'foxfire-operations' ) ); ?></button>
												<button type="button" class="button-link-delete ff-content-image-field__remove" <?php echo $preview_url ? '' : 'hidden'; ?>><?php esc_html_e( 'Remove image', 'foxfire-operations' ); ?></button>
											</div>
										</div>
									<?php else : ?>
										<input class="regular-text" type="<?php echo esc_attr( $field['type'] ); ?>" id="foxfire-<?php echo esc_attr( $key ); ?>" name="foxfire_public_content[<?php echo esc_attr( $key ); ?>]" maxlength="<?php echo esc_attr( (string) $field['max'] ); ?>" value="<?php echo esc_attr( $value ); ?>">
									<?php endif; ?>
									<p class="description"><?php echo esc_html( $field['description'] ); ?><?php if ( ! in_array( $field['type'], array( 'checkbox', 'image' ), true ) ) : ?> <?php esc_html_e( 'Leave blank to use the built-in fallback.', 'foxfire-operations' ); ?><?php endif; ?></p>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody></table>
				</section>
			<?php endforeach; ?>

			<?php submit_button( __( 'Save site content', 'foxfire-operations' ) ); ?>
		</form>

		<section class="ff-content-section ff-content-policy-links" aria-labelledby="foxfire-content-policies">
			<h2 id="foxfire-content-policies"><?php esc_html_e( 'Policy pages', 'foxfire-operations' ); ?></h2>
			<p><?php esc_html_e( 'Policy bodies use the WordPress editor so revisions are retained. The editor copy is public only after the Foxfire Page Display switch is enabled on that page.', 'foxfire-operations' ); ?></p>
			<ul>
				<?php foreach ( $policy_pages as $policy_slug => $policy_label ) : ?>
					<?php
					$policy_page = get_page_by_path( $policy_slug );
					if ( ! $policy_page instanceof WP_Post || ! current_user_can( 'edit_post', $policy_page->ID ) ) {
						continue;
					}
					$policy_enabled = foxfire_operations_should_use_editor_content( $policy_page->ID );
					?>
					<li>
						<a href="<?php echo esc_url( get_edit_post_link( $policy_page->ID, 'raw' ) ); ?>"><?php echo esc_html( $policy_label ); ?></a>
						<span class="ff-content-policy-status <?php echo $policy_enabled ? 'is-active' : 'is-fallback'; ?>">
							<?php echo esc_html( $policy_enabled ? __( 'Editor copy is public', 'foxfire-operations' ) : __( 'Built-in fallback is public', 'foxfire-operations' ) ); ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>

		<?php if ( is_array( $audit ) && ! empty( $audit['updated_gmt'] ) ) : ?>
			<p class="description"><?php printf( esc_html__( 'Last saved: %s UTC.', 'foxfire-operations' ), esc_html( (string) $audit['updated_gmt'] ) ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

<?php
/**
 * Template Name: Frequently Asked Questions
 *
 * Client-managed FAQ page using the scoped Foxfire Operations fields.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$faqs          = array();
$saved_content = get_option( 'foxfire_public_content', null );

$default_faqs = array(
		array(
			'question' => __( 'How do I place an order?', 'foxfire-child' ),
			'answer'   => __( 'Browse the shop, choose an available product and quantity option, add it to your cart, and complete checkout using one of the currently enabled payment methods.', 'foxfire-child' ),
		),
		array(
			'question' => __( 'What quantity options are available?', 'foxfire-child' ),
			'answer'   => __( 'Each product page shows the quantity options currently available for that item. Pricing and availability may vary by product.', 'foxfire-child' ),
		),
		array(
			'question' => __( 'Where can I find testing and COA documents?', 'foxfire-child' ),
			'answer'   => __( 'Use the Testing/COA directory and search for the batch or lot number. A report link appears only when a document has been attached for that batch.', 'foxfire-child' ),
		),
		array(
			'question' => __( 'Where can I find the batch or lot number?', 'foxfire-child' ),
			'answer'   => __( 'Check the product label or packaging for the batch or lot number, then use it in the Testing/COA directory.', 'foxfire-child' ),
		),
		array(
			'question' => __( 'Which payment methods can I use?', 'foxfire-child' ),
			'answer'   => __( 'Checkout displays the payment methods currently enabled by Foxfire Peptides. Available methods may change after the client approves the production payment provider.', 'foxfire-child' ),
		),
		array(
			'question' => __( 'Are these products intended for human consumption?', 'foxfire-child' ),
			'answer'   => __( 'No. Products are offered for laboratory research use only and are not for human consumption.', 'foxfire-child' ),
		),
		array(
			'question' => __( 'How is shipping calculated?', 'foxfire-child' ),
			'answer'   => __( 'Cart and checkout calculate the eligible shipping amount from the destination and current order total. Free shipping is applied automatically when the saved WooCommerce rule is met.', 'foxfire-child' ),
		),
		array(
			'question' => __( 'Is an account required to order?', 'foxfire-child' ),
			'answer'   => __( 'You can browse products without an account. The checkout page reflects the current guest-checkout setting, while an account provides access to saved addresses, orders, tracking, and available COA documents.', 'foxfire-child' ),
		),
	);

for ( $faq_index = 1; $faq_index <= 8; $faq_index++ ) {
	$question_key = 'faq_question_' . $faq_index;
	$answer_key   = 'faq_answer_' . $faq_index;
	$default_faq  = $default_faqs[ $faq_index - 1 ];
	$question     = is_array( $saved_content ) && array_key_exists( $question_key, $saved_content )
		? trim( (string) $saved_content[ $question_key ] )
		: $default_faq['question'];
	$answer       = is_array( $saved_content ) && array_key_exists( $answer_key, $saved_content )
		? trim( (string) $saved_content[ $answer_key ] )
		: $default_faq['answer'];
	if ( '' !== $question && '' !== $answer ) {
		$faqs[] = array( 'question' => $question, 'answer' => $answer );
	}
}
?>
<main id="main-content" class="ff-homepage ff-faq-page" tabindex="-1">
	<section class="ff-home-faq" aria-labelledby="ff-faq-page-heading">
		<div class="ff-home-faq__inner">
			<header class="ff-home-faq__header">
				<span class="ff-home-faq__eyebrow"><?php esc_html_e( 'Help & Information', 'foxfire-child' ); ?></span>
				<h1 id="ff-faq-page-heading" class="ff-home-faq__title"><?php the_title(); ?></h1>
			</header>

			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<?php if ( '' !== trim( wp_strip_all_tags( get_the_content() ) ) ) : ?>
						<div class="ff-faq-page__intro"><?php the_content(); ?></div>
					<?php endif; ?>
				<?php endwhile; ?>
			<?php endif; ?>

			<?php if ( ! empty( $faqs ) ) : ?>
				<div class="ff-home-faq__list">
					<?php foreach ( $faqs as $faq_position => $faq ) : ?>
						<details class="ff-home-faq__item" <?php echo 0 === $faq_position ? 'open' : ''; ?>>
							<summary class="ff-home-faq__question">
								<span class="ff-home-faq__q-text"><?php echo esc_html( $faq['question'] ); ?></span>
								<span class="ff-home-faq__icon" aria-hidden="true">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
								</span>
							</summary>
							<div class="ff-home-faq__answer"><p><?php echo esc_html( $faq['answer'] ); ?></p></div>
						</details>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="ff-faq-page__empty"><?php esc_html_e( 'Frequently asked questions are being prepared. Please contact us if you need assistance.', 'foxfire-child' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();

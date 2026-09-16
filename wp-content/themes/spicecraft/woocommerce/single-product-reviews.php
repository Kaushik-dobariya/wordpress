<?php
/**
 * SpiceCraft - Custom Product Reviews Template
 *
 * Overrides WooCommerce's default single-product-reviews.php to present a
 * refined, trustworthy reviews section with score summary, verified review cards,
 * and a streamlined submission form.
 *
 * @package SpiceCraft
 * @version 9.7.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
	return;
}

$count   = $product->get_review_count();
$average = $product->get_average_rating();
?>
<div id="reviews" class="sc-reviews-container woocommerce-Reviews">

	<!-- 1. Reviews Header & Average Score Summary -->
	<div class="sc-reviews-summary">
		<div class="sc-reviews-summary__header">
			<h2 class="sc-reviews-summary__title"><?php esc_html_e( 'Customer Reviews & Feedback', 'spicecraft' ); ?></h2>
			<p class="sc-reviews-summary__desc">
				<?php esc_html_e( 'Verified feedback from institutional buyers, chefs, and retail customers.', 'spicecraft' ); ?>
			</p>
		</div>

		<?php if ( $count > 0 && wc_review_ratings_enabled() ) : ?>
			<div class="sc-reviews-scorecard">
				<div class="sc-reviews-scorecard__num"><?php echo esc_html( number_format( (float) $average, 1 ) ); ?></div>
				<div class="sc-reviews-scorecard__stars">
					<span class="sc-stars" aria-hidden="true">
						<?php
						$score = round( (float) $average );
						for ( $i = 1; $i <= 5; $i++ ) {
							echo $i <= $score ? '★' : '☆';
						}
						?>
					</span>
					<span class="sc-reviews-scorecard__sub">
						<?php
						/* translators: 1: average, 2: count */
						printf( esc_html__( '%1$s out of 5 based on %2$s reviews', 'spicecraft' ), esc_html( number_format( (float) $average, 1 ) ), esc_html( $count ) );
						?>
					</span>
				</div>
			</div>
		<?php else : ?>
			<div class="sc-reviews-scorecard sc-reviews-scorecard--empty">
				<span class="sc-stars sc-stars--muted" aria-hidden="true">★★★★★</span>
				<span class="sc-reviews-scorecard__sub"><?php esc_html_e( 'No customer reviews submitted yet for this product batch.', 'spicecraft' ); ?></span>
			</div>
		<?php endif; ?>
	</div><!-- .sc-reviews-summary -->

	<!-- 2. Reviews List -->
	<div id="comments" class="sc-reviews-list-wrapper">
		<?php if ( have_comments() ) : ?>
			<ol class="commentlist sc-reviews-list">
				<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) ); ?>
			</ol>

			<?php
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="woocommerce-pagination sc-reviews-pagination">';
				paginate_comments_links(
					apply_filters(
						'woocommerce_comment_pagination_args',
						array(
							'prev_text' => '&larr; ' . esc_html__( 'Previous', 'spicecraft' ),
							'next_text' => esc_html__( 'Next', 'spicecraft' ) . ' &rarr;',
							'type'      => 'list',
						)
					)
				);
				echo '</nav>';
			endif;
			?>
		<?php endif; ?>
	</div><!-- #comments -->

	<!-- 3. Review Submission Form -->
	<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
		<div id="review_form_wrapper" class="sc-review-form-wrapper">
			<div id="review_form">
				<?php
				$commenter    = wp_get_current_commenter();
				$comment_form = array(
					'title_reply'         => have_comments() ? esc_html__( 'Write a Review', 'spicecraft' ) : sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'spicecraft' ), get_the_title() ),
					'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'spicecraft' ),
					'title_reply_before'  => '<h3 id="reply-title" class="comment-reply-title sc-review-form-title">',
					'title_reply_after'   => '</h3>',
					'comment_notes_before' => '<p class="sc-review-form-notes">' . esc_html__( 'Your email address will not be published. Required fields are marked *', 'spicecraft' ) . '</p>',
					'comment_notes_after'  => '',
					'label_submit'        => esc_html__( 'Submit Review', 'spicecraft' ),
					'class_submit'        => 'sc-btn sc-btn--primary sc-review-submit-btn',
					'logged_in_as'        => '',
					'comment_field'       => '',
				);

				$name_email_required = (bool) get_option( 'require_name_email', 1 );
				$fields              = array(
					'author' => array(
						'label'        => __( 'Your Name', 'spicecraft' ),
						'type'         => 'text',
						'value'        => $commenter['comment_author'],
						'required'     => $name_email_required,
						'autocomplete' => 'name',
					),
					'email'  => array(
						'label'        => __( 'Your Email', 'spicecraft' ),
						'type'         => 'email',
						'value'        => $commenter['comment_author_email'],
						'required'     => $name_email_required,
						'autocomplete' => 'email',
					),
				);

				$comment_form['fields'] = array();

				foreach ( $fields as $key => $field ) {
					$field_html  = '<p class="comment-form-' . esc_attr( $key ) . ' sc-form-group">';
					$field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );

					if ( $field['required'] ) {
						$field_html .= '&nbsp;<span class="required">*</span>';
					}

					$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" autocomplete="' . esc_attr( $field['autocomplete'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' class="sc-form-control" /></p>';

					$comment_form['fields'][ $key ] = $field_html;
				}

				if ( wc_review_ratings_enabled() ) {
					$comment_form['comment_field'] = '<div class="comment-form-rating sc-form-group">
						<label for="rating" id="comment-form-rating-label">' . esc_html__( 'Your Rating', 'spicecraft' ) . ( wc_review_ratings_required() ? '&nbsp;<span class="required">*</span>' : '' ) . '</label>
						<select name="rating" id="rating" required class="sc-form-control sc-rating-select">
							<option value="">' . esc_html__( 'Select Rating (1 to 5 stars)', 'spicecraft' ) . '</option>
							<option value="5">★★★★★ ' . esc_html__( '5 Stars - Exceptional Quality', 'spicecraft' ) . '</option>
							<option value="4">★★★★☆ ' . esc_html__( '4 Stars - High Quality', 'spicecraft' ) . '</option>
							<option value="3">★★★☆☆ ' . esc_html__( '3 Stars - Standard / Average', 'spicecraft' ) . '</option>
							<option value="2">★★☆☆☆ ' . esc_html__( '2 Stars - Below Expectations', 'spicecraft' ) . '</option>
							<option value="1">★☆☆☆☆ ' . esc_html__( '1 Star - Poor Quality', 'spicecraft' ) . '</option>
						</select>
					</div>';
				}

				$comment_form['comment_field'] .= '<p class="comment-form-comment sc-form-group">
					<label for="comment">' . esc_html__( 'Your Review & Comments', 'spicecraft' ) . '&nbsp;<span class="required">*</span></label>
					<textarea id="comment" name="comment" cols="45" rows="5" required class="sc-form-control" placeholder="' . esc_attr__( 'Share your experience regarding aroma, flavor potency, packaging condition, or culinary results...', 'spicecraft' ) . '"></textarea>
				</p>';

				comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
				?>
			</div>
		</div>
	<?php else : ?>
		<p class="woocommerce-verification-required sc-verification-note">
			<?php esc_html_e( 'Only verified customers may leave a product review.', 'spicecraft' ); ?>
		</p>
	<?php endif; ?>

</div><!-- #reviews -->

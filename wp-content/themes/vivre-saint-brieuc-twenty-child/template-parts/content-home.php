<?php
/**
 * The default template for displaying content
 *
 * Used for both singular and index.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty
 * @since Twenty Twenty 1.0
 */

?>

<?php $loop_post_id = (int) get_the_ID(); ?>

<article <?php post_class( '', $loop_post_id ); ?> id="post-<?php echo esc_attr( $loop_post_id ); ?>">
	<?php

	get_template_part( 'template-parts/entry-header' );

	if ( ! is_search() && has_post_thumbnail( $loop_post_id ) && ! post_password_required( $loop_post_id ) ) {
		$caption = get_the_post_thumbnail_caption( $loop_post_id );
		?>

		<figure class="featured-media">
			<div class="featured-media-inner section-inner medium">
				<?php echo get_the_post_thumbnail( $loop_post_id ); ?>

				<?php if ( $caption ) : ?>
					<figcaption class="wp-caption-text"><?php echo wp_kses_post( $caption ); ?></figcaption>
				<?php endif; ?>
			</div><!-- .featured-media-inner -->
		</figure><!-- .featured-media -->

		<?php
	}

	?>

	<div class="post-inner <?php echo is_page_template( 'templates/template-full-width.php' ) ? '' : 'thin'; ?> ">

		<div class="entry-content">

			<?php
			$excerpt      = trim( (string) get_post_field( 'post_excerpt', $loop_post_id ) );
			$post_content = (string) get_post_field( 'post_content', $loop_post_id );

			if ( '' === $excerpt ) {
				$excerpt = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post_content ) ), 55, '...' );
			}

			echo wp_kses_post( wpautop( $excerpt ) );
			?>

		</div><!-- .entry-content -->

	</div><!-- .post-inner -->

	<div class="section-inner">
		<?php
		wp_link_pages(
			array(
				'before'      => '<nav class="post-nav-links bg-light-background" aria-label="' . esc_attr__( 'Page', 'twentytwenty' ) . '"><span class="label">' . __( 'Pages:', 'twentytwenty' ) . '</span>',
				'after'       => '</nav>',
				'link_before' => '<span class="page-number">',
				'link_after'  => '</span>',
			)
		);

		edit_post_link();

		// Single bottom post meta.
		twentytwenty_the_post_meta( $loop_post_id, 'single-bottom' );

		if ( post_type_supports( get_post_type( $loop_post_id ), 'author' ) && is_single() ) {

			get_template_part( 'template-parts/entry-author-bio' );

		}
		?>

	</div><!-- .section-inner -->

	<?php

	if ( is_single() ) {

		get_template_part( 'template-parts/navigation' );

	}

	/*
	 * Output comments wrapper if it's a post, or if comments are open,
	 * or if there's a comment number – and check for password.
	 */
	if ( ( is_single() || is_page() ) && ( comments_open() || get_comments_number() ) && ! post_password_required() ) {
		?>

		<div class="comments-wrapper section-inner">

			<?php comments_template(); ?>

		</div><!-- .comments-wrapper -->

		<?php
	}
	?>

</article><!-- .post -->

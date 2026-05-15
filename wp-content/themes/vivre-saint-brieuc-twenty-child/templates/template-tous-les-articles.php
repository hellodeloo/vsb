<?php
/**
 * Template Name: Tous les articles
 * Description: Page listant tous les articles avec pagination.
 */

global $is_articles_page;
$is_articles_page = true;

get_header();
?>

<main id="home-content">
	<?php
	$paged = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );

	$all_posts_query = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => get_option( 'posts_per_page' ),
			'ignore_sticky_posts' => true,
			'paged'               => $paged,
		)
	);
	?>

	<header class="archive-header has-text-align-center header-footer-group">
		<div class="archive-header-inner section-inner medium">
			<h1 class="archive-title"><?php esc_html_e( 'Tous les articles', 'vivre-saint-brieuc-twenty-child' ); ?></h1>
		</div>
	</header>

	<?php if ( $all_posts_query->have_posts() ) : ?>
		<?php
		$post_count = 0;
		while ( $all_posts_query->have_posts() ) :
			$all_posts_query->the_post();
			++$post_count;
			$grid_item_index = $post_count - 1;
			?>

			<?php if ( 1 === $post_count ) : ?>
				<?php get_template_part( 'template-parts/content-home', get_post_type() ); ?>

				<hr class="post-separator styled-separator is-style-wide section-inner" aria-hidden="true" />

			<?php else : ?>

				<?php if ( 1 === ( $grid_item_index % 2 ) ) : ?>
					<div class="home-grid section-inner">
				<?php endif; ?>

				<div class="home-grid-item">
					<?php get_template_part( 'template-parts/content-home', get_post_type() ); ?>
				</div>

				<?php if ( 0 === ( $grid_item_index % 2 ) ) : ?>
					</div>
				<?php endif; ?>

			<?php endif; ?>

		<?php endwhile; ?>

		<?php if ( 0 !== ( ( $post_count - 1 ) % 2 ) ) : ?>
			</div>
		<?php endif; ?>

		<?php $total_pages = (int) $all_posts_query->max_num_pages; ?>
		<?php if ( $total_pages > 1 ) : ?>
			<nav class="pagination-single section-inner" aria-label="<?php esc_attr_e( 'Pagination des articles', 'vivre-saint-brieuc-twenty-child' ); ?>">
				<hr class="styled-separator is-style-wide" aria-hidden="true" />

				<div class="pagination-single-inner">
					<?php
					if ( $paged > 1 ) {
						echo '<a class="previous-post" href="' . esc_url( get_pagenum_link( $paged - 1 ) ) . '"><span class="arrow" aria-hidden="true">&larr;</span><span class="title"><span class="title-inner">' . esc_html__( 'Precedent', 'vivre-saint-brieuc-twenty-child' ) . '</span></span></a>';
					} else {
						echo '<a class="previous-post disabled" aria-disabled="true"><span class="arrow" aria-hidden="true">&larr;</span><span class="title"><span class="title-inner">' . esc_html__( 'Precedent', 'vivre-saint-brieuc-twenty-child' ) . '</span></span></a>';
					}
					?>

					<div class="pagination-numbers">
						<?php
						for ( $page_number = 1; $page_number <= $total_pages; ++$page_number ) {
							if ( $page_number === (int) $paged ) {
								echo '<span aria-current="page" class="page-numbers current disabled">' . esc_html( (string) $page_number ) . '</span>';
							} else {
								echo '<a class="page-numbers" href="' . esc_url( get_pagenum_link( $page_number ) ) . '">' . esc_html( (string) $page_number ) . '</a>';
							}
						}
						?>
					</div>

					<?php
					if ( $paged < $total_pages ) {
						echo '<a class="next-post" href="' . esc_url( get_pagenum_link( $paged + 1 ) ) . '"><span class="arrow" aria-hidden="true">&rarr;</span><span class="title"><span class="title-inner">' . esc_html__( 'Suivant', 'vivre-saint-brieuc-twenty-child' ) . '</span></span></a>';
					} else {
						echo '<a class="next-post disabled" aria-disabled="true"><span class="arrow" aria-hidden="true">&rarr;</span><span class="title"><span class="title-inner">' . esc_html__( 'Suivant', 'vivre-saint-brieuc-twenty-child' ) . '</span></span></a>';
					}
					?>
				</div><!-- .pagination-single-inner -->

				<hr class="styled-separator is-style-wide" aria-hidden="true" />
			</nav><!-- .pagination-single -->
		<?php endif; ?>
	<?php else : ?>
		<p class="section-inner"><?php esc_html_e( 'Aucun article trouve.', 'vivre-saint-brieuc-twenty-child' ); ?></p>
	<?php endif; ?>

	<?php wp_reset_postdata(); ?>
	<?php $is_articles_page = false; ?>
</main>

<?php get_template_part( 'template-parts/footer-menus-widgets' ); ?>

<?php
get_footer();

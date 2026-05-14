<?php
/**
 * Template Name: Tous les articles
 * Description: Page listant tous les articles avec pagination.
 */

get_header();
?>

<main id="site-content">
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

			if ( $post_count > 1 ) {
				echo '<hr class="post-separator styled-separator is-style-wide section-inner" aria-hidden="true" />';
			}

			get_template_part( 'template-parts/content-home', get_post_type() );
		endwhile;
		?>

		<nav class="pagination-single section-inner" aria-label="<?php esc_attr_e( 'Pagination des articles', 'vivre-saint-brieuc-twenty-child' ); ?>">
			<?php
			echo wp_kses_post(
				paginate_links(
					array(
						'total'     => (int) $all_posts_query->max_num_pages,
						'current'   => $paged,
						'mid_size'  => 1,
						'prev_text' => __( 'Precedent', 'vivre-saint-brieuc-twenty-child' ),
						'next_text' => __( 'Suivant', 'vivre-saint-brieuc-twenty-child' ),
					)
				)
			);
			?>
		</nav>
	<?php else : ?>
		<p class="section-inner"><?php esc_html_e( 'Aucun article trouve.', 'vivre-saint-brieuc-twenty-child' ); ?></p>
	<?php endif; ?>

	<?php wp_reset_postdata(); ?>
</main>

<?php get_template_part( 'template-parts/footer-menus-widgets' ); ?>

<?php
get_footer();

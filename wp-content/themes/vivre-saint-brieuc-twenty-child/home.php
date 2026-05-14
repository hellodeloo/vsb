<?php
/**
 * Template de la page d'accueil quand WordPress affiche les derniers articles.
 *
 * Dans cette configuration, WordPress charge home.php avant index.php.
 */

get_header();
?>

<main id="home-content">
  <?php if ( have_posts() ) : ?>
    <?php
    $home_index      = 0;
    $home_grid_open  = false;
    ?>

    <?php while ( have_posts() ) : the_post(); ?>
      <?php ++$home_index; ?>

      <?php if ( 1 === $home_index ) : ?>
        <?php get_template_part( 'template-parts/content-home', get_post_type() ); ?>

        <hr class="post-separator styled-separator is-style-wide section-inner" aria-hidden="true" />

      <?php elseif ( $home_index <= 3 ) : ?>

        <?php if ( ! $home_grid_open ) : ?>
          <div class="home-grid section-inner">
          <?php $home_grid_open = true; ?>
        <?php endif; ?>

        <div class="home-grid-item">
          <?php get_template_part( 'template-parts/content-home', get_post_type() ); ?>
        </div>

      <?php endif; ?>

      <?php if ( 3 === $home_index ) : ?>
        <?php break; ?>
      <?php endif; ?>
    <?php endwhile; ?>

    <?php if ( $home_grid_open ) : ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="pagination-single section-inner">
    <hr class="styled-separator is-style-wide" aria-hidden="true">
    <div class="pagination-single-inner">
      <a href="<?php echo esc_url( $all_posts_page_url ); ?>"><?php esc_html_e( 'Voir tous les articles', 'vivre-saint-brieuc-twenty-child' ); ?></a>
    </div>
    <hr class="styled-separator is-style-wide" aria-hidden="true">
  </div>

</main><!-- #site-content -->

<?php get_template_part( 'template-parts/footer-menus-widgets' ); ?>

<?php
get_footer();

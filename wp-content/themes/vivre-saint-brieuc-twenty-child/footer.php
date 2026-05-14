<?php
/**
 * The template for displaying the footer
 *
 * Contains the opening of the #site-footer div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Twenty
 * @since Twenty Twenty 1.0
 */

?>
			<footer id="site-footer" class="header-footer-group">

				<div class="section-inner">

					<div class="footer-credits">

						<p class="footer-copyright">&copy;
							<?php
							/* translators: Copyright date format, see https://www.php.net/manual/datetime.format.php */
							$date_format = _x( 'Y', 'copyright date format', 'twentytwenty' );
							if ( function_exists( 'wp_date' ) ) {
								echo wp_date( $date_format );
							} else {
								echo date_i18n( $date_format );
							}
							?>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
						</p><!-- .footer-copyright -->

						<?php
						if ( function_exists( 'the_privacy_policy_link' ) ) {
							the_privacy_policy_link( '<p class="privacy-policy">', '</p>' );
						}
						?>

					</div><!-- .footer-credits -->

					<?php
					$has_footer_menu = has_nav_menu( 'footer' );
					?>

					<?php if ( $has_footer_menu ) { ?>
            <ul class="footer-bottom-menu reset-list-style">
              <?php
              wp_nav_menu(
                array(
                  'container'      => '',
                  'depth'          => 1,
                  'items_wrap'     => '%3$s',
                  'theme_location' => 'footer',
                )
              );
              ?>
            </ul>
					<?php } ?>

				</div><!-- .section-inner -->

			</footer><!-- #site-footer -->

		<?php wp_footer(); ?>

	</body>
</html>

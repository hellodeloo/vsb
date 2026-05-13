<?php
/**
 * Theme setup for Vivre Saint-Brieuc Child (Twenty Twenty).
 */

function vsb_twenty_child_enqueue_styles() {
	$theme        = wp_get_theme();
	$parent_theme = $theme->parent();

	wp_enqueue_style(
		'twentytwenty-style',
		get_template_directory_uri() . '/style.css',
		array(),
		$parent_theme ? $parent_theme->get( 'Version' ) : null
	);

	wp_enqueue_style(
		'vsb-twenty-child-style',
		get_stylesheet_uri(),
		array( 'twentytwenty-style' ),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'vsb_twenty_child_enqueue_styles' );


add_filter( 'twentytwenty_site_description', function( $html, $description, $wrapper ) {
    // Exemple: personnaliser le wrapper.
    return sprintf(
        '<p class="site-description screen-reader-text">%s</p>',
        esc_html( $description )
    );
}, 10, 3 );


add_filter(
    'twentytwenty_post_meta_location_single_top',
    function ( $post_meta ) {
        return array_values(
            array_filter(
                (array) $post_meta,
                function ( $item ) {
                    return ! in_array( $item, array( 'comments', 'author' ), true );
                }
            )
        );
    }
);

add_filter( 'edit_post_link', '__return_empty_string', 99 );

/**
 * Register a custom taxonomy to assign "auteurs" to posts from a controlled list.
 */
function vsb_register_auteurs_taxonomy() {
    $labels = array(
        'name'              => __( 'Auteurs', 'vivre-saint-brieuc-twenty-child' ),
        'singular_name'     => __( 'Auteur', 'vivre-saint-brieuc-twenty-child' ),
        'search_items'      => __( 'Rechercher des auteurs', 'vivre-saint-brieuc-twenty-child' ),
        'all_items'         => __( 'Tous les auteurs', 'vivre-saint-brieuc-twenty-child' ),
        'edit_item'         => __( 'Modifier l\'auteur', 'vivre-saint-brieuc-twenty-child' ),
        'update_item'       => __( 'Mettre a jour l\'auteur', 'vivre-saint-brieuc-twenty-child' ),
        'add_new_item'      => __( 'Ajouter un auteur', 'vivre-saint-brieuc-twenty-child' ),
        'new_item_name'     => __( 'Nom du nouvel auteur', 'vivre-saint-brieuc-twenty-child' ),
        'menu_name'         => __( 'Auteurs', 'vivre-saint-brieuc-twenty-child' ),
        'not_found'         => __( 'Aucun auteur trouve.', 'vivre-saint-brieuc-twenty-child' ),
        'back_to_items'     => __( 'Retour aux auteurs', 'vivre-saint-brieuc-twenty-child' ),
    );

    $args = array(
        'labels'            => $labels,
        'public'            => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'auteur' ),
        'meta_box_cb'       => 'post_categories_meta_box',
        'capabilities'      => array(
            'manage_terms' => 'manage_categories',
            'edit_terms'   => 'manage_categories',
            'delete_terms' => 'manage_categories',
            'assign_terms' => 'edit_posts',
        ),
    );

    register_taxonomy( 'vsb_auteur', array( 'post' ), $args );
}
add_action( 'init', 'vsb_register_auteurs_taxonomy' );

/**
 * Display selected taxonomy authors in post meta (replaces the native post author).
 *
 * @param int    $post_id   Current post ID.
 * @param array  $post_meta Post meta keys list.
 * @param string $location  Meta location.
 */
function vsb_output_post_meta_auteurs( $post_id, $post_meta, $location ) {
    if ( 'single-top' !== $location ) {
        return;
    }

    $terms = get_the_terms( $post_id, 'vsb_auteur' );
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return;
    }

    $links = array();
    foreach ( $terms as $term ) {
        $term_link = get_term_link( $term );
        if ( is_wp_error( $term_link ) ) {
            continue;
        }

        $links[] = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url( $term_link ),
            esc_html( $term->name )
        );
    }

    if ( empty( $links ) ) {
        return;
    }
    ?>
    <li class="post-author meta-wrapper post-author-taxonomy">
        <span class="meta-icon">
            <span class="screen-reader-text">
                <?php esc_html_e( 'Post author', 'twentytwenty' ); ?>
            </span>
            <?php twentytwenty_the_theme_svg( 'user' ); ?>
        </span>
        <span class="meta-text">
            <?php
            printf(
                /* translators: %s: Authors list. */
                esc_html__( 'By %s', 'twentytwenty' ),
                wp_kses_post( implode( ', ', $links ) )
            );
            ?>
        </span>
    </li>
    <?php
}
add_action( 'twentytwenty_start_of_post_meta_list', 'vsb_output_post_meta_auteurs', 10, 3 );

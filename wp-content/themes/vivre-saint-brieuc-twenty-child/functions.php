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
    // Cache visuellement le slogan, tout en le conservant pour les lecteurs d'ecran.
    return sprintf(
        '<p class="site-description screen-reader-text">%s</p>',
        esc_html( $description )
    );
}, 10, 3 );


/**
 * Ajuste les metas en haut d'article: masque author/comments natifs, conserve post-date.
 *
 * @param array $post_meta Liste des metas du thème parent.
 * @return array
 */
function vsb_filter_single_top_post_meta( $post_meta ) {
    return array_values(
        array_filter(
            (array) $post_meta,
            function ( $item ) {
                return ! in_array( $item, array( 'comments', 'author' ), true );
            }
        )
    );
}
add_filter( 'twentytwenty_post_meta_location_single_top', 'vsb_filter_single_top_post_meta' );

/**
 * Démarre un buffer local pour retraiter le HTML des metas de tête d'article.
 * Priorité 11 : après les ajouts custom (ex: vsb_auteur en priorité 10).
 *
 * @param int    $post_id   ID de l'article.
 * @param array  $post_meta Liste des metas.
 * @param string $location  Emplacement du bloc meta.
 */
function vsb_start_single_top_meta_buffer( $post_id, $post_meta, $location ) {
    if ( 'single-top' !== $location ) {
        return;
    }

    ob_start();
}
add_action( 'twentytwenty_start_of_post_meta_list', 'vsb_start_single_top_meta_buffer', 11, 3 );

/**
 * Supprime uniquement le lien autour de la date dans le bloc meta.
 *
 * @param string $content HTML du bloc meta capturé.
 * @return string
 */
function vsb_strip_post_date_link( $content ) {
    return preg_replace(
        '/(<li class="post-date[^"]*">.*?<span class="meta-text">\s*)<a[^>]*>(.*?)<\/a>(\s*<\/span>)/s',
        '$1$2$3',
        $content
    );
}

/**
 * Ferme le buffer local et réémet le contenu après retrait du lien de date.
 * Priorité 99 : exécution en fin de bloc.
 *
 * @param int    $post_id   ID de l'article.
 * @param array  $post_meta Liste des metas.
 * @param string $location  Emplacement du bloc meta.
 */
function vsb_end_single_top_meta_buffer( $post_id, $post_meta, $location ) {
    if ( 'single-top' !== $location ) {
        return;
    }

    $content = ob_get_clean();
    echo vsb_strip_post_date_link( $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'twentytwenty_end_of_post_meta_list', 'vsb_end_single_top_meta_buffer', 99, 3 );

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

/**
 * Ajoute l'icone Bluesky aux icones sociales de Twenty Twenty.
 * Le SVG est injecte via le filtre de la classe TwentyTwenty_SVG_Icons.
 */
add_filter( 'twentytwenty_svg_icons_social', function( $icons ) {
	$icons['bluesky'] = '<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.815 2.736 3.713 3.66 6.383 3.364.136-.02.275-.039.415-.056-.138.022-.276.04-.415.056-3.912.58-7.387 2.005-2.83 7.078 5.013 5.19 6.87-1.113 7.823-4.308.953 3.195 2.05 9.271 7.733 4.308 4.267-4.308 1.172-6.498-2.74-7.078a8.741 8.741 0 0 1-.415-.056c.14.017.279.036.415.056 2.67.297 5.568-.628 6.383-3.364.246-.828.624-5.79.624-6.478 0-.69-.139-1.861-.902-2.204-.659-.299-1.664-.62-4.3 1.24C16.046 4.748 13.087 8.687 12 10.8Z"/></svg>';
	return $icons;
} );

/**
 * Mappe les domaines Bluesky vers l'icone 'bluesky'.
 */
add_filter( 'twentytwenty_social_icons_map', function( $map ) {
	$map['bluesky'] = array( 'bsky.app', 'bluesky.social' );
	return $map;
} );

/**
 * Surcharge l'icone Facebook.
 * Modifier width/height et/ou le path SVG selon les besoins.
 */
add_filter( 'twentytwenty_svg_icons_social', function( $icons ) {
	$icons['facebook'] = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M14 13.5H16.5L17.5 9.5H14V7.5C14 6.47062 14 5.5 16 5.5H17.5V2.1401C17.1743 2.09685 15.943 2 14.6429 2C11.9284 2 10 3.65686 10 6.69971V9.5H7V13.5H10V22H14V13.5Z"></path></svg>';
	return $icons;
}, 20 );

/**
 * Surcharge l'icone Instagram.
 * Modifier width/height et/ou le path SVG selon les besoins.
 */
add_filter( 'twentytwenty_svg_icons_social', function( $icons ) {
	$icons['instagram'] = '<svg width="28" height="28" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M12,4.622c2.403,0,2.688,0.009,3.637,0.052c0.877,0.04,1.354,0.187,1.671,0.31c0.42,0.163,0.72,0.358,1.035,0.673 c0.315,0.315,0.51,0.615,0.673,1.035c0.123,0.317,0.27,0.794,0.31,1.671c0.043,0.949,0.052,1.234,0.052,3.637 s-0.009,2.688-0.052,3.637c-0.04,0.877-0.187,1.354-0.31,1.671c-0.163,0.42-0.358,0.72-0.673,1.035 c-0.315,0.315-0.615,0.51-1.035,0.673c-0.317,0.123-0.794,0.27-1.671,0.31c-0.949,0.043-1.233,0.052-3.637,0.052 s-2.688-0.009-3.637-0.052c-0.877-0.04-1.354-0.187-1.671-0.31c-0.42-0.163-0.72-0.358-1.035-0.673 c-0.315-0.315-0.51-0.615-0.673-1.035c-0.123-0.317-0.27-0.794-0.31-1.671C4.631,14.688,4.622,14.403,4.622,12 s0.009-2.688,0.052-3.637c0.04-0.877,0.187-1.354,0.31-1.671c0.163-0.42,0.358-0.72,0.673-1.035 c0.315-0.315,0.615-0.51,1.035-0.673c0.317-0.123,0.794-0.27,1.671-0.31C9.312,4.631,9.597,4.622,12,4.622 M12,3 C9.556,3,9.249,3.01,8.289,3.054C7.331,3.098,6.677,3.25,6.105,3.472C5.513,3.702,5.011,4.01,4.511,4.511 c-0.5,0.5-0.808,1.002-1.038,1.594C3.25,6.677,3.098,7.331,3.054,8.289C3.01,9.249,3,9.556,3,12c0,2.444,0.01,2.751,0.054,3.711 c0.044,0.958,0.196,1.612,0.418,2.185c0.23,0.592,0.538,1.094,1.038,1.594c0.5,0.5,1.002,0.808,1.594,1.038 c0.572,0.222,1.227,0.375,2.185,0.418C9.249,20.99,9.556,21,12,21s2.751-0.01,3.711-0.054c0.958-0.044,1.612-0.196,2.185-0.418 c0.592-0.23,1.094-0.538,1.594-1.038c0.5-0.5,0.808-1.002,1.038-1.594c0.222-0.572,0.375-1.227,0.418-2.185 C20.99,14.751,21,14.444,21,12s-0.01-2.751-0.054-3.711c-0.044-0.958-0.196-1.612-0.418-2.185c-0.23-0.592-0.538-1.094-1.038-1.594 c-0.5-0.5-1.002-0.808-1.594-1.038c-0.572-0.222-1.227-0.375-2.185-0.418C14.751,3.01,14.444,3,12,3L12,3z M12,7.378 c-2.552,0-4.622,2.069-4.622,4.622S9.448,16.622,12,16.622s4.622-2.069,4.622-4.622S14.552,7.378,12,7.378z M12,15 c-1.657,0-3-1.343-3-3s1.343-3,3-3s3,1.343,3,3S13.657,15,12,15z M16.804,6.116c-0.596,0-1.08,0.484-1.08,1.08 s0.484,1.08,1.08,1.08c0.596,0,1.08-0.484,1.08-1.08S17.401,6.116,16.804,6.116z"></path></svg>';
	return $icons;
}, 20 );

/**
 * Google Tag Manager container ID.
 */
function vsb_get_gtm_container_id() {
    return 'GTM-P82GHJ5V';
}

/**
 * Inject GTM script in head.
 */
function vsb_output_gtm_head_script() {
    $container_id = vsb_get_gtm_container_id();

    if ( empty( $container_id ) ) {
        return;
    }
    ?>
    <!-- Google Tag Manager -->
    <script>
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?php echo esc_js( $container_id ); ?>');
    </script>
    <!-- End Google Tag Manager -->
    <?php
}
add_action( 'wp_head', 'vsb_output_gtm_head_script', 1 );

/**
 * Inject GTM noscript iframe right after body open.
 */
function vsb_output_gtm_body_noscript() {
    $container_id = vsb_get_gtm_container_id();

    if ( empty( $container_id ) ) {
        return;
    }
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( $container_id ); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
}
add_action( 'wp_body_open', 'vsb_output_gtm_body_noscript' );

<?php
/**
 * Local Post Type Registration
 *
 * @package EightyFourEM\LocalPages\PostTypes
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

namespace EightyFourEM\LocalPages\PostTypes;

/**
 * Handles registration and management of the 'local' custom post type
 */
class LocalPostType {
    /**
     * Post type name
     */
    public const POST_TYPE = 'local';

    /**
     * Register the custom post type
     */
    public function register(): void {
        add_action( 'init', [ $this, 'registerPostType' ] );
        add_action( 'init', [ $this, 'addRewriteRules' ] );
        add_filter( 'post_type_link', [ $this, 'removeSlugFromPermalink' ], 10, 2 );
    }

    /**
     * Register the local post type
     */
    public function registerPostType(): void {
        $labels = [
            'name'                  => 'Local Pages',
            'singular_name'         => 'Local Page',
            'menu_name'             => 'Local Pages',
            'add_new'               => 'Add New',
            'add_new_item'          => 'Add New Local Page',
            'edit_item'             => 'Edit Local Page',
            'new_item'              => 'New Local Page',
            'view_item'             => 'View Local Page',
            'view_items'            => 'View Local Pages',
            'search_items'          => 'Search Local Pages',
            'not_found'             => 'No local pages found',
            'not_found_in_trash'    => 'No local pages found in Trash',
            'parent_item_colon'     => 'Parent Local Page:',
            'all_items'             => 'All Local Pages',
            'archives'              => 'Local Page Archives',
            'attributes'            => 'Local Page Attributes',
            'insert_into_item'      => 'Insert into local page',
            'uploaded_to_this_item' => 'Uploaded to this local page',
            'featured_image'        => 'Featured Image',
            'set_featured_image'    => 'Set featured image',
            'remove_featured_image' => 'Remove featured image',
            'use_featured_image'    => 'Use as featured image',
            'filter_items_list'     => 'Filter local pages list',
            'items_list_navigation' => 'Local pages list navigation',
            'items_list'            => 'Local pages list',
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => false, // We'll handle rewrites manually
            'capability_type'    => 'page',
            'has_archive'        => false,
            'hierarchical'       => true,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-location-alt',
            'supports'           => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'page-attributes', 'custom-fields' ],
            'show_in_rest'       => true,
        ];

        register_post_type( self::POST_TYPE, $args );
    }

    /**
     * Add custom rewrite rules
     */
    public function addRewriteRules(): void {
        // State pages: /wordpress-development-[state]/
        add_rewrite_rule(
            '^wordpress-development-([^/]+)/?$',
            'index.php?post_type=' . self::POST_TYPE . '&name=$matches[1]',
            'top'
        );

        // City pages: /wordpress-development-[state]/[city]/
        add_rewrite_rule(
            '^wordpress-development-([^/]+)/([^/]+)/?$',
            'index.php?post_type=' . self::POST_TYPE . '&name=$matches[2]',
            'top'
        );
    }

    /**
     * Remove post type slug from permalinks
     *
     * @param  string  $post_link  The post's permalink
     * @param  \WP_Post  $post  The post object
     *
     * @return string
     */
    public function removeSlugFromPermalink( string $post_link, \WP_Post $post ): string {
        if ( self::POST_TYPE !== $post->post_type ) {
            return $post_link;
        }

        // Get the post's parent to determine if it's a state or city page
        if ( 0 === $post->post_parent ) {
            // State page
            return home_url( '/wordpress-development-' . $post->post_name . '/' );
        }
        else {
            // City page - get parent state
            $parent = get_post( $post->post_parent );
            if ( $parent ) {
                return home_url( '/wordpress-development-' . $parent->post_name . '/' . $post->post_name . '/' );
            }
        }

        return $post_link;
    }
}

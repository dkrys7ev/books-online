<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://books.online
 * @since      1.0.0
 *
 * @package    Books_Online
 * @subpackage Books_Online/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Books_Online
 * @subpackage Books_Online/includes
 * @author     Danislav Krastev <dkrystev@gmail.com>
 */
class Books_Online {

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_books_cpt' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Register the Books CPT
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function register_books_cpt() {
		register_post_type( 'books', array(
			'labels' => array(
				'name'               => __( 'Books', 'crb' ),
				'singular_name'      => __( 'Book', 'crb' ),
				'add_new'            => __( 'Add New', 'crb' ),
				'add_new_item'       => __( 'Add new Book', 'crb' ),
				'view_item'          => __( 'View Book', 'crb' ),
				'edit_item'          => __( 'Edit Book', 'crb' ),
				'new_item'           => __( 'New Book', 'crb' ),
				'view_item'          => __( 'View Book', 'crb' ),
				'search_items'       => __( 'Search Books', 'crb' ),
				'not_found'          => __( 'No Books found', 'crb' ),
				'not_found_in_trash' => __( 'No Books found in trash', 'crb' ),
			),
			'public'              => true,
			'has_archive'         => false,
			'show_in_rest'        => false,
			'exclude_from_search' => false,
			'show_ui'             => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'_edit_link'          => 'post.php?post=%d',
			'rewrite'             => array(
				'slug'       => 'book',
				'with_front' => false,
			),
			'query_var'           => true,
			'menu_icon'           => 'dashicons-book-alt',
			'supports'            => array( 'title', 'excerpt', 'author' ),
		) );

		register_taxonomy(
			'book_genres', # Taxonomy name
			array( 'books' ), # Post Types
			array( # Arguments
				'labels'            => array(
					'name'              => __( 'Book Genres', 'crb' ),
					'singular_name'     => __( 'Book Genre', 'crb' ),
					'search_items'      => __( 'Search Book Genres', 'crb' ),
					'all_items'         => __( 'All Book Genres', 'crb' ),
					'parent_item'       => __( 'Parent Book Genre', 'crb' ),
					'parent_item_colon' => __( 'Parent Book Genre:', 'crb' ),
					'view_item'         => __( 'View Book Genre', 'crb' ),
					'edit_item'         => __( 'Edit Book Genre', 'crb' ),
					'update_item'       => __( 'Update Book Genre', 'crb' ),
					'add_new_item'      => __( 'Add New Book Genre', 'crb' ),
					'new_item_name'     => __( 'New Book Genre Name', 'crb' ),
					'menu_name'         => __( 'Book Genres', 'crb' ),
				),
				'hierarchical'      => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'book-genre' ),
			)
		);
	}

	/**
	 * Register the REST routes
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function register_rest_routes() {
		register_rest_route( 'books/v1', '/book/get/(?P<book_id>\d+)', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_book' ),
		) );

		register_rest_route( 'books/v1', '/book/get', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_books' ),
		) );
	}

	/**
	 * Get a book by ID
	 *
	 * @param    $request
	 * @since    1.0.0
	 * @access   public
	 * @return   WP_REST_Response    Information about the book
	 */
	public function get_book( $request ) {
		$book_id     = $request->get_param( 'book_id' );
		$book_genres = wp_get_post_terms( $book_id, 'book_genres', array( 'fields' => 'names' ) );
		$author_id   = get_post_field( 'post_author', $book_id );
		$first_name  = get_the_author_meta( 'first_name', $author_id );
		$last_name   = get_the_author_meta( 'last_name', $author_id );
		$author_name = array(
			'first_name' => $first_name,
			'last_name'  => $last_name
		);

		if ( is_wp_error( $book_genres ) || empty( $book_genres ) ) {
			$book_genres = 'N/A';
		}

		$response    = array(
			'title'       => get_the_title( $book_id ),
			'description' => get_the_excerpt( $book_id ),
			'genre'       => $book_genres,
			'author'      => $author_name,
		);

		return new WP_REST_Response( $response );
	}

	/**
	 * Get all books
	 *
	 * @param    $request
	 * @since    1.0.0
	 * @access   public
	 * @return   WP_REST_Response    All books
	 */
	public function get_books( $request ) {
		$books = get_posts( array(
			'post_type'      => 'books',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		) );

		$response = array();

		if ( ! empty( $books ) ) {
			foreach( $books as $book_id ) {
				$book_genres = wp_get_post_terms( $book_id, 'book_genres', array( 'fields' => 'names' ) );
				$author_id   = get_post_field( 'post_author', $book_id );
				$first_name  = get_the_author_meta( 'first_name', $author_id );
				$last_name   = get_the_author_meta( 'last_name', $author_id );
				$author_name = array(
					'first_name' => $first_name,
					'last_name' => $last_name
				);

				if ( is_wp_error( $book_genres ) || empty( $book_genres ) ) {
					$book_genres = 'N/A';
				}

				$response[] = (object) array(
					'title'       => get_the_title( $book_id ),
					'description' => get_the_excerpt( $book_id ),
					'genre'       => $book_genres,
					'author'      => $author_name,
				);
			}
		}

		return new WP_REST_Response( $response );
	}
}

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
		// Create
		register_rest_route( 'books/v1', '/book/create', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'create_book' ),
		) );

		// Read
		register_rest_route( 'books/v1', '/book/get/(?P<book_id>\d+)', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_book' ),
		) );

		register_rest_route( 'books/v1', '/book/get', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_books' ),
		) );

		// Update
		register_rest_route( 'books/v1', '/book/update/(?P<book_id>\d+)', array(
			'methods'  => 'PUT',
			'callback' => array( $this, 'update_book' ),
		) );

		// Delete
		register_rest_route( 'books/v1', '/book/delete/(?P<book_id>\d+)', array(
			'methods'  => 'DELETE',
			'callback' => array( $this, 'delete_book' ),
		) );
	}

	/**
	 * Create a book
	 *
	 * @param    $request
	 * @since    1.0.0
	 * @access   public
	 * @return   WP_REST_Response    Contains book post_id
	 */
	public function create_book( $request ) {
		$response    = array();
		$title       = $request->get_param( 'title' );
		$description = $request->get_param( 'description' );
		$genre       = $request->get_param( 'genre' );
		$author      = $request->get_param( 'author' );

		if ( isset( $title ) && isset( $description ) && isset( $genre ) && isset( $author ) ) {
			$author_search = new WP_User_Query( array(
				'search'        => $author,
				'search_fields' => array( 'display_name' )
			) );

			if ( ! empty( $author_search->get_results() ) ) {
				$author    = reset( $author_search->get_results() );
				$author_id = $author->get('ID');
			} else {
				$author_name = explode( ' ', $author );
				$author_id   = wp_insert_user( array(
					'user_login'   => sanitize_title( $author ),
					'user_pass'    => wp_generate_password(),
					'first_name'   => $author_name[0],
					'last_name'    => $author_name[1],
					'display_name' => $author,
					'role'         => 'author'
				) );
			}

			$book_id = wp_insert_post( array(
				'post_type'    => 'books',
				'post_title'   => $title,
				'post_author'  => $author_id,
				'post_excerpt' => $description,
				'post_status'  => 'publish',
			) );

			if ( ! is_wp_error( $book_id ) && ! empty( $book_id ) ) {
				wp_set_post_terms( $book_id, $genre, 'book_genres' );

				$response['status'] = 200;
				$response['data']   = (object) array(
					'success' => true,
					'book_id' => $book_id
				);
			}
		}

		return new WP_REST_Response( $response );
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

	/**
	 * Update a book
	 *
	 * @param    $request
	 * @since    1.0.0
	 * @access   public
	 */
	public function update_book( $request ) {
		$response    = array();
		$book_id     = $request->get_param( 'book_id' );
		$book_params = array(
			'post_title'   => 'title',
			'post_excerpt' => 'description',
			'post_genres'  => 'genre',
			'post_author'  => 'author',
		);

		if ( ! empty( $book_id ) ) {
			$book_data = array(
				'ID'        => $book_id,
				'post_type' => 'books',
			);

			foreach( $book_params as $args_key => $param_key ) {
				if ( in_array( $param_key, array( 'title', 'description' ) ) && $value = $request->get_param( $param_key ) ) {
					$book_data[ $args_key ] = $value;

					continue;
				}

				if ( $param_key === 'genre' && $value = $request->get_param( $param_key ) ) {
					wp_set_post_terms( $book_id, $value, 'book_genres' );

					continue;
				}

				if ( $param_key === 'author' && $value = $request->get_param( $param_key ) ) {
					$author_search = new WP_User_Query( array(
						'search'        => $value,
						'search_fields' => array( 'display_name' )
					) );

					if ( ! empty( $author_search->get_results() ) ) {
						$author    = reset( $author_search->get_results() );
						$author_id = $author->get('ID');
					} else {
						$author_name = explode( ' ', $value );
						$author_id   = wp_insert_user( array(
							'user_login'   => sanitize_title( $value ),
							'user_pass'    => wp_generate_password(),
							'first_name'   => $author_name[0],
							'last_name'    => $author_name[1],
							'display_name' => $value,
							'role'         => 'author'
						) );
					}

					$book_data['post_author'] = $author_id;

					continue;
				}
			}

			wp_update_post( $book_data );

			$response['status'] = 200;
			$response['data']   = (object) array(
				'success' => true,
				'book_id' => $book_id
			);

			return new WP_REST_Response( $response );
		}

		$response['status'] = 404;
		$response['data']   = (object) array(
			'success' => false,
			'message' => 'Book not found!'
		);

		return new WP_REST_Response( $response );
	}

	/**
	 * Delete a book
	 *
	 * @param    $request
	 * @since    1.0.0
	 * @access   public
	 */
	public function delete_book( $request ) {
		$response = array();
		$book_id  = $request->get_param( 'book_id' );

		if ( $book_id && ! empty( $book_id ) && get_post_type( $book_id ) === 'books' ) {
			wp_delete_post( $book_id, true );

			$response['status'] = 200;
			$response['data']   = (object) array(
				'success' => true,
				'message' => 'The book has been deleted successfully.'
			);

			return new WP_REST_Response( $response );
		}

		$response['status'] = 404;
		$response['data']   = (object) array(
			'success' => false,
			'message' => 'No book found with the provided ID.'
		);

		return new WP_REST_Response( $response );
	}
}

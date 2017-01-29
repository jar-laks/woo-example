<?php

class FilmPostType {
	const POST_TYPE_KEY = 'film';
	const PROD_META_KEY = '_film_product_id';
	const FEATURED_META_KEY = '_film_is_featured';
	const SUBTITLE_META_KEY = '_film_subtitle';


	public function __construct() {
		$this->_register_film_post_type();
		$this->_register_metaboxes();
		$this->_set_featured_films_on_archive();

	}

	protected function _register_film_post_type() {
		$labels = [
			'name'                  => __( 'Films', 'twentyseventeen' ),
			'singular_name'         => __( 'Film', 'twentyseventeen' ),
			'add_new'               => __( 'Add New', 'twentyseventeen' ),
			'add_new_item'          => __( 'New Film', 'twentyseventeen' ),
			'edit_item'             => __( 'Edit Film', 'twentyseventeen' ),
			'new_item'              => __( 'New Film', 'twentyseventeen' ),
			'all_items'             => __( 'All Films', 'twentyseventeen' ),
			'view_item'             => __( 'View Film', 'twentyseventeen' ),
			'search_items'          => __( 'Search Films', 'twentyseventeen' ),
			'not_found'             => __( 'No films found', 'twentyseventeen' ),
			'not_found_in_trash'    => __( 'No films found in Trash', 'twentyseventeen' ),
			'parent_item_colon'     => '',
			'menu_name'             => __( 'Films', 'twentyseventeen' ),
			'featured_image'        => __( 'Cover', 'twentyseventeen' ),
			'set_featured_image'    => __( 'Set cover', 'twentyseventeen' ),
			'remove_featured_image' => __( 'Remove cover', 'twentyseventeen' ),
			'use_featured_image'    => __( 'Use as cover', 'twentyseventeen' ),
		];
		$args   = [
			'labels'       => $labels,
			'public'       => true,
			'menu_icon'    => 'dashicons-format-video',
			'supports'     => [ 'title', 'editor', 'thumbnail' ],
			'has_archive'  => true,
			'hierarchical' => false,
		];
		register_post_type( static::POST_TYPE_KEY, $args );
		register_taxonomy_for_object_type( 'category', static::POST_TYPE_KEY );
	}

	protected function _register_metaboxes() {

		add_action( 'admin_menu', [ $this, 'add_meta_box' ] );
		add_action( 'save_post', [ $this, 'save_meta_box' ], 10, 2 );
	}


	public function add_meta_box() {
		add_meta_box( 'film_product_meta_box', __( 'Film info', 'twentyseventeen' ), [
			$this,
			'meta_box'
		], static::POST_TYPE_KEY, 'normal', 'high' );
	}

	public function meta_box( $object, $box ) {
		?>
		<p><label
				for="<?= static::SUBTITLE_META_KEY ?>"><b><?= __( 'Subtitle', 'twentyseventeen' ) ?></b></label>
		</p>
		<input type="text" id="<?= static::SUBTITLE_META_KEY ?>" name="<?= static::SUBTITLE_META_KEY ?>"
		       value="<?= esc_attr( get_post_meta( $object->ID, static::SUBTITLE_META_KEY, true ) ); ?>">
		<p><label
				for="<?= static::PROD_META_KEY ?>"><b><?= __( 'Select the product that is required to have been purchased before a user can view the content of this film.', 'twentyseventeen' ) ?></b></label>
		</p>
		<select name="<?= static::PROD_META_KEY ?>" id="<?= static::PROD_META_KEY ?>">
			<?php $available_products = static::_get_products();
			$value                    = get_post_meta( $object->ID, static::PROD_META_KEY, true );
			if ( $available_products ) {
				printf( '<option value="%s">%s</option>', '', __( 'Select product', 'twentyseventeen' ) );
				foreach ( $available_products as $available_product ) {
					printf( '<option %s value="%s">%s</option>', selected( $value, $available_product, false ), $available_product, get_the_title( $available_product ) );
				}
			}
			?>
		</select>
		<p><label
				for="<?= static::FEATURED_META_KEY ?>"><b><?= __( 'Is Featured', 'twentyseventeen' ) ?></b></label>
		</p>
		<?php
		$value = get_post_meta( $object->ID, static::FEATURED_META_KEY, true );
		?>
		<input type="checkbox" id="<?= static::FEATURED_META_KEY ?>"
		       name="<?= static::FEATURED_META_KEY ?>" <?php checked( true, $value ) ?>>

		<?php
	}

	public function save_meta_box( $post_id, $post ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		$value_prod_id  = ( isset( $_POST[ static::PROD_META_KEY ] ) && absint( $_POST[ static::PROD_META_KEY ] ) ) ? absint( $_POST[ static::PROD_META_KEY ] ) : '';
		$value_featured = ( isset( $_POST[ static::FEATURED_META_KEY ] ) && $_POST[ static::FEATURED_META_KEY ] ) ? ! ! $_POST[ static::FEATURED_META_KEY ] : '';
		$value_subtitle = ( isset( $_POST[ static::SUBTITLE_META_KEY ] ) && $_POST[ static::SUBTITLE_META_KEY ] ) ? sanitize_text_field( $_POST[ static::SUBTITLE_META_KEY ] ) : '';

		update_post_meta( $post_id, static::PROD_META_KEY, $value_prod_id );
		update_post_meta( $post_id, static::FEATURED_META_KEY, $value_featured );
		update_post_meta( $post_id, static::SUBTITLE_META_KEY, $value_subtitle );

		return $post_id;
	}

	protected function _get_products() {
		$args = [ 'post_type' => 'product', 'posts_per_page' => 100, 'fields' => 'ids' ];

		return get_posts( $args );
	}

	public static function is_film_content_available( $film_id = null ) {
		$available = false;
		if ( is_null( $film_id ) ) {
			$product_id = static::get_film_product_id( get_the_ID() );
		} else {
			$product_id = static::get_film_product_id( $film_id );
		}
		if ( $product_id && get_post_status( $product_id ) == 'publish' ) {
			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id ) ) {
					$available = true;
				}
			}
		} else {
			$available = true;
		}

		return $available;
	}

	public static function get_buy_this_film_button( $film_id = null ) {
		if ( is_user_logged_in() ) {
			if ( is_null( $film_id ) ) {
				$film_id    = get_the_ID();
				$product_id = static::get_film_product_id( get_the_ID() );
			} else {
				$product_id = static::get_film_product_id( $film_id );
			}
			if ( $product_id && get_post_status( $product_id ) == 'publish' ) {
				printf( '<a target="_blank" rel="nofollow" href="%s" data-quantity="1" data-product_id="%s" data-product_sku="" class="buy-button">Buy Me</a>',
					add_query_arg( [ WooFilm::ARG_KEY => $product_id ], get_the_permalink( $film_id ) ),
					$product_id );
			}
		} else {
			printf( '<a class="login-woo-button" href="%s">%s</a>', get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ), __( 'Login / Register', 'twentyseventeen' ) );
		}
	}

	public static function get_film_product_id( $film_id ) {
		return get_post_meta( $film_id, static::PROD_META_KEY, true );
	}

	protected function _set_featured_films_on_archive() {
		add_action( 'pre_get_posts', function ( $query ) {
			if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( static::POST_TYPE_KEY ) ) {
				$query->set( 'meta_key', static::FEATURED_META_KEY );
				$query->set( 'meta_value', '1' );

			}
		} );
	}

}

new FilmPostType();

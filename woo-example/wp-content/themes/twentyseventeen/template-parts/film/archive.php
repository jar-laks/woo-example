<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php
		the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		echo get_post_meta( get_the_ID(), FilmPostType::SUBTITLE_META_KEY, true );
		?>
	</header><!-- .entry-header -->
	<div class="entry-content">
		<?php
		/* translators: %s: Name of current post */
		if ( FilmPostType::is_film_content_available() ) {
			the_excerpt();
		} else {
			echo '<i><b>'.apply_filters( 'the_content', __( 'Buy this Film before you can see the content.', 'twentyseventeen' ) ).'</b></i>';
		}
		?>
	</div><!-- .entry-content -->
	<?php if ( ! FilmPostType::is_film_content_available() ) {
		FilmPostType::get_buy_this_film_button();
	} ?>

</article><!-- #post-## -->

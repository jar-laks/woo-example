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
	<?php
	if ( is_sticky() && is_home() ) :
		echo twentyseventeen_get_svg( array( 'icon' => 'thumb-tack' ) );
	endif;
	?>
	<header class="entry-header">
		<?php
		the_title( '<h1 class="entry-title">', '</h1>' );
		echo get_post_meta( get_the_ID(), FilmPostType::SUBTITLE_META_KEY, true );
		?>
	</header><!-- .entry-header -->

	<?php if ( '' !== get_the_post_thumbnail() && ! is_single() ) : ?>
		<div class="post-thumbnail">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail( 'twentyseventeen-featured-image' ); ?>
			</a>
		</div><!-- .post-thumbnail -->
	<?php endif; ?>

	<div class="entry-content">
		<?php
		/* translators: %s: Name of current post */
		if ( FilmPostType::is_film_content_available() ) {

			the_content( sprintf(
				__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'twentyseventeen' ),
				get_the_title()
			) );
		} else {
			echo '<i><b>'.apply_filters( 'the_content', __( 'Buy this Film before you can see the content.', 'twentyseventeen' ) ).'</b></i>';
		}
		?>
	</div><!-- .entry-content -->


	<?php twentyseventeen_entry_footer(); ?>
	<?php if ( ! FilmPostType::is_film_content_available() ) {
		FilmPostType::get_buy_this_film_button();
	} ?>

</article><!-- #post-## -->

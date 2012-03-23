<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

get_header(); ?>
<?php while ( have_posts() ) : the_post(); ?>

<?php
	$m_options = array();
	$id = get_the_ID();
	$m_options['origin']['lat'] = get_the_latitude($id);
	$m_options['origin']['lng'] = get_the_longitude($id);
	$m_options['origin']['zoom'] = 19;
	$m_options['mapType'] = 'SATELLITE';
	
	the_map("map_canvas",false,$m_options);
	
	?>
	
	
<div id="main">
		<div id="primary">
			<div id="content" role="main">
					<nav id="nav-single">
						<h3 class="assistive-text"><?php _e( 'Post navigation', 'mymap' ); ?></h3>
						<span class="nav-previous"><?php previous_post_link( '%link', __( '<span class="meta-nav">&larr;</span> Previous', 'mymap' ) ); ?></span>
						<span class="nav-next"><?php next_post_link( '%link', __( 'Next <span class="meta-nav">&rarr;</span>', 'mymap' ) ); ?></span>
					</nav><!-- #nav-single -->

					<?php get_template_part( 'content', 'marker' ); ?>

					<?php comments_template( '', true ); ?>

			

			</div><!-- #content -->
		</div><!-- #primary -->
		
			<?php endwhile; // end of the loop. ?>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
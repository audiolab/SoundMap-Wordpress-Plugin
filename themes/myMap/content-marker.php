<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<h1 class="entry-title"><?php the_title(); ?></h1>		
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php the_content(); ?>
		<?php
			$id_p = get_the_ID();
			$aut = get_marker_author($id_p);
			$dat = get_marker_date($id_p);
			
			$text = __('<p>Author: %1$s</br>Date: %2$s</p>','mymap');
			
			printf(
				$text,
				$aut,
				$dat
			);
		?>
		<?php the_player($id_p); ?>
	</div><!-- .entry-content -->

	<footer class="entry-meta">
		<?php
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( __( ', ', 'mymap' ) );

			/* translators: used between list items, there is a space after the comma */
			$tag_list = get_the_tag_list( '', __( ', ', 'mymap' ) );			
			$utility_text = __( 'Categories: %1$s<br/>Tags: %2$s</br>', 'mymap' );
			
			
			printf(
				$utility_text,
				$categories_list,
				$tag_list
			);
		?>
		<?php edit_post_link( __( 'Edit', 'mymap' ), '<span class="edit-link">', '</span>' ); ?>

	</footer><!-- .entry-meta -->
</article><!-- #post-<?php the_ID(); ?> -->
<?php get_header(); ?>

<div class="l-main-layout">
	<main class="main">
		<h2 class="b-title">Статьи</h2>
		<section class="l-card-list">
			<?php
				while (have_posts()): the_post();
					get_template_part('partials/' . get_post_type(), 'preview');
				endwhile;
			?>
			<?php wp_reset_query(); ?>
		</section>
		<?php if (function_exists('custom_pagination')) custom_pagination(); ?>
	</main>
	
	<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>

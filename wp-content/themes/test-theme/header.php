<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  <head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
  </head>
  <body <?php body_class(); ?>> 
    <header class="b-header">
      <div class="b-header__container">
        <a href="/"><h2>Header</h2></a>
      </div>
      <?php wp_nav_menu( array(
        'theme_location' => 'main',
        'container'       => 'nav',
        'container_class' => 'b-header__container',
        )
      );?>
    </header>
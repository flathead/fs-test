<?php
if ( ! is_active_sidebar( 'sidebar' ) ) {
  return;
}
?>

<aside id="secondary" class="widget-area">
  <?php dynamic_sidebar( 'sidebar' ); ?>
</aside><!-- #secondary -->
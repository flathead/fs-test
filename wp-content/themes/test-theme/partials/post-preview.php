<?php
/**
 * Шаблон для предварительного просмотра поста
 *
 * @package Test Theme
 */

// Проверяем, активирован ли плагин
include_once ABSPATH . 'wp-admin/includes/plugin.php';
// Проверяем, существует ли функция is_plugin_active
if (function_exists('is_plugin_active')) {
    // Проверяем, активирован ли плагин
    if (is_plugin_active('rating-plugin/rating-plugin.php')) {
        $plugin_active = true;
    } else {
        $plugin_active = false;
    }
} else {
    // Функция is_plugin_active не доступна, плагин не активирован
    $plugin_active = false;
}

$buttons = [
    'like' => get_template_directory_uri() . '/img/rating-plus.svg',
    'dislike' => get_template_directory_uri() . '/img/rating-minus.svg',
]
?>

<article class="b-card">
    <img src="<?php the_post_thumbnail_url(); ?>" alt="" class="b-card__img">
    <div class="b-card__body">
        <h3 class="b-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="b-card__subtitle"><?= get_the_excerpt(); ?></p>
        <div class="b-card__footer">
            <div class="b-card__author">
                <p>Автор: <span><?php the_author(); ?></span></p>
            </div>
            <!-- Лайки и дизлайки -->
            <?php if ($plugin_active) : ?>
                <div class="b-card__rating">
                    <?php
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'likes_dislikes';
                    $post_id = get_the_ID();

                    // Получаем количество лайков и дизлайков из новой таблицы
                    $likes = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_name WHERE post_id = %d AND action = 'like'", 
                        $post_id
                    ));
                    $dislikes = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_name WHERE post_id = %d AND action = 'dislike'", 
                        $post_id
                    ));
                    ?>
                    <a href="#" class="b-button b-button--like like" data-post-id="<?php echo $post_id; ?>" data-action="like">
                        <img src="<?php echo $buttons['like']; ?>" alt="Кнопка лайка" width="22" height="22">
                    </a>
                    <span class="like-count"><?php echo $likes; ?></span>
                    <a href="#" class="b-button b-button--dislike dislike" data-post-id="<?php echo $post_id; ?>" data-action="dislike">
                        <img src="<?php echo $buttons['dislike']; ?>" alt="Кнопка дизлайка" width="22" height="22">
                    </a>
                    <input type="hidden" class="dislike-count" value="<?php echo $dislikes; ?>">
                    <!-- <span class="dislike-count"><?php echo $dislikes; ?></span> -->
                </div>
            <?php endif; ?>
        </div>
    </div>
</article>

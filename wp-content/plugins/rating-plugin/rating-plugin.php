<?php
/*
 * Plugin Name: Рейтинг (лайки/дизлайки)
 * Plugin URI: https://github.com/flathead/
 * Description: Плагин добавляет функционал для рейтинга в превью постов
 * Version: 1.0
 * Author: Dmitry Guzeev
 * Author URI: https://github.com/flathead/
 * License: GPL2
 */

// Подключение основных файлов плагина
require_once plugin_dir_path(__FILE__) . 'inc/rating-table.php';
require_once plugin_dir_path(__FILE__) . 'inc/ajax.php';

// Подключение скриптов и стилей
function rating_enqueue_scripts() {
    $version = '1.0';
    // Подключаем CSS файл
    // wp_enqueue_style('rating-style', plugins_url('css/style.css', __FILE__));
    
    // Подключаем JavaScript файл и передаем данные в него
    wp_enqueue_script('jquery');
    wp_enqueue_script('rating-script', plugins_url('js/ajax.js', __FILE__), array('jquery'), $version, true);
    wp_localize_script('rating-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'rating_enqueue_scripts');

// Регистрация хука активации плагина
register_activation_hook(__FILE__, 'rating_plugin_activation');

function rating_plugin_activation() {
    create_likes_dislikes_table(); // Вызываем функцию создания таблицы
}
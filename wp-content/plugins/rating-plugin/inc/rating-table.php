<?php
/**
 * В этом инклюде:
 * - Проверяется, существует ли класс WP_List_Table, и если нет, то он включается.
 * - Создается функция create_likes_dislikes_table(), которая создает таблицу в базе данных WordPress для хранения данных о лайках и дизлайках.
 * - Добавляется класс Like_Dislike_Stats_Table, который наследует от WP_List_Table и отвечает за отображение статистики лайков и дизлайков в админке.
 * - Функция register_like_dislike_stats_page() регистрирует страницу статистики лайков-дизлайков в админке WordPress.
 * - Callback функция render_like_dislike_stats_page() отвечает за вывод содержимого страницы статистики лайков-дизлайков, включая таблицу с данными.
 */
// Проверяем, существует ли класс WP_List_Table; если нет, включаем его
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Функция для создания таблицы wp_likes-dislikes в базе данных, если она не существует
 *
 * @global wpdb $wpdb объект базы данных WordPress.
 * @return void
 */
function create_likes_dislikes_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'likes_dislikes';
    $charset_collate = $wpdb->get_charset_collate();

    // Проверяем, существует ли таблица в базе данных; если нет, создаем ее
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            user_ip varchar(100) DEFAULT '' NOT NULL,
            action varchar(10) NOT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Подключаем функцию dbDelta для создания таблЦЦицы
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Класс для отображения статистики лайков-дизлайков в админке
class Like_Dislike_Stats_Table extends WP_List_Table {

    /**
     * Конструктор для инициализации экземпляра Like_Dislike_Stats_Table
     *
     * @param array $args Аргументы конструктора класса WP_List_Table
     */
    function __construct() {
        parent::__construct(array(
            'singular' => 'like_dislike_stat',
            'plural'   => 'like_dislike_stats',
            'ajax'     => false
        ));
    }

    /**
     * Метод, возвращающий массив колонок для класса Like_Dislike_Stats_Table
     *
     * @return array Ассоциативный массив с ключами колонок и их соответствующими значениями.
     */
    function get_columns() {
        return array(
            'cb'       => '<input type="checkbox" />',
            'title'    => 'Заголовок',
            'likes'    => '👍 Количество лайков',
            'dislikes' => '👎 Количество дизлайков'
        );
    }

    /**
     * Метод для вывода чекбокса в колонке "cb"
     *
     * @param mixed $item Элемент для обработки
     */
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['ID']
        );
    }

    /**
     * Метод для вывода заголовка поста с ссылкой на страницу
     *
     * @param mixed $item Элемент для обработки
     * @return string Форматированный заголовок поста
     */
    function column_title($item) {
        $permalink = get_permalink($item['ID']);
        $title = sprintf('<a href="%s" target="_blank">%s</a>', $permalink, $item['title']);
        return $title;
    }

    /**
     * Метод для подготовки элементов перед отображением таблицы.
     * 
     * Получает записи, получает количество лайков и дизлайков для каждой записи, фильтрует записи с ненулевым количеством лайков или дизлайков,
     * сортирует данные по названию записи и устанавливает данные для отображения в таблице.
     *
     * @throws No exceptions are thrown by this method.
     * @return void
     */
    function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'likes_dislikes';
    
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
    
        // Получаем все посты
        $posts = get_posts(array(
            'posts_per_page' => -1,
            'post_type'      => 'post',
            'post_status'    => 'publish'
        ));
    
        $data = array();
        foreach ($posts as $post) {
            // Получаем количество лайков и дизлайков для каждого поста
            $likes = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE post_id = %d AND action = 'like'", 
                $post->ID
            ));
            $dislikes = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE post_id = %d AND action = 'dislike'", 
                $post->ID
            ));
    
            // Фильтрация постов с ненулевым количеством лайков или дизлайков
            if ($likes > 0 || $dislikes > 0) {
                $data[] = array(
                    'ID'       => $post->ID,
                    'title'    => $post->post_title,
                    'likes'    => $likes,
                    'dislikes' => $dislikes
                );
            }
        }
    
        // Сортируем данные по заголовку поста
        usort($data, function($a, $b) {
            return strcmp($a['title'], $b['title']);
        });
    
        // Устанавливаем данные для отображения в таблице
        $this->items = $data;
    }
    

    /**
     * Метод для обработки значений по умолчанию в колонках таблицы.
     *
     * @param mixed $item Элемент для обработки
     * @param string $column_name Имя колонки для обработки
     * @return mixed Значение специфичной колонки в элементе
     */
    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'title':
            case 'likes':
            case 'dislikes':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }
}

// Добавляем хук для регистрации страницы статистики лайков-дизлайков в админке
add_action('admin_menu', 'register_like_dislike_stats_page');

/**
 * Функция для регистрации страницы статистики лайков-дизлайков в админке WordPress.
 *
 * @return void
 */
function register_like_dislike_stats_page() {
    add_menu_page(
        'Статистика лайков-дизлайков',
        'Статистика лайков-дизлайков',        // Заголовок страницы
        'manage_options',                     // Уровень доступа
        'like_dislike_stats',                 // Slug страницы
        'render_like_dislike_stats_page',     // Callback функция для отображения содержимого страницы
        'dashicons-thumbs-up',                // URL иконки
        25                                    // Позиция в меню
    );
}

/**
 * Коллбек-функция для отображения содержимого страницы статистики лайков-дизлайков.
 */
function render_like_dislike_stats_page() {
    echo '<div class="wrap">';
    echo '<h1>Статистика лайков-дизлайков</h1>';
    echo '<form method="post">';
    echo '<input type="hidden" name="page" value="like_dislike_stats_table">';
    
    // Создаем экземпляр класса Like_Dislike_Stats_Table и подготавливаем данные для отображения
    $like_dislike_stats_table = new Like_Dislike_Stats_Table();
    $like_dislike_stats_table->prepare_items();
    
    // Выводим таблицу статистики лайков-дизлайков
    $like_dislike_stats_table->display();
    
    echo '</form>';
    echo '</div>';
}

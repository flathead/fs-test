<?php 
//THEME SUPPORTS
add_action( 'after_setup_theme', function(){
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
});


//THEME EXTRAS
//require_once get_template_directory() . '/inc/post-types.php';
//require_once get_template_directory() . '/inc/spa.php';
// require_once get_template_directory() . '/inc/ajax.php';
require_once get_template_directory() . '/inc/theme-image.php';



//THEME MENUS
add_action( 'after_setup_theme', 'theme_register_nav_menu' );

function theme_register_nav_menu() {
	register_nav_menu( 'main', 'Top Menu' );
}


//THEME STYLES & SCRIPTS
add_action( 'wp_enqueue_scripts', 'theme_styles_and_scripts' );	

function theme_styles_and_scripts() {
	$ver = '0.2';
	$css_url = get_template_directory_uri() . '/dist/';
	$js_url = get_template_directory_uri() . '/js/';
	
	//Enqueue main theme style
	wp_enqueue_style( 'css-main', get_stylesheet_uri(), array(), $ver);
	
	//Enqueue additional .css files
	wp_enqueue_style( 'css-bundle', $css_url . 'main.css', array(), $ver);

	//Enqueue .js files	
	wp_enqueue_script('main-js', $js_url . 'main.js', array('jquery'), $ver);
}


//ENABLING - DISABLING GUTENBERG FOR CERTAIN POST TYPES
add_filter( 'use_block_editor_for_post_type', 'theme_gutenberg_support_for_post_types', 10, 2 );

function theme_gutenberg_support_for_post_types( $use_block_editor, $post_type ){
	if ($post_type == 'post') { return true;	} else { return false; }
}

function register_wp_sidebars() {
 
	/* В боковой колонке */
	register_sidebar(
		array(
			'id' => 'sidebar', // уникальный id
			'name' => 'Боковая колонка', // название сайдбара
			'description' => 'Перетащите сюда виджеты, чтобы добавить их в сайдбар.', // описание
			'before_widget' => '<div id="%1$s" class="side widget %2$s">', // по умолчанию виджеты выводятся <li>-списком
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widget-title">', // по умолчанию заголовки виджетов в <h2>
			'after_title' => '</h3>'
		)
	);
}
 
add_action( 'widgets_init', 'register_wp_sidebars' );

function custom_pagination() {
    global $wp_query; // Получаем глобальный объект запроса

    $pagination_output = ''; // Переменная для HTML-кода пагинации
    $total_pages = $wp_query->max_num_pages; // Общее количество страниц

    // Определяем текущую страницу, если не установлена, то это первая страница
    if (!$current_page = get_query_var('paged')) {
        $current_page = 1;
    }

    // Настраиваем базовый URL для ссылок пагинации
    $pagination_links = array(
        'base' => str_replace(999999999, '%#%', get_pagenum_link(999999999)),
        'total' => $total_pages,
        'current' => $current_page,
        'mid_size' => 2, // Количество ссылок по обе стороны от текущей страницы
        'end_size' => 2, // Количество ссылок в начале и в конце
        'prev_text' => '<span>Назад</span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M11 1L5 8l6 7" stroke="black"/></svg>',
        'next_text' => '<span>Вперёд</span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M5 1l6 7-6 7" stroke="black"/></svg>'
    );

    // Выводим пагинацию, если страниц больше одной
    if ($total_pages > 1) {
        echo '<nav class="b-pagination">';
        echo $pagination_output . paginate_links($pagination_links);
        echo '</nav>';
    }
}

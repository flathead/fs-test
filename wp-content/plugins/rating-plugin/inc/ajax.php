<?php
/**
 * Код Ajax-обработки запросов при нажатии на лайки и дизлайки:
 * - Добавляется хук для обработки AJAX-запроса при лайках и дизлайках.
 * - Создается callback функция rating_post_callback(), которая обрабатывает AJAX-запрос при лайках и дизлайках.
 */

// Добавляем хук для обработки AJAX-запроса при лайках и дизлайках
add_action('wp_ajax_like_dislike_post', 'rating_post_callback');
add_action('wp_ajax_nopriv_like_dislike_post', 'rating_post_callback');

/**
 * Обработка AJAX-запроса при лайках и дизлайках.
 *
 * @throws wp_die()
 */
function rating_post_callback() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'likes_dislikes';

    // Получаем ID поста и действие (лайк или дизлайк) из POST-запроса
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $action = isset($_POST['like_dislike_action']) ? $_POST['like_dislike_action'] : '';

    // Проверяем корректность полученных данных
    if ($post_id > 0 && in_array($action, array('like', 'dislike'))) {
        $user_ip = $_SERVER['REMOTE_ADDR'];

        // Получаем текущий голос пользователя для данного поста
        $existing_vote = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE post_id = %d AND user_ip = %s", 
            $post_id, 
            $user_ip
        ));

        if ($existing_vote) {
            // Если пользователь уже голосовал, проверяем текущий голос
            if ($existing_vote->action === $action) {
                // Если текущий голос совпадает с действием в запросе, удаляем его (отменяем голос)
                $wpdb->delete(
                    $table_name,
                    array('id' => $existing_vote->id)
                );

                // Обновляем действие, чтобы обновилась надпись на кнопке
                $action = ''; // Пустая строка, так как голос был отменен
            } else {
                // Если текущий голос отличается от действия в запросе, обновляем запись
                $wpdb->update(
                    $table_name,
                    array('action' => $action, 'timestamp' => current_time('mysql')),
                    array('id' => $existing_vote->id)
                );
            }
        } else {
            // Вставляем новую запись
            $wpdb->insert(
                $table_name,
                array(
                    'post_id' => $post_id,
                    'user_ip' => $user_ip,
                    'action' => $action,
                    'timestamp' => current_time('mysql')
                )
            );
        }

        // Получаем обновленные данные для поста
        $likes = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE post_id = %d AND action = 'like'", 
            $post_id
        ));
        $dislikes = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE post_id = %d AND action = 'dislike'", 
            $post_id
        ));

        // Формируем массив с обновленными данными и отправляем его в JSON
        $response = array(
            'likes' => $likes,
            'dislikes' => $dislikes,
            'action' => $action // Добавляем текущее действие для кнопки
        );

        wp_send_json_success($response);
    } else {
        // Если запрос некорректный, отправляем ошибку в JSON
        wp_send_json_error('Invalid request.');
    }

    // Завершаем выполнение скрипта
    wp_die();
}
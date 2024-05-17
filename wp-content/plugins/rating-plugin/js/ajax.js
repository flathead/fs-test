document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.like, .dislike');
    buttons.forEach(button => {
        button.addEventListener('click', event => {
            event.preventDefault();
            
            const postId = button.dataset.postId;
            const action = button.dataset.action;
            
            fetch(ajax_object.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=like_dislike_post&post_id=${postId}&like_dislike_action=${action}`
            })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    const likesElement = button.closest('.b-card__rating').querySelector('.like-count');
                    const dislikesElement = button.closest('.b-card__rating').querySelector('.dislike-count');
                    likesElement.textContent = response.data.likes;
                    dislikesElement.textContent = response.data.dislikes;
                } else {
                    console.log('Error: ' + response.data);
                }
            })
            .catch(error => console.log(error.message));
        });
    });
});


import './bootstrap';

// Импортируем Bootstrap JavaScript
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Инициализация всплывающих подсказок и выпадающих меню
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация выпадающих меню
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
    
    // Обработчик для предпросмотра аватара
    const avatarInput = document.getElementById('avatar');
    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const profileAvatar = document.querySelector('.profile-avatar');
                    if (profileAvatar) {
                        profileAvatar.src = e.target.result;
                    }
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});

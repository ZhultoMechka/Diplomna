// ============================================
// navigation.js - Dynamic User Profile Navigation
// Автоматично променя user icon линка спрямо login статус
// ============================================

(function() {
    'use strict';
    
    // Функция за обновяване на навигацията
    function updateNavigation() {
        // Проверка дали user е логнат
        const user = JSON.parse(localStorage.getItem('user') || 'null');
        
        // Намери всички user icons
        const userIcons = document.querySelectorAll('a[href="login.html"][title="Вход"], a[href="login.html"][title="Профил"], a#userIcon');
        
        userIcons.forEach(icon => {
            if (user) {
                // Ако е логнат → профил
                icon.href = 'user-profile.html';
                icon.title = 'Моят профил';
            } else {
                // Ако НЕ е логнат → login
                icon.href = 'login.html';
                icon.title = 'Вход';
            }
        });
    }
    
    // Update при зареждане на страницата
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateNavigation);
    } else {
        updateNavigation();
    }
    
    // Update при промяна на localStorage (logout от друг tab)
    window.addEventListener('storage', function(e) {
        if (e.key === 'user') {
            updateNavigation();
        }
    });
    
})();
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
document.addEventListener('DOMContentLoaded', function () {
    // Обработка формы создания проекта

    // Обработка формы создания менеджера
    const managerForm = document.getElementById('createManagerForm');
    if (managerForm) {
        managerForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                alert('Ошибка: CSRF-токен не найден. Обратитесь к администратору.');
                return;
            }
            fetch(managerForm.action, {
                method: 'POST',
                body: new FormData(managerForm),
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(data.success);
                        closeModal('createManagerModal');
                        window.location.reload(); // Обновляем страницу для отображения нового менеджера
                    } else {
                        alert('Ошибка: ' + (data.error || 'Не удалось создать менеджера'));
                    }
                })
                .catch(error => alert('Ошибка: ' + error.message));
        });
    }

    // Обработка формы создания оборудования (оставляем пустой, если не используется)
});

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
    }
}
document.addEventListener('DOMContentLoaded', function() {
    // Обработчик для кнопки выпадающего меню
    const dropdownButton = document.querySelector('[x-data] [x-on\\:click]'); // Или укажите конкретный селектор

    if (dropdownButton) {
        console.log('Dropdown button found');

        // Альтернатива: если Alpine не работает, можно использовать чистый JS
        dropdownButton.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdownMenu = this.nextElementSibling; // или document.querySelector('.dropdown-content');

            if (dropdownMenu) {
                const isOpen = dropdownMenu.classList.contains('hidden') ? false : true;

                if (isOpen) {
                    dropdownMenu.classList.add('hidden');
                } else {
                    dropdownMenu.classList.remove('hidden');
                }
            }
        });

        // Закрытие при клике вне меню
        document.addEventListener('click', function(e) {
            if (!dropdownButton.contains(e.target) && !dropdownMenu?.contains(e.target)) {
                dropdownMenu?.classList.add('hidden');
            }
        });
    }
});

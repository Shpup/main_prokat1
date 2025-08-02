document.addEventListener('DOMContentLoaded', function () {
    // Обработка формы создания проекта
    const projectForm = document.getElementById('createProjectForm');
    if (projectForm) {
        projectForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                alert('Ошибка: CSRF-токен не найден. Обратитесь к администратору.');
                return;
            }
            fetch(projectForm.action, {
                method: 'POST',
                body: new FormData(projectForm),
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
                        closeModal('createProjectModal');
                        window.location.reload(); // Обновляем страницу для добавления проекта в календарь
                    } else {
                        alert('Ошибка: ' + (data.error || 'Не удалось создать проект'));
                    }
                })
                .catch(error => alert('Ошибка: ' + error.message));
        });
    }

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

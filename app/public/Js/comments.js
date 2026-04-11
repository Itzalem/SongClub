(function () {
    // Seleccionamos todos los formularios de comentarios de la página
    const forms = document.querySelectorAll('.comment-form');

    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const postId = parseInt(this.dataset.postId, 10);
            const textarea = this.querySelector('.comment-content');
            const content = textarea.value.trim();

            if (!content) return;

            fetch('/comments/store', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: postId, content: content }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                    return;
                }

                // Localizamos la lista de comentarios específica para este post
                const commentsList = document.getElementById('comments-list-' + postId);
                
                // Creamos el nuevo elemento de comentario (protegido contra XSS)
                const li = document.createElement('li');
                li.className = 'comment-item';

                const header = document.createElement('div');
                header.className = 'd-flex justify-content-between mb-1';

                const strong = document.createElement('strong');
                strong.className = 'comment-author';
                strong.textContent = data.username;

                const small = document.createElement('small');
                small.className = 'text-muted';
                small.textContent = data.created_at;

                header.appendChild(strong);
                header.appendChild(small);

                const p = document.createElement('p');
                p.className = 'mb-0';
                p.textContent = data.content;

                li.appendChild(header);
                li.appendChild(p);

                // Añadimos el comentario al final de la lista y limpiamos el formulario
                commentsList.appendChild(li);
                textarea.value = '';
            })
            .catch(err => console.error('Error enviando comentario:', err));
        });
    });
})();
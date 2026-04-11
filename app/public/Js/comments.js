(function () {
    const form = document.getElementById('comment-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const postId  = parseInt(form.dataset.postId, 10);
        const textarea = document.getElementById('comment-content');
        const content  = textarea.value.trim();

        if (!content) return;

        fetch('/comments/store', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ post_id: postId, content: content }),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.error) return;

            // Remove "no comments yet" placeholder if present
            const placeholder = document.getElementById('no-comments');
            if (placeholder) placeholder.remove();

            // Build new comment element (textContent only — no XSS risk)
            const li       = document.createElement('li');
            li.className   = 'mb-3 p-3 bg-white rounded-3 shadow-sm';

            const header   = document.createElement('div');
            header.className = 'd-flex justify-content-between mb-1';

            const strong   = document.createElement('strong');
            strong.textContent = data.username;

            const small    = document.createElement('small');
            small.className = 'text-muted';
            small.textContent = data.created_at;

            header.appendChild(strong);
            header.appendChild(small);

            const p        = document.createElement('p');
            p.className    = 'mb-0';
            p.textContent  = data.content;

            li.appendChild(header);
            li.appendChild(p);

            document.getElementById('comments-list').appendChild(li);
            textarea.value = '';
        });
    });
})();

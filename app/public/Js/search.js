(function () {
    const input   = document.getElementById('user-search');
    const results = document.getElementById('search-results');

    if (!input || !results) return;

    let debounceTimer;

    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const query = input.value.trim();

        if (!query) {
            results.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(function () {
            fetch('/api/users/search?q=' + encodeURIComponent(query))
                .then(function (r) { return r.json(); })
                .then(function (users) {
                    if (!users.length) {
                        results.innerHTML = '<div class="p-2 text-muted small">No users found.</div>';
                        return;
                    }

                    // Build result list using textContent to avoid XSS
                    results.innerHTML = '';
                    users.forEach(function (u) {
                        const a       = document.createElement('a');
                        a.href        = '/profile/' + u.id;
                        a.className   = 'd-block px-3 py-2 text-dark text-decoration-none border-bottom';
                        a.style.fontSize = '0.9rem';

                        const name    = document.createElement('strong');
                        name.textContent = u.username;

                        const bio     = document.createElement('span');
                        bio.className = 'text-muted ms-2';
                        bio.textContent = u.bio ? u.bio.substring(0, 40) : '';

                        a.appendChild(name);
                        a.appendChild(bio);
                        results.appendChild(a);
                    });
                })
                .catch(function () {
                    results.innerHTML = '';
                });
        }, 300);
    });

    // Hide results when clicking outside
    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !results.contains(e.target)) {
            results.innerHTML = '';
        }
    });
})();

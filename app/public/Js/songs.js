document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.btn-like').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var songId  = btn.dataset.songId;
            var countEl = btn.querySelector('.like-count');

            fetch('/likes/toggle', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ song_id: parseInt(songId) })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                btn.classList.toggle('active', data.liked);
                if (countEl) {
                    countEl.textContent = data.count;
                }
            });
        });
    });

    document.querySelectorAll('.btn-fav').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var songId = btn.dataset.songId;

            fetch('/favorites/toggle', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ song_id: parseInt(songId) })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                btn.classList.toggle('active', data.favorited);
            });
        });
    });

});

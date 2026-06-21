-- SongClub database schema + seed data
-- Password for all accounts: password

CREATE DATABASE IF NOT EXISTS SongClub
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE SongClub;


CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    bio        TEXT,
    role       ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS songs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(200) NOT NULL,
    artist     VARCHAR(200) NOT NULL,
    album      VARCHAR(200),
    genre      VARCHAR(100),
    link       VARCHAR(500),
    created_by INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS posts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    song_id    INT NOT NULL,
    caption    TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS comments (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    post_id    INT NOT NULL,
    user_id    INT NOT NULL,
    content    TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS favorites (
    user_id    INT NOT NULL,
    song_id    INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, song_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS likes (
    user_id    INT NOT NULL,
    song_id    INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, song_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO users (username, email, password, bio, role) VALUES
('admin', 'admin@songclub.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Site administrator.', 'admin'),
('alice', 'alice@songclub.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Music lover. Always listening.', 'user'),
('bob', 'bob@songclub.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Rock and metal fanatic.', 'user'),
('carol', 'carol@songclub.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Pop and indie vibes only.', 'user');

INSERT INTO songs (title, artist, album, genre, link, created_by) VALUES
('Blinding Lights',      'The Weeknd',       'After Hours',          'Synth-pop',  'https://open.spotify.com/track/0VjIjW4GlUZAMYd2vXMi3b', 1),
('Bohemian Rhapsody',    'Queen',            'A Night at the Opera', 'Rock',       'https://www.youtube.com/watch?v=fJ9rUzIMcZQ',           1),
('Shape of You',         'Ed Sheeran',       'Divide',               'Pop',        'https://open.spotify.com/track/7qiZfU4dY1lWllzX7mPBI3', 1),
('As It Was',            'Harry Styles',     "Harry's House",        'Indie pop',  'https://open.spotify.com/track/4LRPiXqCikLlN15c3yImP7', 1),
('Levitating',           'Dua Lipa',         'Future Nostalgia',     'Pop',        'https://open.spotify.com/track/463CkQjx2Zk1yXoBuierM9', 2),
('Stay With Me',         'Sam Smith',        'In the Lonely Hour',   'Soul',       'https://open.spotify.com/track/5F6ZxoVRVmae8aZkGJJhOR', 2),
('Master of Puppets',    'Metallica',        'Master of Puppets',    'Metal',      'https://www.youtube.com/watch?v=E0ozmU9cJDg',           3),
('Smells Like Teen Spirit','Nirvana',        'Nevermind',            'Grunge',     'https://www.youtube.com/watch?v=hTWKbfoikeg',           3),
('Bad Guy',              'Billie Eilish',    'When We All Fall Asleep', 'Pop',     'https://open.spotify.com/track/2Fxmhks0live0000000000', 4),
('Cruel Summer',         'Taylor Swift',     'Lover',                'Synth-pop',  'https://open.spotify.com/track/1BxfuPKGuaTgP7aM0Bmque', 4);

INSERT INTO posts (user_id, song_id, caption, created_at) VALUES
(1, 1, 'This song never gets old.',                     NOW() - INTERVAL 2 HOUR),
(2, 5, 'Dua Lipa on repeat today',                  NOW() - INTERVAL 1 HOUR),
(3, 7, 'Classic. Nothing beats this riff.',             NOW() - INTERVAL 30 MINUTE),
(4, 9, 'Billie really something else.',                 NOW() - INTERVAL 10 MINUTE);


INSERT INTO comments (post_id, user_id, content, created_at) VALUES
(1, 2, 'Agreed, absolute banger!',                      NOW() - INTERVAL 90 MINUTE),
(1, 3, 'One of my favorites from The Weeknd.',          NOW() - INTERVAL 80 MINUTE),
(2, 1, 'Future Nostalgia is such a good album.',        NOW() - INTERVAL 50 MINUTE),
(2, 4, 'Levitating is so fun to listen to!',            NOW() - INTERVAL 45 MINUTE),
(3, 2, 'Metallica never disappoints.',                  NOW() - INTERVAL 20 MINUTE),
(4, 3, 'Billie Eilish has a really unique sound.',      NOW() - INTERVAL 5 MINUTE);


INSERT INTO favorites (user_id, song_id) VALUES
(1, 1), (1, 2), (1, 4),
(2, 1), (2, 5), (2, 9), (2, 10),
(3, 7), (3, 8), (3, 2),
(4, 9), (4, 10), (4, 6);


INSERT INTO likes (user_id, song_id) VALUES
(1, 5), (1, 9),
(2, 2), (2, 3), (2, 7),
(3, 1), (3, 9),
(4, 1), (4, 2), (4, 7), (4, 8);

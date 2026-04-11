-- SongClub database schema
-- Run this in PHPMyAdmin (localhost:8080) or via MySQL CLI

CREATE DATABASE IF NOT EXISTS songclub
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE songclub;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    bio        TEXT,
    role       ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Songs
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

-- Last-listened posts (one per user)
CREATE TABLE IF NOT EXISTS posts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL UNIQUE,
    song_id    INT NOT NULL,
    caption    TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comments on last-listened posts
CREATE TABLE IF NOT EXISTS comments (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    post_id    INT NOT NULL,
    user_id    INT NOT NULL,
    content    TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Favorites (many-to-many: users ↔ songs)
CREATE TABLE IF NOT EXISTS favorites (
    user_id    INT NOT NULL,
    song_id    INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, song_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Likes (many-to-many: users ↔ songs)
CREATE TABLE IF NOT EXISTS likes (
    user_id    INT NOT NULL,
    song_id    INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, song_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Seed data ───────────────────────────────────────────────────────────────
-- Both accounts use the password: password

INSERT INTO users (username, email, password, bio, role) VALUES
('admin',
 'admin@songclub.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Site administrator.',
 'admin'),
('alice',
 'alice@songclub.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Music lover. Always listening.',
 'user');

-- Sample songs (created by admin, id=1)
INSERT INTO songs (title, artist, album, genre, link, created_by) VALUES
('Blinding Lights',  'The Weeknd',   'After Hours',  'Synth-pop', 'https://open.spotify.com/track/0VjIjW4GlUZAMYd2vXMi3b', 1),
('Bohemian Rhapsody','Queen',        'A Night at the Opera', 'Rock', 'https://www.youtube.com/watch?v=fJ9rUzIMcZQ', 1),
('Shape of You',     'Ed Sheeran',   'Divide',       'Pop',       'https://open.spotify.com/track/7qiZfU4dY1lWllzX7mPBI3', 1),
('As It Was',        'Harry Styles', "Harry's House", 'Indie pop', 'https://open.spotify.com/track/4LRPiXqCikLlN15c3yImP7', 1);

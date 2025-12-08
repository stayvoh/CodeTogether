CREATE TABLE role (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL,
    privileges VARCHAR(255),
    description TEXT
);

CREATE TABLE user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT,
    profile_picture VARCHAR(255) DEFAULT '',
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    about_me VARCHAR(255) Default 'I''m just a little panda :p',
    email VARCHAR(255) UNIQUE,
    points INT DEFAULT 0,
    is_deleted BOOLEAN DEFAULT FALSE,
    status ENUM('online', 'banned', 'offline','away') DEFAULT 'offline',
    created_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    theme ENUM('dark','light') DEFAULT 'light',
    rain_enabled BOOLEAN DEFAULT FALSE,
    latest_update DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES role(role_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

CREATE TABLE friend_list (
    user_id_1 INT,
    user_id_2 INT,
    status ENUM('friends','not-friends','blocked','pending') DEFAULT 'not-friends',
    created_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    initiated_by INT DEFAULT -1,
    PRIMARY KEY (user_id_1, user_id_2),
    FOREIGN KEY (user_id_1) REFERENCES user(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (user_id_2) REFERENCES user(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE thread (
    thread_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    is_deleted BOOLEAN DEFAULT FALSE,
    created_on DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE thread_bridge (
    user_id INT,
    thread_id INT,
    joined_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, thread_id),
    FOREIGN KEY (user_id) REFERENCES user(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (thread_id) REFERENCES thread(thread_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE post (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    thread_id INT,
    file_path VARCHAR(255),
    contents VARCHAR(255),
    likes INT DEFAULT 0,
    caption VARCHAR(255),
    visibility ENUM('public', 'friends', 'private') DEFAULT 'public',
    is_deleted BOOLEAN DEFAULT FALSE,
    created_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    latest_update DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (thread_id) REFERENCES thread(thread_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE post_likes (
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    liked_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES post(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
);


CREATE TABLE comment (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    post_id INT NOT NULL,
    contents VARCHAR(500),
    is_deleted BOOLEAN DEFAULT FALSE,
    created_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (post_id) REFERENCES post(post_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE chat (
    chat_id INT AUTO_INCREMENT PRIMARY KEY,
    chat_type VARCHAR(50),
    is_deleted BOOLEAN DEFAULT false,
    created_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_message_at DATETIME
);

CREATE TABLE chat_bridge (
    chat_id INT,
    user_id INT,
    joined_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    role ENUM('member', 'admin') DEFAULT 'member',
    PRIMARY KEY (chat_id, user_id),
    FOREIGN KEY (chat_id) REFERENCES chat(chat_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE message (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    thread_id INT,
    user_id INT NOT NULL,
    chat_id INT,
    content VARCHAR(1000),
    is_deleted BOOLEAN DEFAULT FALSE,
    is_edited BOOLEAN DEFAULT FALSE,
    sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (chat_id) REFERENCES chat(chat_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (thread_id) REFERENCES thread(thread_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE game (
    game_id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255),
    points INT DEFAULT 0,
    time_limit INT,
    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    created_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    latest_update DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE game_bridge (
    game_id INT,
    user_id INT,
    score INT DEFAULT 0,
    completed_on DATETIME,
    PRIMARY KEY (game_id, user_id),
    FOREIGN KEY (game_id) REFERENCES game(game_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

ALTER TABLE user ADD COLUMN last_daily_submission_date DATE;
ALTER TABLE user ADD COLUMN last_daily_problem_title VARCHAR(255);

INSERT INTO role (role_id, role_name, privileges, description) VALUES
(1, 'moderator', 'manage_users,edit_content,game_mod,game_review', 'Moderates users, content, and games.'),
(2, 'student', 'view_content,create_content,comment,participate_in_games', 'Can view content, create their own content, comment, and participate in games.'),
(3, 'teacher', 'create_content,grade_students,game_review', 'Can create content, grade students, and review game code.');

INSERT INTO thread (title)
VALUES 
('General Discussion'),
('Announcements'),
('Help and Support'),
('Off Topic');

-- Users table (passwords are hashed)
--Alice through bob the passwords are password123
INSERT INTO user (role_id, username, password, email, points) VALUES
(1, 'Alice', '$2y$10$szc7v.HH8paPYGc01BSTlO2pbitka4mdQ1PBHpbcaO5mM.CQZhp4S', 'alice@example.com', 150),
(1, 'Bob', '$2y$10$szc7v.HH8paPYGc01BSTlO2pbitka4mdQ1PBHpbcaO5mM.CQZhp4S', 'bob@example.com', 90),
(1, 'Charlie', '$2y$10$szc7v.HH8paPYGc01BSTlO2pbitka4mdQ1PBHpbcaO5mM.CQZhp4S', 'charlie@example.com', 45),
(1, 'Dave', '$2y$10$szc7v.HH8paPYGc01BSTlO2pbitka4mdQ1PBHpbcaO5mM.CQZhp4S', 'dave@example.com', 30),
(1, 'Eve', '$2y$10$szc7v.HH8paPYGc01BSTlO2pbitka4mdQ1PBHpbcaO5mM.CQZhp4S', 'eve@example.com', 200),
(1, 'abrahamsharum', '$2y$10$l9A2A/6gUe3L/j4ritpTde8n3Ta2h.B0Lfsv5E6qNmnqWIoc7Qy3q', 'abxbox13@gmail.com', 1000),
(1, 'seth', '$2y$10$l9A2A/6gUe3L/j4ritpTde8n3Ta2h.B0Lfsv5E6qNmnqWIoc7Qy3q', 'seth@matrixmail.com', 1000);

-- Each user has one post
INSERT INTO post (user_id, username, thread_id, contents, caption, visibility) VALUES
(1, 'Alice',1,'Hello from Alice', 'My first post!', 'public'),
(2, 'Bob',1,'Hello from Bob', 'Excited to join!', 'public'),
(3, 'Charlie',1,'Hello from Charlie', 'Just chilling.', 'public'),
(4, 'Dave',1,'Hello from Dave', 'Learning PHP today.', 'public'),
(5, 'Eve',1,'Hello from Eve', 'Loving coding!', 'public'),
(6, 'abrahamsharum',1,'Hello from Abraham', 'Test post for setup.', 'public'),
(7, 'seth',1,'Hello from Seth', 'This is crazy!', 'public');

INSERT INTO friend_list (user_id_1, user_id_2, status) VALUES
(1,2,'friends'), (1,3,'friends'), (1,4,'friends'), (1,5,'friends'), (1,6,'friends'),
(2,3,'friends'), (2,4,'friends'), (2,5,'friends'), (2,6,'friends'),
(3,4,'friends'), (3,5,'friends'), (3,6,'friends'),
(4,5,'friends'), (4,6,'friends'),
(5,6,'friends'),
(6,7,'friends');



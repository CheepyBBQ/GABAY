-- GABAY demo posts for the Peer Circle.
-- Run AFTER gabay.sql (phpMyAdmin: select the gabay database, SQL tab, paste, Go; or the Import tab).
-- It adds 5 demo students (they cannot log in), 7 Peer Circle posts with replies, and a short World Chat conversation.
-- Safe to run again: it replaces the earlier demo posts instead of duplicating them.
USE gabay;

INSERT IGNORE INTO users (role, email, password_hash, full_name, alias, consented_at) VALUES
('student', 'demo.heron@gabay.local',  '!no-login', 'Demo Student 1', 'Gentle Heron',  NOW()),
('student', 'demo.otter@gabay.local',  '!no-login', 'Demo Student 2', 'Calm Otter',    NOW()),
('student', 'demo.lark@gabay.local',   '!no-login', 'Demo Student 3', 'Brave Lark',    NOW()),
('student', 'demo.finch@gabay.local',  '!no-login', 'Demo Student 4', 'Warm Finch',    NOW()),
('student', 'demo.swan@gabay.local',   '!no-login', 'Demo Student 5', 'Hopeful Swan',  NOW());

DELETE FROM posts WHERE student_id IN (SELECT id FROM users WHERE email LIKE 'demo.%@gabay.local');

SET @heron = (SELECT id FROM users WHERE email = 'demo.heron@gabay.local');
SET @otter = (SELECT id FROM users WHERE email = 'demo.otter@gabay.local');
SET @lark  = (SELECT id FROM users WHERE email = 'demo.lark@gabay.local');
SET @finch = (SELECT id FROM users WHERE email = 'demo.finch@gabay.local');
SET @swan  = (SELECT id FROM users WHERE email = 'demo.swan@gabay.local');

-- 1. Midterm stress
INSERT INTO posts (student_id, parent_id, body, created_at) VALUES
(@heron, NULL, 'Midterms start on Monday and I have not slept properly in three days. How do you all handle the pressure?', NOW() - INTERVAL 46 HOUR);
SET @p = LAST_INSERT_ID();
INSERT INTO posts (student_id, parent_id, body, created_at) VALUES
(@otter, @p, 'Same here. Studying in 25-minute blocks with short breaks helped me. It made the pile of lessons feel smaller.', NOW() - INTERVAL 45 HOUR),
(@lark,  @p, 'Try to sleep before cramming. Even a few hours helped me more than I expected. You can do this.', NOW() - INTERVAL 43 HOUR);

-- 2. Homesickness
INSERT INTO posts (student_id, parent_id, body, created_at) VALUES
(@finch, NULL, 'Feeling homesick today. It is my first time living away from my family and the dorm gets so quiet at night.', NOW() - INTERVAL 38 HOUR);
SET @p = LAST_INSERT_ID();
INSERT INTO posts (student_id, parent_id, body, created_at) VALUES
(@swan,  @p, 'It gets easier. I video call home every Sunday and it helps a lot. You are not alone in this.', NOW() - INTERVAL 36 HOUR),
(@heron, @p, 'Kumusta ka na? Maybe say hi to a dormmate tonight. Even a short chat can make the room feel warmer.', NOW() - INTERVAL 35 HOUR);

-- 3. A small win
INSERT INTO posts (student_id, parent_id, body, created_at) VALUES
(@otter, NULL, 'Small win: I finally asked my professor for an extension and she said yes. I was so scared to ask.', NOW() - INTERVAL 30 HOUR);
SET @p = LAST_INSERT_ID();
INSERT INTO posts (student_id, parent_id, body, created_at) VALUES
(@finch, @p, 'Congratulations! Asking is the hardest part. Proud of you.', NOW() - INTERVAL 29 HOUR);

-- 4. Feeling behind in class
INSERT INTO posts (student_id, parent_id, body, created_at) VALUES
(@swan, NULL, 'Sometimes I feel like everyone else understands the lessons and I am the only one who is lost. Does anyone else feel this way?', NOW() - INTERVAL 24 HOUR);
SET @p = LAST_INSERT_ID();
INSERT INTO posts (student_id, parent_id, body, created_at) VALUES
(@lark,  @p, 'Yes, more than you think. I started a small study group and found out that half of us were lost too.', NOW() - INTERVAL 22 HOUR),
(@otter, @p, 'Those feelings are very common. Asking questions in class helped me, even when my voice was shaking.', NOW() - INTERVAL 21 HOUR);

-- 5. A gentle reminder
INSERT INTO posts (student_id, parent_id, body, created_at) VALUES
(@lark, NULL, 'Reminder to drink water and eat a real meal today. I forgot both during my project deadline.', NOW() - INTERVAL 14 HOUR);
SET @p = LAST_INSERT_ID();
INSERT INTO posts (student_id, parent_id, body, created_at) VALUES
(@finch, @p, 'Thank you, I needed this. Going to get lunch now.', NOW() - INTERVAL 13 HOUR);

-- 6. Work and school
INSERT INTO posts (student_id, parent_id, body, created_at) VALUES
(@heron, NULL, 'Balancing a part-time job and school is exhausting. How do you plan your week?', NOW() - INTERVAL 8 HOUR);
SET @p = LAST_INSERT_ID();
INSERT INTO posts (student_id, parent_id, body, created_at) VALUES
(@swan, @p, 'I plan my week on Sunday night and keep one free evening just for rest. It keeps me from burning out.', NOW() - INTERVAL 6 HOUR);

-- 7. The toolkit
INSERT INTO posts (student_id, parent_id, body, created_at) VALUES
(@finch, NULL, 'Tip: the guided breathing exercise on the dashboard really calmed me down before my presentation today.', NOW() - INTERVAL 2 HOUR);

-- World Chat: a short, friendly conversation from the last few hours
INSERT INTO posts (student_id, parent_id, kind, body, created_at) VALUES
(@swan,  NULL, 'chat', 'Good morning everyone! Anyone else surviving on coffee this week?', NOW() - INTERVAL 5 HOUR),
(@lark,  NULL, 'chat', 'Coffee and stubbornness. Hang in there, midterms are almost over.', NOW() - INTERVAL 5 HOUR + INTERVAL 2 MINUTE),
(@otter, NULL, 'chat', 'Reminder: take a short walk between study sessions. It helps more than it sounds.', NOW() - INTERVAL 4 HOUR),
(@finch, NULL, 'chat', 'Thanks for the tips yesterday, I finally slept 7 hours.', NOW() - INTERVAL 3 HOUR),
(@heron, NULL, 'chat', 'Proud of you! We are all in this together.', NOW() - INTERVAL 3 HOUR + INTERVAL 3 MINUTE),
(@swan,  NULL, 'chat', 'Good luck to everyone with exams today. You have got this!', NOW() - INTERVAL 1 HOUR);

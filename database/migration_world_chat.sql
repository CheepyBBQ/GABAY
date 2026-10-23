-- For databases created before World Chat was added. Run once on the gabay database.
USE gabay;
ALTER TABLE posts
  ADD COLUMN kind ENUM('post','chat') NOT NULL DEFAULT 'post' AFTER parent_id,
  ADD KEY idx_chat (kind, is_hidden, id);

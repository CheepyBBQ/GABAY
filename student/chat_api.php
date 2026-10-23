<?php
require __DIR__ . '/../includes/bootstrap.php';
$u = require_role('student');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function out(array $d, int $code = 200): never { http_response_code($code); echo json_encode($d); exit; }

if (is_post()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'send') {
        $body = trim($_POST['body'] ?? '');
        if ($body === '' || mb_strlen($body) > 300) out(['error' => 'Write a message of up to 300 characters.'], 422);
        // Rate limit using the database clock, so PHP and MySQL time zones cannot disagree.
        $recent = q("SELECT 1 FROM posts WHERE student_id = ? AND kind = 'chat' AND created_at > NOW() - INTERVAL 2 SECOND LIMIT 1", [$u['id']])->fetch();
        if ($recent) out(['error' => 'Give it a moment between messages.'], 429);
        $crisis = has_crisis_language($body);
        q("INSERT INTO posts (student_id, kind, body, is_flagged, flag_source, review_state) VALUES (?, 'chat', ?, ?, ?, ?)",
          [$u['id'], $body, $crisis ? 1 : 0, $crisis ? 'crisis' : null, $crisis ? 'open' : null]);
        out(['ok' => true, 'support' => $crisis]);
    }
    if ($action === 'report') {
        $pid = (int)($_POST['post_id'] ?? 0);
        $p = q("SELECT student_id FROM posts WHERE id = ? AND kind = 'chat' AND is_hidden = 0", [$pid])->fetch();
        if (!$p || (int)$p['student_id'] === (int)$u['id']) out(['error' => 'You cannot report that message.'], 422);
        q('INSERT IGNORE INTO post_reports (post_id, reporter_id) VALUES (?,?)', [$pid, $u['id']]);
        q("UPDATE posts SET is_flagged = 1, flag_source = COALESCE(flag_source, 'report'),
             review_state = IF(review_state = 'reviewed', 'reviewed', 'open') WHERE id = ?", [$pid]);
        out(['ok' => true]);
    }
    out(['error' => 'Unknown action.'], 400);
}

$rows = q("SELECT p.id, p.body, p.created_at, p.student_id, u.alias FROM posts p JOIN users u ON u.id = p.student_id
           WHERE p.kind = 'chat' AND p.is_hidden = 0 ORDER BY p.id DESC LIMIT 50")->fetchAll();
out(['messages' => array_map(fn($r) => [
    'id' => (int)$r['id'], 'alias' => $r['alias'], 'body' => $r['body'],
    'time' => date('g:i A', strtotime($r['created_at'])), 'mine' => (int)$r['student_id'] === (int)$u['id'],
], array_reverse($rows))]);

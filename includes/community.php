<?php
// Read-only view of the Peer Circle and World Chat for counselors and admins (aliases only, never real identities).
// Counselors can also hide a post or message ($canHide). Admins can only read.
function community_chat_html(bool $canHide, string $backTab): string {
    $rows = q("SELECT p.id, p.body, p.created_at, p.is_flagged, u.alias FROM posts p JOIN users u ON u.id = p.student_id
               WHERE p.kind = 'chat' AND p.is_hidden = 0 ORDER BY p.id DESC LIMIT 100")->fetchAll();
    if (!$rows) return '<p class="empty">No messages yet.</p>';
    $h = '';
    foreach (array_reverse($rows) as $r) {
        $h .= '<div class="chat-msg"><div class="chat-head"><b>' . e($r['alias']) . '</b><span class="muted small">' . e(date('M j, g:i A', strtotime($r['created_at']))) . '</span>';
        if ($r['is_flagged']) $h .= '<span class="pill pill-pending">Flagged</span>';
        if ($canHide) $h .= '<form method="post" class="chat-hide"><input type="hidden" name="action" value="hide"><input type="hidden" name="post_id" value="' . (int)$r['id']
                          . '"><input type="hidden" name="tab" value="' . e($backTab) . '"><button class="chat-report" type="submit">Hide this message</button></form>';
        $h .= '</div><div class="chat-body">' . e($r['body']) . '</div></div>';
    }
    return $h;
}

function community_page(string $role, bool $canHide): void {
    $u = require_role($role);
    $self = $role . '/community.php';
    if (($_GET['partial'] ?? '') === 'chat') { echo community_chat_html($canHide, 'chat'); exit; }

    if (is_post() && $canHide && ($_POST['action'] ?? '') === 'hide') {
        $n = q("UPDATE posts SET is_hidden = 1, review_state = IF(is_flagged = 1, 'reviewed', review_state) WHERE id = ?", [(int)($_POST['post_id'] ?? 0)])->rowCount();
        flash($n ? 'ok' : 'error', $n ? 'That item is hidden from students.' : 'That item could not be hidden.', $n ? 'Hidden' : null);
        redirect($self . '?tab=' . (($_POST['tab'] ?? '') === 'chat' ? 'chat' : 'posts'));
    }

    $tab = ($_GET['tab'] ?? '') === 'chat' ? 'chat' : 'posts';
    $posts = q("SELECT p.id, p.body, p.created_at, p.is_flagged, u.alias FROM posts p JOIN users u ON u.id = p.student_id
                WHERE p.parent_id IS NULL AND p.kind = 'post' AND p.is_hidden = 0 ORDER BY p.created_at DESC LIMIT 50")->fetchAll();
    $replies = [];
    if ($posts) {
        $in = implode(',', array_fill(0, count($posts), '?'));
        foreach (q("SELECT p.id, p.parent_id, p.body, p.created_at, p.is_flagged, u.alias FROM posts p JOIN users u ON u.id = p.student_id
                    WHERE p.parent_id IN ($in) AND p.is_hidden = 0 ORDER BY p.created_at", array_column($posts, 'id'))->fetchAll() as $r) $replies[$r['parent_id']][] = $r;
    }
    $hideForm = function (int $id, string $label) use ($canHide) {
        if (!$canHide) return '';
        return '<form method="post" class="actions"><input type="hidden" name="action" value="hide"><input type="hidden" name="post_id" value="' . $id
             . '"><input type="hidden" name="tab" value="posts"><button class="btn btn-danger btn-sm" type="submit">' . icon('eyeoff', 16) . e($label) . '</button></form>';
    };

    render_header('Community', 'community', $u); ?>
<div class="page-head"><h1>Community</h1>
  <p>Posts and chat from students, shown by anonymous name only. Students are told that staff can read these spaces to keep everyone safe.<?= $canHide ? '' : ' You can read here, and counselors handle moderation.' ?></p></div>

<div class="tabs" role="tablist" aria-label="Community spaces" style="max-width:420px;margin-bottom:1.25rem">
  <button type="button" role="tab" id="tab-posts" aria-controls="panel-posts" aria-selected="<?= $tab === 'posts' ? 'true' : 'false' ?>" data-tab="posts">Peer Circle</button>
  <button type="button" role="tab" id="tab-chat" aria-controls="panel-chat" aria-selected="<?= $tab === 'chat' ? 'true' : 'false' ?>" data-tab="chat">World Chat</button>
</div>

<section class="panel tab-panel" id="panel-posts" role="tabpanel" aria-labelledby="tab-posts"<?= $tab === 'posts' ? '' : ' hidden' ?>>
  <h2>Peer Circle</h2>
  <?php if (!$posts): ?><p class="empty">No posts yet.</p><?php endif; ?>
  <?php foreach ($posts as $p): ?>
    <article class="post">
      <div class="post-head"><b><?= e($p['alias']) ?></b><span class="muted small"><?= e(date('M j, g:i A', strtotime($p['created_at']))) ?></span>
        <?php if ($p['is_flagged']): ?><span class="pill pill-pending">Flagged</span><?php endif; ?></div>
      <div class="post-body"><?= e($p['body']) ?></div>
      <?php if (!empty($replies[$p['id']])): ?><div class="replies">
        <?php foreach ($replies[$p['id']] as $r): ?>
          <div class="reply"><div class="post-head"><b><?= e($r['alias']) ?></b><span class="muted small"><?= e(date('M j, g:i A', strtotime($r['created_at']))) ?></span>
            <?php if ($r['is_flagged']): ?><span class="pill pill-pending">Flagged</span><?php endif; ?></div>
            <div class="post-body" style="margin:0"><?= e($r['body']) ?></div><?= $hideForm((int)$r['id'], 'Hide this reply') ?></div>
        <?php endforeach; ?></div><?php endif; ?>
      <?= $hideForm((int)$p['id'], 'Hide this post') ?>
    </article>
  <?php endforeach; ?>
</section>

<section class="panel tab-panel" id="panel-chat" role="tabpanel" aria-labelledby="tab-chat"<?= $tab === 'chat' ? '' : ' hidden' ?>>
  <h2>World Chat</h2>
  <p class="muted small">Updates by itself every few seconds.</p>
  <div id="monitor-log" class="chat-log" tabindex="0" role="log" aria-label="World Chat messages" data-src="<?= e(url($self) . '?partial=chat') ?>"><?= community_chat_html($canHide, 'chat') ?></div>
</section>
<?php render_footer(['assets/js/login.js', 'assets/js/community.js']);
}

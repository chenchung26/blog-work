<?php
function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function get_all_tags(PDO $pdo) {
    $stmt = $pdo->query('SELECT id, name FROM tags ORDER BY name');
    return $stmt->fetchAll();
}

function save_post_tags(PDO $pdo, int $post_id, array $tag_ids = null) {
   
    $pdo->prepare('DELETE FROM post_tags WHERE post_id = ?')->execute([$post_id]);
    if (!$tag_ids) return;
    $ins = $pdo->prepare('INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)');
    foreach ($tag_ids as $tid) {
        $ins->execute([$post_id, (int)$tid]);
    }
}

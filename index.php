<?php require __DIR__.'/db.php'; require __DIR__.'/functions.php'; ?>
<?php include __DIR__.'/header.php'; ?>

<h2>記事一覧</h2>

<?php
$allTags = get_all_tags($pdo);
$tagId = isset($_GET['tag_id']) && $_GET['tag_id'] !== '' ? (int)$_GET['tag_id'] : null;
?>
<div class="filters">
  <form method="GET" action="">
    <label>タグで検索：</label>
    <select name="tag_id" onchange="this.form.submit()">
      <option value="">（すべて）</option>
      <?php foreach ($allTags as $t): ?>
        <option value="<?= (int)$t['id'] ?>" <?= $tagId===(int)$t['id']?'selected':'' ?>><?= h($t['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <noscript><button type="submit">検索</button></noscript>
  </form>
</div>

<?php
if ($tagId) {
    $sql = "SELECT p.*, GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ', ') AS tags
            FROM posts p
            JOIN post_tags pt ON pt.post_id = p.id
            JOIN tags t ON t.id = pt.tag_id
            WHERE p.id IN (SELECT post_id FROM post_tags WHERE tag_id = ?)
            GROUP BY p.id
            ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tagId]);
} else {
    $sql = "SELECT p.*, GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ', ') AS tags
            FROM posts p
            LEFT JOIN post_tags pt ON pt.post_id = p.id
            LEFT JOIN tags t ON t.id = pt.tag_id
            GROUP BY p.id
            ORDER BY p.created_at DESC";
    $stmt = $pdo->query($sql);
}
$posts = $stmt->fetchAll();
?>

<?php if (!$posts): ?>
  <p>まだ投稿がありません。まずは <a href="create_post.php">新規投稿</a> または <a href="tags.php">タグ作成</a> !</p>
<?php else: ?>
  <?php foreach ($posts as $p): ?>
    <article class="post">
      <h3><?= h($p['title']) ?></h3>
      <div><?= nl2br(h($p['body'])) ?></div>
      <div class="tags">
        <?php if ($p['tags']): ?>
          <?php foreach (explode(', ', $p['tags']) as $name): ?>
            <span class="tag">#<?= h($name) ?></span>
          <?php endforeach; ?>
        <?php else: ?>
          <span class="tag" style="opacity:.6">（タグなし）</span>
        <?php endif; ?>
      </div>
      <div class="meta">作成日時：<?= h($p['created_at']) ?></div>
      <div class="controls">
        <a href="edit_post.php?id=<?= (int)$p['id'] ?>">編集</a>
        <form action="delete_post.php" method="POST" style="display:inline" onsubmit="return confirm('削除しますか？');">
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <button type="submit">削除</button>
        </form>
      </div>
    </article>
  <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__.'/footer.php'; ?>

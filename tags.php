<?php require __DIR__.'/db.php'; require __DIR__.'/functions.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='create') {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $stmt = $pdo->prepare('INSERT IGNORE INTO tags (name) VALUES (?)');
        $stmt->execute([$name]);
    }
    header('Location: tags.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id>0) {
        // 由於 post_tags 有 ON DELETE CASCADE，刪 tag 會自動清聯結
        $stmt = $pdo->prepare('DELETE FROM tags WHERE id = ?');
        $stmt->execute([$id]);
    }
    header('Location: tags.php');
    exit;
}
$tags = get_all_tags($pdo);
?>
<?php include __DIR__.'/header.php'; ?>
<h2>タグ管理</h2>
<section>
  <h3>新規タグを追加</h3>
  <form method="POST">
    <input type="hidden" name="action" value="create">
    <div class="row">
      <label>タグ名</label>
      <input type="text" name="name" placeholder="例：料理, 学校, 日記">
    </div>
    <button type="submit">追加</button>
  </form>
</section>
<hr>
<section>
  <h3>既存タグ</h3>
  <?php if (!$tags): ?>
    <p>まだタグがありません。</p>
  <?php else: ?>
    <ul>
      <?php foreach ($tags as $t): ?>
        <li>
          <?= h($t['name']) ?>
          <form method="POST" style="display:inline" onsubmit="return confirm('削除しますか？');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
            <button type="submit">削除</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>
<?php include __DIR__.'/footer.php'; ?>

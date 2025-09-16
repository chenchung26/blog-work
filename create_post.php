<?php require __DIR__.'/db.php'; require __DIR__.'/functions.php'; ?>
<?php
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body  = trim($_POST['body'] ?? '');
    $tag_ids = isset($_POST['tag_ids']) ? array_map('intval', (array)$_POST['tag_ids']) : [];

    if ($title === '') $errors[] = 'タイトルを入力してください。';
    if ($body  === '') $errors[] = '本文を入力してください。';

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO posts (title, body) VALUES (?, ?)');
        $stmt->execute([$title, $body]);
        $post_id = (int)$pdo->lastInsertId();
        save_post_tags($pdo, $post_id, $tag_ids);
        header('Location: index.php');
        exit;
    }
}
$allTags = get_all_tags($pdo);
?>
<?php include __DIR__.'/header.php'; ?>
<h2>新規投稿</h2>
<?php if ($errors): ?>
  <ul style="color:#c00;">
    <?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
  </ul>
<?php endif; ?>
<form method="POST">
  <div class="row">
    <label>タイトル</label>
    <input type="text" name="title" value="<?= h($_POST['title'] ?? '') ?>">
  </div>
  <div class="row">
    <label>本文</label>
    <textarea name="body" rows="8"><?= h($_POST['body'] ?? '') ?></textarea>
  </div>
  <div class="row">
    <label>タグ（複数選択可）</label>
    <div>
      <?php if (!$allTags): ?>
        <p>まだタグがありません。先に <a href="tags.php">タグ管理</a> で作成してください。</p>
      <?php else: ?>
        <?php foreach ($allTags as $t): ?>
          <label style="margin-right:12px; display:inline-block;">
            <input type="checkbox" name="tag_ids[]" value="<?= (int)$t['id'] ?>"
              <?= isset($_POST['tag_ids']) && in_array($t['id'], (array)$_POST['tag_ids']) ? 'checked' : '' ?>>
            <?= h($t['name']) ?>
          </label>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
  <button type="submit">保存</button>
</form>
<?php include __DIR__.'/footer.php'; ?>

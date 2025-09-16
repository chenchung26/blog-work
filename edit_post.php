<?php require __DIR__.'/db.php'; require __DIR__.'/functions.php'; ?>
<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->execute([$id]);
$post = $stmt->fetch();
if (!$post) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare('SELECT tag_id FROM post_tags WHERE post_id = ?');
$stmt->execute([$id]);
$selected = array_column($stmt->fetchAll(), 'tag_id');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body  = trim($_POST['body'] ?? '');
    $tag_ids = isset($_POST['tag_ids']) ? array_map('intval', (array)$_POST['tag_ids']) : [];

    if ($title === '') $errors[] = 'タイトルを入力してください。';
    if ($body  === '') $errors[] = '本文を入力してください。';

    if (!$errors) {
        $upd = $pdo->prepare('UPDATE posts SET title = ?, body = ? WHERE id = ?');
        $upd->execute([$title, $body, $id]);
        save_post_tags($pdo, $id, $tag_ids);
        header('Location: index.php');
        exit;
    }
}
$allTags = get_all_tags($pdo);
?>
<?php include __DIR__.'/header.php'; ?>
<h2>投稿を編集</h2>
<?php if ($errors): ?>
  <ul style="color:#c00;">
    <?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
  </ul>
<?php endif; ?>
<form method="POST">
  <div class="row">
    <label>タイトル</label>
    <input type="text" name="title" value="<?= h($_POST['title'] ?? $post['title']) ?>">
  </div>
  <div class="row">
    <label>本文</label>
    <textarea name="body" rows="8"><?= h($_POST['body'] ?? $post['body']) ?></textarea>
  </div>
  <div class="row">
    <label>タグ</label>
    <div>
      <?php foreach ($allTags as $t): ?>
        <?php $checked = isset($_POST['tag_ids']) ? in_array($t['id'], (array)$_POST['tag_ids']) : in_array($t['id'], $selected); ?>
        <label style="margin-right:12px; display:inline-block;">
          <input type="checkbox" name="tag_ids[]" value="<?= (int)$t['id'] ?>" <?= $checked ? 'checked' : '' ?>>
          <?= h($t['name']) ?>
        </label>
      <?php endforeach; ?>
    </div>
  </div>
  <button type="submit">更新</button>
</form>
<?php include __DIR__.'/footer.php'; ?>

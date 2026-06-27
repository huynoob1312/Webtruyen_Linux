

<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/read.css">

<div class="read-wrapper">
    <div class="read-container">

        <div class="text-center mb-3">
            <h4 class="fw-bold mb-1"><?= htmlspecialchars($chapter['title']) ?></h4>
            <p class="text-muted small">
                Truyện: <a href="index.php?route=novel/detail&id=<?= $novel_id ?>" class="text-decoration-none text-secondary fw-bold"><?= htmlspecialchars($novel['title']) ?></a>
                <span class="mx-1">|</span>
                Ngày: <?= date('d/m/Y', strtotime($chapter['created_at'])) ?>
            </p>
        </div>

        <div class="chapter-nav">
            <a href="<?= $prev_link ?>" class="btn btn-secondary btn-nav <?= $disable_prev ?>">⬅ Trước</a>

            <select class="form-select chapter-select" onchange="location = this.value;">
                <?php
                if (!empty($all_chapters)) {
                    foreach ($all_chapters as $row) {
                        $selected = ($row['id'] == $chap_id) ? 'selected' : '';
                        echo '<option value="index.php?route=novel/read&id=' . $row['id'] . '" ' . $selected . '>' . htmlspecialchars($row['title']) . '</option>';
                    }
                }
                ?>
            </select>

            <a href="<?= $next_link ?>" class="btn btn-primary btn-nav <?= $disable_next ?>">Sau ➡</a>
        </div>

        <div class="novel-content">
            <?php
            echo nl2br(htmlspecialchars($chapter['content']));
            ?>
        </div>

        <div class="chapter-nav mt-4">
            <a href="<?= $prev_link ?>" class="btn btn-secondary btn-nav <?= $disable_prev ?>">⬅ Trước</a>
            <a href="<?= $next_link ?>" class="btn btn-primary btn-nav <?= $disable_next ?>">Sau ➡</a>
        </div>

        <div class="mt-5">
            <?php
            $cmt_type = 'novel';
            $cmt_obj_id = $novel_id;
            if (file_exists('app/views/includes/comment_section.php')) {
                include 'app/views/includes/comment_section.php';
            }
            ?>
        </div>
    </div>
</div>

<?php
// File: app/views/category/comic.php
// Dumb View - nhận dữ liệu từ CategoryController
// $comics, $cat_name, $img_domain được extract bởi View::render()
?>

<div class="container mt-4">
    <h3 class="mb-4 border-bottom pb-2">🖼️ Truyện Tranh: <span class="text-danger"><?= htmlspecialchars($cat_name ?? '') ?></span></h3>
    
    <?php if (!empty($comics)): ?>
        <div class="row">
            <?php foreach ($comics as $comic): ?>
                <div class="col-6 col-md-3 col-lg-2 mb-4">
                    <div class="card h-100 shadow-sm">
                        <a href="index.php?route=comic/detail&slug=<?= htmlspecialchars($comic['slug']) ?>">
                            <img src="<?= htmlspecialchars($img_domain . $comic['thumb_url']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                        </a>
                        <div class="card-body p-2">
                            <h6 class="text-truncate">
                                <a href="index.php?route=comic/detail&slug=<?= htmlspecialchars($comic['slug']) ?>" class="text-dark text-decoration-none">
                                    <?= htmlspecialchars($comic['name']) ?>
                                </a>
                            </h6>
                            <small class="text-muted">Chương mới nhất</small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center mt-4">Không tìm thấy truyện tranh trong thể loại này.</div>
    <?php endif; ?>
</div>
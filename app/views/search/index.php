


<div class="container mt-4 mb-5" style="min-height: 600px;">
    
    <div class="mb-4 pb-2 border-bottom">
        <h4 class="fw-bold">
            <i class="fas fa-search me-2 text-secondary"></i>Kết quả tìm kiếm: 
            <span class="text-primary">"<?= htmlspecialchars($keyword) ?>"</span>
        </h4>
        <small class="text-muted">Tìm thấy tổng cộng <strong><?= (count($db_results) + count($api_results)) ?></strong> kết quả.</small>
    </div>

    <div class="mb-5">
        <div class="category-header">
            <h3>
                <i class="fas fa-pen-nib text-success me-2"></i>Truyện Chữ 
                <span class="badge bg-secondary fs-6 align-middle ms-2"><?= count($db_results) ?></span>
            </h3>
        </div>

        <?php if (!empty($db_results)): ?>
            <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
                <?php foreach ($db_results as $novel):
                    $cover = !empty($novel['cover_image']) ? $novel['cover_image'] : 'https://via.placeholder.com/300x400?text=No+Image';
                ?>
                    <div class="col">
                        <div class="book-card">
                            <a href="index.php?route=novel/detail&id=<?= $novel['id'] ?>" class="book-thumb">
                                <span class="badge bg-success badge-overlay">Chữ</span>
                                <img src="<?= $cover ?>" alt="<?= htmlspecialchars($novel['title']) ?>" loading="lazy">
                            </a>
                            <div class="book-body">
                                <div class="book-title">
                                    <a href="index.php?route=novel/detail&id=<?= $novel['id'] ?>" title="<?= htmlspecialchars($novel['title']) ?>">
                                        <?= htmlspecialchars($novel['title']) ?>
                                    </a>
                                </div>
                                <div class="book-info">
                                    <span><i class="fas fa-user-edit"></i> <?= htmlspecialchars($novel['author']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light text-center border border-dashed">
                <p class="mb-0 text-muted">Không tìm thấy truyện chữ nào phù hợp.</p>
            </div>
        <?php endif; ?>
    </div>

    <div>
        <div class="category-header">
            <h3>
                <i class="fas fa-image text-warning me-2"></i>Truyện Tranh 
                <span class="badge bg-secondary fs-6 align-middle ms-2"><?= count($api_results) ?></span>
            </h3>
        </div>

        <?php if (!empty($api_results)): ?>
            <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
                <?php foreach ($api_results as $comic): 
                    // ComicModel->searchComics() đã trả về 'thumb' cả domain rồi
                    $thumb = $comic['thumb'];
                ?>
                    <div class="col">
                        <div class="book-card">
                            <a href="index.php?route=comic/detail&slug=<?= $comic['slug'] ?>" class="book-thumb">
                                <span class="badge bg-warning text-dark badge-overlay">Tranh</span>
                                <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($comic['name']) ?>" loading="lazy">
                            </a>
                            <div class="book-body">
                                <div class="book-title">
                                    <a href="index.php?route=comic/detail&slug=<?= $comic['slug'] ?>" title="<?= htmlspecialchars($comic['name']) ?>">
                                        <?= htmlspecialchars($comic['name']) ?>
                                    </a>
                                </div>
                                <div class="book-info">
                                    <span><i class="fas fa-clock"></i> Cập nhật mới</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light text-center border border-dashed">
                <p class="mb-0 text-muted">Không tìm thấy truyện tranh nào từ API.</p>
            </div>
        <?php endif; ?>
    </div>

</div>
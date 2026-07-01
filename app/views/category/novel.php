<?php
// File: app/views/category/novel.php
// Dumb View - nhận dữ liệu từ CategoryController
// $cat_name và $novels đã được extract từ $data bởi View::render()
?>

<div class="container" style="min-height: 600px;">

    <div class="category-header">
        <h3>
            <i class="fas fa-layer-group text-warning me-2"></i>Truyện: <span class="text-primary"><?php echo htmlspecialchars($cat_name ?? ''); ?></span>
        </h3>
    </div>

    <?php if (!empty($novels)): ?>
        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
            <?php foreach ($novels as $row):
                $anh_bia = !empty($row['cover_image']) ? $row['cover_image'] : 'https://via.placeholder.com/180x260?text=No+Image';
                $link_truyen = "index.php?route=novel/detail&id=" . $row['id'];
                $label = ($row['status'] == 'completed') ? '<span class="badge bg-success badge-overlay">Full</span>' : '';
            ?>
                <div class="col">
                    <div class="book-card">
                        <a href="<?php echo $link_truyen; ?>" class="book-thumb">
                            <?php echo $label; ?>
                            <img src="<?php echo $anh_bia; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" loading="lazy">
                        </a>
                        <div class="book-body">
                            <div class="book-title">
                                <a href="<?php echo $link_truyen; ?>" title="<?php echo htmlspecialchars($row['title']); ?>">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </a>
                            </div>
                            <div class="book-info">
                                <span><i class="fas fa-user-edit"></i> <?php echo !empty($row['author']) ? htmlspecialchars($row['author']) : 'Unknown'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <div class="alert alert-warning text-center mt-4">
            <i class="fas fa-search me-2"></i>Hiện tại chưa có truyện nào thuộc thể loại <strong><?php echo htmlspecialchars($cat_name ?? ''); ?></strong>.
        </div>
    <?php endif; ?>

</div>
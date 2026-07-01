<?php require_once 'app/views/includes/header.php'; ?>

<div class="container py-4" style="min-height: 600px;">
    <div class="row">
        <!-- Sidebar Danh mục -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-list me-2"></i> Chuyên Mục
                </div>
                <div class="list-group list-group-flush">
                    <?php if(!empty($categories)): foreach($categories as $cat): ?>
                        <a href="index.php?route=forum/index&cat=<?= $cat['slug'] ?>" 
                           class="list-group-item list-group-item-action <?= (isset($current_cat) && $current_cat['id'] == $cat['id']) ? 'active' : '' ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="index.php?route=forum/create" class="btn btn-danger w-100 mt-3 shadow-sm">
                    <i class="fas fa-plus me-1"></i> Tạo Chủ Đề Mới
                </a>
            <?php else: ?>
                <div class="alert alert-warning mt-3 small">
                    Vui lòng <a href="index.php?route=home/index" class="fw-bold">đăng nhập</a> để đăng bài.
                </div>
            <?php endif; ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary">
                        <?= isset($current_cat) ? htmlspecialchars($current_cat['name']) : 'Tất cả chủ đề' ?>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Chủ đề</th>
                                    <th class="text-center" width="100">Trả lời</th>
                                    <th class="text-center" width="100">Lượt xem</th>
                                    <th class="text-end" width="150">Đăng lúc</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(isset($topics) && count($topics) > 0): ?>
                                    <?php foreach($topics as $t): ?>
                                        <tr>
                                            <td>
                                                <a href="index.php?route=forum/detail&id=<?= $t['id'] ?>" class="text-decoration-none fw-bold text-dark d-block mb-1">
                                                    <?= htmlspecialchars($t['title']) ?>
                                                </a>
                                                <small class="text-muted">
                                                    Bởi: <span class="<?= $t['role'] == 'admin' ? 'text-danger fw-bold' : ($t['role'] == 'mod' ? 'text-success fw-bold' : 'text-primary') ?>">
                                                        <?= htmlspecialchars($t['username']) ?>
                                                    </span>
                                                </small>
                                            </td>
                                            <td class="text-center"><span class="badge bg-secondary"><?= $t['reply_count'] ?></span></td>
                                            <td class="text-center text-muted small"><?= $t['views'] ?></td>
                                            <td class="text-end text-muted small">
                                                <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Chưa có chủ đề nào trong chuyên mục này.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Phân trang -->
                <?php if(isset($pagination) && isset($pagination['total_pages']) && $pagination['total_pages'] > 1): ?>
                <div class="card-footer bg-white border-top-0 pt-3">
                    <ul class="pagination justify-content-center mb-0">
                        <?php for($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?= ($i == $pagination['page']) ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?route=forum/index&cat=<?= isset($current_cat['slug']) ? $current_cat['slug'] : '' ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/includes/footer.php'; ?>

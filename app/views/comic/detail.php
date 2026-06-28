
<link rel="stylesheet" href="assets/css/detail.css">

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3 text-center mb-4">
            <div class="card shadow-sm border-0">
                <img src="<?= $img_domain . $comic['thumb_url'] ?>"
                    class="card-img-top rounded card-img-detail"
                    alt="<?= htmlspecialchars($comic['name']) ?>"
                    onerror="this.src='https://via.placeholder.com/300x400?text=No+Image'">
            </div>
        </div>

        <div class="col-md-9">
            <h2 class="fw-bold text-primary mb-3"><?= htmlspecialchars($comic['name']) ?></h2>

            <div class="mb-3">
                <span class="badge bg-secondary p-2 me-1"><i class="fas fa-user-edit"></i> <?= implode(', ', $comic['author']) ?></span>
                <span class="badge bg-<?= $comic['status'] == 'Completed' ? 'success' : 'info' ?> p-2 me-1"><?= $comic['status'] ?></span>
                <span class="badge bg-success text-white p-2 me-1"><i class="fas fa-eye"></i> <?= number_format($current_view) ?> view</span>
                <span class="badge bg-danger p-2"><i class="fas fa-heart"></i> <span id="follow-count"><?= $total_followers ?></span> theo dõi</span>
            </div>

            <div class="mb-4 d-flex gap-2 flex-wrap">
                <?php if ($chap_start && $chap_latest): ?>
                    <?php
                    $start_name = urlencode($comic['name'] . ' - Chap ' . $chap_start['chapter_name']);
                    $start_api  = base64_encode($chap_start['chapter_api_data']);
                    ?>
                    <a href="index.php?route=comic/read&api=<?= $start_api ?>&name=<?= $start_name ?>&slug=<?= $slug ?>"
                        class="btn btn-primary btn-lg px-4 shadow-sm">
                        <i class="fas fa-book-open"></i> Đọc Từ Đầu (Chap <?= $chap_start['chapter_name'] ?>)
                    </a>

                    <?php
                    $latest_name = urlencode($comic['name'] . ' - Chap ' . $chap_latest['chapter_name']);
                    $latest_api  = base64_encode($chap_latest['chapter_api_data']);
                    ?>
                    <a href="index.php?route=comic/read&api=<?= $latest_api ?>&name=<?= $latest_name ?>&slug=<?= $slug ?>"
                        class="btn btn-danger btn-lg px-4 shadow-sm">
                        <i class="fas fa-bolt"></i> Mới Nhất (Chap <?= $chap_latest['chapter_name'] ?>)
                    </a>

                <?php else: ?>
                    <button class="btn btn-secondary btn-lg px-4" disabled>Chưa có chương</button>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <button id="btn-comic-fav"
                        class="btn btn-lg <?= $is_favorite ? 'btn-warning text-white' : 'btn-outline-warning text-dark' ?> shadow-sm"
                        onclick="toggleComicFavorite()">
                        <?= $is_favorite ? '<i class="fas fa-heart"></i> Đã Theo Dõi' : '<i class="far fa-heart"></i> Theo Dõi' ?>
                    </button>
                <?php else: ?>
                    <a href="index.php?route=auth/login" class="btn btn-outline-warning text-dark btn-lg shadow-sm"><i class="far fa-heart"></i> Đăng nhập để lưu</a>
                <?php endif; ?>
            </div>
            <div class="card bg-light border-0 rounded-3">
                <div class="card-body">
                    <h5 class="card-title fw-bold border-bottom pb-2 text-dark"><i class="fas fa-file-alt text-warning"></i> Nội dung</h5>
                    <div class="card-text text-secondary desc-box-scroll">
                        <?= strip_tags($comic['content']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-5 shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list-ol text-primary"></i> Danh Sách Chương (<?= count($chapters) ?>)</h5>
        </div>
        <div class="card-body chapter-list-scroll">
            <?php if (!empty($chapters)): ?>
                <div class="row g-2">
                    <?php foreach ($chapters as $chap):
                        $chap_name_display = urlencode($comic['name'] . ' - Chap ' . $chap['chapter_name']);
                    ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <a href="index.php?route=comic/read&api=<?= base64_encode($chap['chapter_api_data']) ?>&name=<?= $chap_name_display ?>&slug=<?= $slug ?>"
                                class="text-decoration-none text-dark d-block p-2 border rounded hover-chap text-truncate"
                                title="Chapter <?= $chap['chapter_name'] ?>">
                                Chapter <?= $chap['chapter_name'] ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">Dữ liệu đang cập nhật...</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="container mt-4">
        <?php
        $cmt_type = 'comic';
        $cmt_obj_id = $slug;
        if (file_exists('app/views/includes/comment_section.php')) {
            include 'app/views/includes/comment_section.php';
        }
        ?>
    </div>
</div>

<script>
    function toggleComicFavorite() {
        let btn = document.getElementById('btn-comic-fav');
        let countSpan = document.getElementById('follow-count');
        let currentCount = parseInt(countSpan.innerText.replace(/,/g, ''));

        let formData = new FormData();
        formData.append('action', 'toggle_favorite');
        formData.append('type', 'comic');
        formData.append('slug', '<?= $slug ?>');
        formData.append('name', `<?= addslashes($comic['name']) ?>`);
        formData.append('thumb', '<?= $img_domain . $comic['thumb_url'] ?>');

        fetch('api/user.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'added') {
                    btn.classList.remove('btn-outline-warning', 'text-dark');
                    btn.classList.add('btn-warning', 'text-white');
                    btn.innerHTML = '<i class="fas fa-heart"></i> Đã Theo Dõi';
                    countSpan.innerText = (currentCount + 1);
                } else if (data.status === 'removed') {
                    btn.classList.remove('btn-warning', 'text-white');
                    btn.classList.add('btn-outline-warning', 'text-dark');
                    btn.innerHTML = '<i class="far fa-heart"></i> Theo Dõi';
                    if (currentCount > 0) countSpan.innerText = (currentCount - 1);
                } else {
                    alert(data.message);
                }
            });
    }
</script>

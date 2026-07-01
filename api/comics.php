<?php
// File: api/comics.php
// Proxy server-side cho API Otruyen

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if (!isset($_GET['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing action']);
    exit;
}

$action = $_GET['action'];

function fetch_from_otruyen($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200 && $response) {
        return json_decode($response, true);
    }
    return null;
}

switch ($action) {
    case 'home':
        $data = fetch_from_otruyen('https://otruyenapi.com/v1/api/danh-sach/dang-phat-hanh');
        if ($data) {
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to fetch home comics']);
        }
        break;

    case 'categories':
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        // Caching in session to lessen requests
        if (!isset($_SESSION['comic_categories']) || empty($_SESSION['comic_categories'])) {
            $data = fetch_from_otruyen('https://otruyenapi.com/v1/api/the-loai');
            if ($data && isset($data['data']['items'])) {
                $_SESSION['comic_categories'] = $data['data']['items'];
            } else {
                $_SESSION['comic_categories'] = [];
            }
        }
        echo json_encode(['status' => 'success', 'data' => $_SESSION['comic_categories']]);
        break;

    case 'list':
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        // Load danh sách truyện đang phát hành (để đảm bảo có chương đọc được, truyện-moi có thể là truyện sắp ra mắt chưa có chương)
        $data = fetch_from_otruyen("https://otruyenapi.com/v1/api/danh-sach/dang-phat-hanh?page=$page");
        if ($data) {
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to fetch comic list']);
        }
        break;

    case 'category':
        $slug = isset($_GET['slug']) ? $_GET['slug'] : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if (!$slug) {
            echo json_encode(['status' => 'error', 'message' => 'Missing category slug']);
            exit;
        }
        $data = fetch_from_otruyen("https://otruyenapi.com/v1/api/the-loai/$slug?page=$page");
        if ($data) {
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to fetch category comics']);
        }
        break;

    case 'detail':
        $slug = isset($_GET['slug']) ? $_GET['slug'] : '';
        if (!$slug) {
            echo json_encode(['status' => 'error', 'message' => 'Missing comic slug']);
            exit;
        }
        $data = fetch_from_otruyen("https://otruyenapi.com/v1/api/truyen-tranh/$slug");
        if ($data) {
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to fetch comic detail']);
        }
        break;

    case 'chapter':
        $api_encoded = isset($_GET['api']) ? $_GET['api'] : '';
        if (!$api_encoded) {
            echo json_encode(['status' => 'error', 'message' => 'Missing API URL']);
            exit;
        }
        $api_url = base64_decode($api_encoded);
        $data = fetch_from_otruyen($api_url);
        if ($data) {
             echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Failed to fetch chapter']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>

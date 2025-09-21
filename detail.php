<?php
// PHP関数の再利用
function getSortedDirectories($dirPath) {
    if (!is_dir($dirPath)) return [];
    $items = array_values(array_filter(scandir($dirPath), fn($item) => !in_array($item, ['.', '..']) && is_dir($dirPath . $item)));
    if (empty($items)) return [];

    $isStrictOrder = true;
    foreach ($items as $item) {
        if (!preg_match('/^\d+_/', $item)) {
            $isStrictOrder = false;
            break;
        }
    }

    $results = [];
    if ($isStrictOrder) {
        sort($items, SORT_STRING);
        foreach ($items as $item) {
            $results[] = [
                'path_name' => $item,
                'display_name' => preg_replace('/^\d+_/', '', $item)
            ];
        }
    } else {
        natsort($items);
        foreach ($items as $item) {
                $results[] = [
                'path_name' => $item,
                'display_name' => $item
            ];
        }
    }
    return $results;
}

// === 設定 ===
$episodes_per_page = 20;

// === GETパラメータの取得 ===
$comicsDir = 'comics/';
// basename() を使って、パス情報を強制的に除去する
$tempPathName = isset($_GET['title']) ? trim($_GET['title']) : '';
// '..' や '/' を取り除き、ディレクトリトラバーサルを防ぐ
$seriesPathName = str_replace(['..', '/'], '', $tempPathName);
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$sortOrder = isset($_GET['sort']) && $_GET['sort'] === 'desc' ? 'desc' : 'asc'; // ★ ソート順を取得
$seriesDisplayName = preg_replace('/^\d+_/', '', $seriesPathName);
$fullSeriesDir = $comicsDir . $seriesPathName . '/';

$isValid = true;
if (empty($seriesPathName) || strpos($seriesPathName, '..') !== false || !is_dir($fullSeriesDir)) {
    $isValid = false;
}

// === データ取得 ===
$thumbnailFiles = $isValid ? glob($fullSeriesDir . '{thumbnail,cover,thumb}.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) : [];
$thumbnailPath = !empty($thumbnailFiles) ? $thumbnailFiles[0] : 'https://placehold.co/400x400/e2e8f0/334155?text=No+Image';

$detailContent = '';
if ($isValid) {
    $detailFilePath = $fullSeriesDir . 'detail.txt';
    if (file_exists($detailFilePath)) {
        $detailContent = nl2br(htmlspecialchars(file_get_contents($detailFilePath), ENT_QUOTES, 'UTF-8'));
    }
}

// === 話数リストとページ計算 ===
$allEpisodes = $isValid ? getSortedDirectories($fullSeriesDir) : [];
// ★ ソート順を適用
if ($sortOrder === 'desc') {
    $allEpisodes = array_reverse($allEpisodes);
}
$totalEpisodes = count($allEpisodes);
$totalPages = ceil($totalEpisodes / $episodes_per_page);
$episodesForCurrentPage = array_slice($allEpisodes, ($currentPage - 1) * $episodes_per_page, $episodes_per_page);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($seriesDisplayName, ENT_QUOTES, 'UTF-8'); ?> - 作品詳細</title>
    <style>
        :root {
            --theme-color: #2563eb;
            --main-bg-color: #f1f5f9;
            --card-bg-color: #ffffff;
            --text-color: #1e293b;
            --border-color: #e2e8f0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            background-color: var(--main-bg-color);
            color: var(--text-color);
        }
        a { color: var(--theme-color); text-decoration: none; }
        .container { max-width: 960px; margin: 0 auto; padding: 2rem 1rem; }

        .site-header {
            background-color: var(--card-bg-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 0.5rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 500;
        }
        .site-title a {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--text-color);
            text-decoration: none;
        }
        .hamburger-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            z-index: 1100;
            font-size: 2.5rem;
            color: var(--text-color);
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .hamburger-btn .icon-open,
        .hamburger-btn .icon-close {
            transition: opacity 0.2s ease-in-out;
            line-height: 1;
        }
        .hamburger-btn .icon-close {
            position: absolute;
            opacity: 0;
            font-size: 2.8rem;
        }
        .menu-open .hamburger-btn .icon-open {
            opacity: 0;
        }
        .menu-open .hamburger-btn .icon-close {
            opacity: 1;
        }

        .mobile-menu {
            position: fixed;
            top: 0;
            right: 0;
            width: 280px;
            height: 100%;
            background-color: var(--card-bg-color);
            box-shadow: -4px 0 15px rgba(0,0,0,0.1);
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
            z-index: 1050;
        }
        .menu-open .mobile-menu {
            transform: translateX(0);
        }
        .mobile-menu-close-btn {
            position: absolute;
            top: 0.5rem;
            right: 1rem;
            font-size: 2.5rem;
            font-weight: 300;
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            line-height: 1;
        }
        .mobile-menu-links {
            list-style: none;
            padding: 0;
            margin: 0;
            padding-top: 60px;
        }
        .mobile-menu-links a {
            display: block;
            padding: 1rem 1.5rem;
            color: var(--text-color);
            text-decoration: none;
            font-size: 1.1rem;
            border-bottom: 1px solid var(--border-color);
        }
        .mobile-menu-links a:hover {
            background-color: #f8f9fa;
        }

        .series-profile {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            background-color: var(--card-bg-color);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2.5rem;
        }
        @media (min-width: 768px) {
            .series-profile { flex-direction: row; }
        }
        .series-thumbnail {
            width: 100%;
            max-width: 250px;
            flex-shrink: 0;
            margin: 0 auto;
        }
        .series-thumbnail img {
            width: 100%;
            height: auto;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border-radius: 8px;
        }
        .series-info {
            width: 100%;
        }
        .series-info h1 {
            font-size: 1.5rem;
            margin: 0 0 1rem 0;
        }
        .series-detail-text {
            line-height: 1.7;
            word-wrap: break-word;
            margin: 0 1rem;
        }
        /* ★★★ 話数一覧セクションのスタイルを修正 ★★★ */
        .episode-section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end; /* タイトルとボタンの底を揃える */
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
        }
        .episode-section-header h2 {
            font-size: 1.5rem;
            margin: 0;
        }
        .sort-button {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            background-color: #f1f5f9;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.9rem;
            color: var(--text-color);
            transition: background-color 0.2s;
        }
        .sort-button:hover {
            background-color: #e2e8f0;
        }
        .sort-arrow {
            font-size: 0.7rem;
        }
        .episode-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
        .episode-card {
            display: flex;
            align-items: center;
            background-color: var(--card-bg-color);
            padding: 1rem;
            border-radius: 8px;
            transition: box-shadow 0.2s;
        }
        .episode-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .episode-card-thumb {
            width: 80px;
            height: 80px;
            aspect-ratio: 1/1;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        .episode-card-title {
            font-weight: bold;
            overflow: hidden;
            -webkit-line-clamp: 3;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            word-break: break-all;
        }

        /* タイトルと日付をまとめるラッパー */
        .episode-card-info {
            display: flex;
            flex-direction: column; /* 子要素（タイトルと日付）を縦に並べる */
            justify-content: center;
            min-width: 0; /* flexアイテム内でのテキストの折り返しに重要 */
            flex-grow: 1; /* 残りのスペースを埋める */
        }

        /* 公開日用のスタイル */
        .episode-card-date {
            font-size: 0.8rem;
            color: #64748b; /* 少し薄いグレー */
            margin-top: 4px; /* タイトルとの間に少しスペースを空ける */
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        .pagination a, .pagination span {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background-color: var(--card-bg-color);
        }
        .pagination a:hover {
            background-color: #f0f2f5;
        }
        .pagination .current-page {
            background-color: var(--theme-color);
            color: #fff;
            border-color: var(--theme-color);
            font-weight: bold;
        }
        .pagination .disabled {
            color: #9ca3af;
            pointer-events: none;
            background-color: #f9fafb;
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="container">
        <?php if ($isValid): ?>
            <section class="series-profile">
                <div class="series-thumbnail">
                    <img src="<?php echo htmlspecialchars($thumbnailPath, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($seriesDisplayName, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="series-info">
                    <h1><?php echo htmlspecialchars($seriesDisplayName, ENT_QUOTES, 'UTF-8'); ?></h1>
                    <div class="series-detail-text">
                        <?php echo $detailContent ? $detailContent : 'この作品の詳細情報はありません。'; ?>
                    </div>
                </div>
            </section>

            <section class="episode-section">
                <!-- ★★★ ヘッダーとソートボタンを追加 ★★★ -->
                <div class="episode-section-header">
                    <h2>話数一覧</h2>
                    <?php
                        $newSortOrder = $sortOrder === 'asc' ? 'desc' : 'asc';
                        $sortLink = "?title=" . urlencode($seriesPathName) . "&sort=" . $newSortOrder;
                        $sortText = $sortOrder === 'asc' ? '昇順' : '降順';
                    ?>
                    <a href="<?php echo $sortLink; ?>" class="sort-button">
                        <?php echo $sortText; ?>
                        <span class="sort-arrow"><?php echo $sortOrder === 'asc' ? '▲' : '▼'; ?></span>
                    </a>
                </div>

                <?php if (!empty($episodesForCurrentPage)): ?>
                    <div class="episode-grid">
                        <?php foreach($episodesForCurrentPage as $episode):
                            $fullEpisodeDir = $fullSeriesDir . $episode['path_name'] . '/';
                            $episodeThumbnailFiles = glob($fullEpisodeDir . '{thumbnail,cover,thumb}.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                            $episodeThumbnailPath = !empty($episodeThumbnailFiles) ? $episodeThumbnailFiles[0] : 'https://placehold.co/100x100/e2e8f0/334155?text=No+Image';
                            $episodeDate = date('Y/m/d', filemtime($fullEpisodeDir));
                            $link = 'viewer.php?title=' . urlencode($seriesPathName) . '&episode=' . urlencode($episode['path_name']);
                        ?>
                            <a href="<?php echo $link; ?>" class="episode-card">
                                <img src="<?php echo htmlspecialchars($episodeThumbnailPath, ENT_QUOTES, 'UTF-8'); ?>" class="episode-card-thumb">
                                <div class="episode-card-info">
                                    <span class="episode-card-title"><?php echo htmlspecialchars($episode['display_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="episode-card-date"><?php echo $episodeDate; ?> 公開</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php
                    if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($currentPage > 1): ?>
                                <a href="?title=<?php echo urlencode($seriesPathName); ?>&sort=<?php echo $sortOrder; ?>&page=1">«</a>
                                <a href="?title=<?php echo urlencode($seriesPathName); ?>&sort=<?php echo $sortOrder; ?>&page=<?php echo $currentPage - 1; ?>">‹</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                    <span class="current-page"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?title=<?php echo urlencode($seriesPathName); ?>&sort=<?php echo $sortOrder; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                             <?php if ($currentPage < $totalPages): ?>
                                <a href="?title=<?php echo urlencode($seriesPathName); ?>&sort=<?php echo $sortOrder; ?>&page=<?php echo $currentPage + 1; ?>">›</a>
                                <a href="?title=<?php echo urlencode($seriesPathName); ?>&sort=<?php echo $sortOrder; ?>&page=<?php echo $totalPages; ?>">»</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <p>この作品に利用可能な話がありません。</p>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <p>指定された作品が見つかりませんでした。</p>
        <?php endif; ?>
        <p style="text-align: center; margin-top: 3rem;"><a href="index.php">トップページに戻る</a></p>
    </main>

</body>
</html>

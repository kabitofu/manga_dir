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

// === GETパラメータの取得 ===
$comicsDir = 'comics/';
$seriesPathName = isset($_GET['title']) ? trim($_GET['title']) : '';
$currentEpisode = isset($_GET['episode']) ? trim($_GET['episode']) : '';
$seriesDisplayName = preg_replace('/^\d+_/', '', $seriesPathName);
$fullSeriesDir = $comicsDir . $seriesPathName . '/';

$isValid = true;
if (empty($seriesPathName) || strpos($seriesPathName, '..') !== false || !is_dir($fullSeriesDir) || empty($currentEpisode)) {
    $isValid = false;
}


// === viewer.php固有の処理 ===
$imageDir = $comicsDir . $seriesPathName . '/' . $currentEpisode . '/';
$imageFiles = [];
if ($isValid && is_dir($imageDir)) {
    $allFiles = glob($imageDir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    if ($allFiles !== false) {
        $imageFiles = array_filter($allFiles, function($file) {
            $filename = basename($file);
            // 'thumbnail', 'cover', 'thumb'で始まらないファイルのみを対象とする
            return !preg_match('/^(thumbnail|cover|thumb)\./i', $filename);
        });
        if(!empty($imageFiles)) {
            natsort($imageFiles);
        }
    }
}

// 前後の話のリンク生成
$prevEpisodeLink = null;
$nextEpisodeLink = null;
if ($isValid) {
    $episodeListForNav = getSortedDirectories($fullSeriesDir);

    $currentIndex = null;
    foreach($episodeListForNav as $index => $episode) {
        if ($episode['path_name'] === $currentEpisode) {
            $currentIndex = $index;
            break;
        }
    }

    if ($currentIndex !== null) {
        if (isset($episodeListForNav[$currentIndex - 1])) {
            $prevEpisode = $episodeListForNav[$currentIndex - 1]['path_name'];
            $prevEpisodeLink = 'viewer.php?title=' . urlencode($seriesPathName) . '&episode=' . urlencode($prevEpisode);
        }
        if (isset($episodeListForNav[$currentIndex + 1])) {
            $nextEpisode = $episodeListForNav[$currentIndex + 1]['path_name'];
            $nextEpisodeLink = 'viewer.php?title=' . urlencode($seriesPathName) . '&episode=' . urlencode($nextEpisode);
        }
    }
}


?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars("{$seriesDisplayName} - {$currentEpisode}", ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        :root { --theme-color: #2563eb; }
        body { margin: 0; background-color: #111827; text-align: center; font-family: sans-serif; }

        .navigation-bar {
            background-color: rgba(31, 41, 55, 0.9);
            backdrop-filter: blur(5px);
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
            flex-wrap: wrap;
        }
        .navigation-bar.top {
            position: sticky;
            top: 0;
            z-index: 10;
            width: 100%;
            box-sizing: border-box;
            transition: top 0.3s ease-in-out;
        }
        .navigation-bar.top.nav-hidden {
            top: -100px;
        }

        .navigation-bar.bottom { padding: 1rem; }

        .nav-button {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: var(--theme-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.2s;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            margin: 4px;
        }
        .nav-button:hover { background-color: #1d4ed8; }
        .nav-button.disabled { background-color: #4b5563; color: #9ca3af; pointer-events: none; }
        .nav-button.secondary { background-color: #4b5563; }
        .nav-button.secondary:hover { background-color: #6b7280; }

        .nav-section { display: flex; align-items: center; justify-content: center; flex-grow: 1; min-width: 150px; }
        .nav-section.left { justify-content: flex-start; }
        .nav-section.right { justify-content: flex-end; }


        .manga-container { display: flex; flex-direction: column; align-items: center; padding: 1rem 0;}

        img.lazy {
            background-color: #374151;
            min-height: 300px;
            transition: opacity 0.5s;
            opacity: 0;
            width: 100%;
            height: auto;
        }
        img.lazy.loaded {
            min-height: auto;
            opacity: 1;
        }

        .manga-image-wrapper {
            width: 100%;
            max-width: 1024px;
            margin-bottom: 5px;
        }
        .shrink-mode .manga-image-wrapper {
            max-width: 800px;
        }

        .message { color: #fff; padding: 2rem; }

        @media (max-width: 520px) {
            #toggle-shrink-mode {
                display: none;
            }
            .nav-section {
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <nav class="navigation-bar top" id="top-nav">
        <div class="nav-section left">
            <?php if ($prevEpisodeLink): ?>
                <a href="<?php echo htmlspecialchars($prevEpisodeLink, ENT_QUOTES, 'UTF-8'); ?>" class="nav-button">前の話へ</a>
            <?php else: ?>
                <span class="nav-button disabled">前の話へ</span>
            <?php endif; ?>
        </div>
        <div class="nav-section">
            <a href="detail.php?title=<?php echo urlencode($seriesPathName); ?>" class="nav-button">作品TOPへ</a>
            <button id="toggle-shrink-mode" class="nav-button secondary">縮小表示</button>
        </div>
        <div class="nav-section right">
            <?php if ($nextEpisodeLink): ?>
                <a href="<?php echo htmlspecialchars($nextEpisodeLink, ENT_QUOTES, 'UTF-8'); ?>" class="nav-button">次の話へ</a>
            <?php else: ?>
                <span class="nav-button disabled">次の話へ</span>
            <?php endif; ?>
        </div>
    </nav>

    <div class="manga-container" id="manga-container">
        <?php
        if (!$isValid) {
            echo "<p class='message'>データが見つかりません。</p>";
        } else if (empty($imageFiles)) {
            echo "<p class='message'>画像ファイルがありません。</p>";
        } else {
            $placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';
            foreach ($imageFiles as $image) {
                $encodedImageSrc = htmlspecialchars($image, ENT_QUOTES, 'UTF-8');
                echo "<div class='manga-image-wrapper'>";
                echo "<img class='lazy' data-src='{$encodedImageSrc}' src='{$placeholder}' alt='漫画ページ'>";
                echo "</div>";
            }
        }
        ?>
    </div>

    <nav class="navigation-bar bottom">
         <div class="nav-section left">
            <?php if ($prevEpisodeLink): ?>
                <a href="<?php echo htmlspecialchars($prevEpisodeLink, ENT_QUOTES, 'UTF-8'); ?>" class="nav-button">前の話へ</a>
            <?php else: ?>
                <span class="nav-button disabled">前の話へ</span>
            <?php endif; ?>
        </div>
        <div class="nav-section">
             <a href="detail.php?title=<?php echo urlencode($seriesPathName); ?>" class="nav-button">作品TOPへ</a>
        </div>
        <div class="nav-section right">
            <?php if ($nextEpisodeLink): ?>
                <a href="<?php echo htmlspecialchars($nextEpisodeLink, ENT_QUOTES, 'UTF-8'); ?>" class="nav-button">次の話へ</a>
            <?php else: ?>
                <span class="nav-button disabled">次の話へ</span>
            <?php endif; ?>
        </div>
    </nav>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const lazyImages = [].slice.call(document.querySelectorAll("img.lazy"));
        if ("IntersectionObserver" in window) {
            let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        let lazyImage = entry.target;
                        lazyImage.src = lazyImage.dataset.src;
                        lazyImage.classList.add("loaded");
                        lazyImageObserver.unobserve(lazyImage);
                    }
                });
            }, { rootMargin: "200px" });
            lazyImages.forEach(function(lazyImage) {
                lazyImageObserver.observe(lazyImage);
            });
        } else {
            lazyImages.forEach(function(lazyImage) {
                lazyImage.src = lazyImage.dataset.src;
                lazyImage.classList.add("loaded");
            });
        }

        const toggleBtn = document.getElementById('toggle-shrink-mode');
        const mangaContainer = document.getElementById('manga-container');

        function applyShrinkState() {
            const isShrunk = localStorage.getItem('mangaShrinkMode') === 'true';
            if (isShrunk) {
                mangaContainer.classList.add('shrink-mode');
                toggleBtn.textContent = '元のサイズ';
            } else {
                mangaContainer.classList.remove('shrink-mode');
                toggleBtn.textContent = '縮小表示';
            }
        }
        if(toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const isCurrentlyShrunk = mangaContainer.classList.contains('shrink-mode');
                localStorage.setItem('mangaShrinkMode', !isCurrentlyShrunk);
                applyShrinkState();
            });
            applyShrinkState();
        }

        const topNav = document.getElementById('top-nav');
        let lastScrollTop = 0;
        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                topNav.classList.add('nav-hidden');
            } else {
                topNav.classList.remove('nav-hidden');
            }
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        }, false);
    });
    </script>
</body>
</html>

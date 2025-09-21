<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>漫画一覧</title>
    <style>
        :root {
            --theme-color: #2563eb;
            --theme-color-hover: #1d4ed8;
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

        /* ★★★ 追加：モーダル表示中の背景スクロールを禁止するスタイル ★★★ */
        body.scroll-lock {
            overflow: hidden;
        }

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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        h1 {
            text-align: center;
            margin-bottom: 2.5rem;
            font-size: 2.5rem;
        }
        .manga-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            justify-content: center;
        }
        @media (min-width: 640px) {
            .manga-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
        }
        .manga-grid.grid-large {
            grid-template-columns: repeat(auto-fit, minmax(220px, 280px));
        }

        .manga-card {
            background-color: var(--card-bg-color);
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-align: left;
            padding: 0;
            font-family: inherit;
        }
        .manga-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .thumbnail-wrapper img {
            width: 100%;
            height: auto;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            display: block;
            background-color: var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }

        .title-wrapper {
            box-sizing: border-box;
            padding: 0.1rem 1rem;
            height: calc( (1.3rem * 1.4 * 3) + (0.1rem * 2) );
            display: flex;
            align-items: center;
        }
        .series-title {
            font-size: 0.9rem;
            font-weight: bold;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: break-all;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background-color: var(--card-bg-color);
            border-radius: 12px;
            width: 90vw;
            max-width: 400px;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            transform: scale(0.95);
            transition: transform 0.3s ease;
            overflow: hidden;
            position: relative;
        }
        .modal-overlay.active .modal-content {
            transform: scale(1);
        }
        .modal-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        .modal-title-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            flex-grow: 1;
            min-width: 0;
        }
        .modal-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin: 0;
            white-space: normal;
            word-wrap: break-word;
        }
        .detail-btn {
            font-size: 0.8rem;
            padding: 2px 8px;
            margin-top: 8px;
            border: 1px solid var(--border-color);
            background-color: #f8f9fa;
            color: var(--text-color);
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
            flex-shrink: 0;
            text-decoration: none;
        }
        .detail-btn:hover {
            background-color: #e9ecef;
        }

        .modal-close-btn {
            font-size: 2rem;
            font-weight: 300;
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            line-height: 1;
            flex-shrink: 0;
            align-self: flex-start;
        }
        .modal-body {
            flex-grow: 1;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .modal-body::-webkit-scrollbar {
            display: none;
        }
        .episode-list {
            list-style: none;
            padding: 0.5rem 0;
            margin: 0;
        }

        .episode-list a {
            display: flex;
            flex-direction: row;
            align-items: center;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            color: var(--text-color);
            transition: background-color 0.2s;
        }
        .episode-list a:hover {
            background-color: #f8f9fa;
        }
        .episode-thumbnail {
            width: 100px;
            height: 100px;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
            background-color: var(--border-color);
            flex-shrink: 0;
        }
        .episode-info {
            display: flex;
            flex-direction: column; /* 子要素（タイトルと日付）を縦に並べる */
            justify-content: center;
        }
        .episode-title-text {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.5;
            font-weight: bold;
            color: var(--theme-color);
            word-break: break-all;
        }
        .episode-date-text {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 4px;
        }

        .custom-scrollbar-track {
            position: absolute;
            right: 10px;
            width: 6px;
            background-color: #e2e8f0;
            border-radius: 3px;
            display: none;
        }
        .custom-scrollbar-thumb {
            position: absolute;
            width: 24px;
            height: 24px;
            background: #fff;
            border: 2px solid var(--theme-color);
            border-radius: 50%;
            cursor: grab;
            left: 50%;
            transform: translateX(-50%);
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            box-sizing: border-box;
        }
        .custom-scrollbar-thumb:active {
            cursor: grabbing;
        }

        .message {
            grid-column: 1 / -1;
            text-align: center;
            padding: 2rem;
            background-color: var(--card-bg-color);
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <div class="container">
            <h1>漫画一覧</h1>
            <?php
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

            $comicsDir = 'comics/';
            if (is_dir($comicsDir)) {
                $seriesList = getSortedDirectories($comicsDir);

                $seriesCount = count($seriesList);
                $gridClass = 'manga-grid';
                if ($seriesCount > 0 && $seriesCount <= 3) {
                    $gridClass .= ' grid-large';
                }

                echo "<div class='" . $gridClass . "'>";

                if (empty($seriesList)) {
                    echo "<p class='message'>利用可能な漫画がありません。</p>";
                } else {
                    foreach ($seriesList as $series) {
                        $seriesPathName = $series['path_name'];
                        $seriesDisplayName = $series['display_name'];
                        $fullSeriesDir = $comicsDir . $seriesPathName . '/';

                        $thumbnailFiles = glob($fullSeriesDir . '{thumbnail,cover,thumb}.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                        $thumbnailPath = !empty($thumbnailFiles) ? $thumbnailFiles[0] : 'https://placehold.co/400x400/e2e8f0/334155?text=No+Image';

                        $episodeList = getSortedDirectories($fullSeriesDir);
                        $episodesForJson = [];
                        foreach($episodeList as $episode) {
                            $fullEpisodeDir = $fullSeriesDir . $episode['path_name'] . '/';
                            $episodeThumbnailFiles = glob($fullEpisodeDir . '{thumbnail,cover,thumb}.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                            $episodeThumbnailPath = !empty($episodeThumbnailFiles)
                                ? $episodeThumbnailFiles[0]
                                : 'https://placehold.co/100x100/e2e8f0/334155?text=No+Image';

                            $episodesForJson[] = [
                                'display' => $episode['display_name'],
                                'original' => $episode['path_name'],
                                'thumbnail' => $episodeThumbnailPath,
                                'date' => date('Y/m/d', filemtime($fullEpisodeDir))
                            ];
                        }

                        $encodedDisplayName = htmlspecialchars($seriesDisplayName, ENT_QUOTES, 'UTF-8');
                        $encodedPathName = htmlspecialchars($seriesPathName, ENT_QUOTES, 'UTF-8');
                        $encodedThumbnailPath = htmlspecialchars($thumbnailPath, ENT_QUOTES, 'UTF-8');
                        $encodedEpisodesJson = htmlspecialchars(json_encode($episodesForJson), ENT_QUOTES, 'UTF-8');

                        echo <<<HTML
                        <button class="manga-card" data-display-title="{$encodedDisplayName}" data-path-name="{$encodedPathName}" data-episodes='{$encodedEpisodesJson}'>
                            <div class="thumbnail-wrapper"><img src="{$encodedThumbnailPath}" alt="{$encodedDisplayName} サムネイル" loading="lazy"></div>
                            <div class="title-wrapper">
                                <h2 class="series-title">{$encodedDisplayName}</h2>
                            </div>
                        </button>
                        HTML;
                    }
                }
                echo "</div>";
            } else {
                echo "<p class='message'>漫画ディレクトリ ('{$comicsDir}') が見つかりません。</p>";
            }
            ?>
        </div>
    </main>

    <div id="episode-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-container">
                    <h3 id="episode-modal-title" class="modal-title"></h3>
                    <div id="detail-button-placeholder"></div>
                </div>
                <button id="episode-modal-close-btn" class="modal-close-btn">&times;</button>
            </div>
            <div id="modal-body" class="modal-body">
                <ul id="modal-episode-list" class="episode-list"></ul>
            </div>
            <div class="custom-scrollbar-track" id="custom-scrollbar-track">
                <div class="custom-scrollbar-thumb" id="custom-scrollbar-thumb"></div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('episode-modal');

        const modalTitle = document.getElementById('episode-modal-title');
        const modalHeader = modal.querySelector('.modal-header');
        const modalBody = document.getElementById('modal-body');
        const modalEpisodeList = document.getElementById('modal-episode-list');
        const closeModalBtn = document.getElementById('episode-modal-close-btn');
        const scrollTrack = document.getElementById('custom-scrollbar-track');
        const scrollThumb = document.getElementById('custom-scrollbar-thumb');

        const detailBtnPlaceholder = document.getElementById('detail-button-placeholder');

        document.querySelectorAll('.manga-card').forEach(card => {
            card.addEventListener('click', () => {
                const displayTitle = card.dataset.displayTitle;
                const pathName = card.dataset.pathName;
                const episodes = JSON.parse(card.dataset.episodes);

                modalTitle.textContent = displayTitle;
                modalEpisodeList.innerHTML = '';
                detailBtnPlaceholder.innerHTML = '';

                const detailLink = document.createElement('a');
                detailLink.textContent = '作品詳細';
                detailLink.className = 'detail-btn';
                detailLink.href = `detail.php?title=${encodeURIComponent(pathName)}`;
                detailBtnPlaceholder.appendChild(detailLink);

                if (episodes.length > 0) {
                    episodes.forEach(ep => {
                        const encodedPathName = encodeURIComponent(pathName);
                        const encodedEpisodeOriginal = encodeURIComponent(ep.original);
                        const link = `viewer.php?title=${encodedPathName}&episode=${encodedEpisodeOriginal}`;

                        const li = document.createElement('li');
                        li.innerHTML = `
                            <a href="${link}">
                                <img src="${ep.thumbnail}" alt="" class="episode-thumbnail">
                                <div class="episode-info">
                                    <span class="episode-title-text">${ep.display}</span>
                                    <span class="episode-date-text">${ep.date} 公開</span>
                                </div>
                            </a>`;
                        modalEpisodeList.appendChild(li);
                    });
                } else {
                    const li = document.createElement('li');
                    li.innerHTML = `<a style="color: #666; pointer-events: none; justify-content: center;">利用可能な話がありません。</a>`;
                    modalEpisodeList.appendChild(li);
                }

                // ★★★ 変更：モーダル表示時に背景のスクロールを禁止 ★★★
                document.body.classList.add('scroll-lock');
                modal.classList.add('active');
                modalBody.scrollTop = 0;
                updateCustomScrollbar();
            });
        });

        function closeModal() {
            modal.classList.remove('active');
            // ★★★ 変更：モーダルを閉じる際に背景のスクロール禁止を解除 ★★★
            document.body.classList.remove('scroll-lock');
        }
        closeModalBtn.addEventListener('click', closeModal);
        modal.addEventListener('mousedown', (event) => {
             if (event.target === modal) {
                closeModal();
             }
        });
        document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeModal(); });

        function updateCustomScrollbar() {
             requestAnimationFrame(() => {
                const scrollHeight = modalBody.scrollHeight;
                const clientHeight = modalBody.clientHeight;

                if (scrollHeight > clientHeight) {
                    scrollTrack.style.display = 'block';
                    const headerHeight = modalHeader.offsetHeight;
                    scrollTrack.style.top = `${headerHeight}px`;
                    scrollTrack.style.height = `calc(100% - ${headerHeight}px)`;
                    positionThumb();
                } else {
                    scrollTrack.style.display = 'none';
                }
             });
        }

        function positionThumb() {
             const scrollHeight = modalBody.scrollHeight;
             const clientHeight = modalBody.clientHeight;
             if(scrollHeight <= clientHeight) return;

             const scrollTop = modalBody.scrollTop;
             const trackHeight = scrollTrack.clientHeight;
             const thumbHeight = scrollThumb.offsetHeight;

             const maxScrollTop = scrollHeight - clientHeight;
             const maxThumbY = trackHeight - thumbHeight;

             const thumbY = maxScrollTop > 0 ? (scrollTop / maxScrollTop) * maxThumbY : 0;
             scrollThumb.style.top = `${thumbY}px`;
        }

        modalBody.addEventListener('scroll', positionThumb);
        window.addEventListener('resize', updateCustomScrollbar);

        let isDragging = false;
        let startY;
        let startScrollTop;

        const startDrag = (e) => {
            e.preventDefault();
            e.stopPropagation();
            isDragging = true;
            startY = (e.touches ? e.touches[0].clientY : e.clientY);
            startScrollTop = modalBody.scrollTop;
            document.body.style.userSelect = 'none';
            document.body.style.cursor = 'grabbing';
            scrollThumb.style.cursor = 'grabbing';

            document.addEventListener('mousemove', onDrag);
            document.addEventListener('mouseup', endDrag);
            document.addEventListener('touchmove', onDrag, { passive: false });
            document.addEventListener('touchend', endDrag);
        }

        const onDrag = (e) => {
            if (!isDragging) return;
            e.preventDefault();
            const currentY = (e.touches ? e.touches[0].clientY : e.clientY);
            const deltaY = currentY - startY;

            const scrollHeight = modalBody.scrollHeight;
            const clientHeight = modalBody.clientHeight;
            if(scrollHeight <= clientHeight) return;

            const trackHeight = scrollTrack.clientHeight;
            const thumbHeight = scrollThumb.offsetHeight;
            const maxScrollTop = scrollHeight - clientHeight;
            const maxThumbY = trackHeight - thumbHeight;

            const scrollRatio = maxThumbY > 0 ? maxScrollTop / maxThumbY : 0;
            const newScrollTop = startScrollTop + deltaY * scrollRatio;

            modalBody.scrollTop = newScrollTop;
        }

        const endDrag = () => {
            if (isDragging) {
                isDragging = false;
                document.body.style.userSelect = '';
                document.body.style.cursor = '';
                scrollThumb.style.cursor = 'grab';

                document.removeEventListener('mousemove', onDrag);
                document.removeEventListener('mouseup', endDrag);
                document.removeEventListener('touchmove', onDrag);
                document.removeEventListener('touchend', endDrag);
            }
        }

        scrollThumb.addEventListener('mousedown', startDrag);
        scrollThumb.addEventListener('touchstart', startDrag, { passive: false });

    });
    </script>
</body>
</html>

<header class="site-header">
    <div class="site-title">
        <a href="index.php">漫画サイト</a>
    </div>
    <button class="hamburger-btn" id="hamburger-btn" aria-label="メニューを開閉">
        <span class="icon-open">≡</span>
        <span class="icon-close">&times;</span>
    </button>
</header>

<nav class="mobile-menu" id="mobile-menu">
    <button class="mobile-menu-close-btn" id="mobile-menu-close-btn" aria-label="メニューを閉じる">&times;</button>
    <ul class="mobile-menu-links">
        <!-- メニュー項目をここに追加・編集してください -->
        <li><a href="#">項目1</a></li>
        <li><a href="#">項目2</a></li>
    </ul>
</nav>

<script>
// DOMContentLoadedは各ページでリッスンされるので、このスクリプトは即時実行で問題ありません。
// ただし、他のスクリプトとの干渉を避けるため、無名関数でスコープを限定します。
(function() {
    // このスクリプトはヘッダー専用です
    if (document.getElementById('hamburger-btn')) {
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const mobileMenuCloseBtn = document.getElementById('mobile-menu-close-btn');

        function toggleMenu() { document.body.classList.toggle('menu-open'); }
        function closeMenu() { document.body.classList.remove('menu-open'); }

        hamburgerBtn.addEventListener('click', toggleMenu);
        mobileMenuCloseBtn.addEventListener('click', closeMenu);

        // メニュー外のクリックでも閉じるようにする
        document.body.addEventListener('click', (event) => {
            // メニューが開いていて、クリックされた場所がメニューの内側でもボタンでもない場合
            if (document.body.classList.contains('menu-open') && !event.target.closest('.mobile-menu') && !event.target.closest('.hamburger-btn')) {
                closeMenu();
            }
        });
    }
})();
</script>

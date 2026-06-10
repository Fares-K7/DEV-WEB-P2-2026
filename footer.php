<footer class="footer">
    <div style="max-width:1200px; margin:0 auto; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; padding:0 20px;">
        <p>&copy; 2026 CY-FAT — Créé pour le projet Creative-Yumland (Filière préING2).</p>
        
        <button id="theme-toggle" class="btn" style="padding:6px 12px; font-size:0.9rem; cursor:pointer; background:var(--color-accent); color:#fff; border:none; border-radius:20px;">
            🌓 Mode Sombre
        </button>
    </div>
</footer>

<script>
    const themeToggleBtn = document.getElementById('theme-toggle');
    
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('theme-sombre');
        themeToggleBtn.textContent = '☀️ Mode Clair';
    }

    themeToggleBtn.addEventListener('click', () => {
        document.body.classList.toggle('theme-sombre');
        
        if (document.body.classList.contains('theme-sombre')) {
            localStorage.setItem('theme', 'dark');
            themeToggleBtn.textContent = '☀️ Mode Clair';
        } else {
            localStorage.setItem('theme', 'light');
            themeToggleBtn.textContent = '🌓 Mode Sombre';
        }
    });
</script>

</body>
</html>

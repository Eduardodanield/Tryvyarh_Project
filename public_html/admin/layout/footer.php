    </main><!-- /.adm-content -->
  </div><!-- /.adm-main -->
</div><!-- /.adm-wrapper -->

<script>
// Sidebar mobile
function toggleSidebar() {
    document.getElementById('adm-sidebar').classList.toggle('aberta');
}

// Mostrar botão hamburguer em telas pequenas
if (window.innerWidth <= 900) {
    document.getElementById('adm-menu-btn').style.display = 'block';
}

window.addEventListener('resize', function() {
    const btn = document.getElementById('adm-menu-btn');
    if (btn) btn.style.display = window.innerWidth <= 900 ? 'block' : 'none';
});

// Confirmar exclusão
document.querySelectorAll('[data-confirm]').forEach(function(el) {
    el.addEventListener('click', function(e) {
        if (!confirm(this.dataset.confirm || 'Tem certeza?')) e.preventDefault();
    });
});
</script>
</body>
</html>

</div> <!-- fecha content-wrap -->

<!-- Rodapé -->
<footer class="bg-dark text-white py-3 mt-auto">
    <div class="container text-center">
        <p class="mb-0">Sistema Dashboard Vendas &copy; <?= date('Y') ?></p>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Ativa tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
</body>
</html>

<?php
$title = 'Pagina non trovata';
$content = '
<div class="text-center">
    <div class="error-page">
        <div class="error-number">
            <i class="fas fa-question-circle text-muted" style="font-size: 5rem;"></i>
        </div>
        <h1 class="display-1 fw-bold text-muted">404</h1>
        <h2 class="mb-4">Pagina non trovata</h2>
        <p class="lead mb-4">
            La pagina che stai cercando non esiste o è stata spostata.
        </p>
        <a href="/" class="btn btn-primary btn-lg">
            <i class="fas fa-home me-2"></i>
            Torna alla Home
        </a>
    </div>
</div>

<style>
.error-page {
    padding: 4rem 0;
}

.error-number {
    margin-bottom: 2rem;
}
</style>';

include '../resources/views/layouts/app.php';
?>
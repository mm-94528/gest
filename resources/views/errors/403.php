// resources/views/errors/403.php - Pagina errore 403
<?php
$title = 'Accesso negato';
$content = '
<div class="text-center">
    <div class="error-page">
        <div class="error-number">
            <i class="fas fa-ban text-danger" style="font-size: 5rem;"></i>
        </div>
        <h1 class="display-1 fw-bold text-danger">403</h1>
        <h2 class="mb-4">Accesso negato</h2>
        <p class="lead mb-4">
            Non hai i permessi necessari per accedere a questa pagina.
        </p>
        <a href="/dashboard" class="btn btn-primary btn-lg">
            <i class="fas fa-tachometer-alt me-2"></i>
            Vai alla Dashboard
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
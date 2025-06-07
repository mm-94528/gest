// resources/views/errors/500.php - Pagina errore 500
<?php
$title = 'Errore del server';
$content = '
<div class="text-center">
    <div class="error-page">
        <div class="error-number">
            <i class="fas fa-exclamation-triangle text-warning" style="font-size: 5rem;"></i>
        </div>
        <h1 class="display-1 fw-bold text-warning">500</h1>
        <h2 class="mb-4">Errore del server</h2>
        <p class="lead mb-4">
            Si è verificato un errore interno del server. Riprova più tardi.
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

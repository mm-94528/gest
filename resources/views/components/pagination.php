// resources/views/components/pagination.php - Template paginazione
<?php if (isset($pagination) && $pagination['last_page'] > 1): ?>
<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="pagination-info">
        <small class="text-muted">
            Visualizzazione da <?= $pagination['from'] ?> a <?= $pagination['to'] ?> 
            di <?= $pagination['total'] ?> risultati
        </small>
    </div>
    
    <nav aria-label="Paginazione">
        <ul class="pagination mb-0">
            <?php if ($pagination['current_page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            
            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<?php endif; ?>
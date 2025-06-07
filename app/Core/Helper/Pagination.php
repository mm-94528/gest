// app/Core/Helper/Pagination.php - Classe per paginazione
<?php
class Pagination {
    private $currentPage;
    private $totalItems;
    private $itemsPerPage;
    private $totalPages;
    private $baseUrl;
    
    public function __construct($currentPage, $totalItems, $itemsPerPage = 15, $baseUrl = '') {
        $this->currentPage = max(1, (int)$currentPage);
        $this->totalItems = (int)$totalItems;
        $this->itemsPerPage = (int)$itemsPerPage;
        $this->totalPages = (int)ceil($this->totalItems / $this->itemsPerPage);
        $this->baseUrl = $baseUrl;
    }
    
    public function getOffset() {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }
    
    public function getLimit() {
        return $this->itemsPerPage;
    }
    
    public function render() {
        if ($this->totalPages <= 1) {
            return '';
        }
        
        $html = '<nav aria-label="Paginazione">';
        $html .= '<ul class="pagination justify-content-center">';
        
        // Precedente
        if ($this->currentPage > 1) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->getPageUrl($this->currentPage - 1) . '">';
            $html .= '<i class="fas fa-chevron-left"></i> Precedente';
            $html .= '</a></li>';
        }
        
        // Numeri pagina
        $start = max(1, $this->currentPage - 2);
        $end = min($this->totalPages, $this->currentPage + 2);
        
        if ($start > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->getPageUrl(1) . '">1</a></li>';
            if ($start > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for ($i = $start; $i <= $end; $i++) {
            $active = ($i === $this->currentPage) ? ' active' : '';
            $html .= '<li class="page-item' . $active . '">';
            $html .= '<a class="page-link" href="' . $this->getPageUrl($i) . '">' . $i . '</a>';
            $html .= '</li>';
        }
        
        if ($end < $this->totalPages) {
            if ($end < $this->totalPages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $html .= '<li class="page-item"><a class="page-link" href="' . $this->getPageUrl($this->totalPages) . '">' . $this->totalPages . '</a></li>';
        }
        
        // Successiva
        if ($this->currentPage < $this->totalPages) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->getPageUrl($this->currentPage + 1) . '">';
            $html .= 'Successiva <i class="fas fa-chevron-right"></i>';
            $html .= '</a></li>';
        }
        
        $html .= '</ul></nav>';
        
        return $html;
    }
    
    private function getPageUrl($page) {
        $query = $_GET;
        $query['page'] = $page;
        
        return $this->baseUrl . '?' . http_build_query($query);
    }
    
    public function getInfo() {
        $from = ($this->currentPage - 1) * $this->itemsPerPage + 1;
        $to = min($this->currentPage * $this->itemsPerPage, $this->totalItems);
        
        return "Visualizzazione da {$from} a {$to} di {$this->totalItems} risultati";
    }
}

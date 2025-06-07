<?php
// Modules/Clienti/Controllers/ClientiController.php
class ClientiController extends BaseController {
    private $clienteModel;
    
    public function __construct() {
        parent::__construct();
        $this->clienteModel = new Cliente();
    }
    
    public function index() {
        $clienti = $this->clienteModel->paginate(20);
        return $this->view('clienti.index', compact('clienti'));
    }
}
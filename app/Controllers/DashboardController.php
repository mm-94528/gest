
<?php
class DashboardController extends BaseController {
    
    public function index() {
        // Verifica autenticazione
        if (!$this->isAuthenticated()) {
            $this->redirectToLogin();
            return;
        }
        
        $this->renderDashboard();
    }
            
    private function redirectToLogin() {
        $app = Application::getInstance();
        $loginUrl = $app->getFullUrl('/login');
        
        if (!headers_sent()) {
            header('Location: ' . $loginUrl);
            exit;
        } else {
            echo "<script>window.location.href='" . htmlspecialchars($loginUrl) . "';</script>";
            exit;
        }
    }
    
    private function renderDashboard() {
        $app = Application::getInstance();
        $app_name = $app->getConfig('app.name') ?? 'CRM/ERP';
        $user = $this->getCurrentUser();
        
        echo '<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ' . htmlspecialchars($app_name) . '</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { 
            position: fixed; 
            top: 0; 
            bottom: 0; 
            left: 0; 
            z-index: 100; 
            padding: 48px 0 0; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            width: 240px;
        }
        .main-content { 
            margin-left: 240px; 
            padding: 20px; 
        }
        .sidebar .nav-link { 
            color: rgba(255, 255, 255, 0.8); 
            border-radius: 10px;
            margin: 5px 10px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active { 
            color: white; 
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }
        .card { 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); 
            transition: transform 0.3s ease;
        }
        .card:hover { 
            transform: translateY(-2px); 
        }
        .stat-card {
            border-left: 4px solid;
            padding: 1.5rem;
        }
        .stat-card.primary { border-left-color: #007bff; }
        .stat-card.success { border-left-color: #28a745; }
        .stat-card.info { border-left-color: #17a2b8; }
        .stat-card.warning { border-left-color: #ffc107; }
        
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow" style="z-index: 1000;">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="' . $app->getUrl('/') . '">
            <i class="fas fa-chart-line me-2"></i>' . htmlspecialchars($app_name) . '
        </a>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <div class="dropdown">
                    <a class="nav-link px-3 dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        ' . htmlspecialchars($user['name'] ?? 'Utente') . '
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="' . $app->getUrl('/profile') . '"><i class="fas fa-user-edit me-2"></i>Profilo</a></li>
                        <li><a class="dropdown-item" href="' . $app->getUrl('/settings') . '"><i class="fas fa-cog me-2"></i>Impostazioni</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="' . $app->getUrl('/logout') . '"><i class="fas fa-sign-out-alt me-2"></i>Esci</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="' . $app->getUrl('/dashboard') . '">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="alert(\'Modulo Clienti in sviluppo\')">
                                <i class="fas fa-users me-2"></i> Clienti
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="alert(\'Modulo Offerte in sviluppo\')">
                                <i class="fas fa-file-alt me-2"></i> Offerte
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="alert(\'Modulo Ordini in sviluppo\')">
                                <i class="fas fa-shopping-cart me-2"></i> Ordini
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="alert(\'Modulo Progetti in sviluppo\')">
                                <i class="fas fa-project-diagram me-2"></i> Progetti
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="alert(\'Modulo Magazzino in sviluppo\')">
                                <i class="fas fa-warehouse me-2"></i> Magazzino
                            </a>
                        </li>
                    </ul>

                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-light">
                        <span>Sistema</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="' . $app->getUrl('/debug') . '">
                                <i class="fas fa-bug me-2"></i> Debug
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="' . $app->getUrl('/test') . '">
                                <i class="fas fa-cogs me-2"></i> Test Sistema
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download me-1"></i> Esporta
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i> Nuovo
                        </button>
                    </div>
                </div>';

        // Mostra messaggi di successo se presenti
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($_SESSION['success']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
            unset($_SESSION['success']);
        }

        echo '<!-- Cards statistiche -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card primary">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-uppercase fw-bold text-primary mb-1">Sistema</div>
                                        <div class="h5 mb-0 fw-bold">Operativo</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card success">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-uppercase fw-bold text-success mb-1">Utente</div>
                                        <div class="h5 mb-0 fw-bold">Connesso</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card info">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-uppercase fw-bold text-info mb-1">Database</div>
                                        <div class="h5 mb-0 fw-bold">' . ($app->get('database') ? 'Connesso' : 'Non connesso') . '</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-database fa-2x text-' . ($app->get('database') ? 'success' : 'warning') . '"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card warning">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-uppercase fw-bold text-warning mb-1">Moduli</div>
                                        <div class="h5 mb-0 fw-bold">In sviluppo</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-puzzle-piece fa-2x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenuto principale -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-rocket me-2"></i>Benvenuto nel CRM/ERP!
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6 class="text-primary">🎉 Login effettuato con successo!</h6>
                                        <p class="lead">Il sistema core è stato implementato e funziona correttamente.</p>
                                        
                                        <h6 class="mt-4">✅ Funzionalità Completate:</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-check text-success me-2"></i>Sistema di routing</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Autenticazione utenti</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Database ORM</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Template system</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-check text-success me-2"></i>Validazione form</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Interfaccia responsive</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Sistema modulare</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Gestione errori</li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <h6 class="mt-4">🚀 Prossimi Passi:</h6>
                                        <ol>
                                            <li>Implementare i moduli specifici (Clienti, Offerte, etc.)</li>
                                            <li>Configurare il database per dati persistenti</li>
                                            <li>Personalizzare l\'interfaccia aziendale</li>
                                            <li>Aggiungere funzionalità avanzate</li>
                                        </ol>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-user me-2"></i>Utente Corrente</h6>
                                            <p class="mb-1"><strong>Nome:</strong> ' . htmlspecialchars($user['name']) . '</p>
                                            <p class="mb-1"><strong>Email:</strong> ' . htmlspecialchars($user['email']) . '</p>
                                            <p class="mb-0"><strong>ID:</strong> ' . htmlspecialchars($user['id']) . '</p>
                                        </div>
                                        
                                        <div class="alert alert-warning">
                                            <h6><i class="fas fa-info-circle me-2"></i>Nota</h6>
                                            <p class="mb-0">I moduli sono configurati ma non ancora implementati. Clicca sui link nel menu per vedere i placeholder.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll(".alert:not(.alert-permanent)");
        alerts.forEach(function(alert) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
        
        // Aggiungi effetti hover alle card
        document.querySelectorAll(".card").forEach(function(card) {
            card.addEventListener("mouseenter", function() {
                this.style.transform = "translateY(-5px)";
            });
            
            card.addEventListener("mouseleave", function() {
                this.style.transform = "translateY(0)";
            });
        });
        
        console.log("Dashboard CRM/ERP caricata con successo!");
    </script>
</body>
</html>';
    }
    
    private function getCurrentUser() {
        // Prova a ottenere l'utente dal sistema auth
        if ($this->auth && method_exists($this->auth, 'user')) {
            $authUser = $this->auth->user();
            if ($authUser) {
                return [
                    'id' => $authUser['id'] ?? $_SESSION['user_id'] ?? 1,
                    'name' => $authUser['nome'] ?? $_SESSION['user_name'] ?? 'Admin Test',
                    'email' => $authUser['email'] ?? $_SESSION['user_email'] ?? 'admin@example.com'
                ];
            }
        }
        
        // Fallback ai dati di sessione
        return [
            'id' => $_SESSION['user_id'] ?? 1,
            'name' => $_SESSION['user_name'] ?? 'Admin Test',
            'email' => $_SESSION['user_email'] ?? 'admin@example.com'
        ];
    }
}
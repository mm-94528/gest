<?php
// app/Controllers/AuthController.php - VERSIONE CORRETTA CON REDIRECT FUNZIONANTE
class AuthController extends BaseController {
    
    public function showLogin() {
        // Se già loggato, redirect alla dashboard
        if ($this->auth && $this->auth->check()) {
            $app = Application::getInstance();
            $redirectUrl = $app->getFullUrl('/dashboard');
            
            if (!headers_sent()) {
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                echo "<script>window.location.href='" . htmlspecialchars($redirectUrl) . "';</script>";
                echo "<meta http-equiv='refresh' content='0;url=" . htmlspecialchars($redirectUrl) . "'>";
                return;
            }
        }
        
        $this->renderLoginPage();
    }
    
    private function renderLoginPage() {
        $app = Application::getInstance();
        $app_name = $app->getConfig('app.name') ?? 'CRM/ERP';
        
        echo '<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ' . htmlspecialchars($app_name) . '</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
        .auth-card { 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1); 
            padding: 2rem; 
            max-width: 400px; 
            margin: 5vh auto; 
        }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        .logo { font-size: 3rem; color: #667eea; margin-bottom: 1rem; }
        .btn-auth { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            border: none; 
            border-radius: 10px; 
            padding: 12px; 
            font-weight: 500; 
            transition: all 0.3s ease;
        }
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .form-control { 
            border-radius: 10px; 
            border: 2px solid #e9ecef; 
            transition: border-color 0.3s ease;
        }
        .form-control:focus { 
            border-color: #667eea; 
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); 
        }
        .loading {
            display: none;
        }
        .loading.show {
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo"><i class="fas fa-chart-line"></i></div>
                <h1>' . htmlspecialchars($app_name) . '</h1>
                <p>Sistema di gestione aziendale</p>
            </div>';

        // Debug info se richiesto
        if (isset($_GET['debug'])) {
            echo '<div class="alert alert-info">';
            echo '<h6>🔍 Debug Login</h6>';
            echo '<small>';
            echo 'Session ID: ' . session_id() . '<br>';
            echo 'Auth Status: ' . ($this->auth->check() ? 'Logged In' : 'Not Logged In') . '<br>';
            echo 'User ID in Session: ' . ($_SESSION['user_id'] ?? 'None') . '<br>';
            echo 'Headers Sent: ' . (headers_sent() ? 'YES' : 'NO');
            echo '</small>';
            echo '</div>';
        }

        // Mostra messaggi di errore se presenti
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show">';
            echo '<i class="fas fa-exclamation-circle me-2"></i>' . htmlspecialchars($_SESSION['error']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            echo '</div>';
            unset($_SESSION['error']);
        }

        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success alert-dismissible fade show">';
            echo '<i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($_SESSION['success']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            echo '</div>';
            unset($_SESSION['success']);
        }

        echo '<form method="POST" action="' . $app->getUrl('/login') . '" id="loginForm">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="admin@example.com" required>
                    <label for="email">Email</label>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" value="password" required>
                    <label for="password">Password</label>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-auth" id="loginBtn">
                        <span class="login-text">
                            <i class="fas fa-sign-in-alt me-2"></i> Accedi
                        </span>
                        <span class="loading">
                            <i class="fas fa-spinner fa-spin me-2"></i> Accesso in corso...
                        </span>
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <div class="alert alert-info">
                    <small><strong>Credenziali di test:</strong><br>
                    Email: admin@example.com<br>
                    Password: password</small>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <a href="' . $app->getUrl('/test') . '" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-cogs me-1"></i> Test Sistema
                </a>
                
                <a href="' . $app->getUrl('/debug') . '" class="btn btn-outline-info btn-sm ms-2">
                    <i class="fas fa-bug me-1"></i> Debug
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gestisci loading del form
        document.getElementById("loginForm").addEventListener("submit", function() {
            const btn = document.getElementById("loginBtn");
            const loginText = btn.querySelector(".login-text");
            const loading = btn.querySelector(".loading");
            
            loginText.style.display = "none";
            loading.classList.add("show");
            btn.disabled = true;
        });
        
        // Auto-redirect se c\'è un messaggio di successo
        const successAlert = document.querySelector(".alert-success");
        if (successAlert) {
            setTimeout(function() {
                window.location.href = "' . $app->getUrl('/dashboard') . '";
            }, 2000);
        }
    </script>
</body>
</html>';
    }
    
    public function login() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Validazione input
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Email e password sono obbligatori.';
            $this->redirectToLogin();
            return;
        }
        
        // Verifica credenziali
        $loginSuccessful = false;
        
        if ($this->auth) {
            // Usa il sistema di auth se disponibile
            $loginSuccessful = $this->auth->attempt($email, $password);
        } else {
            // Credenziali di test hardcoded
            if ($email === 'admin@example.com' && $password === 'password') {
                $_SESSION['user_id'] = 1;
                $_SESSION['user_name'] = 'Admin Test';
                $_SESSION['user_email'] = $email;
                $loginSuccessful = true;
            }
        }
        
        if ($loginSuccessful) {
            $_SESSION['success'] = 'Accesso effettuato con successo! Reindirizzamento in corso...';
            
            // Debug info
            error_log("Login successful for: $email");
            error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));
            
            $this->redirectToDashboard();
        } else {
            $_SESSION['error'] = 'Credenziali non valide. Riprova.';
            $this->redirectToLogin();
        }
    }
    
    private function redirectToLogin() {
        $app = Application::getInstance();
        $loginUrl = $app->getFullUrl('/login');
        
        if (!headers_sent()) {
            header('Location: ' . $loginUrl);
            exit;
        } else {
            echo "<script>window.location.href='" . htmlspecialchars($loginUrl) . "';</script>";
            echo "<meta http-equiv='refresh' content='0;url=" . htmlspecialchars($loginUrl) . "'>";
            echo "<p>Reindirizzamento... <a href='" . htmlspecialchars($loginUrl) . "'>Clicca qui se non vieni reindirizzato</a></p>";
            exit;
        }
    }
    
    private function redirectToDashboard() {
        $app = Application::getInstance();
        $dashboardUrl = $app->getFullUrl('/dashboard');
        
        if (!headers_sent()) {
            header('Location: ' . $dashboardUrl);
            exit;
        } else {
            echo "<!DOCTYPE html><html><head>";
            echo "<meta http-equiv='refresh' content='2;url=" . htmlspecialchars($dashboardUrl) . "'>";
            echo "<title>Reindirizzamento...</title>";
            echo "<style>body{font-family:Arial,sans-serif;text-align:center;padding:50px;}</style>";
            echo "</head><body>";
            echo "<div style='max-width:400px;margin:0 auto;padding:20px;border:1px solid #ddd;border-radius:10px;'>";
            echo "<h2 style='color:#28a745;'>✅ Login Effettuato!</h2>";
            echo "<p>Reindirizzamento alla dashboard in corso...</p>";
            echo "<div style='margin:20px 0;'>";
            echo "<i class='fas fa-spinner fa-spin' style='font-size:2rem;color:#007bff;'></i>";
            echo "</div>";
            echo "<p><a href='" . htmlspecialchars($dashboardUrl) . "' style='color:#007bff;'>Clicca qui se non vieni reindirizzato automaticamente</a></p>";
            echo "</div>";
            echo "<script>";
            echo "setTimeout(function(){ window.location.href='" . htmlspecialchars($dashboardUrl) . "'; }, 2000);";
            echo "</script>";
            echo "</body></html>";
            exit;
        }
    }
    
    public function logout() {
        // Pulisci la sessione
        if ($this->auth) {
            $this->auth->logout();
        } else {
            // Logout manuale
            unset($_SESSION['user_id']);
            unset($_SESSION['user_name']);
            unset($_SESSION['user_email']);
        }
        
        $_SESSION['success'] = 'Logout effettuato con successo.';
        
        $app = Application::getInstance();
        $loginUrl = $app->getFullUrl('/login');
        
        if (!headers_sent()) {
            header('Location: ' . $loginUrl);
            exit;
        } else {
            echo "<script>window.location.href='" . htmlspecialchars($loginUrl) . "';</script>";
            echo "<meta http-equiv='refresh' content='0;url=" . htmlspecialchars($loginUrl) . "'>";
            exit;
        }
    }
}
<?php
// routes/web.php - Route principali dell'applicazione
$router = Application::getInstance()->get('router');

// Route pubbliche
$router->get('/', function() {
    $auth = Application::getInstance()->get('auth');
    if ($auth->check()) {
        header('Location: /dashboard');
    } else {
        header('Location: /login');
    }
    exit;
});

// Route autenticazione
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// Route protette
$router->get('/dashboard', 'DashboardController@index');
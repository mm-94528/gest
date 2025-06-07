// resources/views/auth/login.php - Form di login
<form method="POST" action="/login">
    <?php form_input('email', 'Email', 'email', null, ['required' => true]); ?>
    
    <?php form_input('password', 'Password', 'password', null, ['required' => true]); ?>
    
    <?php form_checkbox('remember', 'Ricordami'); ?>
    
    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-auth">
            <i class="fas fa-sign-in-alt me-2"></i>
            Accedi
        </button>
    </div>
    
    <div class="text-center mt-3">
        <a href="/password/reset" class="text-decoration-none">
            Password dimenticata?
        </a>
    </div>
</form>
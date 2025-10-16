<?php $activePage = 'login'; ?>
<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Connexion</h1>

        <form method="POST" action="<?php echo BASE_URL; ?>login" class="auth-form">
            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input"
                    placeholder="votre.email@example.com"
                    required>
            </div>

            <!-- Mot de passe -->
            <div class="form-group">
                <label for="password" class="form-label">Mot de passe</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input"
                    placeholder="••••••••"
                    required>
            </div>

            <!-- Bouton de soumission -->
            <button type="submit" class="btn btn-primary btn-block">
                Se connecter
            </button>
        </form>


        <!-- Lien vers l'inscription -->
        <div class="auth-footer">
            <p>Pas encore de compte ? <a href="<?php echo BASE_URL; ?>register" class="auth-link">S'inscrire</a></p>
        </div>
    </div>
</div>

<div class="login-pic">
    <img src="<?php echo BASE_URL; ?>assets/inscription.png" alt="Person reading a book while sitting comfortably in a cozy library setting with warm lighting, conveying a peaceful and inviting atmosphere for book lovers">
</div>
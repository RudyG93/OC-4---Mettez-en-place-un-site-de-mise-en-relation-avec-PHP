<?php $activePage = 'login'?>
<div class="login-container">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Connexion</h1>

            <form method="POST" action="<?= BASE_URL ?>login" class="auth-form">
                <!-- Token CSRF -->
                <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Adresse email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input"
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
                        required>
                </div>

                <!-- Bouton de soumission -->
                <button type="submit" class="btn btn-primary btn-block">
                    Se connecter
                </button>
            </form>


            <!-- Lien vers l'inscription -->
            <div class="auth-footer">
                <p>Pas de compte ? <a href="<?= BASE_URL ?>register" class="auth-link">Inscrivez-vous</a></p>
            </div>
        </div>
    </div>

    <div class="login-pic">
        <img src="<?= BASE_URL ?>assets/inscription.png" alt="Person reading a book while sitting comfortably in a cozy library setting with warm lighting, conveying a peaceful and inviting atmosphere for book lovers">
    </div>
</div>
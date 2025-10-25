<?php $activePage = 'login'?>
<div class="login-container">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Inscription</h1>

            <form method="POST" action="<?= BASE_URL ?>register" class="auth-form">
                <!-- Token CSRF -->
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <!-- Pseudo -->
                <div class="form-group">
                    <label for="username" class="form-label">Pseudo</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-input <?= isset($errors['username']) ? 'input-error' : '' ?>"
                        value="<?= e($oldInput['username'] ?? '') ?>"
                        required>
                    <?php if (isset($errors['username'])): ?>
                        <span class="error-message"><?= e($errors['username']) ?></span>
                    <?php endif?>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Adresse email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input <?= isset($errors['email']) ? 'input-error' : '' ?>"
                        value="<?= e($oldInput['email'] ?? '') ?>"
                        required>
                    <?php if (isset($errors['email'])): ?>
                        <span class="error-message"><?= e($errors['email']) ?></span>
                    <?php endif?>
                </div>

                <!-- Mot de passe -->
                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input <?= isset($errors['password']) ? 'input-error' : '' ?>"
                        required>
                    <?php if (isset($errors['password'])): ?>
                        <span class="error-message"><?= e($errors['password']) ?></span>
                    <?php endif?>
                </div>

                <!-- Bouton de soumission -->
                <button type="submit" class="btn btn-primary btn-block">
                    S'inscrire
                </button>
            </form>

            <!-- Lien vers la connexion -->
            <div class="auth-footer">
                <p>Déjà inscrit ? <a href="<?= BASE_URL ?>login" class="auth-link">Connectez-vous</a></p>
            </div>
        </div>
    </div>
    <div class="login-pic">
        <img src="<?= BASE_URL ?>assets/inscription.png" alt="Person reading a book while sitting comfortably in a cozy library setting with warm lighting, conveying a peaceful and inviting atmosphere for book lovers">
    </div>
</div>
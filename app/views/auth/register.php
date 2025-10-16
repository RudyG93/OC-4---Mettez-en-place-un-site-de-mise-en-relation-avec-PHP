<?php $activePage = 'login'; ?>
<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Inscription</h1>

        <form method="POST" action="<?php echo BASE_URL; ?>register" class="auth-form">
            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

            <!-- Pseudo -->
            <div class="form-group">
                <label for="username" class="form-label">Pseudo *</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-input <?php echo isset($errors['username']) ? 'input-error' : ''; ?>"
                    value="<?php echo htmlspecialchars($oldInput['username'] ?? ''); ?>"
                    placeholder="Votre pseudo"
                    required
                >
                <?php if (isset($errors['username'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['username']); ?></span>
                <?php endif; ?>
                <small class="form-help">3 à 50 caractères, lettres, chiffres, tirets et underscores uniquement</small>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label">Email *</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input <?php echo isset($errors['email']) ? 'input-error' : ''; ?>"
                    value="<?php echo htmlspecialchars($oldInput['email'] ?? ''); ?>"
                    placeholder="votre.email@example.com"
                    required
                >
                <?php if (isset($errors['email'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['email']); ?></span>
                <?php endif; ?>
            </div>

            <!-- Mot de passe -->
            <div class="form-group">
                <label for="password" class="form-label">Mot de passe *</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input <?php echo isset($errors['password']) ? 'input-error' : ''; ?>"
                    placeholder="••••••••"
                    required
                >
                <?php if (isset($errors['password'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['password']); ?></span>
                <?php endif; ?>
                <small class="form-help">Minimum 6 caractères</small>
            </div>

            <!-- Confirmation mot de passe -->
            <div class="form-group">
                <label for="password_confirm" class="form-label">Confirmer le mot de passe *</label>
                <input 
                    type="password" 
                    id="password_confirm" 
                    name="password_confirm" 
                    class="form-input <?php echo isset($errors['password_confirm']) ? 'input-error' : ''; ?>"
                    placeholder="••••••••"
                    required
                >
                <?php if (isset($errors['password_confirm'])): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errors['password_confirm']); ?></span>
                <?php endif; ?>
            </div>

            <!-- Bouton de soumission -->
            <button type="submit" class="btn btn-primary btn-block">
                S'inscrire
            </button>
        </form>

        <!-- Lien vers la connexion -->
        <div class="auth-footer">
            <p>Vous avez déjà un compte ? <a href="<?php echo BASE_URL; ?>login" class="auth-link">Se connecter</a></p>
        </div>
    </div>
</div>

<?php $activePage = 'account'; ?>

<div class="profile-page">
    <div class="container">
        <div class="profile-edit-container">
            <h1 class="auth-title">Modifier mon profil</h1>

            <form method="POST" action="<?php echo BASE_URL; ?>mon-compte/update" class="profile-form">
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
                        value="<?php echo htmlspecialchars($oldInput['username'] ?? $user->getUsername()); ?>"
                        placeholder="Votre pseudo"
                        required>
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
                        value="<?php echo htmlspecialchars($oldInput['email'] ?? $user->getEmail()); ?>"
                        placeholder="votre.email@example.com"
                        required>
                    <?php if (isset($errors['email'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($errors['email']); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Nouveau mot de passe (optionnel) -->
                <div class="form-group">
                    <label for="password" class="form-label">Nouveau mot de passe (optionnel)</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input <?php echo isset($errors['password']) ? 'input-error' : ''; ?>"
                        placeholder="••••••••">
                    <?php if (isset($errors['password'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($errors['password']); ?></span>
                    <?php endif; ?>
                    <small class="form-help">Laissez vide pour conserver votre mot de passe actuel</small>
                </div>

                <!-- Confirmation mot de passe -->
                <div class="form-group">
                    <label for="password_confirm" class="form-label">Confirmer le nouveau mot de passe</label>
                    <input
                        type="password"
                        id="password_confirm"
                        name="password_confirm"
                        class="form-input <?php echo isset($errors['password_confirm']) ? 'input-error' : ''; ?>"
                        placeholder="••••••••">
                    <?php if (isset($errors['password_confirm'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($errors['password_confirm']); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Boutons d'action -->
                <div class="profile-form-actions">
                    <button type="submit" class="btn btn-primary">
                        Enregistrer les modifications
                    </button>
                    <a href="<?php echo BASE_URL; ?>mon-compte" class="btn btn-secondary">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
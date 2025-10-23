<?php $activePage = 'account'; ?>

<div class="profile-page">
    <div class="container">
        <div class="profile-container">
            <!-- En-tête du profil -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <div class="avatar-placeholder">
                        <?php echo strtoupper(substr($user->getUsername(), 0, 1)); ?>
                    </div>
                </div>
                <h1 class="profile-title"><?php echo htmlspecialchars($user->getUsername()); ?></h1>
            </div>

            <!-- Informations du profil -->
            <div class="profile-info">
                <div class="profile-info-item">
                    <span class="info-label">Email :</span>
                    <span class="info-value"><?php echo htmlspecialchars($user->getEmail()); ?></span>
                </div>

                <div class="profile-info-item">
                    <span class="info-label">Membre depuis :</span>
                    <span class="info-value">
                        <?php 
                        $createdAt = $user->getCreatedAt();
                        if ($createdAt) {
                            $date = new DateTime($createdAt);
                            echo $date->format('d/m/Y');
                        } else {
                            echo 'Date inconnue';
                        }
                        ?>
                    </span>
                </div>

                <?php if ($user->getUpdatedAt()): ?>
                <div class="profile-info-item">
                    <span class="info-label">Dernière mise à jour :</span>
                    <span class="info-value">
                        <?php 
                        $updatedAt = new DateTime($user->getUpdatedAt());
                        echo $updatedAt->format('d/m/Y à H:i');
                        ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <div class="profile-actions">
                <a href="<?php echo BASE_URL; ?>mon-compte/modifier" class="btn btn-primary">
                    Modifier mon profil
                </a>
            </div>

            <!-- Statistiques (future fonctionnalité) -->
            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-value">0</span>
                    <span class="stat-label">Livres partagés</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">0</span>
                    <span class="stat-label">Messages envoyés</span>
                </div>
            </div>
        </div>
    </div>
</div>

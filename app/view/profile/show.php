<?php $activePage = ''?>

<div class="profile-page">
    <div class="container">
        <div class="profile-container profile-public">
            <!-- En-tête du profil -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <div class="avatar-placeholder">
                        <?= strtoupper(substr($user->getUsername(), 0, 1))?>
                    </div>
                </div>
                <h1 class="profile-title"><?= e($user->getUsername()) ?></h1>
                <p class="profile-subtitle">Profil public</p>
            </div>

            <!-- Informations publiques -->
            <div class="profile-info">
                <div class="profile-info-item">
                    <span class="info-label">Membre depuis :</span>
                    <span class="info-value">
                        <?php 
                        $createdAt = $user->getCreatedAt();
                        if ($createdAt) {
                            $date = new DateTime($createdAt);
                            echo $date->format('F Y');
                        } else {
                            echo 'Date inconnue';
                        }
                        ?>
                    </span>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-value">0</span>
                    <span class="stat-label">Livres partagés</span>
                </div>
            </div>

            <!-- Actions (futures fonctionnalités) -->
            <div class="profile-actions">
                <a href="<?= BASE_URL?>books?user_id=<?= $user->getId()?>" class="btn btn-primary">
                    Voir ses livres
                </a>
                <?php if (Session::isLoggedIn() && Session::get('user_id') != $user->getId()): ?>
                <a href="<?= BASE_URL?>messages/new?to=<?= $user->getId()?>" class="btn btn-secondary">
                    Envoyer un message
                </a>
                <?php endif?>
            </div>
        </div>
    </div>
</div>

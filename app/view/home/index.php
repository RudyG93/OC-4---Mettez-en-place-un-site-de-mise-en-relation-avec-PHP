<?php $activePage = 'home'?>
<div class="hero">
    <div class="container">
        <h1 class="hero-title"><?= e($heading)?></h1>
        <p class="hero-subtitle"><?= e($message)?></p>
    </div>
</div>

<section class="features-section">
    <div class="container">
        <h2 class="section-title">Architecture du projet</h2>
        <div class="features-grid">
            <?php foreach ($features as $feature): ?>
                <div class="feature-card">
                    <div class="feature-icon">✓</div>
                    <h3 class="feature-title"><?= e($feature)?></h3>
                </div>
            <?php endforeach?>
        </div>
    </div>
</section>

<section class="info-section">
    <div class="container">
        <div class="info-box">
            <h2>Prochaines étapes</h2>
            <ul class="info-list">
                <li>✅ Structure MVC créée</li>
                <li>✅ Base de données SQL définie</li>
                <li>✅ Système de routage fonctionnel</li>
                <li>✅ Classes core implémentées</li>
                <li>⏳ Système d'authentification (à venir)</li>
                <li>⏳ Gestion des livres (à venir)</li>
                <li>⏳ Messagerie (à venir)</li>
            </ul>
        </div>
        
        <div class="info-box">
            <h2>Installation de la base de données</h2>
            <p>Pour initialiser la base de données :</p>
            <ol class="info-list">
                <li>Importez le fichier <code>sql/database.sql</code> dans phpMyAdmin</li>
                <li>Configurez vos identifiants dans <code>config/config.local.php</code></li>
                <li>La base contient des données de test pour démarrer rapidement</li>
            </ol>
        </div>
    </div>
</section>

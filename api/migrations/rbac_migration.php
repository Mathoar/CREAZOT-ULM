<?php

/**
 * Migration RBAC :
 * 1. Crée les tables role + permission
 * 2. Insère les rôles et permissions par défaut via RbacInitializer
 * 3. Ajoute la colonne role_id sur user_client_role
 * 4. Migre les données existantes (string role → FK role_id)
 * 5. Supprime l'ancienne colonne role (string)
 *
 * Exécution : docker exec -it php php api/migrations/rbac_migration.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv->loadEnv(dirname(__DIR__) . '/.env');
}

$kernel = new \App\Kernel($_SERVER['APP_ENV'] ?? 'prod', (bool) ($_SERVER['APP_DEBUG'] ?? false));
$kernel->boot();

$container = $kernel->getContainer();
/** @var \Doctrine\DBAL\Connection $conn */
$conn = $container->get('doctrine')->getConnection();

echo "=== Migration RBAC ===\n\n";

// 1. Créer la table role
echo "1. Création de la table role...\n";
$conn->executeStatement("
    CREATE TABLE IF NOT EXISTS role (
        id SERIAL PRIMARY KEY,
        code VARCHAR(30) NOT NULL UNIQUE,
        label VARCHAR(100) NOT NULL,
        is_system BOOLEAN NOT NULL DEFAULT TRUE
    )
");
echo "   OK\n";

// 2. Créer la table permission
echo "2. Création de la table permission...\n";
$conn->executeStatement("
    CREATE TABLE IF NOT EXISTS permission (
        id SERIAL PRIMARY KEY,
        role_id INT NOT NULL REFERENCES role(id) ON DELETE CASCADE,
        resource VARCHAR(50) NOT NULL,
        can_read BOOLEAN NOT NULL DEFAULT FALSE,
        can_write BOOLEAN NOT NULL DEFAULT FALSE,
        UNIQUE(role_id, resource)
    )
");
echo "   OK\n";

// 3. Insérer les rôles et permissions par défaut
echo "3. Initialisation des rôles et permissions...\n";
/** @var \App\Service\RbacInitializer $initializer */
$initializer = $container->get(\App\Service\RbacInitializer::class);
$initializer->initialize();
echo "   OK\n";

// 4. Ajouter la colonne role_id sur user_client_role
echo "4. Ajout de la colonne role_id sur user_client_role...\n";

$columns = $conn->fetchAllAssociative("
    SELECT column_name FROM information_schema.columns
    WHERE table_name = 'user_client_role' AND column_name = 'role_id'
");

if (empty($columns)) {
    $conn->executeStatement("ALTER TABLE user_client_role ADD COLUMN role_id INT NULL");
    echo "   Colonne role_id ajoutée\n";
} else {
    echo "   Colonne role_id déjà existante\n";
}

// 5. Migrer les données existantes
echo "5. Migration des rôles existants...\n";

$roleMapping = [
    'admin' => 'admin',
    'pilot' => 'pilote',
    'pilote' => 'pilote',
];

foreach ($roleMapping as $oldRole => $newCode) {
    $roleId = $conn->fetchOne("SELECT id FROM role WHERE code = :code", ['code' => $newCode]);
    if ($roleId) {
        $count = $conn->executeStatement(
            "UPDATE user_client_role SET role_id = :roleId WHERE role = :oldRole AND role_id IS NULL",
            ['roleId' => $roleId, 'oldRole' => $oldRole]
        );
        echo "   '{$oldRole}' → '{$newCode}' (role_id={$roleId}) : {$count} lignes\n";
    }
}

// Les entrées restantes sans role_id → pilote par défaut
$piloteId = $conn->fetchOne("SELECT id FROM role WHERE code = 'pilote'");
if ($piloteId) {
    $remaining = $conn->executeStatement(
        "UPDATE user_client_role SET role_id = :roleId WHERE role_id IS NULL",
        ['roleId' => $piloteId]
    );
    if ($remaining > 0) {
        echo "   {$remaining} entrées restantes → pilote (défaut)\n";
    }
}

// 6. Rendre role_id NOT NULL et ajouter la FK
echo "6. Contraintes sur role_id...\n";
$conn->executeStatement("ALTER TABLE user_client_role ALTER COLUMN role_id SET NOT NULL");

$fkExists = $conn->fetchOne("
    SELECT 1 FROM information_schema.table_constraints
    WHERE constraint_name = 'fk_ucr_role' AND table_name = 'user_client_role'
");
if (!$fkExists) {
    $conn->executeStatement("
        ALTER TABLE user_client_role
        ADD CONSTRAINT fk_ucr_role FOREIGN KEY (role_id) REFERENCES role(id)
    ");
}
echo "   OK\n";

// 7. Supprimer l'ancienne colonne role (string)
echo "7. Suppression de l'ancienne colonne role (string)...\n";
$oldCol = $conn->fetchAllAssociative("
    SELECT column_name FROM information_schema.columns
    WHERE table_name = 'user_client_role' AND column_name = 'role' AND data_type = 'character varying'
");
if (!empty($oldCol)) {
    $conn->executeStatement("ALTER TABLE user_client_role DROP COLUMN role");
    echo "   Colonne 'role' (varchar) supprimée\n";
} else {
    echo "   Colonne 'role' (varchar) déjà supprimée\n";
}

echo "\n=== Migration RBAC terminée avec succès ===\n";

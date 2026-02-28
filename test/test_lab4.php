<?php
declare(strict_types=1);

// Autoload minimal (si pas déjà en bootstrap)
spl_autoload_register(function(string $class){
    $prefix = 'App\\'; $base = __DIR__ . '/..//'; $len = strlen($prefix);
    if (strncmp($class, $prefix, $len) !== 0) return;
    $rel = substr($class, $len);
    $file = $base . str_replace('\\', DIRECTORY_SEPARATOR, $rel) . '.php';
    if (is_file($file)) require $file;
});

$bootstrap = __DIR__ . '/../bootstrap.php';
if (file_exists($bootstrap)) {
    require $bootstrap;
}
use App\Container\AppFactory;
use App\Controller\Response;

function printResponse(string $label, Response $res): void {
    echo "[RESPONSE] $label => success=" . ($res->isSuccess() ? 'true' : 'false');
    if ($res->isSuccess()) {
        echo ' data=' . json_encode($res->getData(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } else {
        echo ' error=' . $res->getError() . PHP_EOL;
    }
}
$ctrl = AppFactory::createController();

// 1) OK: création filière + création étudiant (transaction combinée)
$r1 = $ctrl->handle([
    'action' => 'create_filiere_then_student',
    'code' => 'bio', 'libelle' => 'Biologie',
    'cne' => 'CNE7777', 'nom' => 'Test', 'prenom' => 'Tx', 'email' => 'test.tx@example.com'
]);
printResponse('Create Filiere + Etudiant', $r1);

// 2) Erreur: email interdit (mailinator)
$r2 = $ctrl->handle([
    'action' => 'create_etudiant',
    'cne' => 'CNE1234', 'nom' => 'Doe', 'prenom' => 'Jane', 'email' => 'jane@mailinator.com',
    'filiere_id' => 1
]);
printResponse('Email interdit', $r2);

// 3) Erreur: CNE invalide
$r3 = $ctrl->handle([
    'action' => 'create_etudiant',
    'cne' => 'ABC9999', 'nom' => 'Bad', 'prenom' => 'Cne', 'email' => 'ok@example.com',
    'filiere_id' => 1
]);
printResponse('CNE invalide', $r3);

// 4) Erreur: suppression filière avec étudiants
// Assurer une filière avec au moins un étudiant (ex: filière 1 des données d'amorce LAB 3)
$r4 = $ctrl->handle(['action' => 'delete_filiere', 'id' => 1]);
printResponse('Suppression filière interdite', $r4);
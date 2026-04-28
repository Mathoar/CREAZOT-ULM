<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Kernel;

$kernel = new Kernel('prod', false);
$kernel->boot();
$conn = $kernel->getContainer()->get('doctrine')->getManager()->getConnection();

$html = '<h3>Gestion des qualifications et aptitudes</h3>
<p>L\'application Logic\'Ciel intègre des mécanismes automatisés de contrôle de l\'aptitude des pilotes avant chaque vol :</p>
<h4>Blocage par qualification</h4>
<p>Chaque circuit peut être associé à une ou plusieurs qualifications requises. Un pilote ne peut être affecté à un vol sur un circuit donné que s\'il possède l\'ensemble des qualifications exigées et que celles-ci sont en cours de validité.</p>
<h4>Contrôle du certificat médical</h4>
<p>Le système vérifie automatiquement la validité du certificat médical de chaque pilote. Un pilote dont le certificat médical est expiré ou absent ne peut pas être assigné à un vol. Les types de certificats gérés incluent : Classe 1, Classe 2, Certificat d\'aptitude, Certificat de non contre-indication et Certificat exceptionnel.</p>
<h4>Alertes de validité</h4>
<p>Des alertes automatiques sont générées lorsque les qualifications ou certificats médicaux approchent de leur date d\'expiration, permettant aux exploitants d\'anticiper les renouvellements.</p>';

$count = $conn->executeStatement(
    'UPDATE manex_section SET has_auto_content = false, custom_html = :html WHERE section_key = :key AND custom_html IS NULL',
    ['html' => $html, 'key' => 'procedures_ops']
);

echo "procedures_ops: $count rows updated\n";

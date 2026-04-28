<?php

declare(strict_types=1);

namespace App\Service\Manex;

final class ManexSections
{
    public const SECTIONS = [
        'presentation'      => ['title' => 'Présentation de l\'exploitant',             'auto' => true,  'position' => 1],
        'organisation'      => ['title' => 'Organisation de l\'exploitation',           'auto' => true,  'position' => 2],
        'formation'         => ['title' => 'Formation',                                 'auto' => false, 'position' => 3],
        'activites'         => ['title' => 'Types d\'activités',                        'auto' => true,  'position' => 4],
        'flotte'            => ['title' => 'Flotte utilisée',                           'auto' => true,  'position' => 5],
        'procedures_prevol' => ['title' => 'Procédures pré-vol',                        'auto' => false, 'position' => 6],
        'procedures_pax'    => ['title' => 'Procédures passagers',                      'auto' => true,  'position' => 7],
        'procedures_ops'    => ['title' => 'Procédures opérationnelles',                'auto' => false, 'position' => 8],
        'limites_meteo'     => ['title' => 'Limites météorologiques',                   'auto' => true,  'position' => 9],
        'analyse_risques'   => ['title' => 'Analyse des risques',                       'auto' => false, 'position' => 10],
        'incidents'         => ['title' => 'Gestion des incidents',                     'auto' => false, 'position' => 11],
        'documentation'     => ['title' => 'Documentation terrain',                     'auto' => true,  'position' => 12],
        'responsabilites'   => ['title' => 'Responsabilités',                           'auto' => false, 'position' => 13],
        'facteurs_humains'  => ['title' => 'Facteurs humains et retour d\'expérience',  'auto' => false, 'position' => 14],
        'gestion_doc'       => ['title' => 'Gestion documentaire et révisions',         'auto' => true,  'position' => 15],
        'annexes'           => ['title' => 'Annexes',                                   'auto' => false, 'position' => 16],
    ];

    public const DEFAULT_CONTENT = [
        'procedures_ops' => '<h3>Gestion des qualifications et aptitudes</h3>
<p>L\'application Logic\'Ciel intègre des mécanismes automatisés de contrôle de l\'aptitude des pilotes avant chaque vol :</p>
<h4>Blocage par qualification</h4>
<p>Chaque circuit peut être associé à une ou plusieurs qualifications requises. Un pilote ne peut être affecté à un vol sur un circuit donné que s\'il possède l\'ensemble des qualifications exigées et que celles-ci sont en cours de validité.</p>
<h4>Contrôle du certificat médical</h4>
<p>Le système vérifie automatiquement la validité du certificat médical de chaque pilote. Un pilote dont le certificat médical est expiré ou absent ne peut pas être assigné à un vol. Les types de certificats gérés incluent : Classe 1, Classe 2, Certificat d\'aptitude, Certificat de non contre-indication et Certificat exceptionnel.</p>
<h4>Alertes de validité</h4>
<p>Des alertes automatiques sont générées lorsque les qualifications ou certificats médicaux approchent de leur date d\'expiration, permettant aux exploitants d\'anticiper les renouvellements.</p>',

        'procedures_prevol' => '<h3>Check-list pré-vol</h3>
<p>Avant chaque vol, le pilote commandant de bord effectue les vérifications suivantes :</p>
<ul>
    <li><strong>Conditions météorologiques</strong> : consultation du METAR/TAF et vérification des conditions locales (vent, visibilité, plafond) au regard des minima définis dans la section « Limites météorologiques ».</li>
    <li><strong>Aéronef</strong> : visite pré-vol complète selon la check-list constructeur, vérification du carburant, de l\'état général et du carnet de route.</li>
    <li><strong>Documents réglementaires</strong> : validité du certificat de navigabilité, assurance, licence et qualification du pilote, certificat médical.</li>
    <li><strong>Masse et centrage</strong> : vérification de la masse au décollage et du centrage en fonction du nombre de passagers et du carburant embarqué.</li>
    <li><strong>Zone de vol</strong> : consultation des NOTAM, vérification des espaces aériens et restrictions éventuelles.</li>
    <li><strong>Communication passagers</strong> : envoi du SMS pré-vol aux passagers conformément au modèle configuré dans l\'application.</li>
</ul>
<h3>Critères NO GO</h3>
<p>Le vol est annulé si l\'une des conditions suivantes est constatée :</p>
<ul>
    <li>Conditions météorologiques inférieures aux minima définis.</li>
    <li>Anomalie technique détectée lors de la visite pré-vol.</li>
    <li>Documents pilote ou aéronef non à jour.</li>
    <li>Qualification pilote non valide pour le type de vol prévu.</li>
</ul>',

        'incidents' => '<h3>Procédure de gestion des événements de sécurité</h3>
<p>En cas d\'événement de sécurité (incident, accident, quasi-accident, observation), la procédure suivante s\'applique :</p>

<h4>1. Actions immédiates</h4>
<ul>
    <li>Assurer la sécurité des personnes (passagers, équipage, tiers).</li>
    <li>Sécuriser l\'aéronef et la zone concernée.</li>
    <li>Prévenir les secours si nécessaire (SAMU, pompiers).</li>
</ul>

<h4>2. Enregistrement et notification automatisée</h4>
<p>L\'exploitant utilise le module « Événements sécurité » de Logic\'Ciel pour centraliser et automatiser la gestion des événements :</p>
<ul>
    <li><strong>Création de l\'événement</strong> : dès la saisie, un <strong>email de notification est automatiquement envoyé</strong> au dirigeant responsable, incluant le type d\'événement, la description, le pilote et l\'aéronef concernés.</li>
    <li><strong>Compte rendu pré-formaté</strong> : un modèle structuré (description des faits, circonstances, conséquences, analyse des causes, mesures correctives, retour d\'expérience) est <strong>automatiquement pré-rempli</strong> dans l\'éditeur, facilitant la rédaction.</li>
    <li><strong>Types d\'événements</strong> : incident, accident, quasi-accident, observation, ou note interne (pour les événements moins critiques ne nécessitant pas de notification réglementaire).</li>
</ul>

<h4>3. Délais réglementaires et envoi simplifié</h4>
<ul>
    <li>Notification à la DGAC / DSAC dans un délai maximal de <strong>72 heures</strong> suivant l\'événement.</li>
    <li>En cas d\'accident ou d\'incident grave : notification au <strong>BEA</strong> dans les plus brefs délais.</li>
    <li>Rédaction d\'un compte rendu de suivi dans les <strong>30 jours</strong> suivant la notification.</li>
    <li>Logic\'Ciel intègre un <strong>bouton d\'envoi du compte rendu en PDF</strong> directement à la DSAC, au BEA ou à un autre destinataire, avec <strong>mise à jour automatique des dates de notification</strong>.</li>
    <li>Les <strong>délais réglementaires sont affichés en temps réel</strong> sur chaque événement ouvert, avec un décompte visuel.</li>
</ul>

<h4>4. Suivi et clôture</h4>
<ul>
    <li>Le statut de chaque événement est suivi : ouvert, en cours d\'analyse, ou clôturé.</li>
    <li>La <strong>date de clôture est automatiquement renseignée</strong> lorsque le statut passe à « clôturé » (et réinitialisée si le statut est rouvert).</li>
    <li>Conservation de tous les éléments de preuve (carnet de route, données de vol, témoignages).</li>
</ul>

<h4>5. Analyse et retour d\'expérience</h4>
<ul>
    <li>Analyse des causes de l\'événement dans le compte rendu structuré.</li>
    <li>Mise en place et documentation des actions correctives.</li>
    <li>Communication du retour d\'expérience à l\'ensemble des pilotes.</li>
</ul>',

        'formation' => '<h3>Politique de formation</h3>
<p>L\'exploitant assure la <strong>formation continue et le maintien des compétences</strong> de l\'ensemble de ses pilotes. La formation est dispensée <strong>en interne</strong>, sous la supervision d\'un instructeur qualifié rattaché à la structure.</p>

<h4>Conditions d\'accès à la formation</h4>
<p>Tout pilote souhaitant exercer une activité au sein de la structure doit satisfaire aux conditions suivantes :</p>
<ul>
    <li>Être titulaire du <strong>brevet de pilote ULM</strong> dans la classe correspondante (multiaxes, pendulaire, paramoteur, etc.).</li>
    <li>Disposer d\'un <strong>certificat médical</strong> en cours de validité (classe 2 ou certificat de non contre-indication selon le type d\'activité).</li>
    <li>Pour les vols à titre onéreux : être titulaire de la <strong>qualification d\'emport de passagers</strong> et, le cas échéant, de la qualification de <strong>pilote professionnel</strong>.</li>
</ul>

<h4>Programme de formation interne</h4>
<p>Le programme de formation interne comprend :</p>
<ul>
    <li><strong>Formation initiale (FI)</strong> : prise en main de la flotte, connaissance des circuits, procédures locales, briefing sécurité, conditions météorologiques spécifiques au site d\'exploitation.</li>
    <li><strong>Formation continue (FC)</strong> : vols d\'entraînement périodiques, révision des procédures d\'urgence, mises à jour réglementaires, retour d\'expérience collectif suite aux événements de sécurité.</li>
    <li><strong>Contrôle des compétences</strong> : vols de contrôle réalisés par l\'instructeur afin de vérifier le maintien des compétences opérationnelles.</li>
</ul>

<h4>Suivi des formations</h4>
<p>Chaque formation est tracée dans Logic\'Ciel, qui permet de suivre :</p>
<ul>
    <li>Le programme de formation associé.</li>
    <li>L\'avancement des leçons (validées, en cours).</li>
    <li>L\'instructeur responsable.</li>
    <li>Les documents associés (attestations, certificats).</li>
</ul>
<p>Les qualifications obtenues par les pilotes sont enregistrées avec leur date d\'obtention et de validité. Le système génère des <strong>alertes automatiques</strong> lorsqu\'une qualification ou un certificat médical approche de son expiration.</p>',

        'analyse_risques' => '<h3>Méthodologie d\'analyse des risques</h3>
<p>L\'exploitant met en œuvre une démarche d\'identification et de gestion des risques basée sur la méthode <strong>TEM (Threat and Error Management)</strong>. Cette analyse est réalisée pour chaque <strong>activité particulière (AP)</strong> déclarée.</p>

<h4>Principe de la méthode TEM</h4>
<p>La méthode TEM distingue trois niveaux :</p>
<ol>
    <li><strong>Menaces (Threats)</strong> : facteurs externes susceptibles d\'affecter la sécurité du vol (météo, relief, trafic, état de la piste, etc.).</li>
    <li><strong>Erreurs (Errors)</strong> : actions ou inactions de l\'équipage pouvant réduire les marges de sécurité.</li>
    <li><strong>États indésirables (Undesired States)</strong> : situations résultant de menaces non gérées ou d\'erreurs non détectées.</li>
</ol>

<p><em>Échelle de probabilité : Rare — Occasionnel — Probable — Fréquent</em><br/>
<em>Échelle de gravité : Mineur — Modéré — Majeur — Critique — Catastrophique</em></p>

<p>Les grilles d\'analyse détaillées ci-dessous sont générées automatiquement pour chaque activité particulière (AP) identifiée parmi les circuits de l\'exploitant. L\'exploitant est invité à compléter et adapter ces grilles en fonction de ses conditions locales d\'exploitation.</p>',

        'facteurs_humains' => '<h3>Gestion des facteurs humains</h3>
<p>L\'exploitant intègre la dimension <strong>facteurs humains</strong> dans sa politique de sécurité, en reconnaissant que la majorité des événements de sécurité aérienne trouvent leur origine dans des facteurs humains.</p>

<h4>Principes appliqués</h4>
<ul>
    <li><strong>Culture juste</strong> : les erreurs involontaires sont traitées de manière non punitive, afin d\'encourager le signalement spontané des événements et quasi-événements.</li>
    <li><strong>Conscience situationnelle</strong> : les pilotes sont formés à maintenir une vigilance constante sur l\'environnement, les paramètres de vol et l\'état de l\'aéronef.</li>
    <li><strong>Gestion de la fatigue</strong> : les temps de vol et de service sont surveillés pour prévenir les effets de la fatigue sur les performances des pilotes.</li>
    <li><strong>Prise de décision</strong> : la décision GO/NO GO appartient au pilote commandant de bord, qui ne doit subir aucune pression commerciale pour effectuer un vol dans des conditions qu\'il juge insuffisantes.</li>
</ul>

<h4>Retour d\'expérience (REX)</h4>
<p>L\'exploitant organise un processus de retour d\'expérience structuré :</p>
<ul>
    <li><strong>Signalement facilité</strong> : tout pilote peut signaler un événement de sécurité ou une observation via le module « Événements sécurité » de Logic\'Ciel, y compris sous forme de « note interne » pour les observations mineures.</li>
    <li><strong>Analyse collective</strong> : les événements significatifs font l\'objet d\'une analyse partagée avec l\'ensemble des pilotes lors de réunions de sécurité ou par diffusion écrite.</li>
    <li><strong>Actions correctives</strong> : les enseignements tirés sont traduits en actions concrètes (mise à jour de procédures, formation complémentaire, modification des minima, etc.).</li>
    <li><strong>Archivage</strong> : l\'historique des événements et des actions correctives est conservé dans Logic\'Ciel, permettant d\'identifier les tendances et de mesurer l\'efficacité des mesures prises.</li>
</ul>

<h4>Communication sécurité</h4>
<p>L\'exploitant veille à maintenir un dialogue ouvert sur la sécurité avec l\'ensemble de ses pilotes. Les briefings, les retours d\'expérience et les mises à jour de procédures sont communiqués de manière régulière et systématique.</p>',

        'gestion_doc' => '<h3>Politique d\'archivage</h3>
<p>L\'ensemble des documents relatifs à l\'exploitation sont conservés et archivés selon les règles suivantes :</p>
<ul>
    <li><strong>Documents pilotes</strong> (qualifications, certificats médicaux, attestations de formation) : conservés pendant toute la durée d\'activité du pilote au sein de la structure, puis 5 ans après son départ.</li>
    <li><strong>Carnets de route et carnets de vol</strong> : conservés de manière permanente dans Logic\'Ciel.</li>
    <li><strong>Événements de sécurité et comptes rendus</strong> : conservés de manière permanente, incluant les notifications, les analyses et les actions correctives.</li>
    <li><strong>Documents aéronef</strong> (suivi de maintenance, certificats de navigabilité) : conservés pendant toute la durée d\'exploitation de l\'aéronef, puis 2 ans après sa cession.</li>
    <li><strong>Versions du MANEX</strong> : chaque version générée est archivée avec son numéro de version, sa date et son auteur.</li>
</ul>

<h4>Révision du document</h4>
<p>Le présent manuel est révisé à chaque modification significative de l\'exploitation :</p>
<ul>
    <li>Changement de flotte (ajout ou retrait d\'aéronef).</li>
    <li>Modification des circuits ou des types d\'activités.</li>
    <li>Évolution des procédures de sécurité.</li>
    <li>Changement réglementaire impactant l\'exploitation.</li>
    <li>Retour d\'expérience nécessitant une mise à jour des procédures.</li>
</ul>
<p>Chaque révision donne lieu à une nouvelle version du MANEX, générée et archivée via Logic\'Ciel.</p>',

        'responsabilites' => '<h3>Responsabilités du dirigeant responsable</h3>
<ul>
    <li>Garantir le respect de la réglementation applicable à l\'exploitation.</li>
    <li>S\'assurer que les pilotes disposent des qualifications et certifications médicales à jour.</li>
    <li>Maintenir la flotte en état de navigabilité.</li>
    <li>Définir et mettre à jour les procédures d\'exploitation.</li>
    <li>Organiser les formations et contrôles périodiques des pilotes.</li>
    <li>Tenir à jour le présent MANEX.</li>
</ul>

<h3>Responsabilités du pilote commandant de bord</h3>
<ul>
    <li>Effectuer la visite pré-vol et vérifier l\'aptitude de l\'aéronef au vol.</li>
    <li>Prendre la décision GO / NO GO en fonction des conditions météorologiques et de l\'état de l\'aéronef.</li>
    <li>Assurer la sécurité des passagers et le respect du briefing de sécurité.</li>
    <li>Respecter les limitations définies dans le présent MANEX (météo, qualifications, procédures).</li>
    <li>Signaler tout événement de sécurité au dirigeant responsable.</li>
    <li>Tenir à jour le carnet de route de l\'aéronef.</li>
</ul>

<h3>Responsabilités du personnel au sol</h3>
<ul>
    <li>Accueillir et orienter les passagers conformément aux procédures.</li>
    <li>S\'assurer du bon déroulement du briefing passagers.</li>
    <li>Vérifier l\'identité et le nombre de passagers avant embarquement.</li>
    <li>Signaler toute anomalie ou situation inhabituelle au pilote ou au dirigeant.</li>
</ul>',
    ];

    public static function getDefault(string $key): array
    {
        return self::SECTIONS[$key] ?? throw new \InvalidArgumentException("Section inconnue : $key");
    }

    public static function keys(): array
    {
        return array_keys(self::SECTIONS);
    }

    public static function hasAutoContent(string $key): bool
    {
        return self::SECTIONS[$key]['auto'] ?? false;
    }

    public static function getDefaultContent(string $key): ?string
    {
        return self::DEFAULT_CONTENT[$key] ?? null;
    }
}

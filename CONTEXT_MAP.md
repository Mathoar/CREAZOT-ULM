# Carte des Contextes — Logic'Ciel

> **Tableau de bord** pour suivre les fichiers contexte du projet.
> Format `.mdc` = Cursor Rules — mémoire persistante de l'IA entre les sessions.

---

## Fichiers contexte

### Toujours chargés (`alwaysApply: true`)

| # | Fichier | Domaine | Dernière MAJ | État |
|---|---------|---------|--------------|------|
| 1 | [context.mdc](.cursor/rules/context.mdc) | **Vue d'ensemble** (stack, infra, modules, entités, multi-tenant, migration) | 2026-04-21 | A jour |
| 2 | [context-preferences.mdc](.cursor/rules/context-preferences.mdc) | Préférences développeur, conventions, style de travail | 2026-03-26 | A jour |
| 3 | [context-git.mdc](.cursor/rules/context-git.mdc) | Workflow Git, branches, conventions de commit, périmètre repo | 2026-03-26 | A jour |
| 4 | [context-maintenance.mdc](.cursor/rules/context-maintenance.mdc) | Règle auto : proposer la MAJ des contextes | 2026-03-26 | A jour |

### Chargés selon les fichiers ouverts (`globs`)

| # | Fichier | Domaine | Activé quand | Dernière MAJ |
|---|---------|---------|--------------|--------------|
| 5 | [context-code-map.mdc](.cursor/rules/context-code-map.mdc) | Cartographie du code | `api/src/**`, `pwa/components/**`, `pwa/app/**` | 2026-04-21 |
| 6 | [context-vapi.mdc](.cursor/rules/context-vapi.mdc) | Assistant Vocal Vapi.ai | `VapiService`, `VapiController`, `ClientsEdit` | 2026-03-26 |
| 7 | [context-ai.mdc](.cursor/rules/context-ai.mdc) | IA Kimi, Score OPS, Météo | `KimiAiService`, `ScoreOps`, `dashboard/**` | 2026-03-26 |
| 8 | [context-billing.mdc](.cursor/rules/context-billing.mdc) | Facturation Odoo | `Odoo*`, `InvoiceCalc`, `ModulePack`, `PricingTier` | 2026-03-26 |
| 9 | [context-deploy.mdc](.cursor/rules/context-deploy.mdc) | Déploiement Docker | `compose.yaml`, `Dockerfile` | 2026-04-21 |
| 10 | [context-briefing.mdc](.cursor/rules/context-briefing.mdc) | Briefing public passager (`/r/{shortcode}`) | `Briefing.php`, `PublicReservationController`, `ImageResizer`, `pwa/app/r/**`, `admin/briefing/**`, `admin/circuit/**` | 2026-04-21 |

---

## Comment mettre à jour ?

Après une session de développement significative, demander à l'agent :

> « Mets à jour les fichiers context concernés par les changements de cette session »

---

## Dashboard visuel

Ouvrir dans le navigateur : [docs/context-dashboard.html](docs/context-dashboard.html)

Cliquer sur une carte → ouvre le fichier directement dans Cursor (lien `vscode://`).

---

## Historique des migrations

| Date | Migration | Statut | Scripts |
|------|-----------|--------|---------|
| 2026-04-09 | Planetair-Gestion → Logic'Ciel (`client_id=5`) | ✅ Terminée | `docs/generate_migration.py`, `docs/migration_planetair_to_logicciel.sql` |

---

## Rappels

- Format obligatoire : **`.mdc`** (Cursor Rules), pas `.md`
- `alwaysApply: true` → chargé à chaque conversation
- `globs: [...]` → chargé quand un fichier correspondant est ouvert
- `description` → visible dans **Cursor > Settings > Rules**
- Chaque fichier doit rester **< 200 lignes**

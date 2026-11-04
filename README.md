# avecLesFemmes — lignée `livrable/4`

Fork personnel de [Gafclerck/avecLesFemmes-Symfony](https://github.com/Gafclerck/avecLesFemmes-Symfony),
travaillée à partir de la branche de cours la plus récente
(`feat/exercice-and-groupement-search`, 3 novembre 2026).

Cette lignée termine ce que la séance du **03-11-2026** demandait : intégrer les
formulaires dynamiques liés à un DTO, le filtre et la navigabilité vus en cours,
puis compléter le DTO exercice avec la date de fin et le calcul des jours restants.

## Stack

- Symfony 7.4, PHP 8.5, Doctrine ORM 3.7
- SQLite (fichier `var/data_l4.db`),Twig + AssetMapper/importmap
- PHPUnit 12.5 avec `failOnDeprecation`, `failOnNotice` et `failOnWarning`

## Installation

```bash
composer install
php bin/console doctrine:schema:create     # les migrations de la classe ciblent PostgreSQL
php bin/console doctrine:fixtures:load
php -S 127.0.0.1:8009 -t public
```

`.env.dev` et `.env.test` portent déjà l'URL SQLite ; un `.env.local` avec votre
`APP_SECRET` suffit pour lancer l'application. À l'ISMD, l'écran doit être
accessible sur `www.nom.prenom.ism.edu.sn/aveclesfemmes`, ce qui demande un
enregistrement A local pointant vers la machine de dev.

## Écrans

| URL | Écran |
| --- | --- |
| `/groupement` | liste des groupements, formulaire de recherche et compteurs |
| `/groupement?query=mbour` | même liste filtrée sur le nom **ou** la ville |
| `/{id}/exercice` | fiche du groupement : frise de l'exercice en cours et historique |
| `/{id}/exercice` (sans exercice) | état vide « Ouvrir le premier exercice » |

## Ce qui était demandé, et où le trouver

| Consigne du 03-11-2026 | Réalisation |
| --- | --- |
| Formulaire inline, `display: flex` appliqué via `form_start` | `templates/groupement/index.html.twig`, classes `recherche form-search-inline` |
| Héritage du layout `base.html.twig` (`{{ parent() }}`, blocs redéfinis) | `templates/base.html.twig`, `templates/groupement/index.html.twig` |
| Filtre dans le repository : `andWhere` conditionnel si le DTO n'est pas nul | `GroupementRepository::appliquerFiltre()` |
| `$form->handleRequest($request)` mappe la soumission dans le DTO | `GroupementController::index()`, `GroupeSearchDto` |
| Route paramétrée `{idGroupement}` avec `requirements: '\d+'` | `ExerciceController::index()` |
| `findBy([...], ['statut' => 'DESC'])` pour les exercices triés | `GroupementExerciceService::findOneGroupementWithExercices()` |
| Compléter `ExerciceDTO` : date de fin et jours restants | `src/Dto/ExerciceDto.php` |
| Calcul des jours restants côté service | `GroupementExerciceService::joursRestants()` |

## Ce qui a été corrigé par rapport à la version de cours

- La condition `where('e.statut = :statut')` annulait le `leftJoin` : un groupement
  sans exercice en cours disparaissait de la liste. Elle est passée dans le `WITH`
  de la jointure, et le paramètre est l'enum `StatutExercice::ENCOURS` plutôt
  qu'une chaîne.
- Le service renvoyait des `null` dans la liste ; `ExerciceEncoursDto` y est
  maintenant nullable et la vue affiche « Aucun exercice en cours ».
- Le compteur d'exercices de la colonne « Exercices » était codé en dur : il vient
  d'une requête d'agrégation `COUNT … GROUP BY` (`ExerciceRepository::compterParGroupements`).
- Le filtre insensible à la casse passe par `LOWER()` côté SQL ; aucune donnée
  n'est triée ni filtrée en PHP.
- La frise et l'historique de la fiche affichaient des dates de maquette : ils
  viennent de l'exercice, et les étiquettes (« Encore 41 jours pour prêter »,
  « Prêts clos depuis 20 jours ») sont produites par le service.
- Un identifiant de groupement inconnu renvoie une 404 au lieu de rediriger en
  silence vers la liste, et l'état vide est rendu avec les données du groupement
  visité.
- Les routes d'écran ne répondent plus qu'en `GET`.

## Données de démonstration

Cinq groupements, dont les exercices sont datés **relativement au jour de la
démonstration** pour que les libellés restent justes le jour de la soutenance :

| Groupement | État illustré |
| --- | --- |
| GIE Teranga Mbour | trois exercices, un en cours, prêts encore ouverts |
| GIE Jappo Dakar | deux exercices clôturés, aucun en cours |
| GIE Dëgg Saint-Louis | date limite de prêts atteinte aujourd'hui |
| GIE Ndingël Louga | prêts clos, exercice toujours en cours |
| GIE Wàllar Thiès | aucun exercice : écran d'état vide |

## Tests

```bash
php vendor/bin/phpunit      # 29 tests, 72 assertions
```

- `tests/Dto/ExerciceDtoTest.php` : libellés des jours restants.
- `tests/Service/GroupementExerciceServiceTest.php` : filtre, comptage, frise, états.
- `tests/Controller/GroupementListeTest.php` : formulaire GET, liens, aucun `<form>` brut dans Twig, 404.

## Branches de la lignée

`livrable/4` est la branche de livraison, alimentée par des branches de travail
fusionnées en `--no-ff` : `livrable/4-filtre`, `livrable/4-form`,
`livrable/4-jours`, `livrable/4-nav`, `livrable/4-fixtures`, `livrable/4-tests`,
`livrable/4-docs`.

La lignée précédente (`livrable/3`, refactor DTO/service/pagination complet) est
publiée sur <https://github.com/AABSmog/Symfony-Femmes> et ne partage pas
d'ancêtre avec cette branche-ci : elle part de la version rendue en cours, pas de
celle retravaillée.

## Prochaine séance (annoncée)

Validation des données portées par le DTO formulaire et gestion des sessions.

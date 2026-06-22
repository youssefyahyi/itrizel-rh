---
description: Audit fonctionnel Itrizel-RH — contrôle des règles métier sans correction automatique
---

# Skill : Audit RH

Ce skill lance l'audit fonctionnel du module RH et présente les résultats de façon structurée.

## Ce que fait le skill

1. Exécute `php artisan rh:audit` dans `C:\xampp\htdocs\itrizel-rh`
2. Présente les anomalies groupées par niveau et section
3. Ne corrige rien — sortie en lecture seule

## Instruction

Lance la commande suivante et présente le rapport complet à l'utilisateur :

```
cd C:\xampp\htdocs\itrizel-rh && php artisan rh:audit
```

Présente les résultats en groupant par niveau :
- 🔴 **ERREURS** — violations de règles métier bloquantes
- 🟡 **ATTENTIONS** — incohérences à examiner
- 🔵 **INFOS** — observations non bloquantes

Après le rapport, propose une synthèse des points les plus critiques et attends les instructions de l'utilisateur avant toute correction.

## Contrôles effectués

| Section | Règles |
|---|---|
| Contrats | CDD sans date_fin, en_cours expiré, doublons actifs, salaire nul, date_debut > date_fin, sans fiche de poste |
| Employés | Actif sans contrat, inactif avec contrat, sans fiche de poste, sans date embauche, date embauche > date contrat, sans unité |
| Bulletins | Net négatif, double bulletin, salaire nul, bulletin sans contrat sur la période |
| Congés | date_debut > date_fin, nb_jours invalide, chevauchements approuvés |
| Absences | date_debut > date_fin, chevauchement avec congé approuvé |
| Paramétrage | Taux CNSS/AMO/IR hors plages réglementaires marocaines |

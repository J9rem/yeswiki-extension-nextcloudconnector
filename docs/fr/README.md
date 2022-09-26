# Extension nextcloud connector

Extension [YesWiki](https://yeswiki.net/) pour synchroniser des fichiers depuis Nextcloud

## Configuration

 1. Se connecter à la session Nextcloud avec le compte concerné, via l'interface web.
 2. Se rendre dans l'interface web Nextcloud dans `Paramètres` > Onglet/Menu `Sécurité`
 3. En bas, taper un nom d'application (ex. `YesWiki`), et cliquer sur `Créer un nouveau mot de passe d'application`
 4. Noter l'identifiant et le mot de passe fourni
 5. Se rendre sur votre `YesWiki` sur la page `GererConfig` et entrer les informations de connexions dans la partie `Nextcloud connector`

## Utilisation dans une page ou un champ `textelong`

- éditer la page/la fiche
- cliquer sur le bouton `Composants` dans la barre d'édition concernée
- choisir `Nextcloud connector`
- puis choisir l'action `{{nextcloudconnectorattach}}`
- compléter ce qui est demandé en précisant bien le lien vers le fichier qui DOIT être le LIEN INTERNE du serveur nextcloud concerné
- les autres paramètres sont ceux de l'action `{{attach}}`

## Utlisation dans un champ `fichier`

- se rendre dans la page de modification du formulaire concerné
- si le champ n'existe pas encore : ajouter le champ `Fichier (local ou nextcloud)` (le reste est alors identique au champ `fichier` standard)
- si le champ existe déjà :
    - se rendre sur la page d'édition du formulaire en mode texte
    - remplacer le début de la ligne concernée `fichier***` par `nextcloudconnectorfichier***`
    - sauvegarder
    - ne rien changer au nom du champ pour éviter d'invalider les données existantes pour les fiches déjà créées
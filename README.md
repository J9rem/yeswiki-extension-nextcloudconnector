# yeswiki-extension-nextcloudconnector

 - [English](#english)
 - [Français](#français)

## English

Extension [YesWiki](https://yeswiki.net/) to synchronize files from Nextcloud

### Authors

 - Jérémy Dufraisse
 - Elycoop
 - and all contributors indicated on this page : <https://github.com/YesWiki/yeswiki-extension-nextcloudconnector/graphs/contributors>

### Install

In page `GererMisesAJour` on your YesWiki website, search extension `Nextcloud Connector` and install it.

### Configuration

 1. Connect Nextcloud session with concerned account, via web interface.
 2. In Nextcloud web interface, go to `Parameters` > Tab/Menu `Security`
 3. At bottom, type an application name (ex. `YesWiki`), and click on `Create a new application password`
 4. Note furnihsed username and password
 5. Go on your `YesWiki` website on page `GererConfig` and put connection data in part `Nextcloud connector`

### [Usage](https://github.com/YesWiki/yeswiki-extension-nextcloudconnector/blob/master/docs/en/README.md)

Actions are available in button `components` when editing a page.
 - **In a page or a field `textelong`** :
   - edit page/entry
   - click on button `Components` in concerned edit bar
   - choose `Nextcloud connector`
   - then choose action `nextcloudconnectorattach`
   - fill what is needed by giving the link to the file which MUST be an INTERNAL LINK to concerned nextcloud server
   - others parameters are the same as `{{attach}}` action's one
 - **in field `fichier`  (file)** :
   - go to the edit page for the concerned form
   - if the field does not exist : add the field `File (local or nextcloud)` (others parameters are the same as standard `fichier` field's one)
   - if the field already exists :
     - go to the text mode edit page for the concerned form
     - replace the beginning of the concerned line `fichier***` by `nextcloudconnectorfichier***`
     - save in text mode
     - change nothing to the fieldname to prevent invalisation of existing data for already created entries

### Warranty

Like written in the licence file, there is no warranty on usage of this software. Refer to licence file for details.
Developpers of this extension can not be responsible of consequences of the usage of this extension.

## Français

Extension [YesWiki](https://yeswiki.net/) pour synchroniser des fichiers depuis Nextcloud

### Auteurs

 - Jérémy Dufraisse
 - Elycoop
 - et tous les contributeurs et toutes les contributrices indiqués sur cette page : <https://github.com/YesWiki/yeswiki-extension-nextcloudconnector/graphs/contributors>

### Installation

Dans la page `GererMisesAJour` de votre YesWiki, recherchez l'extension `Nextcloud Connector` et installez-là.

### Configuration

 1. Se connecter à la session Nextcloud avec le compte concerné, via l'interface web.
 2. Se rendre dans l'interface web Nextcloud dans `Paramètres` > Onglet/Menu `Sécurité`
 3. En bas, taper un nom d'application (ex. `YesWiki`), et cliquer sur `Créer un nouveau mot de passe d'application`
 4. Noter l'identifiant et le mot de passe fourni
 5. Se rendre sur votre `YesWiki` sur la page `GererConfig` et entrer les informations de connexions dans la partie `Nextcloud connector`

### [Utilisation](https://github.com/YesWiki/yeswiki-extension-nextcloudconnector/blob/master/docs/fr/README.md)

 - **Dans une page ou un champ `textelong`** :
   - éditer la page/la fiche
   - cliquer sur le bouton `Composants` dans la barre d'édition concernée
   - choisir `Nextcloud connector`
   - puis choisir l'action `nextcloudconnectorattach`
   - compléter ce qui est demandé en précisant bien le lien vers le fichier qui DOIT être le LIEN INTERNE du serveur nextcloud concerné
   - les autres paramètres sont ceux de l'action `{{attach}}`
 - **Dans un champ `fichier`** :
   - se rendre dans la page de modification du formulaire concerné
   - le champ n'existe pas encore : ajouter le champ `Fichier (local ou nextcloud)` (le reste est alors identique au champ `fichier` standard)
   - le champ exsite déjà :
     - se rendre sur la page d'édition du formulaire en mode texte
     - remplacer le début de la ligne concernée `fichier***` par `nextcloudconnectorfichier***`
     - sauvegarder
     - ne rien changer au nom du champ pour éviter d'invalider les données existantes pour les fiches déjà créées

### Garantie

Comme énoncé dans le fichier de licence, il n'y a pas de garantie sur l'usage de ce logiciel. Référer au fichier de licence pour les détails.
Les développeurs de cette extension ne peuvent être responsables des conséquences qui découlent de l'usage de cette extension.
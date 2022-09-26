# Extension nextcloud connector

Extension [YesWiki](https://yeswiki.net/) to synchronize files from Nextcloud

## Configuration

 1. Connect Nextcloud session with concerned account, via web interface.
 2. In Nextcloud web interface, go to `Parameters` > Tab/Menu `Security`
 3. At bottom, type an application name (ex. `YesWiki`), and click on `Create a new application password`
 4. Note furnihsed username and password
 5. Go on your `YesWiki` website on page `GererConfig` and put connection data in part `Nextcloud connector`

## Usage in a page or a field `textelong`

- edit page/entry
- click on button `Components` in concerned edit bar
- choose `Nextcloud connector`
- then choose action `nextcloudconnectorattach`
- fill what is needed by giving the link to the file which MUST be an INTERNAL LINK to concerned nextcloud server
- others parameters are the same as `{{attach}}` action's one

## Usage in field `fichier`  (file)

- go to the edit page for the concerned form
- if the field does not exist : add the field `File (local or nextcloud)` (others parameters are the same as standard `fichier` field's one)
- if the field already exists :
   - go to the text mode edit page for the concerned form
   - replace the beginning of the concerned line `fichier***` by `nextcloudconnectorfichier***`
   - save in text mode
   - change nothing to the fieldname to prevent invalisation of existing data for already created entries

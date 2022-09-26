<?php

/*
 * This file is part of the YesWiki Extension nextcloudconnector.
 *
 * Authors : see README.md file that was distributed with this source code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
return [
    // core/actions/EditConfigAction.php
    'EDIT_CONFIG_GROUP_NEXTCLOUDCONNECTOR' => 'Extension "Nextcloud connector"',
    'EDIT_CONFIG_HINT_NEXTCLOUDCONNECTOR[SERVERNAME]' => 'Url du serveur nextcloud',
    'EDIT_CONFIG_HINT_NEXTCLOUDCONNECTOR[USERNAME]' => 'Identifiant',
    'EDIT_CONFIG_HINT_NEXTCLOUDCONNECTOR[APPLICATIONPASSWORD]' => 'Mot de passe d\'application',

    // actions/documentation.yaml
    'AB_NEXTCLOUDCONNECTOR_FILE_HINT' => 'Fournir l\'url interne du fichier. Ex. : https://example.org/nextcloud/f/18455 ou https://example.org/nextcloud/apps/onlyoffice/18455 ou https://nextcloud.example.org/apps/files/?dir=/my-folder&openfile=18455',
    'AB_NEXTCLOUDCONNECTOR_FILETYPE_LABEL' => 'Type de fichier',
    'AB_NEXTCLOUDCONNECTOR_FILETYPE_GENERAL' => 'Général',
    'AB_NEXTCLOUDCONNECTOR_FILETYPE_IMAGE' => 'Image (sauf svg)',
    'AB_NEXTCLOUDCONNECTOR_FILETYPE_SVG' => 'SVG',
    'AB_NEXTCLOUDCONNECTOR_FILETYPE_PDF' => 'PDF',
    'AB_NEXTCLOUDCONNECTOR_REFRESHTIME_LABEL' => 'Temps de rafraîchissement de la copie locale',
    'AB_NEXTCLOUDCONNECTOR_REFRESHTIME_ONE_MINUTE' => 'Une minute',
    'AB_NEXTCLOUDCONNECTOR_REFRESHTIME_HALF_AN_HOUR' => 'Une demi-heure',
    'AB_NEXTCLOUDCONNECTOR_REFRESHTIME_ONE_HOUR' => 'Une heure',
    'AB_NEXTCLOUDCONNECTOR_REFRESHTIME_ONE_DAY' => 'Un jour',
    'AB_NEXTCLOUDCONNECTOR_REFRESHTIME_ONE_MONTH' => 'Un mois',

    // services/NextcloudConnectorService.php
    'NEXTCLOUDCONNECTOR_BAD_SERVERNAME_CONFIG' => 'Le paramètre "nextcloudconnector[\'servername\']" est mal configuré dans "wakka.config.php" et devrait être une url.',
    'NEXTCLOUDCONNECTOR_BAD_FILEURL' => 'Le paramètre "fileurl" de l\'action "nextcloudconnectorattach" ne correspond à l\'adresse d\'un fichier du serveur nextcloud.',
    'NEXTCLOUDCONNECTOR_NOT_POSSIBLE_FIND_FILEINFO' => 'Il n\'a pas été possible d\'extraire les informations associées au fichier %{fileId} !',
    'NEXTCLOUDCONNECTOR_NOT_POSSIBLE_TO_UPDATE_FILE' => 'Il n\'a pas été possible de mettre à jour le fichier %{fileUrl} !',

    // templates/bazar/inputs/file-nextcloud-connector.twig
    'NEXTCLOUDCONNECTOR_FILE_INPUT' => 'Fichier local',
    'NEXTCLOUDCONNECTOR_NEXTCLOUD_INPUT' => 'Fichier sur un Nextcloud',
    'NEXTCLOUDCONNECTOR_NEXTCLOUD_URL_PLACEHOLDER' => 'Fournir l\'url interne du fichier.',
    'NEXTCLOUDCONNECTOR_NEXTCLOUD_REFRESH_TIME' => 'Temps de rafraîchissement',
];

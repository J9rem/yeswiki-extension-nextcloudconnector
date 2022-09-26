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
    // actions/EditConfigAction.php
    'EDIT_CONFIG_GROUP_NEXTCLOUDCONNECTOR' => '"Nextcloud connector" extension',
    'EDIT_CONFIG_HINT_NEXTCLOUDCONNECTOR[SERVERNAME]' => 'Nextcloud server\'s url',
    'EDIT_CONFIG_HINT_NEXTCLOUDCONNECTOR[USERNAME]' => 'Username',
    'EDIT_CONFIG_HINT_NEXTCLOUDCONNECTOR[APPLICATIONPASSWORD]' => 'Application password',

    // actions/documentation.yaml
    'AB_NEXTCLOUDCONNECTOR_FILE_HINT' => 'Give intenal file url. Ex. : https://example.org/nextcloud/f/18455 or https://example.org/nextcloud/apps/onlyoffice/18455 or https://nextcloud.example.org/apps/files/?dir=/my-folder&openfile=18455',
    'AB_NEXTCLOUDCONNECTOR_FILETYPE_LABEL' => 'File type',
    'AB_NEXTCLOUDCONNECTOR_FILETYPE_GENERAL' => 'General',
    'AB_NEXTCLOUDCONNECTOR_FILETYPE_IMAGE' => 'Image (except svg)',
    'AB_NEXTCLOUDCONNECTOR_FILETYPE_SVG' => 'SVG',
    'AB_NEXTCLOUDCONNECTOR_FILETYPE_PDF' => 'PDF',
    'AB_NEXTCLOUDCONNECTOR_REFRESHTIME_LABEL' => 'Refresh time for local copy',
    'AB_NEXTCLOUDCONNECTOR_REFRESHTIME_ONE_MINUTE' => 'One minute',
    'AB_NEXTCLOUDCONNECTOR_REFRESHTIME_HALF_AN_HOUR' => 'Half an hour',
    'AB_NEXTCLOUDCONNECTOR_REFRESHTIME_ONE_HOUR' => 'An hour',
    'AB_NEXTCLOUDCONNECTOR_REFRESHTIME_ONE_DAY' => 'A day',
    'AB_NEXTCLOUDCONNECTOR_REFRESHTIME_ONE_MONTH' => 'A month',

    // services/NextcloudConnectorService.php
    'NEXTCLOUDCONNECTOR_BAD_SERVERNAME_CONFIG' => 'The "nextcloudconnector[\'servername\']" param is badly set in "wakka.config.php" and should be an url.',
    'NEXTCLOUDCONNECTOR_BAD_FILEURL' => 'The parameter "fileurl" of "nextcloudconnectorattach" action does not correspond to address of file from nextcloud server.',
    'NEXTCLOUDCONNECTOR_NOT_POSSIBLE_FIND_FILEINFO' => 'It has not been possible to extract information associated to file %{fileId} !',
    'NEXTCLOUDCONNECTOR_NOT_POSSIBLE_TO_UPDATE_FILE' => 'It has not been possible to update file %{fileUrl} !',

    // templates/bazar/inputs/file-nextcloud-connector.twig
    'NEXTCLOUDCONNECTOR_FILE_INPUT' => 'Local file',
    'NEXTCLOUDCONNECTOR_NEXTCLOUD_INPUT' => 'File on a Nextcloud',
    'NEXTCLOUDCONNECTOR_NEXTCLOUD_URL_PLACEHOLDER' => 'Give intenal file url.',
    'NEXTCLOUDCONNECTOR_NEXTCLOUD_REFRESH_TIME' => 'Refresh time',
];

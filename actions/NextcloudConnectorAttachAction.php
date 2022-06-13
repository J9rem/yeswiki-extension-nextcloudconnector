<?php

/*
 * This file is part of the YesWiki Extension nextcloudconnector.
 *
 * Authors : see README.md file that was distributed with this source code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace YesWiki\NextcloudConnector;

use YesWiki\Core\YesWikiAction;
use YesWiki\Nextcloudconnector\Exception\NextcloudException;
use YesWiki\Nextcloudconnector\Service\NextcloudConnectorService;

class NextcloudConnectorAttachAction extends YesWikiAction
{
    protected $nextcloudConnectorService;

    public function formatArguments($arg)
    {
        return [
            'fileurl' => isset($arg['fileurl']) && is_string($arg['fileurl']) ? $arg['fileurl'] : '',
            'file' => '',
            'refreshtime' => isset($arg['refreshtime']) && is_scalar($arg['refreshtime']) ? abs(intval($arg['refreshtime'])) : 0,
        ];
    }

    public function run()
    {
        // get services
        $this->nextcloudConnectorService = $this->getservice(NextcloudConnectorService::class);

        try {
            $fileId = $this->nextcloudConnectorService->getIdFromUrl($this->arguments['fileurl']);
            $fData = $this->nextcloudConnectorService->getFilenameFromId($fileId);
            $this->nextcloudConnectorService->updateFileIfNeeded($fData, $this->arguments['refreshtime']);

            $attachArgs = $this->arguments;
            unset($attachArgs['fileurl']);
            unset($attachArgs['refreshtime']);
            $attachArgs['file'] = $fData['filename'];
            return $this->callAction('attach', $attachArgs);
        } catch (NextcloudException $ex) {
            return $this->render("@templates/alert-message.twig", [
                'type' => 'danger',
                'message' => $ex->getMessage(),
            ]);
        }
    }
}

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

class NextcloudConnectorAttachAction extends YesWikiAction
{
    public function formatArguments($arg)
    {
        return [
            'fileurl' => isset($arg['fileurl']) && is_string($arg['fileurl']) ? $arg['fileurl'] : '',
            'file' => '',
        ];
    }

    public function run()
    {
        return $this->callAction('attach', $this->arguments);
    }
}

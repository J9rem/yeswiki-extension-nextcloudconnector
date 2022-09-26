<?php

/*
 * This file is part of the YesWiki Extension nextcloudconnector.
 *
 * Authors : see README.md file that was distributed with this source code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace YesWiki\Nextcloudconnector\Field;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use YesWiki\Bazar\Field\FileField;
use YesWiki\Bazar\Service\EntryManager;
use YesWiki\Nextcloudconnector\Exception\NextcloudException;
use YesWiki\Nextcloudconnector\Service\NextcloudConnectorService;

/**
 * @Field({"nextcloudconnectorfichier"})
 */
class NextcloudConnectorFileField extends FileField
{
    protected $nextcloudConnectorPropertyName ;
    protected $nextcloudConnectorRefreshTimePropertyName ;

    public function __construct(array $values, ContainerInterface $services)
    {
        parent::__construct($values, $services);

        $this->type = "fichier";
        $this->propertyName = $this->type . $this->name;
        $this->nextcloudConnectorPropertyName =  "nextcloudconnectorfichier" . $this->name;
        $this->nextcloudConnectorRefreshTimePropertyName =  "nextcloudconnectorfichierRefreshTime" . $this->name;
    }

    protected function renderInput($entry)
    {
        $value = $this->getValue($entry);

        $deletedFile = false;

        if (!empty($value)) {
            if (!empty($entry) && isset($_GET['delete_file']) && $_GET['delete_file'] === $value) {
                if ($this->isAllowedToDeleteFile($entry, $value)) {
                    if (substr($value, 0, strlen($this->defineFilePrefix($entry))) == $this->defineFilePrefix($entry)) {
                        $attach = $this->getAttach();
                        $rawFileName = filter_input(INPUT_GET, 'delete_file', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                        $attach->fmDelete($rawFileName);
                    } else {
                        // do not delete file if not same entry name (only remove from this entry)
                        $deletedFile = true;
                        $this->updateEntryAfterFileDelete($entry);
                    }
                } else {
                    $alertMessage = '<div class="alert alert-info">' . _t('BAZ_DROIT_INSUFFISANT') . '</div>' . "\n";
                }
            }
        }

        return ($alertMessage ?? '') . $this->render('@bazar/inputs/file-nextcloud-connector.twig', (
            $deletedFile || (!$this->isNextcloudFile($entry) && (empty($value) ||  !file_exists($this->getBasePath().  $value)))
            ? []
            : [
                'value' => $value,
                'shortFileName' => empty($value) ? "" : $this->getShortFileName($value),
                'fileUrl' => empty($value) ? "" : $this->getBasePath().  $value,
                'deleteUrl' => empty($value) ? "" : (empty($entry) ? '' : $this->getWiki()->href('edit', $entry['id_fiche'], ['delete_file' => $value], false)),
                'isAllowedToDeleteFile' => empty($entry) ? false : $this->isAllowedToDeleteFile($entry, $value),
                'nextcloudLink' => $this->isNextcloudFile($entry) ? $entry[$this->nextcloudConnectorPropertyName] : "",
                'refreshTime' => $this->isNextcloudFile($entry) ? $this->getRefreshTime($entry) : "",
            ]
        ));
    }

    public function formatValuesBeforeSave($entry)
    {
        $isNextcloud = $this->isNextcloudFile($entry);
        if ($isNextcloud) {
            // update file
            unset($_FILES[$this->propertyName]);
            $data = $this->updateFile($entry);
            if (!empty($data['message'])) {
                flash($data['message'], 'error');
                $entry[$this->propertyName] = "";
            } else {
                $entry[$this->propertyName] = $data['filename'];
            }
        }
        $result = parent::formatValuesBeforeSave($entry);
        if ($isNextcloud) {
            $result[$this->nextcloudConnectorPropertyName] = $entry[$this->nextcloudConnectorPropertyName];
            $result[$this->nextcloudConnectorRefreshTimePropertyName] = $this->getRefreshTime($entry);
        } else {
            if (!isset($result['fields-to-remove']) || !is_array($result['fields-to-remove'])) {
                $result['fields-to-remove'] = [];
            }
            $result['fields-to-remove'][] = $this->nextcloudConnectorPropertyName;
            $result['fields-to-remove'][] = $this->nextcloudConnectorRefreshTimePropertyName;
        }
        return $result;
    }

    protected function renderStatic($entry)
    {
        if ($this->isNextcloudFile($entry)) {
            // update file if needed
            $data = $this->updateFile($entry);
            if (!empty($data['message'])) {
                return $this->render("@templates/alert-message.twig", [
                    'message' => $data['message'],
                    'type' => 'danger'
                ]);
            } else {
                $entry[$this->propertyName] = $data['filename'];
            }
        }

        $value = $this->getValue($entry);

        $basePath = $this->getBasePath() ;
        if (!empty($value) && file_exists($basePath.$value)) {
            $shortFileName = $this->getShortFileName($value);
            return $this->render('@bazar/fields/file.twig', [
                'value' => $value,
                'fileUrl' => ($shortFileName == $value)
                    ? $this->getWiki()->getBaseUrl().'/'.$basePath . $value
                    : $this->getWiki()->Href('download', $entry['id_fiche']."_".$this->getPropertyName(), ['file'=>$value], false),
                'shortFileName' => $shortFileName,
            ]);
        }

        return null;
    }

    public function getNextcloudConnectorPropertyName(): string
    {
        return $this->nextcloudConnectorPropertyName;
    }

    public function getNextcloudConnectorRefreshTimePropertyName(): string
    {
        return $this->nextcloudConnectorRefreshTimePropertyName;
    }

    // change return of this method to keep compatible with php 7.3 (mixed is not managed)
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            [
              'nextcloudConnectorPropertyName' => $this->getNextcloudConnectorPropertyName(),
              'nextcloudConnectorRefreshTimePropertyName' => $this->getNextcloudConnectorRefreshTimePropertyName(),
            ]
        );
    }

    private function isNextcloudFile($entry): bool
    {
        return !empty($entry[$this->nextcloudConnectorPropertyName]) && filter_var($entry[$this->nextcloudConnectorPropertyName], FILTER_VALIDATE_URL);
    }

    private function updateFile($entry): array
    {
        $nextcloudConnectorService = $this->getservice(NextcloudConnectorService::class);
        try {
            $fileId = $nextcloudConnectorService->getIdFromUrl($entry[$this->nextcloudConnectorPropertyName]);
            $fData = $nextcloudConnectorService->getFilenameFromId($fileId);
            
            $wiki = $this->getWiki();
            $previousPageTag = $wiki->tag;
            $wiki->tag = $entry['id_fiche'];
            $fullfilename = $nextcloudConnectorService->updateFileIfNeeded($fData, (int)$this->getRefreshTime($entry));
            $wiki->tag = $previousPageTag;
        } catch (NextcloudException $ex) {
            return ['message' => $ex->getMessage(),'filename'=> ''];
        }
        return ['message' => "",'filename'=> $fullfilename];
    }

    private function getRefreshTime($entry): string
    {
        return empty($entry[$this->nextcloudConnectorRefreshTimePropertyName]) || !is_scalar($entry[$this->nextcloudConnectorRefreshTimePropertyName]) ||
            intval($entry[$this->nextcloudConnectorRefreshTimePropertyName]) < 0
            ? 0
            : intval($entry[$this->nextcloudConnectorRefreshTimePropertyName]);
    }
    
    protected function updateEntryAfterFileDelete($entry)
    {
        $entryManager = $this->services->get(EntryManager::class);

        // unset value in entry from db without modifier from GET
        $entryFromDb = $entryManager->getOne($entry['id_fiche']);
        if (!empty($entryFromDb)) {
            $previousGet = $_GET;
            $_GET = ['wiki' => $previousGet['wiki']];
            $previousPost = $_POST;
            $_POST= [];
            $previousRequest = $_REQUEST;
            $_REQUEST = [];
            unset($entryFromDb[$this->propertyName]);
            unset($entryFromDb[$this->nextcloudConnectorPropertyName]);
            unset($entryFromDb[$this->nextcloudConnectorRefreshTimePropertyName]);
            $entryFromDb['antispam'] = 1;
            $entryFromDb['date_maj_fiche'] = date('Y-m-d H:i:s', time());
            $entryManager->update($entryFromDb['id_fiche'], $entryFromDb, false, true);
            
            $_GET = $previousGet;
            $_POST = $previousPost;
            $_REQUEST = $previousRequest;
        }
    }
}

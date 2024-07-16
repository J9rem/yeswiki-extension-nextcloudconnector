<?php

/*
 * This file is part of the YesWiki Extension nextcloudconnector.
 *
 * Authors : see README.md file that was distributed with this source code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace YesWiki\Nextcloudconnector\Service;

use attach;
use DateInterval;
use DateTime;
use Sabre\DAV\Client as SabreDavClient;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use YesWiki\Nextcloudconnector\Exception\NextcloudException;
use YesWiki\Wiki;

class NextcloudConnectorService
{
    private const CACHE_FOLDER = "cache";
    private const CACHE_PREFIX = "fileinfo-";

    protected $attach;
    protected $nextcloudparams;
    protected $servername ;
    protected $wiki ;

    public function __construct(
        ParameterBagInterface $params,
        Wiki $wiki
    ) {
        $this->attach = null;
        $this->wiki = $wiki;
        $this->nextcloudparams = $params->get('nextcloudconnector');
        $this->servername = isset($this->nextcloudparams['servername']) ? $this->nextcloudparams['servername'] . (
            substr($this->nextcloudparams['servername'], -1) != "/"
            ? "/"
            : ""
        ) : '';
    }

    /**
     * retrieve file id from url
     * @param string $fileurl
     * @return string $fileId
     * @throws NextcloudException
     */
    public function getIdFromUrl(string $fileurl): string
    {
        if (empty($this->servername) || !filter_var($this->servername, FILTER_VALIDATE_URL)) {
            throw new NextcloudException(_t('NEXTCLOUDCONNECTOR_BAD_SERVERNAME_CONFIG'));
        }
        $pregQuotedServerName = preg_quote($this->servername, "/");
        $quotedSlash = preg_quote("/", "/");
        if (!preg_match("/^{$pregQuotedServerName}(?:f$quotedSlash|apps{$quotedSlash}onlyoffice$quotedSlash|apps{$quotedSlash}files{$quotedSlash}\?dir=[^&]+&openfile=)([0-9]+)/", $fileurl, $matches)) {
            throw new NextcloudException(_t('NEXTCLOUDCONNECTOR_BAD_FILEURL'));
        }
        return $matches[1];
    }

    /**
     * retrieve filename form id using cache
     * @param string $fileId
     * @param bool $forced
     * @return array $fData ['filename'=>$filename,'dirname'=>$dirname,'fileId'=>$fileId]
     * @throws NextcloudException
     */
    public function getFilenameFromId(string $fileId, bool $forced = false): array
    {
        $cachefilename = self::CACHE_FOLDER . "/" . self::CACHE_PREFIX . "{$this->sanitizeFileName($this->servername)}-$fileId.json";
        if (!$forced && file_exists($cachefilename)) {
            $fileinfo = json_decode(file_get_contents($cachefilename), true);
            $filename = $fileinfo['filename'] ?? '';
            $dirname = $fileinfo['dirname'] ?? '';
        }
        if (empty($filename) || empty($dirname)) {
            $url = "{$this->servername}f/$fileId";

            $fp_tmp = tmpfile();
            $fp_fullpath = stream_get_meta_data($fp_tmp)['uri'];
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_USERNAME, $this->nextcloudparams['username'] ?? '');
            curl_setopt($ch, CURLOPT_PASSWORD, $this->nextcloudparams['applicationPassword'] ?? '');
            curl_setopt($ch, CURLOPT_FILE, $fp_tmp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_exec($ch);
            curl_close($ch);
            $content = file_get_contents($fp_fullpath);
            fclose($fp_tmp);

            $lines = explode("\n", $content);
            $location = '';
            foreach ($lines as $line) {
                if (substr($line, 0, strlen("Location: ")) == "Location: ") {
                    $location = substr($line, strlen("Location: "));
                }
            }
            if (!preg_match("/^\/apps\/files\/\?dir=([^&]+)(?:&openfile=$fileId)?&scrollto=([^&]+)(?:&openfile=$fileId)?\s*$/", $location, $matches)) {
                throw new NextcloudException(_t('NEXTCLOUDCONNECTOR_NOT_POSSIBLE_FIND_FILEINFO', ['fileId' => "$fileId"]));
            }
            $filename = urldecode(preg_replace("/\s*$/", "", $matches[2]));
            $dirname = urldecode($matches[1]);
            file_put_contents($cachefilename, json_encode(['filename' => $filename,'dirname' => $dirname]));
        }
        if (empty($filename) || empty($dirname)) {
            throw new NextcloudException(_t('NEXTCLOUDCONNECTOR_NOT_POSSIBLE_FIND_FILEINFO', ['fileId' => $fileId]));
        }
        return ['filename' => $filename,'dirname' => $dirname,'fileId' => $fileId];
    }

    /**
     * updateFileIfNeeded
     * @param array $fData
     * @param int $maxAge
     * @param bool $alreadyForced
     * @return string $fullFileName
     * @throws NextcloudException
     */
    public function updateFileIfNeeded(array $fData, int $maxAge, bool $alreadyForced = false): string
    {
        $attach = $this->getAttach();
        $files = $attach->fmGetFiles(false);

        $foundFiles = [];

        foreach ($files as $key => $filedata) {
            if (!empty($filedata['name']) && !empty($filedata['ext']) && $attach->sanitizeFilename("{$filedata['name']}.{$filedata['ext']}") == $attach->sanitizeFilename($fData['filename'])) {
                if (empty($filedata['dateupload']) ||
                    (new DateTime($filedata['dateupload']))
                    ->add(new DateInterval("PT{$maxAge}S"))
                    ->diff(new DateTime())
                    ->invert == 0
                ) {
                    $attach->fmDelete($filedata['realname']);
                    $updatedFiles = $attach->fmGetFiles(true);
                    foreach ($updatedFiles as $newFData) {
                        if (
                            !empty($newFData['name']) &&
                            !empty($newFData['ext']) &&
                            $newFData['name'] == $filedata['name'] &&
                            $newFData['ext'] == $filedata['ext'] &&
                            !empty($newFData['trashdate'])) {
                            if (file_exists("files/{$newFData['realname']}")) {
                                unlink("files/{$newFData['realname']}");
                            }
                        }
                    }
                } else {
                    $foundFiles[] = $filedata;
                }
            }
        }
        if (empty($foundFiles)) {
            $sabreWebDavClient = $this->getSabreWebDavClient();
            $fileUrl = "{$this->servername}remote.php/dav/files/{$this->nextcloudparams['username']}{$fData['dirname']}/{$fData['filename']}";
            // request($method, $url = '', $body = null, array $headers = [])
            $fileResponse = $sabreWebDavClient->request('GET', $fileUrl);
            if (empty($fileResponse['body']) || empty($fileResponse['statusCode']) || $fileResponse['statusCode'] != 200) {
                if ($alreadyForced) {
                    throw new NextcloudException(_t('NEXTCLOUDCONNECTOR_NOT_POSSIBLE_TO_UPDATE_FILE', ['fileUrl' => $fileUrl]));
                } else {
                    $fData = $this->getFilenameFromId($fData['fileId'], true);
                    $this->updateFileIfNeeded($fData, $maxAge, true);

                    $attach->file = $fData['filename'];
                    $fullFileName = $attach->GetFullFilename(true);
                    if (!file_exists("files/$fullFileName")) {
                        throw new NextcloudException(_t('NEXTCLOUDCONNECTOR_NOT_POSSIBLE_TO_UPDATE_FILE', ['fileUrl' => $fileUrl]));
                    }
                }
            } else {
                $fileContent = $fileResponse['body'];
                $attach->file = $fData['filename'];
                $fullFileName = $attach->GetFullFilename(true);
                file_put_contents($fullFileName, $fileContent);
            }
            return preg_replace("/^" . preg_quote($attach->GetUploadPath(), "/") . "\//", "", $fullFileName);
        }
        return $foundFiles[0]['realname'];
    }

    /**
     * @return SabreDavClient $sabreDavClient
     */
    private function getSabreWebDavClient(): SabreDavClient
    {
        $url = "{$this->servername}remote.php/dav";
        return new SabreDavClient([
            'baseUri' => $url,
            'userName' => $this->nextcloudparams['username'] ?? '',
            'password' => $this->nextcloudparams['applicationPassword'] ?? ''
        ]);
    }


    /**
     * sanitize file name
     * @param string $inputString
     * @return string $outputString
     */
    private function sanitizeFileName(string $inputString): string
    {
        return removeAccents(preg_replace('/--+/u', '-', preg_replace('/[[:punct:]]/', '-', $inputString)));
    }

    protected function getAttach(): attach
    {
        if (is_null($this->attach)) {
            if (!class_exists('attach')) {
                include('tools/attach/libs/attach.lib.php');
            }

            $this->attach = new attach($this->wiki);
        }
        return $this->attach;
    }
}

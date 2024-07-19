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

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use YesWiki\Nextcloudconnector\Exception\NextcloudException;
use YesWiki\Wiki;

class NextcloudConnectorService
{
    private const CACHE_FOLDER = 'cache';
    private const CACHE_PREFIX = 'fileinfo-';

    protected $attach;
    protected $nextcloudparams;
    protected $servername;
    protected $wiki;

    public function __construct(
        ParameterBagInterface $params,
        Wiki $wiki
    ) {
        $this->attach = null;
        $this->wiki = $wiki;
        $this->nextcloudparams = $params->get('nextcloudconnector');
        $this->servername = isset($this->nextcloudparams['servername']) ? $this->nextcloudparams['servername'] . (
            substr($this->nextcloudparams['servername'], -1) != '/'
            ? '/'
            : ''
        ) : '';
    }

    /**
     * retrieve file id from url.
     *
     * @return string $fileId
     *
     * @throws NextcloudException
     */
    public function getIdFromUrl(string $fileurl): string
    {
        if (empty($this->servername) || !filter_var($this->servername, FILTER_VALIDATE_URL)) {
            throw new NextcloudException(_t('NEXTCLOUDCONNECTOR_BAD_SERVERNAME_CONFIG'));
        }
        $pregQuotedServerName = preg_quote($this->servername, '/');
        $quotedSlash = preg_quote('/', '/');
        if (!preg_match("/^{$pregQuotedServerName}(?:f$quotedSlash|apps{$quotedSlash}onlyoffice$quotedSlash|apps{$quotedSlash}files{$quotedSlash}(?:files{$quotedSlash})?\?dir=[^&]+&openfile=)([0-9]+)/", $fileurl, $matches)) {
            throw new NextcloudException(_t('NEXTCLOUDCONNECTOR_BAD_FILEURL'));
        }

        return $matches[1];
    }

    /**
     * retrieve filename form id using cache.
     *
     * @return array $fData ['filename'=>$filename,'dirname'=>$dirname,'fileId'=>$fileId]
     *
     * @throws NextcloudException
     */
    public function getFilenameFromId(string $fileId, bool $forced = false): array
    {
        $cachefilename = self::CACHE_FOLDER . '/' . self::CACHE_PREFIX . "{$this->sanitizeFileName($this->servername)}-$fileId.json";
        if (!$forced && file_exists($cachefilename)) {
            $fileinfo = json_decode(file_get_contents($cachefilename), true);
            $filename = $fileinfo['filename'] ?? '';
            $dirname = $fileinfo['dirname'] ?? '';
        }
        if (empty($filename) || empty($dirname)) {
            $lines = $this->extractHeaders(
                "{$this->servername}f/$fileId",
                $this->nextcloudparams['username'] ?? '',
                $this->nextcloudparams['applicationPassword'] ?? ''
            );

            $location = $this->getLocationFromHeaders($lines);

            $matches = [];
            if (preg_match("/^\/apps\/files\/\?dir=([^&]+)(?:&openfile=$fileId)?&scrollto=([^&]+)(?:&openfile=$fileId)?\s*$/", $location, $matches)) {
                $filename = urldecode(preg_replace("/\s*$/", '', $matches[2]));
                $dirname = trim(urldecode($matches[1]));
            } else {
                $matches = [];
                if (preg_match("/^.*(?:\?|&)dir=([^&]+).*$/", $location, $matches)) {
                    $dirname = trim(urldecode($matches[1]));
                    $filename = $this->getFileNameInFolderFromFileId($dirname, $fileId);
                } else {
                    throw new NextcloudException(_t('NEXTCLOUDCONNECTOR_NOT_POSSIBLE_FIND_FILEINFO', ['fileId' => "$fileId"]));
                }
            }
            file_put_contents($cachefilename, json_encode(['filename' => $filename, 'dirname' => $dirname]));
        }
        if (empty($filename) || empty($dirname)) {
            throw new NextcloudException(_t('NEXTCLOUDCONNECTOR_NOT_POSSIBLE_FIND_FILEINFO', ['fileId' => $fileId]));
        }

        return ['filename' => $filename, 'dirname' => $dirname, 'fileId' => $fileId];
    }

    /**
     * get fileName infolder from fileId.
     *
     * @return string $fileName
     */
    protected function getFileNameInFolderFromFileId(
        string $folderName,
        string $fileId
    ): string {
        $url = "{$this->servername}remote.php/dav/files/{$this->nextcloudparams['username']}$folderName";

        $sabreWebDavClient = $this->getSabreWebDavClient();
        $depth = 1;
        $data = $sabreWebDavClient->propFind($url, ['{DAV:}displayname', '{oc:}fileid'], $depth);
        if (is_array($data)) {
            foreach ($data as $remoteUrl => $fileInfo) {
                if (!empty($fileInfo['{DAV:}displayname'])
                    && !empty($fileInfo['{http://owncloud.org/ns}fileid'])
                    && $fileId === $fileInfo['{http://owncloud.org/ns}fileid']) {
                    return $fileInfo['{DAV:}displayname'];
                }
            }
        }

        return '';
    }

    /**
     * extract header of url via curl.
     *
     * @return array $lines
     */
    protected function extractHeaders(
        string $url,
        string $username,
        string $password
    ): array {
        $fp_tmp = tmpfile();
        $fp_fullpath = stream_get_meta_data($fp_tmp)['uri'];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERNAME, $username);
        curl_setopt($ch, CURLOPT_PASSWORD, $password);
        curl_setopt($ch, CURLOPT_FILE, $fp_tmp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_exec($ch);
        curl_close($ch);
        $content = file_get_contents($fp_fullpath);
        fclose($fp_tmp);

        return explode("\n", $content);
    }

    /**
     * get Location from headers.
     *
     * @return string $location
     */
    protected function getLocationFromHeaders(array $lines): string
    {
        $location = '';
        foreach ($lines as $line) {
            if (substr($line, 0, strlen('Location: ')) == 'Location: ') {
                $location = substr($line, strlen('Location: '));
            }
        }

        return $location;
    }

    /**
     * updateFileIfNeeded.
     *
     * @return string $fullFileName
     *
     * @throws NextcloudException
     */
    public function updateFileIfNeeded(array $fData, int $maxAge, bool $alreadyForced = false): string
    {
        $attach = $this->getAttach();
        $files = $attach->fmGetFiles(false);

        $foundFiles = $this->extractFiles($files, $attach, $fData, $maxAge);

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

            return preg_replace('/^' . preg_quote($attach->GetUploadPath(), '/') . "\//", '', $fullFileName);
        }

        return $foundFiles[0]['realname'];
    }

    /**
     * extrat files from files data.
     *
     * @return array $files
     */
    protected function extractFiles(array $filesData, \attach $attach, array $fData, int $maxAge): array
    {
        $foundFiles = [];
        foreach ($filesData as $key => $filedata) {
            if (
                !empty($filedata['name'])
                && !empty($filedata['ext'])
                && $attach->sanitizeFilename("{$filedata['name']}.{$filedata['ext']}") == $attach->sanitizeFilename($fData['filename'])
            ) {
                if (empty($filedata['dateupload'])
                    || (new \DateTime($filedata['dateupload']))
                    ->add(new \DateInterval("PT{$maxAge}S"))
                    ->diff(new \DateTime())
                    ->invert == 0
                ) {
                    $attach->fmDelete($filedata['realname']);
                    $updatedFiles = $attach->fmGetFiles(true);
                    foreach ($updatedFiles as $newFData) {
                        if (
                            !empty($newFData['name'])
                            && !empty($newFData['ext'])
                            && $newFData['name'] == $filedata['name']
                            && $newFData['ext'] == $filedata['ext']
                            && !empty($newFData['trashdate'])) {
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

        return $foundFiles;
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
            'password' => $this->nextcloudparams['applicationPassword'] ?? '',
        ]);
    }

    /**
     * sanitize file name.
     *
     * @return string $outputString
     */
    private function sanitizeFileName(string $inputString): string
    {
        return removeAccents(preg_replace('/--+/u', '-', preg_replace('/[[:punct:]]/', '-', $inputString)));
    }

    protected function getAttach(): \attach
    {
        if (is_null($this->attach)) {
            if (!class_exists('attach')) {
                include 'tools/attach/libs/attach.lib.php';
            }

            $this->attach = new \attach($this->wiki);
        }

        return $this->attach;
    }
}

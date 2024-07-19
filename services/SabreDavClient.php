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

use Sabre\DAV\Client;
use Sabre\HTTP;

class SabreDavClient extends Client
{
    /**
     * Does a PROPFIND request.
     *
     * The list of requested properties must be specified as an array, in clark
     * notation.
     *
     * The returned array will contain a list of filenames as keys, and
     * properties as values.
     *
     * The properties array will contain the list of properties. Only properties
     * that are actually returned from the server (without error) will be
     * returned, anything else is discarded.
     *
     * Depth should be either 0 or 1. A depth of 1 will cause a request to be
     * made to the server to also return all child resources.
     *
     * @param string $url
     * @param int    $depth
     *
     * @return array
     */
    public function propFind($url, array $properties, $depth = 0)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $root = $dom->createElementNS('DAV:', 'd:propfind');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:oc', 'http://owncloud.org/ns');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:nc', 'http://nextcloud.org/ns');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ocs', 'http://open-collaboration-services.org/ns');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ocm', 'http://open-cloud-mesh.org/ns');
        $prop = $dom->createElement('d:prop');

        foreach ($properties as $property) {
            list(
                $namespace,
                $elementName
            ) = \Sabre\Xml\Service::parseClarkNotation($property);

            $namespace = is_string($namespace) ? $namespace : '';
            switch ($namespace) {
                case 'DAV:':
                    $element = $dom->createElement('d:' . $elementName);
                    break;
                case 'oc:':
                case 'http://owncloud.org/ns:':
                    $element = $dom->createElement('oc:' . $elementName);
                    break;
                case 'nc:':
                case 'http://nextcloud.org/ns:':
                    $element = $dom->createElement('nc:' . $elementName);
                    break;
                case 'ocs:':
                case 'http://open-collaboration-services.org/ns:':
                    $element = $dom->createElement('ocs:' . $elementName);
                    break;
                case 'ocm:':
                case 'http://open-cloud-mesh.org/ns:':
                    $element = $dom->createElement('ocm:' . $elementName);
                    break;

                default:
                    $element = $dom->createElementNS($namespace, 'x:' . $elementName);
                    break;
            }

            $prop->appendChild($element);
        }

        $dom->appendChild($root)->appendChild($prop);
        $body = $dom->saveXML();

        $url = $this->getAbsoluteUrl($url);

        $request = new HTTP\Request('PROPFIND', $url, [
            'Depth' => $depth,
            'Content-Type' => 'application/xml',
        ], $body);

        $response = $this->send($request);

        if ((int)$response->getStatus() >= 400) {
            throw new HTTP\ClientHttpException($response);
        }

        $result = $this->parseMultiStatus($response->getBodyAsString());

        // If depth was 0, we only return the top item
        if (0 === $depth) {
            reset($result);
            $result = current($result);

            return isset($result[200]) ? $result[200] : [];
        }

        $newResult = [];
        foreach ($result as $href => $statusList) {
            $newResult[$href] = isset($statusList[200]) ? $statusList[200] : [];
        }

        return $newResult;
    }
}

<?php
namespace Sawjan\OcisSdk\Lib;

use SimpleXMLElement;


class Files {
    private $request;

    function __construct($request){
        $this->request = $request;
    }

    function list($path) {
        $path = trim($path, "/");
        // TODO: use webdav library
        $res = $this->request->send("PROPFIND", "/remote.php/webdav/$path");

        $body = $this->parseIntoXml($res->getBody()->getContents());

        return $this->createResponse($res->getStatusCode(), $body);
    }

    /**
     * ---------------------------------------
     * TODO: deserve a separate helper class
     * ---------------------------------------
     */
    function parseIntoXml($xmlString) {
        if (!$xmlString) {
            return null;
        }
        return new SimpleXMLElement($xmlString);
    }

    function createResponse($statuscode, $body) {
        $type = "success";
        if ($statuscode >= 400) {
            $type = "failure";
        }

        return [
            "message" => $type,
            "statuscode" => $statuscode,
            "body" => $body
        ];
    }
}
?>
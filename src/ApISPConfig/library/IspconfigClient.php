<?php
namespace ApISPConfig\Library;

class IspconfigClient extends \SoapClient {

    private $client;

    public function __construct($location, $uri) {
        try {
            $wsdl = NULL;
            $options = array('location' => $location,'uri' => $uri);
            parent::__construct($wsdl, $options);
        } catch (SoapFault $e) {
            echo 'SOAP Error: '.$e->getMessage();
            echo 'Error, please contact the server administator';
        }
    }

}
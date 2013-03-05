<?php
/**
 * Calls the Skyhook API and retrieves a street address for a given MAC address.
 *
 * This program requires: PHP5 CLI with curl.
 *
 * To run, change the MAC Address and invoke the script.
 */

$macAddress = 'D8C7C81CF2F8';
$cls = new SkyhookLookup();
echo $cls->doRequest($macAddress);

class SkyhookLookup {

    /**
     * @var String The mac address to look up.
     */
    protected $_macAddress;
    
    /**
     *
     * @var String The url of the API.
     */
    protected $_apiUrl = 'https://api.skyhookwireless.com/wps2/location';
    
    /**
     * Sets the mac address to perform the lookup on.
     *
     * @var $macAddress The mac address to request.
     */
    public function setMacAddress($macAddress) {
        $this->_macAddress = $macAddress;
    }

    function getResults($macAddress) {
        $request = utf8_encode("<?xml version='1.0'?>
            <LocationRQ xmlns='http://skyhookwireless.com/wps/2005' version='2.6' street-address-lookup='full'>
              <authentication version='2.0'>
                <simple>
                  <username>beta</username>
                  <realm>js.loki.com</realm>
                </simple>
              </authentication>
              <access-point>
                <mac>" . $macAddress . "</mac>
                <signal-strength>-50</signal-strength>
              </access-point>
            </LocationRQ>");
        if (!function_exists('curl_init')) {
	    die("You do not have the curl library for PHP installed. Please install it and make sure it is set to load in php.ini/php.conf and try again.");
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
        curl_setopt($ch, CURLOPT_URL, $this->_apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $result = curl_exec($ch);

        curl_close($ch);
        return $result;
    }

    /**
     * Performs the request.
     *
     * @param  String $macAddress
     * @return String the result of the lookup.
     */
    function doRequest($macAddress) {
        $this->_macAddress = $macAddress;
        $result = $this->getResults($macAddress);
        try {
            $xml = new SimpleXMLElement($result);
        } catch (Exception $error) {
            return("Whoops! This MAC Address does not appear to be in the skyhook database");
        }
        $location = (array) $xml->location;
        $address = (array) $location['street-address'];
        return "We found a result!\nMAC Address: \"" . $this->_macAddress . "\" was found in the Skyhook database, the location returned was:\n"
        . $address['street-number'] . " " . $address['address-line'] . "\n" . $address['city'] . ", " . $address['state'] . " " . $address['postal-code']."\n";
    }

}
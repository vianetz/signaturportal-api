<?php
/**
 * Signaturportal API Client Class
 *
 * @section LICENSE
 * This file is created by vianetz <info@vianetz.com>.
 * The code is distributed under the GPL license.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@vianetz.com so we can send you a copy immediately.
 *
 * @category    Vianetz
 * @package     Vianetz\Signaturportal
 * @author      Christoph Massmann, <C.Massmann@vianetz.com>
 * @link        https://www.vianetz.com
 * @copyright   Copyright (c) since 2017 vianetz - Dipl.-Ing. C. Massmann (http://www.vianetz.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.txt GNU GENERAL PUBLIC LICENSE
 */

namespace Vianetz\Signaturportal;

class Client
{
    /**
     * @var string
     */
    const WSDL = 'https://www.signaturportal.de/wsdl/smmi_basic.wsdl';

    /**
     * @var \SoapClient
     */
    private $soapClient;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $accountNumber;

    /**
     * @var null|string
     */
    private $region;

    /**
     * @var null|string
     */
    private $locale;

    /**
     * Client constructor.
     *
     * @param string $username
     * @param string $password
     * @param string $accountNumber
     * @param null|string $region
     * @param null|string $locale
     */
    public function __construct($username, $password, $accountNumber, $region = null, $locale = null)
    {
        $this->soapClient = new \SoapClient(self::WSDL, $this->getOptions());

        $this->username = $username;
        $this->password = $password;
        $this->accountNumber = $accountNumber;
        $this->region = $region;
        $this->locale = $locale;
    }

    /**
     * Sign the specified file and return the response.
     *
     * @param string $filename the pdf file to sign
     *
     * @return string the signed pdf contents
     * @throws \SOAPFault
     */
    public function sign($filename)
    {
        return $this->soapClient->__soapCall(
            'sign',
            array(
                $this->username,
                $this->password,
                $this->accountNumber,
                $filename,
                $this->encodeContents($filename),
                $this->region,
                $this->locale
            )
        );
    }

    /**
     * @return array
     */
    private function getOptions()
    {
        return array('soap_version' => SOAP_1_1);
    }

    /**
     * @param string $filename
     *
     * @return string
     * @throws \Vianetz\Signaturportal\SignException
     */
    private function encodeContents($filename)
    {
        if (file_exists($filename) === false || is_readable($filename) === false) {
            throw new SignException('Cannot open file to sign: ' . $filename);
        }

        $binaryData = '';
        try {
            $fp = fopen($filename, 'r');
            while ($fp !== false && feof($fp) === false) {
                $binaryData .= fgets($fp, 4096);
            }
            fclose($fp);
        } catch (\Exception $exception) {
            throw new SignException('Cannot encode PDF data: ' . $exception->getMessage());
        }

        return $binaryData;
    }
}
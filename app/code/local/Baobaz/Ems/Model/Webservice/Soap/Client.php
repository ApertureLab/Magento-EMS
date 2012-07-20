<?php
/**
 * @category   Baobaz
 * @package    Baobaz_Ems
 * @author     Experian CheetahMail
 */

/**
 * SoapClient EMS class
 */
class Baobaz_Ems_Model_Webservice_Soap_Client extends SoapClient
{
    const EMS_WS_NAMESPACE = 'http://ws.ems6.net/';

    public function __construct($args)
    {
        try {
            $options = array();
            extract($args);
            if (!$wsdl) {
                //Mage::throwException(Mage::helper('baobaz_ems')->__('WTF, $wsdl is empty?! oO.'));
                throw Mage::exception('Baobaz_Ems', Mage::helper('baobaz_ems')->__('WTF, $wsdl is empty?! oO.'));
            }
            parent::__construct($wsdl, $options);
            $this->__setSoapHeaders($this->createHeader($Login, $Password, $Idmlist));
        } catch (Baobaz_Ems_Exception $e) {
            Baobaz_Ems_Model_Logger::logException($e);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    private function createHeader($Login, $Password, $Idmlist)
    {
        $struct = new stdClass(); // fix PHP strict by al@baobaz.com
        $struct->UserName = new SoapVar($Login, XSD_STRING, null, null, null, self::EMS_WS_NAMESPACE);
        $struct->Password = new SoapVar($Password, XSD_STRING, null, null, null, self::EMS_WS_NAMESPACE);
        $struct->IdMlist = new SoapVar($Idmlist, XSD_INTEGER, null, null, null, self::EMS_WS_NAMESPACE);
        $header_values = new SoapVar($struct, SOAP_ENC_OBJECT, null, null,null, self::EMS_WS_NAMESPACE);
        $header = new SoapHeader(self::EMS_WS_NAMESPACE, "AuthHeader", $header_values);
        return $header;
    }
}
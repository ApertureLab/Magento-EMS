<?php
/**
 * @category   Baobaz
 * @package    Baobaz_Ems
 * @copyright  Copyright (c) 2010 Baobaz (http://www.baobaz.com)
 * @author     Arnaud Ligny <arnaud.ligny@baobaz.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Baobaz Ems Webservice model
 */
class Baobaz_Ems_Model_Webservice extends Varien_Object
{
    protected $_soapBridge  = null;
    protected $_soapOptions = array();
    public $webserviceName  = null;

    /**
     * Create EMS Soap client connexion
     *
     * @todo abstraction, interface (implement), etc.
     *
     * @return Baobaz_Ems_Model_Soap_Client
     */
    protected function _soapBridge()
    {
        $config = Mage::getSingleton('baobaz_ems/config'); /* @var $config Baobaz_Ems_Model_Config */
        if (is_null($this->_soapBridge)) {
            // use proxy?
            if ($config->isUseProxy() && empty($this->_soapOptions)) {
                $this->_soapOptions = array('trace' => true);
                if ($config->getSoapConfig('proxy_host') && $config->getSoapConfig('proxy_port')) {
                    $this->_soapOptions = array_merge(array(
                        'proxy_host' => $config->getSoapConfig('proxy_host'),
                        'proxy_port' => $config->getSoapConfig('proxy_port'),
                    ));
                }
            }
            // SOAP client
            $this->_soapBridge = new Baobaz_Ems_Model_Webservice_Soap_Client(array(
                'Login'    => $config->getSoapConfig('login'),
                'Password' => $config->getSoapConfig('password'),
                'Idmlist'  => $config->getSoapConfig('idmlist'),
                'wsdl'     => sprintf($config->getSoapSettings('wsdl'), $this->webserviceName),
                'options'  => $this->_soapOptions,
            )); /* Baobaz_Ems_Model_Soap_Client */
        }
        return $this->_soapBridge;
    }
}
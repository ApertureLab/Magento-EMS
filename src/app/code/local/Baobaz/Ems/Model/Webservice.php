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
     * Create EMS Soap client connection
     *
     * @todo abstraction, interface (implement), etc.
     * 
     * @param array $args
     * @return Baobaz_Ems_Model_Soap_Client
     */
    protected function _soapBridge($args=null)
    {
        $config = Mage::getSingleton('baobaz_ems/config'); /* @var $config Baobaz_Ems_Model_Config */
        if (is_null($this->_soapBridge)) {
            // options
            if (empty($this->_soapOptions)) {
                // use proxy?
                if ($config->isUseProxy()) {
                    if ($config->getSoapConfig('proxy_host') && $config->getSoapConfig('proxy_port')) {
                        $this->_soapOptions = array_merge($this->_soapOptions, array(
                            'proxy_host' => $config->getSoapConfig('proxy_host'),
                            'proxy_port' => $config->getSoapConfig('proxy_port'),
                            'proxy_login'    => NULL, 
                            'proxy_password' => NULL,
                        ));
                    }
                }
            }
            // debug mode?
            if (Baobaz_Ems_Model_Config::isDebug()) {
                 $this->_soapOptions = array_merge($this->_soapOptions, array(
                     'trace'      => true,
                     'exceptions' => true,
                     'cache_wsdl' => WSDL_CACHE_NONE,
                 ));
            }
            
            // Instantiates SOAP client
            if ($args == null) {
                //$this->_soapBridge = new Baobaz_Ems_Model_Webservice_Soap_Client(array(
                $this->_soapBridge = Mage::getSingleton('baobaz_ems/webservice_soap_client', array(
                    'Login'    => $config->getSoapConfig('login'),
                    'Password' => $config->getSoapConfig('password'),
                    'Idmlist'  => $config->getSoapConfig('idmlist'),
                    'wsdl'     => sprintf($config->getSoapSettings('wsdl'), $this->webserviceName),
                    'options'  => $this->_soapOptions,
                )); /* Baobaz_Ems_Model_Webservice_Soap_Client */
            }
            // Force arguments (login, password and idmlist)
            // to test connection
            else {
                extract($args);
                //$this->_soapBridge = new Baobaz_Ems_Model_Webservice_Soap_Client(array(
                $this->_soapBridge = Mage::getSingleton('baobaz_ems/webservice_soap_client', array(
                    'Login'    => $login,
                    'Password' => $password,
                    'Idmlist'  => $idmlist,
                    'wsdl'     => sprintf($config->getSoapSettings('wsdl'), $this->webserviceName),
                    'options'  => $this->_soapOptions,
                )); /* Baobaz_Ems_Model_Webservice_Soap_Client */
            }
        }

        return $this->_soapBridge;
    }
}
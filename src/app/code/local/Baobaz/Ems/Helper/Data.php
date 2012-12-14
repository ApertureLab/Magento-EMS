<?php
/**
 * @category   Baobaz
 * @package    Baobaz_Ems
 * @copyright  Copyright (c) 2010 Baobaz (http://www.baobaz.com)
 * @author     Arnaud Ligny <arnaud.ligny@baobaz.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Baobaz Ems default Helper
 */
class Baobaz_Ems_Helper_Data extends Mage_Core_Helper_Abstract
{
    const MAGENTO_WEBSITE_NAME_DEFAULT = 'Main Website';

    protected $_origin = '';

    /**
     * Subscriber origin
     *
     * @todo website or store group or store view?
     *
     * @return string
     */
    public function getSubscriberOrigin()
    {
        if (!$this->_origin) {
            $this->_origin = Mage::app()->getWebsite()->getName(); // Website name
            if ($this->_origin == self::MAGENTO_WEBSITE_NAME_DEFAULT) {
                $this->_origin = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            }
            $this->_origin .= ' (' . Mage::app()->getWebsite()->getCode() . ')'; // Website code
        }
        return $this->_origin;
    }

    /**
     * Log debug helper
     *
     * @param string $message
     */
    public function logDebug($message)
    {
        $config = Mage::getSingleton('baobaz_ems/config');
        /* @var $config Baobaz_Ems_Model_Config */
        if ($config->isDebug()) {
            Baobaz_Ems_Model_Logger::logDebug($message);
        }
    }
    
    /**
     * Replace password by '*****' in array
     * 
     * @param array $array
     * @param string $password
     */
    public function hidePassword($array, $password)
    {
        $arrayWithoutPassword = array_replace($array, array_fill_keys(
            array_keys($array, $password),
            '*****'
        ));
        return $arrayWithoutPassword;
    }
}
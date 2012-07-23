<?php
/**
 * @category   Baobaz
 * @package    Baobaz_Ems
 * @copyright  Copyright (c) 2010 Baobaz (http://www.baobaz.com)
 * @author     Arnaud Ligny <arnaud.ligny@baobaz.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Baobaz Ems Model Config
 */
class Baobaz_Ems_Model_Config
{
    const XML_PATH_SETTINGS       = 'settings/ems';
    const XML_PATH_SOAP_CONFIG    = 'ems/soap';
    const XML_PATH_MAPPING_FIELDS = 'ems/mapping/fields';
    const XML_PATH_MAPPING_OPTIN  = 'ems/mapping/optin';

    /**
     * Return EMS config $key item from config.xml > settings
     *
     * @param string $key
     * @return string
     */
    public static function getSettings($key)
    {
        $value = Mage::getConfig()->getNode(self::XML_PATH_SETTINGS . '/' . $key);
        if (!$value) {
            return false;
        }
        return (string) $value;
    }

    /**
     * Return EMS soap config $key item from config.xml > settings
     *
     * @param string $key
     * @return string
     */
    public static function getSoapSettings($key)
    {
        return self::getSettings('soap/' . $key);
    }

    /**
     * Return EMS soap config $key item from core_config_data
     *
     * @param string $key
     * @return string
     */
    public static function getSoapConfig($key)
    {
        return Mage::getStoreConfig(self::XML_PATH_SOAP_CONFIG . '/' . $key);
    }

    public static function isDebug()
    {
        return self::getSettings('debug');
    }

    /**
     * Check whether EMS Soap credentials are available
     *
     * @return bool
     */
    public static function isSoapAvailabe()
    {
        if ($this->getSoapConfig('login')
            && $this->getSoapConfig('password')
            && $this->getSoapConfig('idmlist')
        ) {
            return true;
        }
    }

    /**
     * Use proxy?
     *
     * @return bool
     */
    public static function isUseProxy()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SOAP_CONFIG . '/use_proxy');
    }

    /**
     * Get mapping configuration
     *
     * @return array $mappingArray
     */
    public static function getFieldsMapping()
    {
        $mappingArray = array();
        $fieldsMapping = (array)unserialize(Mage::getStoreConfig(self::XML_PATH_MAPPING_FIELDS, Mage::app()->getStore()->getStoreId()));
        foreach($fieldsMapping as $key) {
            try {
                if (isset($key['customer_attribute']) && isset($key['ems_field'])) {
                    $mappingArray[$key['customer_attribute']] = $key['ems_field'];
                }
                else {
                    Mage::throwException(Mage::helper('baobaz_ems')->__('Unable to fetch mapping between Customer attributes and EMS fields.'));
                }
            } catch (Baobaz_Ems_Exception $e) {
                Baobaz_Ems_Model_Logger::logException($e);
                return false;
            } catch (Exception $e) {
                Mage::logException($e);
                return false;
            }
        }
        return $mappingArray;
    }

    /**
     * Get optin mapping configuration
     *
     * @return array $mappingArray
     */
    public static function getOptinMapping()
    {
        $mappingArray = array();
        $optinMapping = (array)unserialize(Mage::getStoreConfig(self::XML_PATH_MAPPING_OPTIN, Mage::app()->getStore()->getStoreId()));
        foreach($optinMapping as $key) {
            try {
                if (isset($key['ems_optin_field'])
                && isset($key['ems_optin_yes'])
                && isset($key['ems_optin_no'])
                ) {
                    $mappingArray = array(
                        'field' => $key['ems_optin_field'],
                        'yes' => $key['ems_optin_yes'],
                        'no' => $key['ems_optin_no'],
                    );
                }
                else {
                    Mage::throwException(Mage::helper('baobaz_ems')->__('Unable to fetch optin mapping field.'));
                }
            } catch (Baobaz_Ems_Exception $e) {
                Baobaz_Ems_Model_Logger::logException($e);
                return false;
            } catch (Exception $e) {
                Mage::logException($e);
                return false;
            }
        }
        return $mappingArray;
    }

    /**
     * Get optin field value for a specfic key
     * 
     * @param string $key
     * @return string 
     */
    public static function getOptin($key)
    {
        $optinMapping = self::getOptinMapping();
        if ($optinMapping && array_key_exists($key, $optinMapping)) {
            return $optinMapping[$key];
        }
        return '';
    }

    /**
     * Module $moduleName is active?
     * 
     * Use to check dependencies
     * 
     * @param string $moduleName
     * @return bool 
     */
    public static function isModuleActive($moduleName)
    {
        $node = Mage::getConfig()->getNode('modules/' . $moduleName);
        if (is_object($node) && strval($node->active) == 'true') {
            return true;
        }
        return false;
    }
}
<?php
/**
 * @category   Baobaz
 * @package    Baobaz_Ems
 * @copyright  Copyright (c) 2010 Baobaz (http://www.baobaz.com)
 * @author     Arnaud Ligny <arnaud.ligny@baobaz.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Baobaz Ems Customer adapter
 */
class Baobaz_Ems_Model_Adapter_Customer
{
    /**
     * Object customer
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer = null;
    /**
     * Array attributes
     *
     * @var array
     */
    protected $_attributes = array();
    /**
     * Array data
     *
     * @var array
     */
    protected $_data = array();

    public function setCustomer(Mage_Customer_Model_Customer $customer) {
        try {
            if (!$customer instanceof Mage_Customer_Model_Customer) {
                throw new Exception(Mage::helper('baobaz_ems')->__('$customer is not an instance of Mage_Customer_Model_Customer'));
            }
            $this->_customer = $customer;
        } catch (Baobaz_Ems_Exception $e) {
            Baobaz_Ems_Model_Logger::logException($e);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    public function getCustomer() {
        try {
            if ($this->_customer !== null) {
                return $this->_customer;
            }
            else {
                throw new Exception(Mage::helper('baobaz_ems')->__('$_customer is not defined'));
            }
        } catch (Baobaz_Ems_Exception $e) {
            Baobaz_Ems_Model_Logger::logException($e);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Retrieve all customer attributes
     * array(code => label)
     *
     * @todo use local method like "_addAttribute($key, $value='')"?
     * @todo move this code in config/source method? add an event?
     *
     * @return array
     */
    public function getAttributes()
    {
        if (empty($this->_attributes)) {
            $this->_attributes = $this->_getAttributes();
        }
        return $this->_attributes;
    }

    /**
     * Default attributes
     * 
     * @return array $attributes
     */
    protected function _getAttributes()
    {
        $internal = array(
            'store_id',
            'entity_id',
            'website_id',
            'group_id',
            'created_in',
            'default_billing',
            'default_shipping',
            'country_id'
        );

        $attributes['entity_id']     = 'customer_id';
        $attributes['website']       = 'website';
        $attributes['email']         = 'email';
        $attributes['group']         = 'group';
        $attributes['create_in']     = 'create_in';
        $attributes['is_subscribed'] = 'is_subscribed';

        $customerAttributes = Mage::getResourceModel('customer/attribute_collection')
            ->load()->getIterator();
        foreach ($customerAttributes as $attr) {
            $code = $attr->getAttributeCode();
            if (in_array($code, $internal) || $attr->getFrontendInput()=='hidden') {
                continue;
            }
            $attributes[$code] = $code;
        }
        $attributes['password_hash'] = 'password_hash';

        $addressAttributes = Mage::getResourceModel('customer/address_attribute_collection')
            ->load()->getIterator();
        foreach ($addressAttributes as $attr) {
            $code = $attr->getAttributeCode();
            if (in_array($code, $internal) || $attr->getFrontendInput()=='hidden') {
                continue;
            }
            $attributes['billing_'.$code] = 'billing_'.$code;
        }
        $attributes['billing_country'] = 'billing_country';
        $attributes['billing_country_name'] = 'billing_country_name';
        foreach ($addressAttributes as $attr) {
            $code = $attr->getAttributeCode();
            if (in_array($code, $internal) || $attr->getFrontendInput()=='hidden') {
                continue;
            }
            $attributes['shipping_'.$code] = 'shipping_'.$code;
        }
        $attributes['shipping_country'] = 'shipping_country';
        $attributes['shipping_country_name'] = 'shipping_country_name';

        return $attributes;
    }

    /**
     * Load Customer data according to fields mapping > available attributes > converted values
     */
    public function load(Mage_Customer_Model_Customer $customer)
    {
        $this->setCustomer($customer);

        $fieldsMapping = Mage::getSingleton('baobaz_ems/config')->getFieldsMapping();
        if ($fieldsMapping) {
            foreach(array_flip($fieldsMapping) as $emsField => $mageAttribute) {
                if ($mageAttribute) {
                    $method = $this->_attrToMethod($mageAttribute);
                    Mage::helper('baobaz_ems')->logDebug($method . '()'); // debug
                    try {
                        $data = $this->$method();
                        if (is_array($data)) {
                            throw new Exception(Mage::helper('baobaz_ems')->__('$data returned by method \'%s()\' must be a string, not an array: ' . print_r($data, true), $method));
                            $data = '';
                        }
                    } catch (Baobaz_Ems_Exception $e) {
                        Baobaz_Ems_Model_Logger::logException($e);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    if ($data != '') {
                        $this->setData($emsField, $data);
                    }
                }
            }
        }
        return $this;
    }

    public function __call($method, $args)
    {
        Mage::helper('baobaz_ems')->logDebug('__call(' . $method . ')'); // debug
        try {
            if (substr($method, 0, 3) == 'get') {
                /**
                 * Customer
                 */
                // billing
                if (stristr($method, 'getBilling') !== false) {
                    $customerMethod = str_replace('Billing', '', $method);
                    $value = Mage::getModel('customer/address')
                        ->load($this->getCustomer()->getDefaultBilling())
                        ->$customerMethod();
                }
                // shipping
                elseif (stristr($method, 'getShipping') !== false) {
                    $customerMethod = str_replace('Shipping', '', $method);
                    $value = Mage::getModel('customer/address')
                        ->load($this->getCustomer()->getDefaultShipping())
                        ->$customerMethod();
                }
                // others
                else {
                    $value = $this->getCustomer()->$method();
                }
                return $value;
            }
            Mage::throwException('Baobaz_Ems Invalid method ' . get_class($this) . '::' . $method . '(' . print_r($args, true) . ')');
        } catch (Baobaz_Ems_Exception $e) {
            Baobaz_Ems_Model_Logger::logException($e);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function getPrefix()
    {
        $prefix = $this->getCustomer()->getPrefix();
        switch ($prefix) {
            case 'MR':
                $value = '0';
                break;
            case 'MME':
                $value = '1';
                break;
            case 'MLE':
                $value = '2';
                break;
            default:
                $value = '';
        }
        return $value;
    }

    public function getBillingCountryName() {
        $value = '';
        $country_code = Mage::getModel('customer/address')
            ->load($this->getCustomer()->getDefaultBilling())
            ->getCountry();
        if ($country_code) {
            $value = Mage::getModel('directory/country')->loadByCode($country_code)
                ->getName();
        }
        return $value;
    }

    public function getIsSubscribed()
    {
        Mage::helper('baobaz_ems')->logDebug('isSubscribed(): ' . Mage::getModel('newsletter/subscriber')->loadByCustomer($this->getCustomer())->isSubscribed()); // debug
        $value = '2';
        if (Mage::getModel('newsletter/subscriber')->loadByCustomer($this->getCustomer())->isSubscribed()) {
            $value = '1';
        }
        return $value;
    }

    public function setData($key, $value=null)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    public function getData($key='')
    {
        if ($key == '') {
            return $this->_data;
        }
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }
        return '';
    }

    protected function _camelize($name)
    {
        return uc_words($name, '');
    }

    protected function _attrToMethod($attribute)
    {
        return 'get' . $this->_camelize($attribute);
    }

    /**
     * Convert object attributes to EMS array
     *
     * $data = array(
     *     'FLD4' => '0',
     *     'FLD5' => 'test',
     * )
     * TO
     * $data = array(
     *     array('FLD4', '0'),
     *     array('FLD5', 'test')
     * )
     *
     * @return array
     */
    protected function __toEms()
    {
        $dataForEms = array();
        foreach ($this->_data as $field => $value) {
            $dataForEms[] = array($field, $value);
        }
        return $dataForEms;
    }

    /**
     * Public wrapper for __toEms
     *
     * @return array
     */
    public function toEms()
    {
        return $this->__toEms();
    }
}
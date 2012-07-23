<?php
/**
 * @category   Baobaz
 * @package    Baobaz_Ems
 * @copyright  Copyright (c) 2011 Baobaz (http://www.baobaz.com)
 * @author     Arnaud Ligny <arnaud.ligny@baobaz.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * HTML select element block with customer attributes options
 */
class Baobaz_Ems_Block_Adminhtml_Form_Field_Customerattributes extends Mage_Core_Block_Html_Select
{
    /**
     * Customer attributes cache
     *
     * @var array
     */
    private $_customerAttributes;

    /**
     * Flag whether to add empty option or no
     *
     * @var bool
     */
    protected $_addEmptyAttribute = true;

    /**
     * Retrieve customer attributes
     *
     * @param int $attributeCode return label by customer attribute code
     * @return array|string
     */
    protected function _getCustomerAttributes($attributeCode = null)
    {
        if (is_null($this->_customerAttributes)) {
            $this->_customerAttributes = Mage::getSingleton('baobaz_ems/adapter_customer')->getAttributes();
        }
        if (!is_null($attributeCode)) {
            return isset($this->_customerAttributes[$attributeCode]) ? $this->_customerAttributes[$attributeCode] : null;
        }
        return $this->_customerAttributes;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            // empty option
            if ($this->_addEmptyAttribute) {
                $this->addOption('', Mage::helper('baobaz_ems')->__('- empty -'));
            }
            // add options

            // WORK IN PROGRESS
//            $attributes['Default'] = array(
//                'code1' => 'label1',
//                'code2' => 'label2',
//                'code3' => 'label3',
//                'code4' => 'label4',
//                'code5' => 'label5',
//            );
//            $attributes['Orders'] = array(
//                'code1' => 'o1',
//                'code2' => 'o2',
//                'code3' => 'o3',
//                'code4' => 'o4',
//                'code5' => 'o5',
//            );
//            foreach ($attributes as $group => $group_attributes) {
//                foreach ($group_attributes as $code => $label) {
//                    echo "$group > $code > $label\n";
//                }
//            }
            // /WORK IN PROGRESS

            $customerOptions = array();
            foreach ($this->_getCustomerAttributes() as $attributeCode => $attributeLabel) {
                $customerOptions[] = array('value' => $attributeCode, 'label' => $attributeLabel);
            }
            $this->addOption($customerOptions, $this->__('Customer'));
        }
        return parent::_toHtml();
    }
}
<?php
/**
 * @category   Baobaz
 * @package    Baobaz_Ems
 * @copyright  Copyright (c) 2011 Baobaz (http://www.baobaz.com)
 * @author     Arnaud Ligny <arnaud.ligny@baobaz.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Baobaz EMS field mapping Block
 */
class Baobaz_Ems_Block_Adminhtml_Form_Field_Mapping
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * @var Baobaz_Ems_Block_Adminhtml_Form_Field_Mapping
     */
    protected $_customerAttributesRenderer;
    protected $_emsFieldsRenderer;

    /**
     * Retrieve customer attributes column renderer
     *
     * @return Baobaz_Ems_Block_Adminhtml_Form_Field_Mapping
     */
    protected function _getCustomerAttributesRenderer()
    {
        if (!$this->_customerAttributesRenderer) {
            $this->_customerAttributesRenderer = $this->getLayout()->createBlock(
                'baobaz_ems/adminhtml_form_field_customerattributes', '',
                array('is_render_to_js_template' => true)
            );
            $this->_customerAttributesRenderer->setClass('customer_attribute_select');
            $this->_customerAttributesRenderer->setExtraParams('style="width:175px"');
        }
        return $this->_customerAttributesRenderer;
    }

    /**
     * Retrieve EMS fields column renderer
     *
     * @return Baobaz_Ems_Block_Adminhtml_Form_Field_Mapping
     */
    protected function _getEmsFieldsRenderer()
    {
        if (!$this->_emsFieldsRenderer) {
            $this->_emsFieldsRenderer = $this->getLayout()->createBlock(
                'baobaz_ems/adminhtml_form_field_emsfields', '',
                array(
                    'is_render_to_js_template' => true,
                    'escaped_fields'           => $this->_getOptinField() // escape optin field
                )
            );
            $this->_emsFieldsRenderer->setClass('ems_fields_select');
            $this->_emsFieldsRenderer->setExtraParams('style="width:225px"');
        }
        return $this->_emsFieldsRenderer;
    }

    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $this->addColumn('customer_attribute', array(
            'label'    => Mage::helper('baobaz_ems')->__('Customer Attributes'),
            'renderer' => $this->_getCustomerAttributesRenderer(),
        ));
        $this->addColumn('ems_field', array(
            'label' => Mage::helper('baobaz_ems')->__('EMS fields'),
            'renderer' => $this->_getEmsFieldsRenderer(),
        ));
        $this->_addAfter = true;
        $this->_addButtonLabel = Mage::helper('baobaz_ems')->__('Add Field Mapping');
    }

    /**
     * Prepare existing row data object
     *
     * @param Varien_Object
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getCustomerAttributesRenderer()->calcOptionHash($row->getData('customer_attribute')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_' . $this->_getEmsFieldsRenderer()->calcOptionHash($row->getData('ems_field')),
            'selected="selected"'
        );
    }

    protected function _getOptinField()
    {
        $optinField = Mage::getSingleton('baobaz_ems/config')->getOptin('field');
        return array($optinField);
    }
}
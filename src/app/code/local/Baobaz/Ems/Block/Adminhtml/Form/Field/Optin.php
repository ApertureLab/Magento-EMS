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
class Baobaz_Ems_Block_Adminhtml_Form_Field_Optin
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * @var Baobaz_Ems_Block_Adminhtml_Form_Field_Optin
     */
    protected $_emsFieldsRenderer;

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
                array('is_render_to_js_template' => true)
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
        $this->addColumn('ems_optin_field', array(
            'label' => Mage::helper('baobaz_ems')->__('EMS field'),
            'renderer' => $this->_getEmsFieldsRenderer(),
        ));
        $this->addColumn('ems_optin_yes', array(
            'label' => Mage::helper('baobaz_ems')->__('"Yes" value'),
            'style' => 'width:100px',
        ));
        $this->addColumn('ems_optin_no', array(
            'label' => Mage::helper('baobaz_ems')->__('"No" value'),
            'style' => 'width:100px',
        ));
        $this->_addAfter = false;
        //$this->_addButtonLabel = false;
        $this->_addButtonLabel = Mage::helper('baobaz_ems')->__('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param Varien_Object
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getEmsFieldsRenderer()->calcOptionHash($row->getData('ems_optin_field')),
            'selected="selected"'
        );
    }

    /**
     * Retrieve block view from file (template)
     *
     * @param   string $fileName
     * @return  string
     */
    public function fetchView($fileName)
    {
        $templates_path = Mage::getModuleDir('', $this->getModuleName());
        $this->setScriptPath($templates_path . '/design/adminhtml/templates');
        return parent::fetchView($this->getTemplate());
    }
}
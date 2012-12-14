<?php
/**
 * @category   Baobaz
 * @package    Baobaz_Ems
 * @copyright  Copyright (c) 2011 Baobaz (http://www.baobaz.com)
 * @author     Arnaud Ligny <arnaud.ligny@baobaz.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * HTML select element block with EMS fields options
 */
class Baobaz_Ems_Block_Adminhtml_Form_Field_Emsfields extends Mage_Core_Block_Html_Select
{
    /**
     * Customer attributes cache
     *
     * @var array
     */
    private $_emsFields = null;
    /**
     * Escape system field
     *   FLD0: IdUser
     *   FLD1: Adresse EMail
     *   FLD2: Date d'inscription
     *   FLD3: Date de desinscription
     *   FLD4: Etat de l'abonné
     * 
     * @var array 
     */
    private $_escapedFields = array(
        Baobaz_Ems_Model_Webservice_Subscribers::FIELD_USERID,
        Baobaz_Ems_Model_Webservice_Subscribers::FIELD_EMAIL,
        Baobaz_Ems_Model_Webservice_Subscribers::FIELD_DATEIN,
        Baobaz_Ems_Model_Webservice_Subscribers::FIELD_DATEOUT,
        Baobaz_Ems_Model_Webservice_Subscribers::FIELD_STATUS,
    );

    /**
     * Retrieve EMS Fields
     *
     * @param int $fieldId return label by EMS field code
     * @return array|string
     */
    protected function _getEmsFields($fieldId = null)
    {
        if (is_null($this->_emsFields)) {
            if ($this->getEscapedFields()) {
                $this->_escapedFields = array_merge($this->_escapedFields, $this->getEscapedFields());
            }
            $this->_emsFields = Mage::getSingleton('baobaz_ems/source_fields')
                ->setEscapedFields($this->_escapedFields)
                ->toOptionArray();
        }

        return $this->_emsFields;
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
            $this->setOptions($this->_getEmsFields());
        }
        return parent::_toHtml();
    }
}
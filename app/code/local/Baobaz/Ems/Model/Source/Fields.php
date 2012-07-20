<?php
/**
 * @category   Baobaz
 * @package    Baobaz_Ems
 * @copyright  Copyright (c) 2010 Baobaz (http://www.baobaz.com)
 * @author     Arnaud Ligny <arnaud.ligny@baobaz.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Baobaz Ems Fields Source Model
 */
class Baobaz_Ems_Model_Source_Fields
{
    /**
     * Escaped fields
     *
     * @var array
     */
    protected $_escapedFields = array();

    /**
     * Return escaped fields
     *
     * @return array
     */
    public function getEscapedFields()
    {
        return $this->_escapedFields;
    }

    /**
     * Setter for escaped fields
     *
     * @param $values
     * @return Baobaz_Ems_Model_Source_Fields
     */
    public function setEscapedFields(array $values)
    {
        $this->_escapedFields = $values;
        return $this;
    }

    /**
     * Retrieve all EMS fields
     *
     * @return array
     */
    public function getFields()
    {
        $fields = array();
        $modelEms = Mage::getModel('baobaz_ems/webservice_subscribers'); /* @var $modelEms Baobaz_Ems_Model_Webservice_Subscribers */
        $emsFieldsDefinition = $modelEms->getFieldsDefinition();
        if (is_array($emsFieldsDefinition) && !empty($emsFieldsDefinition)) {
            foreach($emsFieldsDefinition as $field) {
                $emsFieldIdAsString = 'FLD' . $field['Id'];
                $fields[$emsFieldIdAsString] = $emsFieldIdAsString . ' - ' . addslashes($field['Description']);
            }
        }
        return $fields;
    }

    public function toOptionArray()
    {
        $options = array();
        $fields = $this->getFields();
        $escaped = $this->getEscapedFields();
        foreach ($fields as $field => $description) {
            if (!in_array($field, $escaped)) {
                $options[] = array(
                   'value' => $field,
                   'label' => $description
                );
            }
        }
        if (empty($options)) {
            $options = array(
                'value' => 'FLD0',
                'label' => 'empty'
            );
        }
        return $options;
    }
}
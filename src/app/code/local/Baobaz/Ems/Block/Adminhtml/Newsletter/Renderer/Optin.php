<?php
/**
 * @category   Baobaz
 * @package    Baobaz_Ems
 * @copyright  Copyright (c) 2010 Baobaz (http://www.baobaz.com)
 * @author     Arnaud Ligny <arnaud.ligny@baobaz.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Baobaz Ems Adminhtml Block optin render
 */
class Baobaz_Ems_Block_Adminhtml_Newsletter_Renderer_Optin extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {   
        $optin = Mage::getModel('baobaz_ems/webservice_subscribers')->getDetails($row->getSubscriberEmail(), Baobaz_Ems_Model_Config::getOptin('field'));
        switch ($optin) {
            case Baobaz_Ems_Model_Config::getOptin('yes'):
                $optinRenderer = Mage::helper('newsletter')->__('Subscribed');
                break;
            case Baobaz_Ems_Model_Config::getOptin('no'):
                $optinRenderer = Mage::helper('newsletter')->__('Unsubscribed');
                break;
            default:
                $optinRenderer = Mage::helper('baobaz_ems')->__('Unknown');
                break;
        }
        return $optinRenderer;
    }
}
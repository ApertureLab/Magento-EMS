<?php
/**
 * @category   Baobaz
 * @package    Baobaz_Ems
 * @copyright  Copyright (c) 2010 Baobaz (http://www.baobaz.com)
 * @author     Arnaud Ligny <arnaud.ligny@baobaz.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Baobaz Ems Adminhtml Block status render
 */
class Baobaz_Ems_Block_Adminhtml_Newsletter_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $status = (Mage::getModel('baobaz_ems/webservice_subscribers')->getStatusByEmail($row->getSubscriberEmail()) ? Mage::helper('newsletter')->__('Subscribed') : Mage::helper('newsletter')->__('Unsubscribed'));
        return $status;
    }
}
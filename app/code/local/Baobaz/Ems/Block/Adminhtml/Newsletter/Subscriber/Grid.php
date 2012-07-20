<?php
/**
 * @category   Baobaz
 * @package    Baobaz_Ems
 * @copyright  Copyright (c) 2010 Baobaz (http://www.baobaz.com)
 * @author     Arnaud Ligny <arnaud.ligny@baobaz.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Baobaz Ems Adminhtml newsletter subscribers grid block
 */
class Baobaz_Ems_Block_Adminhtml_Newsletter_Subscriber_Grid extends Mage_Adminhtml_Block_Newsletter_Subscriber_Grid
{
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        // EMS Status
        $this->addColumnAfter('ems_status', array(
            'header'    => Mage::helper('newsletter')->__('EMS Status'),
            'renderer'  => new Baobaz_Ems_Block_Adminhtml_Newsletter_Renderer_Status(),
            'filter'    => false,
            'sortable'  => false,
        ), 'status');

        // Export to EMS?
//        $this->addExportType('*/*/exportEms', Mage::helper('baobaz_ems')->__('EMS'));
    }

    // mass action to sync with EMS?
//    protected function _prepareMassaction()
//    {
//        parent::_prepareMassaction();
//        $this->getMassactionBlock()->addItem('Synchronize', array(
//             'label'        => Mage::helper('newsletter')->__('Synchronize'),
//             'url'          => $this->getUrl('*/*/massSynchronize')
//        ));
//        return $this;
//    }
}
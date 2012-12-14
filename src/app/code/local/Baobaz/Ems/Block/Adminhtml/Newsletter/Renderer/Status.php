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
        $status = Mage::getModel('baobaz_ems/webservice_subscribers')->getStatusByEmail($row->getSubscriberEmail());
        switch ((string)$status) {
            case Baobaz_Ems_Model_Webservice_Subscribers::STATUS_NOTEXIST:
                $statusRenderer = Mage::helper('baobaz_ems')->__('Not exist');
                break;
            case Baobaz_Ems_Model_Webservice_Subscribers::STATUS_NOTSUBSCRIBED:
                $statusRenderer = Mage::helper('baobaz_ems')->__('Not subscribed');
                break;
            case Baobaz_Ems_Model_Webservice_Subscribers::STATUS_SUBSCRIBED:
                $statusRenderer = Mage::helper('baobaz_ems')->__('Subscribed');
                break;
            default:
                $statusRenderer = Mage::helper('baobaz_ems')->__('Unknown');
                break;
        }
        
        if (Baobaz_Ems_Model_Config::isDebug()) {
            $subscriberDetails = print_r(Mage::getModel('baobaz_ems/webservice_subscribers')->getDetails($row->getSubscriberEmail()), true);
            $statusRendererDebug = '<script type="text/javascript">
//<![CDATA[
function toggleSubscribersDetails(id){
   $(\'subscriber-\'+id+\'-details\').toggle();
}
//]]>
</script>
<pre id="subscriber-' . $row->getSubscriberId() . '-details" style="display:none;">' . $subscriberDetails . '</pre>
<a href="#show-details" onclick="toggleSubscribersDetails(' . $row->getSubscriberId() . ');">' . $statusRenderer . '</a>';
            $statusRenderer = $statusRendererDebug;
        }
        
        return $statusRenderer;
    }
}
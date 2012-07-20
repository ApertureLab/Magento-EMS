<?php
/**
 * @category   Baobaz
 * @package    Baobaz_Ems
 * @copyright  Copyright (c) 2010 Baobaz (http://www.baobaz.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Baobaz EMS Observer
 *
 * Event handlers
 */
class Baobaz_Ems_Model_Observer
{
    /**
     * Observes customer_save_after event
     * to add subscriber to EMS via webservice
     *
     * @param Varien_Event_Observer $observer
     * @return Baobaz_Ems_Model_Observer
     */
    public function updateCustomer(Varien_Event_Observer $observer)
    {
        Mage::helper('baobaz_ems')->logDebug('Observer: ' . $observer->getEvent()->getName()); // debug
        $customer = $observer->getEvent()->getCustomer();
        $emsSubscribers = Mage::getModel('baobaz_ems/webservice_subscribers'); /* @var $emsSubscribers Baobaz_Ems_Model_Webservice_Subscribers */
        // subscribe Customer to EMS
        if (Mage::getModel('newsletter/subscriber')->loadByCustomer($customer)->isSubscribed()) {
            $emsSubscribers->subscribe($customer->getEmail());
        }
        // unsubscribe
        else {
            $emsSubscribers->unsubscribe($customer->getEmail());
        }
        // update EMS fields
        $data = Mage::getModel('baobaz_ems/adapter_customer')->load($customer)->toEms();
        Mage::helper('baobaz_ems')->logDebug($data); // debug
        $emsSubscribers->update($customer->getEmail(), $data);
        return $this;
    }

    /**
     * Observes customer_delete_before event
     * to unsubscribe subscriber to EMS via webservice
     *
     * @param Varien_Event_Observer $observer
     * @return Baobaz_Ems_Model_Observer
     */
    public function deleteCustomer(Varien_Event_Observer $observer)
    {
        Mage::helper('baobaz_ems')->logDebug('Observer: ' . $observer->getEvent()->getName()); // debug
        $customer   = $observer->getEvent()->getCustomer();
        $emsSubscribers = Mage::getModel('baobaz_ems/webservice_subscribers'); /* @var $emsSubscribers Baobaz_Ems_Model_Webservice_Subscribers */
        // unsubscribe
        $emsSubscribers->unsubscribe($customer->getEmail());
        return $this;
    }

    /**
     * Observes newsletter_subscriber_save_before event
     * to update subscriber change status date
     *
     * @param Varien_Event_Observer $observer
     * @return Baobaz_Ems_Model_Observer
     */
//    public function updateSubscriberStatusDate(Varien_Event_Observer $observer)
//    {
//        Mage::helper('baobaz_ems')->logDebug('Observer: ' . $observer->getEvent()->getName()); // debug
//        $subscriber = $observer->getEvent()->getSubscriber();
//        // date (Zend_Date with default time zone)
//        $date = Mage::app()->getLocale()->date(null, null, null, false);
//        // set change status date
//        $subscriber->setChangeStatusAt($date->toString('yyyy-MM-dd HH:mm:ss'));
//        Mage::helper('baobaz_ems')->logDebug('Update date: ' . $date->toString('yyyy-MM-dd HH:mm:ss')); // debug
//        return $this;
//    }

    /**
     * Observes newsletter_subscriber_save_after event
     * to send subscriber to EMS via webservice
     *
     * @todo: delete subscribers from BO don't works...
     * @todo add updateSubscriberFromAdmin()
     *
     * @param Varien_Event_Observer $observer
     * @return Baobaz_Ems_Model_Observer
     */
    public function updateSubscriber(Varien_Event_Observer $observer)
    {
        Mage::helper('baobaz_ems')->logDebug('Observer: ' . $observer->getEvent()->getName()); // debug
        $subscriber = $observer->getEvent()->getSubscriber();
        // anonymous only
        if (!$subscriber->getCustomerId()) {
            $emsSubscribers = Mage::getModel('baobaz_ems/webservice_subscribers'); /* @var $emsSubscribers Baobaz_Ems_Model_Webservice_Subscribers */
            // subscribe
            //if ($subscriber->getSubscriberStatus() == '1') {
                $emsSubscribers->subscribe($subscriber->getSubscriberEmail());
            //}
        }
        return $this;
    }

    /**
     * Scheduled actions (cron)
     * Asynchronous process
     *
     * @todo
     *  - update Magento customer datas to EMS database
     *  - update subscription status : check date beetween Magento & EMS
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Baobaz_Ems_Model_Observer
     */
//    public function scheduledActions($schedule)
//    {
//        Mage::log('run', Zend_Log::INFO, 'cron.ems.log');
//        return $this;
//
//        // Notes :
//        // on va consulter la base EMS et consulter le statut d'abonnement de chacun des comptes utilisateurs présent dans la BDD
//        // si le compte est désabonné et que la date de désincription est supérieur à la date de changement de statut dans Magento (change_status_at)
//        // alors on désinscrit le customer côté Magento
//
//        // date format test
//        //$date_test = new Zend_Date('31/12/2011 23:59:59', 'dd/MM/yyyy HH:mm:ss');
//        //Mage::helper('baobaz_ems')->logDebug($date_test->toString('yyyy-MM-dd HH:mm:ss')); // debug
//    }
}
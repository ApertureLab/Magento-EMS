<?php

class Baobaz_Ems_Adminhtml_EmsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Return testing result
     *
     * @return void
     */
    public function testAction()
    {
        // Get params
        $login    = $this->getRequest()->getParam('login');
        $password = $this->getRequest()->getParam('password');
        if ($password == '******') {
            $password = Baobaz_Ems_Model_Config::getSoapConfig('password');
        }
        $idmlist  = $this->getRequest()->getParam('idmlist');
        
        if (empty($login) || empty($password) || empty($idmlist)) {
            $result = array(
                'status'  => 'notice',
                'message' => 'Login, Password and List ID should be filled.',
            );
        }
        // try to connect to WS
        else {    
            $modelEms = Mage::getModel('baobaz_ems/webservice_subscribers');
            /* @var $modelEms Baobaz_Ems_Model_Webservice_Subscribers */
            $test = $modelEms->test($login, $password, $idmlist);
            if ($test === false || $test instanceof SoapFault) {
                $result = array(
                    'status'  => 'error',
                    'message' => 'Unable to connect to the Webservice.',
                );
                if ($test instanceof SoapFault && Baobaz_Ems_Model_Config::isDebug()) {
                    $result = array(
                        'status'  => 'error',
                        'message' => $test->getMessage(),
                    );
                }
            }
            else {
                $result = array(
                    'status'  => 'success',
                    'message' => 'Connection success!',
                );
            }
        }

        Mage::app()->getResponse()
            ->setHeader('Content-Type', 'application/json', true)
            ->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
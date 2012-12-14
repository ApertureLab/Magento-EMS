<?php
/**
 * @category   Baobaz
 * @package    Baobaz_Ems
 * @copyright  Copyright (c) 2010 Baobaz (http://www.baobaz.com)
 * @author     Arnaud Ligny <arnaud.ligny@baobaz.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Baobaz Ems Webservice Subscribers Model
 */
class Baobaz_Ems_Model_Webservice_Subscribers
    extends Baobaz_Ems_Model_Webservice
{
    /**
     * EMS Webservice name, use to build WSDL path
     */
    const WEBSERVICE_NAME = 'subscribers';
    /**
     * EMS constants
     */
    const FIELD_USERID  = 'FLD0';
    const FIELD_EMAIL   = 'FLD1';
    const FIELD_DATEIN  = 'FLD2';
    const FIELD_DATEOUT = 'FLD3';
    const FIELD_STATUS  = 'FLD4';
    const STATUS_NOTEXIST      = '-1';
    const STATUS_SUBSCRIBED    = '0';
    const STATUS_NOTSUBSCRIBED = '1';
    
    const TEST_EMAIL = 'test@baobaz.com';

    /**
     * Create EMS Soap client connection
     * 
     * @param array $args
     * @return Baobaz_Ems_Model_Soap_Client
     */
    protected function _soapBridge($args=null)
    {
        $this->webserviceName = self::WEBSERVICE_NAME;
        $this->_soapBridge = parent::_soapBridge($args);
        return $this->_soapBridge;
    }

    /**
     * WebService connection test
     * 
     * @param string $login
     * @param string $password
     * @param string $idmlist
     * @return \SoapFault|boolean
     */
    public function test($login, $password, $idmlist)
    {
        try {
            $result = $this->_soapBridge(array(
                'login'    => $login,
                'password' => $password,
                'idmlist'  => $idmlist,
            ))->GetIdByEmail(array('email'=>self::TEST_EMAIL));
            return true;
        } catch (SoapFault $e) {
            Baobaz_Ems_Model_Logger::logException($e);
            return $e;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }
    
    /**
     * Get EMS fields definition
     *
     * @return array
     */
    public function getFieldsDefinition()
    {
        try {
            $result = $this->_soapBridge()->GetFieldsDefinition();
            foreach ($result->GetFieldsDefinitionResult->FieldDefinition as $res) {
                $fieldsDefinition[$res->Id] = array(
                    'Id'          => $res->Id,
                    'Description' => $res->Description,
                    'Type'        => $res->Type,
                    'Detail'      => $res->Detail,
                );
            }
            return $fieldsDefinition;
        } catch (SoapFault $e) {
            Baobaz_Ems_Model_Logger::logException($e);
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }

    /**
     * Get EMS subscriber status by email
     *
     * @param string $email
     * @return string
     */
    public function getStatusByEmail($email)
    {
        try {
            $result = $this->_soapBridge()->GetIdByEmail(array('email'=>$email));
            if ($result->GetIdByEmailResult == '-1') {
                return self::STATUS_NOTEXIST;
            }
            else if ($result->GetIdByEmailResult == '0') {
                return self::STATUS_NOTSUBSCRIBED;
            }
            else {
                return self::STATUS_SUBSCRIBED;
            }
        } catch (SoapFault $e) {
            Baobaz_Ems_Model_Logger::logException($e);
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }

    /**
     * Get EMS subscriber ID by email
     *
     * @param string $email
     * @return mixed bool|int
     */
    public function getIdByEmail($email)
    {
        try {
            $result = $this->_soapBridge()->GetIdByEmail(array('email'=>$email));
            // not exist
            if ($result->GetIdByEmailResult == '-1') {
                return false;
            }
            // exist but not subscribed,
            // must use "Find" method to return subscriber ID
            else if ($result->GetIdByEmailResult == '0') {
                $result = $this->_soapBridge()->Find(array('criteria'=>array(array('FLD1', $email))));

                // cleaning EMS database: delete duplicate account (e-mail is the key)
                // @todo move in dedicated method called by subscribe()
                if (is_array($result->FindResult->int) && count($result->FindResult->int) > 1) {
                    foreach ($result->FindResult->int as $key => $userId) {
                        if ($key == 0) {
                            continue;
                        }
                        $this->_soapBridge()->Delete(array('subscriberId'=>$userId));
                    }
                }

                return $result->FindResult->int;
            }
            // exist and subscribed
            else {
                return $result->GetIdByEmailResult;
            }
            return false;
        } catch (SoapFault $e) {
            Baobaz_Ems_Model_Logger::logException($e);
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }

    /**
     * Get EMS subscriber details
     *
     * @param string $email
     * @param string $field specific EMS field
     * @return array
     */
    public function getDetails($email, $field=null)
    {
        try {
            $subscriberId = $this->getIdByEmail($email);
            if ($subscriberId !== false) {
                $result = $this->_soapBridge()->Get(array('subscriberId'=>$subscriberId));
                foreach ($result->GetResult->ArrayOfString as $res) {
                    $subscriberDetails[$res->string[0]] = $res->string[1];
                }
                if ($field != null && array_key_exists($field, $subscriberDetails)) {
                    return $subscriberDetails[$field];
                }
                return $subscriberDetails;
            }
            return false;
        } catch (SoapFault $e) {
            Baobaz_Ems_Model_Logger::logException($e);
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }

    /**
     * EMS subscription (add) by email
     *
     * @param string $email
     * @return int|bool
     */
    public function subscribe($email)
    {
        Mage::helper('baobaz_ems')->logDebug('Subscribe (' . Baobaz_Ems_Model_Config::getOptin('field') . '): ' . $email); // debug
        // 1. FIND
        $subscriberId = $this->getIdByEmail($email);
        // 2. ADD?
        if ($subscriberId === false) {
            $subscriberId = $this->add($email);
        }
        // 3. UPDATE
        try {
            // dedicated field
            if (Baobaz_Ems_Model_Config::getOptin('field') != '') {
                $data = array(
                    array(
                        Baobaz_Ems_Model_Config::getOptin('field'),
                        Baobaz_Ems_Model_Config::getOptin('yes')
                    )
                );
            }
            // or default system field (FLD4)
            else {
                $data = array(
                    array(
                        self::FIELD_STATUS,
                        self::STATUS_SUBSCRIBED
                    )
                );
            }            
            Mage::helper('baobaz_ems')->logDebug(array('subscribe data' => $data)); // debug
            $this->update($email, $data);
            return $subscriberId;
        } catch (SoapFault $e) {
            Baobaz_Ems_Model_Logger::logException($e);
            Baobaz_Ems_Model_Logger::logSubscriptionError($e, $email, __METHOD__);
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
        return false;
    }

    /**
     * EMS unsubscription by email
     *
     * @param string $email
     * @return bool
     */
    public function unsubscribe($email)
    {
        try {
            Mage::helper('baobaz_ems')->logDebug('Unsubscribe (' . Baobaz_Ems_Model_Config::getOptin('field') . '): ' . $email); // debug
            $subscriberId = $this->getIdByEmail($email);
            if ($subscriberId !== false) {
                // dedicated field
                if (Baobaz_Ems_Model_Config::getOptin('field') != '') {
                    $data = array(
                        array(
                            Baobaz_Ems_Model_Config::getOptin('field'),
                            Baobaz_Ems_Model_Config::getOptin('no')
                        )
                    );
                }
                // or default system field (FLD4)
                else {
                    $data = array(
                        array(
                            self::FIELD_STATUS,
                            self::STATUS_NOTSUBSCRIBED
                        )
                    );
                }
                Mage::helper('baobaz_ems')->logDebug(array('unsubscribe data' => $data)); // debug
                return $this->update($email, $data); // true or false
            }
            return false;
        } catch (SoapFault $e) {
            Baobaz_Ems_Model_Logger::logException($e);
            Baobaz_Ems_Model_Logger::logSubscriptionError($e, $email, __METHOD__);
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }

    /**
     * EMS subscriber fields update by email
     *
     * ie:
     * $data = array(
     *     array('FLD4', '0'),
     *     array('FLD5', 'test')
     * )
     *
     * @param string $email
     * @param array $data
     * @return bool
     */
    public function update($email, $data)
    {
        try {
            $subscriberId = $this->getIdByEmail($email);
            $updateResult = $this->_soapBridge()->Update(array(
                'subscriberId' => $subscriberId,
                'data'         => $data
            ));
            $updateResult = $updateResult->UpdateResult; // true or false
            Mage::helper('baobaz_ems')->logDebug('UpdateResult: ' . (string)$updateResult); // debug
            if ($updateResult === false) {
                throw new SoapFault(Mage::helper('baobaz_ems')->__('Error during update'), null, null);
            }
            return $updateResult;
        } catch (SoapFault $e) {
            Baobaz_Ems_Model_Logger::logException($e);
            Baobaz_Ems_Model_Logger::logSubscriptionError($e, $email, __METHOD__);
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }

    /**
     * Add EMS subscriber by email
     *
     * Return subscriber ID
     *
     * @param string $email
     * @return integer|bool
     */
    public function add($email)
    {
        try {
            $data = array(
                'email'     => $email,
                'IPAddress' => Mage::helper('core/http')->getRemoteAddr(),
                'origin'    => Mage::helper('baobaz_ems')->getSubscriberOrigin()
            );
            Mage::helper('baobaz_ems')->logDebug(array('add data' => $data)); // debug
            $addResult = $this->_soapBridge()->Add($data);
            Mage::helper('baobaz_ems')->logDebug('New ID: ' . $addResult->AddResult); // debug
            return $addResult->AddResult;
        } catch (SoapFault $e) {
            Baobaz_Ems_Model_Logger::logException($e);
            Baobaz_Ems_Model_Logger::logSubscriptionError($e, $email, __METHOD__);
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }
}
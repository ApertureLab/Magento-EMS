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
class Baobaz_Ems_Model_Webservice_Subscribers extends Baobaz_Ems_Model_Webservice
{
    /**
     * EMS Webservice name, use to build WSDL path
     */
    const WEBSERVICE_NAME = 'subscribers';

    protected function _soapBridge()
    {
        $this->webserviceName = self::WEBSERVICE_NAME;
        return parent::_soapBridge();
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
            Baobaz_Ems_Model_Logger::logException($e, null, true);
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
     */
    public function getStatusByEmail($email)
    {
        try {
            $result = $this->_soapBridge()->GetIdByEmail(array('email'=>$email));
            // not exist
            if ($result->GetIdByEmailResult == '-1') {
                return false;
            }
            // exist but not abonned
            else if ($result->GetIdByEmailResult == '0') {
                return false;
            }
            // exist
            else {
                return true;
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
            // exist but not abonned
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
            // exist
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
     * @return array
     */
    public function getDetails($email)
    {
        try {
            $subscriberId = $this->getIdByEmail($email);
            if ($subscriberId !== false) {
                $result = $this->_soapBridge()->Get(array('subscriberId'=>$subscriberId));
                foreach ($result->GetResult->ArrayOfString as $res) {
                    $subscriberDetails[$res->string[0]] = $res->string[1];
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
     * @return mixed:int|bool
     */
    public function subscribe($email)
    {
        Mage::helper('baobaz_ems')->logDebug('Subscribe (' . Baobaz_Ems_Model_Config::getOptin('field') . '): ' . $email); // debug
        // FIND
        $subscriberId = $this->getIdByEmail($email);
        // ADD
        if ($subscriberId === false) {
            $subscriberId = $this->add($email);
        }
        // UPDATE
        try {
            $data = array(
                array(
                    Baobaz_Ems_Model_Config::getOptin('field'),
                    Baobaz_Ems_Model_Config::getOptin('yes')
                )
            );
            Mage::helper('baobaz_ems')->logDebug($data); // debug
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
                $data = array(
                    array(
                        Baobaz_Ems_Model_Config::getOptin('field'),
                        Baobaz_Ems_Model_Config::getOptin('no')
                    )
                );
                Mage::helper('baobaz_ems')->logDebug($data); // debug
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
                //throw new SoapFault(Mage::helper('baobaz_ems')->__('Error during update'), null, null);
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
     * @return integer
     */
    public function add($email)
    {
        try {
            $data = array(
                'email'     => $email,
                'IPAddress' => Mage::helper('core/http')->getRemoteAddr(),
                'origin'    => Mage::helper('baobaz_ems')->getSubscriberOrigin()
            );
            Mage::helper('baobaz_ems')->logDebug($data); // debug
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
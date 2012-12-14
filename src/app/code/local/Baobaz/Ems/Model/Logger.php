<?php
/**
 * @category   Baobaz
 * @package    Baobaz_Ems
 * @copyright  Copyright (c) 2010 Baobaz (http://www.baobaz.com)
 * @author     Arnaud Ligny <arnaud.ligny@baobaz.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Baobaz Ems Logger model
 */
class Baobaz_Ems_Model_Logger
{
    /**
     * Write to log
     * 
     * @param string $message
     * @param integer $level
     * @param string $file
     * @param bool $forceLog
     */
    public static function log($message, $level = null, $file = '', $forceLog = false)
    {
        if (empty($file)) {
            $file = 'ems.log';
        }
        Mage::log($message, $level, $file, $forceLog = false);
    }

    /**
     * Write debug to log
     * 
     * @param string $message 
     */
    public static function logDebug($message)
    {
        self::log($message, Zend_Log::DEBUG, 'debug.ems.log');
    }

    /**
     * Write exception to log
     * 
     * @param Exception $e
     * @param const $level 
     */
    public static function logException(Exception $e, $level = null, $messageOnly = true)
    {
        if ($level == null) {
            $level = Zend_Log::ERR;
        }        
        if ($messageOnly) {
            Mage::log("\n" . $e->getMessage(), $level, 'exception.ems.log');
        }
        else {
            Mage::log("\nCode: " . $e->getCode() . "\n" . $e->__toString() . get_class($e), $level, 'exception.ems.log');
        }
    }

    /**
     * Write subscription error to XML log
     * 
     * @staticvar array $loggers
     * @param Exception $e
     * @param string $email
     * @param string $source 
     */
    public static function logSubscriptionError(Exception $e, $email, $source = null)
    {
        static $loggers = array();
        $level = Zend_Log::ERR;
        $file = 'subscribers.ems.xml';

        try {
            if (!isset($loggers[$file])) {
                $logFile = Mage::getBaseDir('var') . DS . 'log' . DS . $file;
                $writer = new Zend_Log_Writer_Stream($logFile);
                $formatter = new Baobaz_Ems_Model_Logger_FormatterXml('subscriber', array(
                    'email'             => 'email',
                    'source'            => 'source',
                    'error-code'        => 'code',
                    'error-description' => 'description',
                    'type'              => 'type',
                    'created-at'        => 'createdAt',
                ));
                $writer->setFormatter($formatter);
                $loggers[$file] = new Zend_Log($writer);
                $loggers[$file]->setEventItem('email', $email);
                $loggers[$file]->setEventItem('source', $source);
                $loggers[$file]->setEventItem('code', $e->getCode());
                $loggers[$file]->setEventItem('description', $e->getMessage());
                $loggers[$file]->setEventItem('type', $level);
                $loggers[$file]->setEventItem('createdAt', now());
            }

            $loggers[$file]->log('', $level);
        } catch (Exception $e) {
            self::logException($e);
        }
    }
}
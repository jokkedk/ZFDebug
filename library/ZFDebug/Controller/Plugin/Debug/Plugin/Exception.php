<?php
/**
 * ZFDebug Zend Additions
 *
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 * @version    $Id: $
 */

/**
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class ZFDebug_Controller_Plugin_Debug_Plugin_Exception implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'exception';

    /**
     * Contains any errors
     *
     * @var param array
     */
    static $errors = array();

    /**
     * Gets identifier for this plugin
     *
     * @return string
     */
    public function getIdentifier ()
    {
        return $this->_identifier;
    }

    /**
     * Creates Error Plugin ans sets the Error Handler
     *
     * @return void
     */
    public function __construct ()
    {
        set_error_handler(array($this , 'errorHandler'));
    }

    /**
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab ()
    {
        $response = Zend_Controller_Front::getInstance()->getResponse();
        $errorCount = count(self::$errors);
        if (! $response->isException() && ! $errorCount)
            return '';
        $error = '';
        $exception = '';
        if ($errorCount)
            $error = ($errorCount == 1 ? '1 error' : $errorCount . ' errors');
        $count = count($response->getException());
        if ($this->_options['show_exceptions'] && $count)
            $exception = ($count == 1) ? '1 exception' : $count . ' exceptions';
        $text = $exception . ($exception == '' || $error == '' ? '' : ' - ') . $error;
        return $text;
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel ()
    {
        $response = Zend_Controller_Front::getInstance()->getResponse();
        $errorCount = count(self::$errors);
        if (! $response->isException() && ! $errorCount)
            return '';
        $html = '';

        foreach ($response->getException() as $e) {
            $html .= '<h4>' . get_class($e) . ': ' . $e->getMessage() . '</h4><p>thrown in ' . $e->getFile() . ' on line ' . $e->getLine() . '</p>';
            $html .= '<h4>Call Stack</h4><ol>';
            foreach ($e->getTrace() as $t) {
                $func = $t['function'] . '()';
                if (isset($t['class']))
                    $func = $t['class'] . $t['type'] . $func;
                if (! isset($t['file']))
                    $t['file'] = 'unknown';
                if (! isset($t['line']))
                    $t['line'] = 'n/a';
                $html .= '<li>' . $func . '<br>in ' . str_replace($_SERVER['DOCUMENT_ROOT'], '', $t['file']) . ' on line ' . $t['line'] . '</li>';
            }
            $html .= '</ol>';
        }

        if ($errorCount) {
            $html .= '<h4>Errors</h4><ol>';
            foreach (self::$errors as $error) {
                $html .= '<li>' . sprintf("%s: %s in %s on line %d", $error['type'], $error['message'], str_replace($_SERVER['DOCUMENT_ROOT'], '', $error['file']), $error['line']) . '</li>';
            }
            $html .= '</ol>';
        }
        return $html;
    }

    /**
     * Debug Bar php error handler
     *
     * @param string $level
     * @param string $message
     * @param string $file
     * @param string $line
     * @return bool
     */
    public static function errorHandler ($level, $message, $file, $line)
    {
        if (! ($level & error_reporting()))
            return false;
        switch ($level) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $type = 'Notice';
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $type = 'Warning';
                break;
            case E_ERROR:
            case E_USER_ERROR:
                $type = 'Fatal Error';
                break;
            default:
                $type = 'Unknown, ' . $level;
                break;
        }
        self::$errors[] = array('type' => $type , 'message' => $message , 'file' => $file , 'line' => $line);
        if (ini_get('log_errors'))
            error_log(sprintf("%s: %s in %s on line %d", $type, $message, $file, $line));
        return true;
    }
}
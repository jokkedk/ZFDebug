<?php
/**
 * ZFDebug Zend Additions
 *
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Session
 */
require_once 'Zend/Session.php';

/**
 * @see Zend_Session_Namespace
 */
require_once 'Zend/Session/Namespace.php';

/**
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class ZFDebug_Controller_Plugin_Debug_Plugin_Time extends Zend_Controller_Plugin_Abstract implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'time';

    /**
     * @var array
     */
    protected $_timer = array();

    /**
     * Gets identifier for this plugin
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

    public function __construct()
    {
        Zend_Controller_Front::getInstance()->registerPlugin($this);
    }

    /**
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab()
    {
        return round($this->_timer['postDispatch'],2) . ' ms';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $html = '<h4>Custom Timers</h4>';
        $html .= 'Controller: ' . round(($this->_timer['postDispatch']-$this->_timer['preDispatch']),2) .' ms<br />';
        if (isset($this->_timer['user']) && count($this->_timer['user'])) {
            foreach ($this->_timer['user'] as $name => $time) {
                $html .= ''.$name.': '. round($time,2).' ms<br>';
            }
        }

        if(!Zend_Session::isStarted())
        {
            Zend_Session::start();
        }

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();


        $timerNamespace = new Zend_Session_Namespace('ZFDebug_Time',true);
        $timerNamespace->data[$module][$controller][$action][] = $this->_timer['postDispatch'];

        $html .= '<h4>Overall Times</h4>';

        foreach($timerNamespace->data as $module => $controller)
        {
            $html .= $module . '<br />';
            $html .= '<div class="pre">';
            foreach($controller as $con => $action)
            {
                $html .= '    ' . $con . '<br />';
                $html .= '<div class="pre">';
                foreach ($action as $key => $data)
                {
                    $html .= '        ' . $key . '<br />';
                    $html .= '<div class="pre">';
                    $html .= '            Avg: ' . $this->calcAvg($data) . ' ms<br />';
                    $html .= '            Min: ' . min($data) . 'ms<br />';
                    $html .= '            Max: ' . max($data) . ' ms<br />';
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        #$html .= $this->cleanData($timerNamespace->data);

        return $html;
    }

    /**
     * Sets a time mark identified with $name
     *
     * @param string $name
     */
    public function mark($name) {
        if (isset($this->_timer['user'][$name]))
            $this->_timer['user'][$name] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000-$this->_timer['user'][$name];
        else
            $this->_timer['user'][$name] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
    }

    #public function routeStartup(Zend_Controller_Request_Abstract $request) {
    #     $this->timer['routeStartup'] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
    #}

    #public function routeShutdown(Zend_Controller_Request_Abstract $request) {
    #     $this->timer['routeShutdown'] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
    #}

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_timer['preDispatch'] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
    }

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_timer['postDispatch'] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
    }

    protected function cleanData($values)
    {
        ksort($values);

        $retVal = '<div class="pre">';
        foreach ($values as $key => $value)
        {
            $key = htmlentities($key);
            if (is_numeric($value)) {
                $retVal .= $key.' => '.$value.'<br>';
            }
            else if (is_string($value)) {
                $retVal .= $key.' => \''.htmlentities($value).'\'<br>';
            }
            else if (is_array($value))
            {
                $retVal .= $key.' => '.self::cleanData($value);
            }
            else if (is_object($value))
            {
                $retVal .= $key.' => '.get_class($value).' Object()<br>';
            }
            else if (is_null($value))
            {
                $retVal .= $key.' => NULL<br>';
            }
        }
        return $retVal.'</div>';
    }

    protected function calcAvg(Array $array, $precision=2)
    {
        if(!is_array($array))
            return 'ERROR in function array_avg(): this is a not array';

        foreach($array as $value)
            if(!is_numeric($value))
                return 'ERROR in function array_avg(): the array contains one or more non-numeric values';

        $cuantos=count($array);
        return round(array_sum($array)/$cuantos,$precision);
    }
}
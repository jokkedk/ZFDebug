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
     * Creating time plugin
     * @return void
     */
    public function __construct()
    {
        Zend_Controller_Front::getInstance()->registerPlugin($this);
    }

    /**
     * Gets identifier for this plugin
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
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


        if (Zend_Session::namespaceIsset('ZFDebug_Time')) {
        	$timerNamespace = Zend_Session::namespaceGet('ZFDebug_Time');
        } else {
        	$timerNamespace = new Zend_Session_Namespace('ZFDebug_Time',true);
        }
        $timerNamespace->data[$module][$controller][$action][] = $this->_timer['postDispatch'];

        $html .= '<h4>Overall Timers</h4>';

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
                    $html .= '            Avg: ' . $this->_calcAvg($data) . ' ms / '.count($data).' requests<br />';
                    $html .= '            Min: ' . round(min($data), 2) . ' ms<br />';
                    $html .= '            Max: ' . round(max($data), 2) . ' ms<br />';
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }

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

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param Zend_Controller_Request_Abstract
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_timer['preDispatch'] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
    }

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param Zend_Controller_Request_Abstract
     * @return void
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_timer['postDispatch'] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
    }

    /**
     * Calculate average time from $array
     *
     * @param array $array
     * @param int $precision
     * @return float
     */
    protected function _calcAvg(array $array, $precision=2)
    {
        if(!is_array($array)) {
            return 'ERROR in method _calcAvg(): this is a not array';
        }

        foreach($array as $value)
            if(!is_numeric($value)) {
                return 'ERROR in method _calcAvg(): the array contains one or more non-numeric values';
            }

        $cuantos=count($array);
        return round(array_sum($array)/$cuantos,$precision);
    }
}
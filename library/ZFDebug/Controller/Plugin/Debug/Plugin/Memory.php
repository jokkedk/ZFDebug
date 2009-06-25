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
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class ZFDebug_Controller_Plugin_Debug_Plugin_Memory extends Zend_Controller_Plugin_Abstract implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'memory';

    /**
     * @var array
     */
    protected $_memory = array();

    protected $_closingBracket = null;

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
        if (function_exists('memory_get_peak_usage')) {
            return round(memory_get_peak_usage()/1024) . 'K of '.ini_get("memory_limit");
        }
        return 'MemUsage n.a.';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $panel = '<h4>Memory Usage</h4>';
        $panel .= 'Controller: ' . round(($this->_memory['postDispatch']-$this->_memory['preDispatch'])/1024,2) .'K'.$this->getLinebreak();
        if (isset($this->_memory['user']) && count($this->_memory['user'])) {
            foreach ($this->_memory['user'] as $key => $value) {
                $panel .= $key.': '.round($value/1024).'K'.$this->getLinebreak();
            }
        }
        return $panel;
    }
    
    /**
     * Sets a memory mark identified with $name
     *
     * @param string $name
     */
    public function mark($name) {
        if (!function_exists('memory_get_peak_usage')) {
            return;
        }
        if (isset($this->_memory['user'][$name]))
            $this->_memory['user'][$name] = memory_get_peak_usage()-$this->_memory['user'][$name];
        else
            $this->_memory['user'][$name] = memory_get_peak_usage();
    }
    
    
    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param Zend_Controller_Request_Abstract
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if (function_exists('memory_get_peak_usage')) {
            $this->_memory['preDispatch'] = memory_get_peak_usage();
        }
    }

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param Zend_Controller_Request_Abstract
     * @return void
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        if (function_exists('memory_get_peak_usage')) {
            $this->_memory['postDispatch'] = memory_get_peak_usage();
        }
    }
    
    public function getLinebreak()
    {
        return '<br'.$this->getClosingBracket();
    }

    public function getClosingBracket()
    {
        if (!$this->_closingBracket) {
            if ($this->_isXhtml()) {
                $this->_closingBracket = ' />';
            } else {
                $this->_closingBracket = '>';
            }
        }

        return $this->_closingBracket;
    }  
    
    protected function _isXhtml()
    {
        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
        $doctype = $view->doctype();
        return $doctype->isXhtml();
    }
    
}
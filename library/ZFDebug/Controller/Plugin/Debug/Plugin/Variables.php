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
class ZFDebug_Controller_Plugin_Debug_Plugin_Variables extends ZFDebug_Controller_Plugin_Debug_Plugin implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'variables';

    /**
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * Create ZFDebug_Controller_Plugin_Debug_Plugin_Variables
     *
     * @return void
     */
    public function __construct()
    {

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
     * Returns the base64 encoded icon
     *
     * @return string
     **/
    public function getIconData()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAFWSURBVBgZBcE/SFQBAAfg792dppJeEhjZn80MChpqdQ2iscmlscGi1nBPaGkviKKhONSpvSGHcCrBiDDjEhOC0I68sjvf+/V9RQCsLHRu7k0yvtN8MTMPICJieaLVS5IkafVeTkZEFLGy0JndO6vWNGVafPJVh2p8q/lqZl60DpIkaWcpa1nLYtpJkqR1EPVLz+pX4rj47FDbD2NKJ1U+6jTeTRdL/YuNrkLdhhuAZVP6ukqbh7V0TzmtadSEDZXKhhMG7ekZl24jGDLgtwEd6+jbdWAAEY0gKsPO+KPy01+jGgqlUjTK4ZroK/UVKoeOgJ5CpRyq5e2qjhF1laAS8c+Ymk1ZrVXXt2+9+fJBYUwDpZ4RR7Wtf9u9m2tF8Hwi9zJ3/tg5pW2FHVv7eZJHd75TBPD0QuYze7n4Zdv+ch7cfg8UAcDjq7mfwTycew1AEQAAAMB/0x+5JQ3zQMYAAAAASUVORK5CYII=';
    }

    /**
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab()
    {
        return ' Variables';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $this->_request = Zend_Controller_Front::getInstance()->getRequest();
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        if ($viewRenderer->view && method_exists($viewRenderer->view, 'getVars')) {
            $viewVars = $this->_cleanData($viewRenderer->view->getVars());
        } else {
            $viewVars = "No 'getVars()' method in view class";
        }
        $vars = '<div style="width:50%;float:left;">';
        $vars .= '<h4>View variables</h4>'
              . '<div id="ZFDebug_vars" style="margin-left:-22px">' . $viewVars . '</div>'
              . '<h4>Request parameters</h4>'
              . '<div id="ZFDebug_requests" style="margin-left:-22px">' . $this->_cleanData($this->_request->getParams()) . '</div>';
        $vars .= '</div><div style="width:45%;float:left;">';
        if ($this->_request->isPost())
        {
            $vars .= '<h4>Post variables</h4>'
                   . '<div id="ZFDebug_post" style="margin-left:-22px">' . $this->_cleanData($this->_request->getPost()) . '</div>';
        }
        
        $vars .= '<h4>Constants</h4>';
        $constants = get_defined_constants(true);
        ksort($constants['user']);
        $vars .= '<div id="ZFDebug_constants" style="margin-left:-22px">' . $this->_cleanData($constants['user']) . '</div>';

        $registry = Zend_Registry::getInstance();
        $vars .= '<h4>Zend Registry</h4>';
        $registry->ksort();
        $vars .= '<div id="ZFDebug_registry" style="margin-left:-22px">' . $this->_cleanData($registry) . '</div>';

        $cookies = $this->_request->getCookie();
        $vars .= '<h4>Cookies</h4>'
               . '<div id="ZFDebug_cookie" style="margin-left:-22px">' . $this->_cleanData($cookies) . '</div>';

        $vars .= '</div><div style="clear:both">&nbsp;</div>';
        return $vars;
    }

}
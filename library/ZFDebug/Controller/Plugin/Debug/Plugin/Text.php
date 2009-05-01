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
class ZFDebug_Controller_Plugin_Debug_Plugin_Text implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * @var string
     */
    protected $_tab = '';

    /**
     * @var string
     */
    protected $_panel = '';

        /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'text';

    /**
     * @var array
     */
    protected $_timer = array();

    /**
     * Create ZFDebug_Controller_Plugin_Debug_Plugin_Text
     *
     * @return void
     */
    public function __construct($tab = '', $panel = '')
    {
        $this->setTab($tab);
        $this->setPanel($panel);
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
     * Sets identifier for this plugin
     *
     * @param string $name
     * @return ZFDebug_Controller_Plugin_Debug_Plugin_Text Provides a fluent interface
     */
    public function setIdentifier($name)
    {
        $this->_identifier = $name;
        return $this;
    }

    /**
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab()
    {
        return $this->_tab;
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        return $this->_panel;
    }

    /**
     * Sets tab content
     *
     * @param string $tab
     * @return ZFDebug_Controller_Plugin_Debug_Plugin_Text Provides a fluent interface
     */
    public function setTab($tab)
    {
        $this->_tab = $tab;
        return $this;
    }

    /**
     * Sets panel content
     *
     * @param string $panel
     * @return ZFDebug_Controller_Plugin_Debug_Plugin_Text Provides a fluent interface
     */
    public function setPanel($panel)
    {
        $this->_panel = $panel;
        return $this;
    }
}
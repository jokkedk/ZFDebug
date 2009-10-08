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
class ZFDebug_Controller_Plugin_Debug_Plugin_Log
    extends Zend_Log_Writer_Abstract
    implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    protected $_logger;
    protected $_messages = array();
    protected $_errors = 0;
    
    public function __construct()
    {
        $this->_logger = new Zend_Log($this);
    }
    
    public function logger()
    {
        return $this->_logger;
    }
    
    /**
     * Has to return html code for the menu tab
     *
     * @return string
     */
    public function getTab()
    {
        return " Log ($this->_errors)";
    }

    /**
     * Has to return html code for the content panel
     *
     * @return string
     */
    public function getPanel()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $module = $request->getModuleName();
        if ('default' !== $module) {
            $module = " ($module module)";
        } else {
            $module = '';
        }
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        
        $panel = "<h4>Event log for {$controller}Controller->{$action}Action() {$module}</h4>";
        $panel .= implode('<br>', $this->_messages);
        return $panel;
    }

    /**
     * Has to return a unique identifier for the specific plugin
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'log';
    }
    
    
    /**
     * Return the path to an icon
     *
     * @return string
     */
    public function getIconData()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHhSURBVDjLpZI9SJVxFMZ/r2YFflw/kcQsiJt5b1ije0tDtbQ3GtFQYwVNFbQ1ujRFa1MUJKQ4VhYqd7K4gopK3UIly+57nnMaXjHjqotnOfDnnOd/nt85SURwkDi02+ODqbsldxUlD0mvHw09ubSXQF1t8512nGJ/Uz/5lnxi0tB+E9QI3D//+EfVqhtppGxUNzCzmf0Ekojg4fS9cBeSoyzHQNuZxNyYXp5ZM5Mk1ZkZT688b6thIBenG/N4OB5B4InciYBCVyGnEBHO+/LH3SFKQuF4OEs/51ndXMXC8Ajqknrcg1O5PGa2h4CJUqVES0OO7sYevv2qoFBmJ/4gF4boaOrg6rPLYWaYiVfDo0my8w5uj12PQleB0vcp5I6HsHAUoqUhR29zH+5B4IxNTvDmxljy3x2YCYUwZVlbzXJh9UKeQY6t2m0Lt94Oh5loPdqK3EkjzZi4MM/Y9Db3MTv/mYWVxaqkw9IOATNR7B5ABHPrZQrtg9sb8XDKa1+QOwsri4zeHD9SAzE1wxBTXz9xtvMc5ZU5lirLSKIz18nJnhOZjb22YKkhd4odg5icpcoyL669TAAujlyIvmPHSWXY1ti1AmZ8mJ3ElP1ips1/YM3H300g+W+51nc95YPEX8fEbdA2ReVYAAAAAElFTkSuQmCC';
    }
    
    // Logging
    
    /**
     * Write a message to the log.
     *
     * @param  array  $event  event data
     * @return void
     */
    protected function _write($event)
    {
        $output = '<span style="color:%color%;"><span style="width:75px;display:inline-block">%priorityName%</span>: %message%</span>'; // (%priority%)
        $event['color'] = '';
        // Count errors
        if ($event['priority'] < 6) {
            $event['color'] = 'red';
            $this->_errors++;
        }
        
        foreach ($event as $name => $value) {
            if ((is_object($value) && !method_exists($value,'__toString'))
                || is_array($value)) {

                $value = gettype($value);
            }
            $output = str_replace("%$name%", ucfirst(strtolower($value)), $output);
        }
        $this->_messages[] = $output;
    }
}
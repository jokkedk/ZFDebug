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
    const ZFLOG = 10;
    
    protected $_logger;
    protected $_messages = array();
    protected $_errors = 0;
    
    protected $_marks = array();
    
    public function __construct()
    {
        $this->_logger = new Zend_Log($this);
        $this->_logger->addPriority('ZFLOG', self::ZFLOG);
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
        // $this->_logger->zflog('test');
        $tab = " Log";
        if (count($this->_errors)) {
            $tab .= " ($this->_errors)";
        }
        return $tab;
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
        $panel .= implode('', $this->_messages);
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
    
    /**
     * Sets a time mark identified with $name
     *
     * @param string $name
     */
    public function mark($name, $logFirst = false) {
        if (isset($this->_marks[$name])) {
            $this->_marks[$name]['time'] = round((microtime(true)-$_SERVER['REQUEST_TIME'])*1000-$this->_marks[$name]['time']).'ms';
            $this->_marks[$name]['memory'] = round((memory_get_peak_usage()-$this->_marks[$name]['memory'])/1024) . 'K';
            $this->_logger->zflog(
                array('time' => $this->_marks[$name]['time'], 
                      'memory' => $this->_marks[$name]['memory'],
                      'message' => $name . " completed"
                )
            );
        } else {
            $this->_marks[$name]['time'] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
            $this->_marks[$name]['memory'] = memory_get_peak_usage();
            if ($logFirst) {
                $this->_logger->zflog(
                    array('time' => round($this->_marks[$name]['time']).'ms', 
                          'memory' => round($this->_marks[$name]['memory']/1024).'K',
                          'message' => $name
                    )
                );
            }
        }
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
        $output = '<div style="overflow:auto;color:%color%;">';
        $output .= '<div style="width:6em;float:left;text-align:right;margin-right:1em;">%priorityName%</div>';
        $output .= '<div style="width:6em;float:left;text-align:right; margin-right:1em;">%memory%</div>';
        $output .= '<div style="float:left">%message%</div></div>'; // (%priority%)
        $event['color'] = 'lightgrey';
        // Count errors
        if ($event['priority'] < 7) {
            $event['color'] = 'green';
            $this->_errors++;
        }
        if ($event['priority'] < 6) {
            $event['color'] = 'orange';
            $this->_errors++;
        } 
        if ($event['priority'] < 5) {
            $event['color'] = 'red';
        }

        if ($event['priority'] == self::ZFLOG) {
            $event['priorityName'] = $event['message']['time'];
            $event['memory'] = $event['message']['memory'];
            $event['message'] = $event['message']['message'];
        } else {
            // self::$_lastEvent = null;
            $event['message'] = $event['priorityName'] .': '. $event['message'];
            $event['priorityName'] = '&nbsp;';
            $event['memory'] = '&nbsp;';
        }
        foreach ($event as $name => $value) {
            if ('message' == $name) {
                $measure = '&nbsp;';
                if ((is_object($value) && !method_exists($value,'__toString'))) {
                    $value = gettype($value);
                } elseif (is_array($value)) {
                    $measure = $value[0];
                    $value = $value[1];
                }
            }
            $output = str_replace("%$name%", ucfirst(strtolower($value)), $output);
        }
        $this->_messages[] = $output;
    }
}
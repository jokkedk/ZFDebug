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
class ZFDebug_Controller_Plugin_Debug_Plugin_Cache implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'cache';

    /**
     * @var Zend_Cache_Backend_ExtendedInterface
     */
    protected $_cacheBackends = array();

    /**
     * Create ZFDebug_Controller_Plugin_Debug_Plugin_Cache
     *
     * @param Zend_Cache_Backend_ExtendedInterface $backend
     * @return void
     */
    public function __construct($backends = array())
    {
        foreach ($backends as $name => $backend) {
            if ($backend instanceof Zend_Cache_Backend_ExtendedInterface ) {
                $this->_cacheBackends[$name] = $backend;
            }
        }
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
        return ' Cache';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        # Support for APC
        if (function_exists('apc_sma_info') && ini_get('apc.enabled')) {
            $mem = apc_sma_info();
            $mem_size = $mem['num_seg']*$mem['seg_size'];
            $mem_avail = $mem['avail_mem'];
            $mem_used = $mem_size-$mem_avail;
            
            $cache = apc_cache_info();
            
            $panel = '<h4>APC '.phpversion('apc').' Enabled</h4>';
            $panel .= round($mem_avail/1024/1024, 1).'M available, '.round($mem_used/1024/1024, 1).'M used<br />'
                    . $cache['num_entries'].' Files cached ('.round($cache['mem_size']/1024/1024, 1).'M)<br />'
                    . $cache['num_hits'].' Hits ('.round($cache['num_hits'] * 100 / ($cache['num_hits']+$cache['num_misses']), 1).'%)<br />'
                    . $cache['expunges'].' Expunges (cache full count)'; 
        }

        foreach ($this->_cacheBackends as $name => $backend) {
            $fillingPercentage = $backend->getFillingPercentage();
            $ids = $backend->getIds();
            
            # Print full class name, backends might be custom
            $panel .= '<h4>Cache '.$name.' ('.get_class($backend).')</h4>';
            $panel .= count($ids).' Entr'.(count($ids)>1?'ies':'y').'<br />'
                    . 'Filling Percentage: '.$backend->getFillingPercentage().'%<br />';
            
            $cacheSize = 0;
            foreach ($ids as $id)
            {
                # Calculate valid cache size
                $mem_pre = memory_get_usage();
                if ($cached = $backend->load($id)) {
                    $mem_post = memory_get_usage();
                    $cacheSize += $mem_post-$mem_pre;
                    unset($cached);
                }                
            }
            $panel .= 'Valid Cache Size: '.round($cacheSize/1024, 1). 'K';
        }
        return $panel;
    }
}
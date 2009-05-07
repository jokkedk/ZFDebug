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
 * @see Zend_Date
 */
require_once 'Zend/Date.php';

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
    protected $_cacheBackend = null;

    /**
     * @var float
     */
    protected $_fillingPercentage;

    /**
     * @var array
     */
    protected $_ids;

    /**
     * @var Zend_date
     */
    protected $_date = null;

    /**
     * Create ZFDebug_Controller_Plugin_Debug_Plugin_Cache
     *
     * @param Zend_Cache_Backend_ExtendedInterface $backend
     * @return void
     */
    public function __construct(Zend_Cache_Backend_ExtendedInterface $backend)
    {
        $this->_cacheBackend = $backend;
        $this->_loadData();
        $this->_date = new Zend_Date();
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
        return ' Cache (' .  substr(strrchr(get_class($this->_cacheBackend),'_'),1) . ')';
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
            $panel .= round($mem_avail/1024/1024, 1).'M available, '.round($mem_used/1024/1024, 1).'M used<br>'
                    . $cache['num_entries'].' Files cached ('.round($cache['mem_size']/1024/1024, 1).'M)<br>'
                    . $cache['num_hits'].' Hits ('.round($cache['num_hits'] * 100 / ($cache['num_hits']+$cache['num_misses']), 1).'%)'; 
        }
        $panel .= '<h4>Zend_Cache Information</h4>'.
                  '<p>Filling Factor: ' . $this->_fillingPercentage . '%</p>'.
                  '<h4>Ids in Cache:</h4>';

        foreach ($this->_ids as $id)
        {
            $idData = $this->_getMetadata($id);
            $panel .= $id . '<br />';
            $panel .= '<div class="pre">';
            $this->_date->set($idData['mtime']);
            #@todo add support for tags
            $panel .= '   created: ' . $this->_date->toString() . '<br />';
            $this->_date->set($idData['expire']);
            $panel .= '   expires: ' . $this->_date->toString() . '<br />';
            $panel .= '</div>';
        }
        return $panel;
    }

    /**
     * Loads static data
     *
     * @return void
     */
    protected function _loadData()
    {
        $this->_fillingPercentage = $this->_cacheBackend->getFillingPercentage();
        $this->_ids = $this->_cacheBackend->getIds();
    }

    /**
     * Gets Metadata from the Cache Backend
     *
     * @return array
     */
    protected function _getMetadata($id)
    {
        return $this->_cacheBackend->getMetadatas($id);
    }
}
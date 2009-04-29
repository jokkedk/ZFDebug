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
 * @see Zend_Db_Table_Abstract
 */
require_once 'Zend/Db/Table/Abstract.php';

/**
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class ZFDebug_Controller_Plugin_Debug_Plugin_Database implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{

    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'database';

    /**
     * @var Zend_Db_Adapter_Abstract $db
     */
    protected $_db;

    /**
     * Create ZFDebug_Controller_Plugin_Debug_Plugin_Variables
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @return void
     */
    public function __construct(Zend_Db_Adapter_Abstract $db = null)
    {
        if(is_null($db)) {
            $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
        }
        else {
            $this->_db = $db;
        }
        $this->_db->getProfiler()->setEnabled(true);
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
        $profiler = $this->_db->getProfiler();
        $html = $profiler->getTotalNumQueries () . ' in ' . round ( $profiler->getTotalElapsedSecs () * 1000, 2 ) . ' ms';

        return $html;
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $html = '<h4>Database queries</h4>';
        if (Zend_Db_Table_Abstract::getDefaultMetadataCache ()) {
            $html .= 'Metadata cache is ENABLED';
        } else {
            $html .= 'Metadata cache is DISABLED';
        }

        $profiles = $this->_db->getProfiler()->getQueryProfiles();

        $html .= '<ol>';
        foreach ( $profiles as $profile )
        {
            $html .= '<li><strong>[' . round ( $profile->getElapsedSecs () * 1000, 2 ) . ' ms]</strong> '
                   . htmlspecialchars($profile->getQuery());
                    if ($profile->getQueryParams()) {
                        $html .= '<br><br><strong>Parameters</strong> ';
                    }
            $html .= $this->_cleanData($profile->getQueryParams())
                   . '</li>';
        }
        $html .= '</ol>';

        return $html;
    }

    protected function _cleanData($values)
    {
        ksort($values);

        $retVal = '<div class="pre">';
        foreach ($values as $key => $value)
        {
            $key = htmlspecialchars($key);
            if (is_numeric($value)) {
                $retVal .= $key.' => '.$value.'<br>';
            }
            else if (is_string($value)) {
                $retVal .= $key.' => \''.htmlspecialchars($value).'\'<br>';
            }
            else if (is_array($value))
            {
                $retVal .= $key.' => '.self::cleanData($value);
            }
            else if (is_null($value))
            {
                $retVal .= $key.' => NULL<br>';
            }
        }
        return $retVal.'</div>';
    }
}
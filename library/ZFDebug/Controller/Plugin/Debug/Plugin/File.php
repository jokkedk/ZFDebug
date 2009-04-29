<?php
/**
 * ZFDebug Zend Additions
 *
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 * @version    $Id: $
 */

/**
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class ZFDebug_Controller_Plugin_Debug_Plugin_File implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'file';

    /**
     * Base path of this application
     * String is used to strip it from filenames
     *
     * @var string
     */
    protected $_basePath;


    /**
     * Setting Base Path
     *
     * @param string $basePath
     * @revurn void
     */
    public function __construct($basePath = '')
    {
        $this->_basePath = $basePath;
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
        return 'Files (' . count(get_included_files()) . ')';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $included = get_included_files();
        $html = '<h4>File Information</h4>';
        $html .= 'Total Files included: ' . count($included) . '<br />';
        $html .= 'DocumentRoot: ' . $_SERVER['DOCUMENT_ROOT'] . '<br />';
        $html .= '<h4>BasePath: ' . $this->_basePath . '</h4>';

        $frameworkFiles = '<h4>Zend Framework Files</h4>';

        sort($included);
        $included = str_replace($this->_basePath,'',$included);
        $html .= '<h4>Application Files</h4>';
        foreach ($included as $file) {
            if (false === strstr($file, 'Zend'))
                $html .= str_replace($_SERVER['DOCUMENT_ROOT'], '', $file).'<br>';
            else
                $frameworkFiles .= str_replace($_SERVER['DOCUMENT_ROOT'], '', $file).'<br>';
        }
        $html .= $frameworkFiles;
        return $html;
    }
}
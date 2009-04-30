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
    
    protected $_includedFiles = null;


    /**
     * Setting Base Path
     *
     * @param string $basePath
     * @revurn void
     */
    public function __construct($basePath = '')
    {
        if ($basePath == '')
            $basePath = $_SERVER['DOCUMENT_ROOT'];
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
        return 'Files (' . count($this->getIncludedFiles()) . ')';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $included = $this->getIncludedFiles();
        $html = '<h4>File Information</h4>';
        $html .= 'Total Files included: ' . count($included) . '<br />';
        $html .= 'Basepath: ' . $this->_basePath . '<br />';

        $frameworkFiles = '<h4>Zend Framework Files</h4>';
        $zfdebugFiles = '<h4>ZFDebug Files</h4>';

        $html .= '<h4>Application Files</h4>';
        foreach ($included as $file) {
            $file = str_replace($this->_basePath, '', $file);
            if (false !== strstr($file, 'Zend'))
                $frameworkFiles .= $file.'<br>';
            elseif (false !== strstr($file, 'ZFDebug'))
                $zfdebugFiles .= $file.'<br>';
            else
                $html .= $file.'<br>';
        }
        $html .= $frameworkFiles . $zfdebugFiles;
        return $html;
    }
    
    protected function getIncludedFiles()
    {
        if (null !== $this->_includedFiles)
            return $this->_includedFiles;
            
        $this->_includedFiles = get_included_files();
        sort($this->_includedFiles);
        return $this->_includedFiles;
    }
}
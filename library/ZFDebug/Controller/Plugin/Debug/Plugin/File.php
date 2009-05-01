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

    /**
     * Stores included files
     *
     * @var array
     */
    protected $_includedFiles = null;

    /**
     * Stores name of own extension library
     *
     * @var string
     */
    protected $_myLibrary;

    /**
     * Setting Options
     *
     * basepath:
     * This will normally not your document root of your webserver, its your
     * application root directory with /application, /library and /public
     *
     * myLibrary:
     * Your own library extension
     *
     * @param string $basePath
     * @param
     * @return void
     */
    public function __construct($basePath = '', $myLibary = null)
    {
        if ($basePath == '') {
            $basePath = $_SERVER['DOCUMENT_ROOT'];
        }
        $this->_basePath = $basePath;
        $this->_myLibrary = $myLibary;
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
        return 'Files (' . count($this->_getIncludedFiles()) . ')';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $included = $this->_getIncludedFiles();
        $html = '<h4>File Information</h4>';
        $html .= 'Total Files included: ' . count($included) . '<br />';
        $html .= 'Basepath: ' . $this->_basePath . '<br />';

        $frameworkFiles = '<h4>Zend Framework Files</h4>';
        $zfdebugFiles = '<h4>ZFDebug Files</h4>';
        $myFiles = '<h4>' . $this->_myLibrary . ' Files</h4>';

        $html .= '<h4>Application Files</h4>';
        foreach ($included as $file) {
            $file = str_replace($this->_basePath, '', $file);
            if (false !== strstr($file, 'Zend')) {
                $frameworkFiles .= $file . '<br />';
            } elseif (false !== strstr($file, 'ZFDebug')) {
                $zfdebugFiles .= $file . '<br />';
            } else {
            	if (null !== $this->_myLibrary)
            	{
            		if(false !== strstr($file, $this->_myLibrary)) {
            			$myFiles .= $file . '<br />';
            		} else {
            			$html .= $file . '<br />';
            		}
            	} else {
                $html .= $file . '<br />';
            	}
            }
        }

        if(null !== $this->_myLibrary) {
        	$html .= $myFiles;
        }

        $html .=  $zfdebugFiles . $frameworkFiles;
        return $html;
    }

    /**
     * Gets included files
     *
     * @return array
     */
    protected function _getIncludedFiles()
    {
        if (null !== $this->_includedFiles) {
            return $this->_includedFiles;
        }

        $this->_includedFiles = get_included_files();
        sort($this->_includedFiles);
        return $this->_includedFiles;
    }
}
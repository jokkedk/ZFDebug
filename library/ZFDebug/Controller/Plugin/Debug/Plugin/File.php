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
class ZFDebug_Controller_Plugin_Debug_Plugin_File extends ZFDebug_Controller_Plugin_Debug_Plugin implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
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
    protected $_library;

    /**
     * Setting Options
     *
     * basePath:
     * This will normally not your document root of your webserver, its your
     * application root directory with /application, /library and /public
     *
     * library:
     * Your own library extension(s)
     *
     * @param array $options
     * @return void
     */
    public function __construct(array $options = array())
    {
        isset($options['base_path']) || $options['base_path'] = $_SERVER['DOCUMENT_ROOT'];
        isset($options['library']) || $options['library'] = null;
        
        $this->_basePath = realpath($options['base_path']);
        is_array($options['library']) || $options['library'] = array($options['library']);
        $this->_library = array_merge($options['library'], array('Zend', 'ZFDebug'));
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
        return count($this->_getIncludedFiles()) . ' Files';
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
        $html .= count($included).' Files Included'.$this->getLinebreak();
        $size = 0;
        foreach ($included as $file) {
            $size += filesize($file);
        }
        $html .= 'Total Size: '. round($size/1024, 1).'K'.$this->getLinebreak();
        
        $html .= 'Basepath: ' . $this->_basePath .$this->getLinebreak();

        $libraryFiles = array();
        foreach ($this->_library as $key => $value) {
            if ('' != $value) {
                $libraryFiles[$key] = '<h4>' . $value . ' Files</h4>';
            }
        }

        $html .= '<h4>Application Files</h4>';
        foreach ($included as $file) {
            $file = str_replace($this->_basePath, '', $file);
            $filePaths = explode(DIRECTORY_SEPARATOR, $file);
            $inUserLib = false;
        	foreach ($this->_library as $key => $library)
        	{
        		if('' != $library && in_array($library, $filePaths)) {
        			$libraryFiles[$key] .= $file . $this->getLinebreak();
        			$inUserLib = TRUE;
        		}
        	}
        	if (!$inUserLib) {
    			$html .= $file .$this->getLinebreak();
        	}
        }

    	$html .= implode('', $libraryFiles);

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
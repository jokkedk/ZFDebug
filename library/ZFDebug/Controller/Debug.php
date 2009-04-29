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
 * @see Zend_Controller_Exception
 */
require_once 'Zend/Controller/Exception.php';

/**
 * @see Zend_Version
 */
require_once 'Zend/Version.php';

/**
 * @see ZFDebug_Controller_Plugin_Debug_Plugin_Text
 */
require_once 'ZFDebug/Controller/Plugin/Debug/Plugin/Text.php';

/**
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class ZFDebug_Controller_Plugin_Debug extends Zend_Controller_Plugin_Abstract
{
    /**
     * Contains registered plugins
     *
     * @var array
     */
    protected $_plugins = array();

    /**
     * Contains options to change Debug Bar behavior
     */
    protected $_options = array(
        'z-index'           => 255,
        'jquery_path'       => 'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js',
        'image_path'        => '/images/debugbar'
    );

    /**
     * Debug Bar Version Number
     * for internal use only
     *
     * @var string
     */
    protected $_version = '1.5';

    /**
     * Creates a new instance of the Debug Bar
     *
     * @param array|Zend_Config $options
     * @throws Zend_Controller_Exception
     * @return void
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        /*
         * Verify that adapter parameters are in an array.
         */
        if (!is_array($options)) {
            throw new Zend_Controller_Exception('Debug parameters must be in an array or a Zend_Config object');
        }

        if (isset($options['jquery_path'])) {
            $this->_options['jquery_path'] = $options['jquery_path'];
        }

        if (isset($options['z-index'])) {
            $this->_options['z-index'] = $options['z-index'];
        }

        if (isset($options['image_path'])) {
            $this->_options['image_path'] = $options['image_path'];
        }

        /**
         * Creating ZF Version Tab with always schown
         */
        $version = new ZFDebug_Controller_Plugin_Debug_Plugin_Text();
        $version->setPanel($this->getVersionPanel())
                ->setTab($this->getVersionTab())
                ->setIdentifier('copyright');
        $this->registerPlugin($version);
    }

    /**
     * Register a new plugin in the Debug Bar
     *
     * @param ZFDebug_Controller_Plugin_Debug_Plugin_Interface
     * @return ZFDebug_Controller_Plugin_Debug
     */
    public function registerPlugin(ZFDebug_Controller_Plugin_Debug_Plugin_Interface $plugin)
    {
        $this->_plugins[] = $plugin;
        return $this;
    }

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     */
    public function dispatchLoopShutdown()
    {
        $html = '';

        if ($this->getRequest()->isXmlHttpRequest())
            return;

        /**
         * Creating menu tab for all registered plugins
         */
        foreach ($this->_plugins as $plugin)
        {
            /* @var $plugin ZFDebug_Controller_Plugin_Debug_Plugin_Interface */
            $html .= '<div id="ZFDebug_' . $plugin->getIdentifier()
                  . '" class="ZFDebug_panel">' . $plugin->getPanel() . '</div>';
        }

        $html .= '<div id="ZFDebug_info">';

        /**
         * Creating panel content for all registered plugins
         */
        foreach ($this->_plugins as $plugin)
        {
            /* @var $plugin ZFDebug_Controller_Plugin_Debug_Plugin_Interface */
            $html .= '<span class="ZFDebug_span clickable" onclick="ZFDebugPanel(\'ZFDebug_' . $plugin->getIdentifier() . '\');">';
            if(false !== $this->_options['image_path']) {
                $html .= '<img src="' . $this->icon($plugin->getIdentifier()) . '" style="vertical-align:middle" alt="' . $plugin->getIdentifier() . '" title="' . $plugin->getIdentifier() . '" />';
            }
            $html .= $plugin->getTab() . '</span>';
        }

        $html .= '<span class="ZFDebug_span ZFDebug_last clickable" id="ZFDebug_toggler" onclick="ZFDebugSlideBar()">&#171;</span>';

        $html .= '</div>';
        $this->output($html);
    }

    ### INTERNAL METHODS BELOW ###

    /**
     * Return version tab
     *
     * @return string
     */
    protected function getVersionTab()
    {
        return ' ' . Zend_Version::VERSION;
    }

    /**
     * Returns version panel
     *
     * @return string
     */
    protected function getVersionPanel()
    {
        return '<h4>ZFDebug v'.$this->_version.'</h4>' .
               '<p>©2008-2009 <a href="http://jokke.dk">Joakim Nygård</a> & <a href="http://www.bangal.de">Andreas Pankratz</a></p>' .
               'The project is hosted at <a href="http://code.google.com/p/zfdebug/">http://zfdebug.googlecode.com</a> and released under the BSD License</p>' .
               '<p>Includes images from the <a href="http://www.famfamfam.com/lab/icons/silk/">Silk Icon set</a> by Mark James</p>';
    }

    /**
     * Returns path to the specific icon
     *
     * @return string
     */
    protected function icon($kind)
    {
        switch ($kind) {
            case 'database':
                return $this->_options['image_path'] . '/database.png';
                break;
            case 'time':
                return $this->_options['image_path'] . '/time.png';
            case 'memory':
                return $this->_options['image_path'] . '/memory.png';
                break;
            case 'copyright':
                return $this->_options['image_path'] . '/copyright.gif';
                break;
            case 'variables':
                return $this->_options['image_path'] . '/variables.png';
                break;
            case 'exception':
                return $this->_options['image_path'] . '/exception.png';
                 break;
            case 'error':
                return $this->_options['image_path'] . '/error.png';
                break;
            case 'cache':
                return $this->_options['image_path'] . '/cache.png';
                break;
            case 'text':
                return $this->_options['image_path'] . '/text.png';
                break;
            case 'file':
                return $this->_options['image_path'] . '/file.png';
                break;
            default:
                return $this->_options['image_path'] . '/unknown.png';
                break;
        }
    }

    /**
     * Returns html header for the Debug Bar
     *
     * @return string
     */
    protected function headerOutput() {
        return ('
            <style type="text/css" media="screen">
                #ZFDebug_debug { font: 11px/1.4em Lucida Grande, Lucida Sans Unicode, sans-serif; position:fixed; bottom:5px; left:5px; color:#000; z-index: ' . $this->_options['z-index'] . ';}
                #ZFDebug_debug ol {margin:10px 0px; padding:0 25px}
                #ZFDebug_debug li {margin:0 0 10px 0;}
                #ZFDebug_debug .clickable {cursor:pointer}
                #ZFDebug_toggler { font-weight:bold; background:#BFBFBF; }
                .ZFDebug_span { border: 1px solid #999; border-right:0px; background:#DFDFDF; padding: 3px 5px; }
                .ZFDebug_last { border: 1px solid #999; }
                .ZFDebug_panel { text-align:left; position:absolute;bottom:19px;width:600px; max-height:400px; overflow:auto; display:none; background:#E8E8E8; padding:5px; border: 1px solid #999; }
                .ZFDebug_panel .pre {font: 11px/1.4em Monaco, Lucida Console, monospace; margin:0 0 0 22px}
                #ZFDebug_exception { border:1px solid #CD0A0A;display: block; }
            </style>
            <script type="text/javascript" charset="utf-8">
                if (typeof jQuery == "undefined") {
                    var scriptObj = document.createElement("script");
                    scriptObj.src = "'.$this->_options['jquery_path'].'";
                    scriptObj.type = "text/javascript";
                    var head=document.getElementsByTagName("head")[0];
                    head.insertBefore(scriptObj,head.firstChild);
                    window.onload = function(){jQuery.noConflict()};
                }

                function ZFDebugPanel(name) {
                    jQuery(".ZFDebug_panel").each(function(i){
                        if(jQuery(this).css("display") == "block") {
                            jQuery(this).slideUp();
                        } else {
                            if (jQuery(this).attr("id") == name)
                                jQuery(this).slideDown();
                            else
                                jQuery(this).slideUp();
                        }
                    });
                }

                function ZFDebugSlideBar() {
                    if (jQuery("#ZFDebug_debug").position().left > 0) {
                        ZFDebugPanel();
                        jQuery("#ZFDebug_toggler").html("&#187;");
                        return jQuery("#ZFDebug_debug").animate({left:"-"+parseInt(jQuery("#ZFDebug_debug").outerWidth()-jQuery("#ZFDebug_toggler").outerWidth())+"px"}, "normal", "swing");
                    } else {
                        jQuery("#ZFDebug_toggler").html("&#171;");
                        return jQuery("#ZFDebug_debug").animate({left:"5px"}, "normal", "swing");
                    }
                }

                function ZFDebugToggleElement(name, whenHidden, whenVisible){
                    if(jQuery(name).css("display")=="none"){
                        jQuery(whenVisible).show();
                        jQuery(whenHidden).hide();
                    } else {
                        jQuery(whenVisible).hide();
                        jQuery(whenHidden).show();
                    }
                    jQuery(name).slideToggle();
                }
            </script>');
    }

    /**
     * Appends Debug Bar html output to the original page
     *
     * @param string $html
     * @return void
     */
    protected function output($html)
    {
        $response = $this->getResponse();
        $response->setBody(str_ireplace('<head>', '<head>' . $this->headerOutput(), $response->getBody()));
        $response->setBody(str_ireplace('</body>', '<div id="ZFDebug_debug">'.$html.'</div></body>', $response->getBody()));
    }
}
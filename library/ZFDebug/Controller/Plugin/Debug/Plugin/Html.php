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
class ZFDebug_Controller_Plugin_Debug_Plugin_Html 
    extends ZFDebug_Controller_Plugin_Debug_Plugin 
    implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'html';

    /**
     * Create ZFDebug_Controller_Plugin_Debug_Plugin_Html
     *
     * @param string $tab
     * @paran string $panel
     * @return void
     */
    public function __construct()
    {

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
     * Returns the base64 encoded icon
     *
     * @return string
     **/
    public function getIconData()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEdSURBVDjLjZIxTgNBDEXfbDZIlIgmCKWgSpMGxEk4AHehgavQcJY0KRKJJiBQLkCR7PxvmiTsbrJoLY1sy/Ibe+an9XodtqkfSUd+Op0mTlgpidFodKpGRAAwn8/pstI2AHvfbi6KAkndgHZx31iP2/CTE3Q1A0ji6fUjsiFn8fJ4k44mSCmR0sl3QhJXF2fYwftXPl5hsVg0Xr0d2yZnIwWbqrlyOZlMDtc+v33H9eUQO7ACOZAC2Ye8qqIJqCfZRtnIIBnVQH8AdQOqylTZWPBwX+zGj93ZrXU7ZLlcxj5vArYi5/Iweh+BNQCbrVl8/uAMvjvvJbBU/++6rVarGI/HB0BbI4PBgNlsRtGlsL4CK7sAfQX2L6CPwH4BZf1E9tbX5ioAAAAASUVORK5CYII=';
    }

    /**
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab()
    {
        return 'HTML';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $body = Zend_Controller_Front::getInstance()->getResponse()->getBody();
        $liberrors = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHtml($body);
        libxml_use_internal_errors($liberrors);
        $panel = '<h4>HTML Information</h4>';
        $panel .= $this->_isXhtml();
        $linebreak = $this->getLinebreak();
        $panel .= $dom->getElementsByTagName('*')->length.' Tags in ' . round(strlen($body)/1024, 2).'K'.$linebreak
                . $dom->getElementsByTagName('link')->length.' Link Tags'.$linebreak
                . $dom->getElementsByTagName('script')->length.' Script Tags'.$linebreak
                . $dom->getElementsByTagName('img')->length.' Images'.$linebreak
                . '<form method="post" action="http://validator.w3.org/check"><p><input type="hidden" name="fragment" value="'.htmlentities($body).'"'.$this->getClosingBracket().'<input type="submit" value="Validate With W3C"'.$this->getClosingBracket().'</p></form>';
        return $panel;
    }
}
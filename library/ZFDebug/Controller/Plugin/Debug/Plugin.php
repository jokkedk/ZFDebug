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
class ZFDebug_Controller_Plugin_Debug_Plugin
{
    protected $_closingBracket = null;

    public function getLinebreak()
    {
        return '<br'.$this->getClosingBracket();
    }

    public function getClosingBracket()
    {
        if (!$this->_closingBracket) {
            if ($this->_isXhtml()) {
                $this->_closingBracket = ' />';
            } else {
                $this->_closingBracket = '>';
            }
        }

        return $this->_closingBracket;
    }  
    
    protected function _isXhtml()
    {
        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
        $doctype = $view->doctype();
        return $doctype->isXhtml();
    }
    
    /**
     * Transforms data into readable format
     *
     * @param array $values
     * @return string
     */
    protected function _cleanData($values)
    {
        if (is_array($values)) {
            ksort($values);
        }
        $retVal = '<div class="pre">';
        foreach ($values as $key => $value)
        {
            $key = htmlspecialchars($key);
            if (is_numeric($value)) {
                $retVal .= $key.' => '.$value.$this->getLinebreak();
            }
            else if (is_string($value)) {
                $retVal .= $key.' => \''.htmlspecialchars($value).'\''.$this->getLinebreak();
            }
            else if (is_array($value))
            {
                $retVal .= $key.' => '.self::_cleanData($value);
            }
            else if (is_object($value))
            {
                $retVal .= $key.' => '.get_class($value).' Object()'.$this->getLinebreak();
            }
            else if (is_null($value))
            {
                $retVal .= $key.' => NULL'.$this->getLinebreak();
            }
        }
        return $retVal.'</div>';
    }
}
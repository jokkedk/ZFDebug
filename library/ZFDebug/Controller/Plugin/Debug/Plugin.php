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
    /**
     * Transforms data into readable format
     *
     * @param array $values
     * @return string
     */
    protected function _cleanData($values)
    {
        if (is_array($values))
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
                $retVal .= $key.' => '.self::_cleanData($value);
            }
            else if (is_object($value))
            {
                $retVal .= $key.' => '.get_class($value).' Object()<br>';
            }
            else if (is_null($value))
            {
                $retVal .= $key.' => NULL<br>';
            }
        }
        return $retVal.'</div>';
    }
}
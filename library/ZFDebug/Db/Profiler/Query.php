<?php
/**
 * ZFDebug Zend Additions
 *
 * @category   ZFDebug
 * @package    ZFDebug_Db
 * @subpackage Profiler
 * @author     Joakim Nygård
 * @license    http://github.com/jokkedk/ZFDebug/blob/master/license     New BSD License
 * @version    $Id$
 */

 /**
 * @category   ZFDebug
 * @package    ZFDebug_Db
 * @subpackage Profiler
 * @author     Joakim Nygård
 * @license    http://github.com/jokkedk/ZFDebug/blob/master/license     New BSD License
 */
class ZFDebug_Db_Profiler_Query extends Zend_Db_Profiler_Query
{
    protected $_trace = [];

    public function __construct($query, $queryType, $trace)
    {
        $this->_trace = $trace;

        parent::__construct($query, $queryType);
    }

    public function getTrace()
    {
        return $this->_trace;
    }
}

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
class ZFDebug_Db_ZendDbProfiler extends Zend\Db\Adapter\Profiler\Profiler
{
    public function profilerStart($target)
    {
        parent::profilerStart($target);

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $trace = array_filter(
            $trace,
            function ($t) {
                return strstr($t['file'], 'application/') !== false;
            }
        );
        $trace = array_values($trace);
        $trace = array_map(
            function ($t) {
                $t = $t['class'].$t['type'].$t['function'].'() in '
                   . $t['file'] . ':'.$t['line'];
                return $t;
            },
            $trace
        );
        $this->profiles[$this->currentIndex]['trace'] = $trace;

        return $this;
    }

    public function profilerFinish()
    {
        parent::profilerFinish();

        return $this;
    }
}

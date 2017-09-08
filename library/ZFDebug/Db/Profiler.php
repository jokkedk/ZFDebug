<?php

class ZFDebug_Db_Profiler extends Zend_Db_Profiler
{
    /**
     * Undocumented function
     *
     * @param  string  $queryText   SQL statement
     * @param  integer $queryType   OPTIONAL Type of query, one of the Zend_Db_Profiler::* constants
     * @return integer|null
     */
    public function queryStart($queryText, $queryType = null)
    {
        if (!$this->_enabled) {
            return null;
        }

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
                $t = $t['class'].$t['type'].$t['function'].'() '
                   . $t['file'] . ':'.$t['line'];
                return $t;
            },
            $trace
        );

        // make sure we have a query type
        if (null === $queryType) {
            switch (strtolower(substr(ltrim($queryText), 0, 6))) {
                case 'insert':
                    $queryType = self::INSERT;
                    break;
                case 'update':
                    $queryType = self::UPDATE;
                    break;
                case 'delete':
                    $queryType = self::DELETE;
                    break;
                case 'select':
                    $queryType = self::SELECT;
                    break;
                default:
                    $queryType = self::QUERY;
                    break;
            }
        }

        $this->_queryProfiles[] = new ZFDebug_Db_Profiler_Query($queryText, $queryType, $trace);

        end($this->_queryProfiles);

        return key($this->_queryProfiles);
    }
}

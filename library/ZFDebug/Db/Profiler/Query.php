<?php

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

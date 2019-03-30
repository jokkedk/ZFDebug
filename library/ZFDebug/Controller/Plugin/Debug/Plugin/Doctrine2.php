<?php
/**
 * ZFDebug Zend Additions
 *
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2011 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 * @version    $Id$
 */

/**
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2011 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class ZFDebug_Controller_Plugin_Debug_Plugin_Doctrine2
    extends ZFDebug_Controller_Plugin_Debug_Plugin
    implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{

    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'doctrine2';

    /**
     * Contains entityManagers
     * @var array
     */
    protected $_em = array();

    /**
     * Create ZFDebug_Controller_Plugin_Debug_Plugin_Variables
     *
     * @param array $options 
     * @return void
     */
    public function __construct(array $options = array())
    {
        if (isset($options['entityManagers'])) {
            $this->_em = $options['entityManagers'];
        }
    }

    /**
     * Gets icon
     * @return string
     */
    public function getIconData()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAidJREFUeNqUk9tLFHEUxz+/mdnZnV1Tt5ttWVC+pBG+9RAYRNBDICT5D1hgL/VQWRAVEfVoCGURhCBFEj6IRkRFF7BAxPZlIbvZBTQq0q677u5c9tdvZyPaS1QHZh7OnPM93/me8xWC4rAnR6WbuAdSYjRvwWzaVFpSFEZpwvvwGnu4GwJB5OwMfwutNKHXrQFrASJcjTM+RPJMh/wvALOpRVh7+pC6gahegjMxQvLsTvnPAHkN5NxbhB5AfptDy4OMD5PsrQwiRElz5uoJvKdjaMsb0FesxX3yEBGsQiY/YWxopWpvv/gjg8zgSXJvEojapVid5wl3DRLc3qWYfCz8ztgQqf6DsiJA5vZFmZuKIyI1kPyC9zJOvjLYuh9zx2Hk5/doNXU4Dwawpx7JMgA3cVe9VT4YRl/djHOnDzd+vQDSdgiz7QAy9RUcG29ytPwOcrPTiEX1RI7fQqhJeDbSdRVmTn30CLUfhfnvZEdOI7PpChoYAVWo5rmOz0R6XoER4ueTx/IKsv8m/S8G+sp1OK8ukzq1DS1cS85OY+3qwWhs8W8ic+UIzv1LSqMoWjRWziCwsV1dkQWKnjf9WIm3z2/OR1Y12zcvqHWG0RbG0GIN5QDm+s3C3LrbXxmBECK6rLCdgWN+M5a6hew8oc7eIoOJUqulr/VI+8Y5pJP2p+VmnkEogrZ4FaGO7jJ3ikpezV+k93wC790L31R6faNPu5K1fwgwAMKf1kgHZKePAAAAAElFTkSuQmCC';
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
        if (!$this->_em)
            return 'No entitymanagers available';
        $adapterInfo = array();

        foreach ($this->_em as $em) {
            if ($logger = $em->getConnection()->getConfiguration()->getSqlLogger()) {
                $totalTime = 0;
                foreach($logger->queries as $query) {
                    $totalTime += $query['executionMS'];
                }
                $adapterInfo[] = count($logger->queries) . ' in ' . round($totalTime * 1000, 2) . ' ms';
            }
        }
        $html = implode(' / ', $adapterInfo);

        return $html;
    }

    /**
     * Gets content panel for the Debug bar
     *
     * @return string
     */
    public function getPanel()
    {
        if (!$this->_em)
            return '';

        $html = '<h4>Doctrine2 queries - Doctrine2 (Common v' . Doctrine\Common\Version::VERSION .
                ' | DBAL v' . Doctrine\DBAL\Version::VERSION .
                ' | ORM v' . Doctrine\ORM\Version::VERSION .
                ')</h4>';

        foreach ($this->_em as $name => $em) {
            $html .= '<h4>EntityManager ' . $name . '</h4>';
            if ($logger = $em->getConnection()->getConfiguration()->getSqlLogger()) {
                $html .= $this->getProfile($logger);
            } else {
                $html .= "No logger enabled!";
            }
        }

        return $html;
    }

    /**
     * Gets sql queries from the logger
     * @param $logger
     * @return string
     */
    protected function getProfile($logger)
    {
        $queries = '<table cellspacing="0" cellpadding="0" width="100%">';
        foreach($logger->queries as $query) {

            $queries .= "<tr>\n<td style='text-align:right;padding-right:2em;' nowrap>\n"
                               . sprintf('%0.2f', round($query['executionMS']*1000, 2))
                               . "ms</td>\n<td>";
            $params = array();
            if(!empty($query['params'])) {
              $params = $query['params'];
              array_walk($params, array($this, '_addQuotes'));
            }
            $paramCount = count($params);
            if ($paramCount) {
                $queries .= htmlspecialchars(preg_replace(array_fill(0, $paramCount, '/\?/'), $params, $query['sql'], 1));
            } else {
                $queries .= htmlspecialchars($query['sql']);
            }
            $queries .= "</td>\n</tr>\n";
        }
        $queries .= "</table>\n";
        return $queries;
    }

    /**
     * Add quotes to query params
     * @param mixed $value
     * @param mixed $key
     * @return void
     */
    protected function _addQuotes(&$value, $key)
    {
        $value = "'" . $value . "'";
    }

}
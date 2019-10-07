<?php
/**
 * ZFDebug Zend Additions
 *
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    https://github.com/jokkedk/ZFDebug/blob/master/license     New BSD License
 * @version    $Id$
 */

 /**
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    https://github.com/jokkedk/ZFDebug/blob/master/license     New BSD License
 */
class ZFDebug_Controller_Plugin_Debug_Plugin_ZendDb extends ZFDebug_Controller_Plugin_Debug_Plugin implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $identifier = 'zenddb';

    /**
     * @var array
     */
    protected $db = array();

    protected $backtrace = false;

    /**
     * Create ZFDebug_Controller_Plugin_Debug_Plugin_Variables
     *
     * @param Zend\Db\Adapter\AdapterInterface|array $adapters
     * @return void
     */
    public function __construct(array $options = array())
    {
        if ($options['adapter'] instanceof Zend\Db\Adapter\AdapterInterface) {
            $this->db[0] = $options['adapter'];
            if (isset($options['backtrace']) && $options['backtrace']) {
                $this->backtrace = true;
                $this->db[0]->setProfiler(new ZFDebug_Db_ZendDbProfiler);
            } else {
                $this->db[0]->setProfiler(new Zend\Db\Adapter\Profiler\Profiler);
            }
        } else {
            foreach ($options['adapter'] as $name => $adapter) {
                if ($adapter instanceof Zend\Db\AdapterInterface) {
                    $this->db[$name] = $adapter;
                }
            }
        }
    }

    /**
     * Gets identifier for this plugin
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns the base64 encoded icon
     *
     * @return string
     **/
    public function getIconData()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEYSURBVBgZBcHPio5hGAfg6/2+R980k6wmJgsJ5U/ZOAqbSc2GnXOwUg7BESgLUeIQ1GSjLFnMwsKGGg1qxJRmPM97/1zXFAAAAEADdlfZzr26miup2svnelq7d2aYgt3rebl585wN6+K3I1/9fJe7O/uIePP2SypJkiRJ0vMhr55FLCA3zgIAOK9uQ4MS361ZOSX+OrTvkgINSjS/HIvhjxNNFGgQsbSmabohKDNoUGLohsls6BaiQIMSs2FYmnXdUsygQYmumy3Nhi6igwalDEOJEjPKP7CA2aFNK8Bkyy3fdNCg7r9/fW3jgpVJbDmy5+PB2IYp4MXFelQ7izPrhkPHB+P5/PjhD5gCgCenx+VR/dODEwD+A3T7nqbxwf1HAAAAAElFTkSuQmCC';
    }

    /**
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab()
    {
        if (!$this->db) {
            return 'No adapter';
        }
        foreach ($this->db as $adapter) {
            $profiler = $adapter->getProfiler();
            $profiles = $profiler->getProfiles();
            $adapterInfo[] = count($profiles)
                            . ' in '
                            . round(
                                array_reduce(
                                    $profiles,
                                    function ($carry, $item) {
                                        return $carry + $item['elapse'];
                                    }
                                ) * 1000,
                                2
                            ) . ' ms';
        }
        $html = implode(' / ', $adapterInfo);

        return $html;
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        if (!$this->db)
            return '';

        $html = '<h4>Zend\Db queries</h4>';

        return $html . $this->getProfile();
    }

    public function getProfile()
    {
        $queries = '';
        foreach ($this->db as $name => $adapter) {
            if ($profiles = $adapter->getProfiler()->getProfiles()) {
                if (1 < count($this->db)) {
                    $queries .= '<h4>Adapter '.$name.'</h4>';
                }
                $queries .='<table cellspacing="0" cellpadding="0" width="100%">';
                foreach ($profiles as $profile) {
                    $queries .= "<tr>\n<td style='text-align:right;padding-right:2em;' nowrap>\n"
                           . sprintf('%0.2f', $profile['elapse']*1000)
                           . "ms</td>\n<td>";

                    if ($profile['parameters']) {
                        $queryParameters = array_values($profile['parameters']->getNamedArray());
                        $queryParameters[] = null;
                        $parts = explode('?', $profile['sql']);
                        foreach ($parts as $partIndex => $partValue) {
                            $queries .= $partValue . $queryParameters[$partIndex];
                        }
                    } else {
                        $queries .= $profile['sql'];
                    }

                    $queries .= "</td>\n</tr>\n";
                    if ($this->backtrace) {
                        $trace = $profile['trace'];
                        array_walk(
                            $trace,
                            function (&$v, $k) {
                                $v = ($k+1).'. '.$v;
                            }
                        );
                        $queries .= "<tr>\n<td></td>\n<td>".implode('<br>', $trace)."</td>\n</tr>\n";
                    }
                }
                $queries .= "</table>\n";
            }
        }
        return $queries;
    }
}

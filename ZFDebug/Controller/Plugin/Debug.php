<?php
/**
 * ZFDebug
 *
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 Joakim Nygård (http://jokke.dk)
 * @version    $Id$
 */


/**
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 Joakim Nygård (http://jokke.dk)
 */
class ZFDebug_Controller_Plugin_Debug extends Zend_Controller_Plugin_Abstract
{
    protected $db           = array();
    protected $timer        = null;
    protected $memory       = null;
    static    $errors       = array();

    protected $_options = array(
        'database_adapter'  => null,
        'memory_usage'      => true,
        'collect_view_vars' => true,
        'sort_view_vars'    => true,
        'show_exceptions'   => true,
        'handle_errors'     => false,
        'jquery_path'       => 'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js',
    );

    protected $_version = '1.4.1';
    
    public function __construct($options = array())
    {
        $this->timer = array();
        $this->timer['construct'] = round((microtime(true)-$_SERVER['REQUEST_TIME'])*1000, 2);

        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        
        /*
         * Verify that adapter parameters are in an array.
         */
        if (!is_array($options)) {
            throw new Exception('Debug parameters must be in an array or a Zend_Config object');
        }
        
        if (isset($options['jquery_path']))
            $this->_options['jquery_path'] = $options['jquery_path'];

        if (isset($options['handle_errors']) && is_bool($options['handle_errors']))
	        $this->_options['handle_errors'] = $options['handle_errors'];

        if ($this->_options['handle_errors'])
            set_error_handler(array($this, 'errorHandler'));

        if (isset($options['database_adapter'])) {
            if ($options['database_adapter'] instanceof Zend_Db_Adapter_Abstract) {
                $options['database_adapter']->getProfiler()->setEnabled(true);
                $this->db[] = $options['database_adapter'];
            }
            else if (is_array($options['database_adapter'])) {
                foreach ($options['database_adapter'] as $name => $adapter) {
                    if ($adapter instanceof Zend_Db_Adapter_Abstract) {
                        $adapter->getProfiler()->setEnabled(true);
                        $this->db[$name] = $adapter;
                    }
                }
            }
        }
        if (isset($options['memory_usage']) && is_bool($options['memory_usage'])) {
            $this->_options['memory_usage'] = $options['memory_usage'];
            $this->memory = array();
            $this->memory['construct'] = memory_get_peak_usage();
            
        }
        if (isset($options['collect_view_vars']) && is_bool($options['collect_view_vars'])) {
            $this->_options['collect_view_vars'] = $options['collect_view_vars'];
        }
        if (isset($options['sort_view_vars']) && is_bool($options['sort_view_vars'])) {
            $this->_options['sort_view_vars'] = $options['sort_view_vars'];
        }
        if (isset($options['show_exceptions']) && is_bool($options['show_exceptions'])) {
            $this->_options['show_exceptions'] = $options['show_exceptions'];
        }
    }
        
    // public function routeStartup(Zend_Controller_Request_Abstract $request) {
    //     $this->timer['routeStartup'] = round((microtime(true)-$_SERVER['REQUEST_TIME'])*1000, 2);
    // }
    // 
    // public function routeShutdown(Zend_Controller_Request_Abstract $request) {
    //     $this->timer['routeShutdown'] = round((microtime(true)-$_SERVER['REQUEST_TIME'])*1000, 2);
    // }
    
    public function mark($name) {
        if (isset($this->timer['user'][$name]))
            $this->timer['user'][$name] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000-$this->timer['user'][$name];
        else
            $this->timer['user'][$name] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
    }
    
    public function preDispatch(Zend_Controller_Request_Abstract $request) 
    {
        $this->timer['preDispatch'] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
    }
    
    public function postDispatch(Zend_Controller_Request_Abstract $request) 
    { 
        $this->timer['postDispatch'] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
    }
    
    public function dispatchLoopShutdown() 
    {        
        $html = '';

        $response = $this->getResponse();
        if ($this->getRequest()->isXmlHttpRequest())
            return;

        $html .= $this->getVersionPanel();
        if ($this->_options['collect_view_vars']) {
            $html .= $this->getVarsPanel();
        }
        $html .= $this->getDatabasePanel();

        if ($this->_options['show_exceptions'] || $this->_options['handle_errors']) {
    		$html .= $this->getExceptionPanel();
    	}
        // $html .= $this->getErrorsPanel();
        $html .= $this->getFilesPanel();
        
        $html .= '<div id="scienta_info">';
        $html .= $this->getVersionTab();
        if ($this->_options['collect_view_vars']) {
            $html .= $this->getVarsTab();
        }
        $html .= $this->getDatabaseTab();
        # @TODO: Table metadata cache
        # @TODO: Tips for improvements based on findings / settings
        # @TODO: Only counts for current dispatch, not <?=$this->action() in views
        
        if ($this->_options['memory_usage']) {
            $html .= $this->getMemoryTab();
        }
        if ($this->_options['show_exceptions'] || $this->_options['handle_errors']) {
    		$html .= $this->getExceptionTab();
    	}
        $html .= $this->getTimeTab();
        
        $html .= '<span class="scienta_span scienta_last clickable" id="scienta_toggler" onclick="scientaSlideBar()">&#171;</span>';

        $html .= '</div>';
        $this->output($html);        
    }
    
    ### INTERNAL METHODS BELOW ###
    
    protected function getVersionTab()
    {
        return '<span class="scienta_span clickable" onclick="scientaPanel(\'scienta_version\');">'
               .'<img src="'.$this->icon('logo').'" style="vertical-align:middle" alt=""> '
               .Zend_Version::VERSION.'</span>';
    }
    
    protected function getVersionPanel()
    {
        return '<div id="scienta_version" class="scienta_panel">'.
               '<h4>ZFDebug v'.$this->_version.'</h4>'.
               '<p>©2008-2009 <a href="http://jokke.dk/software/scientadebugbar">Joakim Nygård</a><br>
               The project is hosted at <a href="http://code.google.com/p/zfdebug/">http://zfdebug.googlecode.com</a> and released under the BSD License</p>'.
               '<p>Includes images from the <a href="http://www.famfamfam.com/lab/icons/silk/">Silk Icon set</a> by Mark James</p>'.
               '</div>';
    }
    
    protected function getVarsTab()
    {
        return '<span class="scienta_span clickable" onclick="scientaPanel(\'scienta_config\');">'
               .'<img src="'.$this->icon('config').'" style="vertical-align:middle" alt=""> '
               .'variables</span>';
    }
    
    protected function getVarsPanel()
    {
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewVars = $viewRenderer->view->getVars();
        $vars = '<div id="scienta_config" class="scienta_panel">';
        if ($this->getRequest()->isPost()) {
            $vars .= '<h4 class="clickable" onclick="scientaToggleElement(\'#scienta_post\',\'#scienta_post_arrow_left\',\'#scienta_post_arrow_down\')">'
    			   . '$_POST <img id="scienta_post_arrow_down" src="'.$this->icon('arrow_down').'" style="vertical-align:middle;display:none" alt="">'
    			   . '<img id="scienta_post_arrow_left" src="'.$this->icon('arrow_left').'" style="vertical-align:middle" alt="">'
    			   . '</h4>'
                   . '<div id="scienta_post" style="display:none">'.$this->cleanData($this->getRequest()->getPost(), 1).'</div>';
        }
        $vars .= '<h4 class="clickable" onclick="scientaToggleElement(\'#scienta_cookie\',\'#scienta_cookie_arrow_left\',\'#scienta_cookie_arrow_down\')">'
			   . '$_COOKIE <img id="scienta_cookie_arrow_down" src="'.$this->icon('arrow_down').'" style="vertical-align:middle;display:none" alt="">'
			   . '<img id="scienta_cookie_arrow_left" src="'.$this->icon('arrow_left').'" style="vertical-align:middle" alt="">'
			   . '</h4>'
               . '<div id="scienta_cookie" style="display:none">'.$this->cleanData($this->getRequest()->getCookie(), 1).'</div>'
			   . '<h4 class="clickable" onclick="scientaToggleElement(\'#scienta_requests\',\'#scienta_requests_arrow_left\',\'#scienta_requests_arrow_down\')">'
			   . 'Request <img id="scienta_requests_arrow_down" src="'.$this->icon('arrow_down').'" style="vertical-align:middle;display:none" alt="">'
			   . '<img id="scienta_requests_arrow_left" src="'.$this->icon('arrow_left').'" style="vertical-align:middle" alt="">'
			   . '</h4>'
               . '<div id="scienta_requests" style="display:none">'.$this->cleanData($this->getRequest()->getParams(), 1).'</div>'
               . '<h4 class="clickable" onclick="scientaToggleElement(\'#scienta_vars\',\'#scienta_vars_arrow_left\',\'#scienta_vars_arrow_down\')">'
               . 'View vars <img id="scienta_vars_arrow_down" src="'.$this->icon('arrow_down').'" style="vertical-align:middle" alt="">'
			   . '<img id="scienta_vars_arrow_left" src="'.$this->icon('arrow_left').'" style="vertical-align:middle;display:none" alt="">'
			   . '</h4>'
               . '<div id="scienta_vars">'.$this->cleanData($viewVars, 1).'</div>';
        return $vars.'</div>';
    }
    
    protected function getMemoryTab()
    {
        if (function_exists('memory_get_peak_usage')) {
            return '<span class="scienta_span" title="peak usage">'
                   .'<img src="'.$this->icon('memory').'" style="vertical-align:middle" alt=""> '
                   .round(memory_get_peak_usage()/1024)
                   .' KB</span>';
        }
    }
    
    protected function getTimeTab()
    {
        return '<span class="scienta_span clickable" onclick="scientaPanel(\'scienta_files\');">'
               .'<img src="'.$this->icon('time').'" style="vertical-align:middle" alt=""> '
               .round($this->timer['postDispatch'], 2)
               .' ms</span>';
    }
    
    protected function getFilesPanel()
    {
        $frameworkFiles = '<h4>Zend Framework Files</h4>';
        $included = get_included_files();
        sort($included);
        $html = '<div id="scienta_files" class="scienta_panel">';
        $html .= '<h4>Timers</h4>';
        if (isset($this->timer['user']) && count($this->timer['user'])) {
            foreach ($this->timer['user'] as $name => $time) {
                $html .= ''.$name.': '.round($time, 2).' ms<br>';
            }
        }
        $html .= 'Controller: '.round($this->timer['postDispatch']-$this->timer['preDispatch'], 2).' ms<br>';
        $html .= '<h4>Application Files ('.count($included).' total)</h4>';
        foreach ($included as $file) {
            if (false === strstr($file, 'Zend/'))
                $html .= str_replace($_SERVER['DOCUMENT_ROOT'], '', $file).'<br>';
            else
                $frameworkFiles .= str_replace($_SERVER['DOCUMENT_ROOT'], '', $file).'<br>';
        }
        $html .= $frameworkFiles.'</div>';
        return $html;
    }
    
    protected function getDatabaseTab()
    {
        if (!$this->db)
            return '';
            
        foreach ($this->db as $adapter) {
            $profiler = $adapter->getProfiler();
            $adapterInfo[] = $profiler->getTotalNumQueries().' in '.round($profiler->getTotalElapsedSecs()*1000, 2).' ms';
            $queryTime = round($profiler->getTotalElapsedSecs()*1000, 2);
        }
        return ('<span class="scienta_span clickable" onclick="scientaPanel(\'scienta_database\');">'
                .'<img src="'.$this->icon('db').'" style="vertical-align:middle" alt=""> '
                .implode(' / ', $adapterInfo).'</span>');
    }
    
    protected function getDatabasePanel()
    {
        if (!$this->db)
            return '';

        $html = '<div id="scienta_database" class="scienta_panel"><h4>Database queries</h4>';
        if ($cache = Zend_Db_Table_Abstract::getDefaultMetadataCache()) {
            $html .= 'Metadata cache is ENABLED';
        } else {
            $html .= 'Metadata cache is DISABLED';
        }
        foreach ($this->db as $name => $adapter) {
            if ($profiles = $adapter->getProfiler()->getQueryProfiles()) {
                $html .= '<h4>Adapter '.$name.'</h4><ol>';
                foreach ($profiles as $profile) {
                    $html .= '<li><strong>['.round($profile->getElapsedSecs()*1000, 2).' ms]</strong> '
                             .htmlentities($profile->getQuery());
                    if ($profile->getQueryParams())
                        $html .= '<br><br><strong>Parameters</strong> '.$this->cleanData($profile->getQueryParams());
                    $html .= '</li>';
                }
                $html .= '</ol>';
            }
        }
        $html .= '</div>';
        return $html;
    }
    
    public static function errorHandler($level, $message, $file, $line)
    {
        if (!($level & error_reporting()))
            return;
            
        switch ($level) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $type = 'Notice';
                break;
                
            case E_WARNING:
            case E_USER_WARNING:
                $type = 'Warning';
                break;
                
            case E_ERROR:
            case E_USER_ERROR:
                $type = 'Fatal Error';
                break;
                
            default:
                $type = 'Unknown, '.$level;
                break;
        }
        self::$errors[] = array(
            'type'    => $type,
            'message' => $message,
            'file'    => $file,
            'line'    => $line
        );
        
        if (ini_get ('log_errors'))
            error_log (sprintf ("%s: %s in %s on line %d", $type, $message, $file, $line));
        
        return true;
    }

	protected function getExceptionTab(){
		$response = $this->getResponse();
        $errorCount = count(self::$errors);
        if(!$response->isException() && !$errorCount)
			return '';
			
		$error = '';
		$exception = '';
        if ($errorCount)
            $error = ($errorCount==1 ? '1 error' : $errorCount.' errors');
        
		$count = count($response->getException());
		if ($this->_options['show_exceptions'] && $count)
		    $exception = ($count==1) ? '1 exception' : $count.' exceptions';
		$text = $exception.($exception==''||$error==''?'':' - ').$error;
		return '<span class="scienta_span clickable" onclick="scientaPanel(\'scienta_exception\');">'
               .'<img src="'.$this->icon(($this->_options['show_exceptions'] && $count>0)?'exclamation':'error').'" style="vertical-align:middle;" alt=""> '
               .$text.'</span>';		
	}
	
	protected function getExceptionPanel(){
		$response = $this->getResponse();
		$errorCount = count(self::$errors);
		if(!$response->isException() && !$errorCount)
			return '';
		        
		$html = '<div id="scienta_exception" class="scienta_panel">';
		if ($this->_options['show_exceptions']) {
		    foreach($response->getException() as $e){
    			$html .= '<h4>'.get_class($e).': '.$e->getMessage().'</h4><p>thrown in '.$e->getFile().' on line '.$e->getLine().'</p>';
    			$html .= '<h4>Call Stack</h4><ol>';
    			foreach($e->getTrace() as $nr=>$t){
    				$func = $t['function'].'()';
    				if(isset($t['class']))
    					$func = $t['class'].$t['type'].$func;
    				if (!isset($t['file']))
    				    $t['file'] = 'unknown';
    				if (!isset($t['line']))
    				    $t['line'] = 'n/a';
                    $html .= '<li>'.$func.'<br>in '.str_replace($_SERVER['DOCUMENT_ROOT'], '', $t['file']).' on line '.$t['line'].'</li>';
				
    			}
    			$html .= '</ol>';
    		}
		}
		if ($errorCount) {
    		$html .= '<h4>Errors</h4><ol>';
            foreach (self::$errors as $error) {
                $html .= '<li>'
                      .sprintf("%s: %s in %s on line %d", $error['type'], $error['message'], str_replace($_SERVER['DOCUMENT_ROOT'], '', $error['file']), $error['line'])
                      .'</li>';
            }
            $html .= '</ol>';
        }
        return $html.'</div>';
		
        // $html .= '</div>';
        // return $html;
		
		
	}

    protected function icon($kind)
    {
        switch ($kind) {
            case 'db':
                return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEYSURBVBgZBcHPio5hGAfg6/2+R980k6wmJgsJ5U/ZOAqbSc2GnXOwUg7BESgLUeIQ1GSjLFnMwsKGGg1qxJRmPM97/1zXFAAAAEADdlfZzr26miup2svnelq7d2aYgt3rebl585wN6+K3I1/9fJe7O/uIePP2SypJkiRJ0vMhr55FLCA3zgIAOK9uQ4MS361ZOSX+OrTvkgINSjS/HIvhjxNNFGgQsbSmabohKDNoUGLohsls6BaiQIMSs2FYmnXdUsygQYmumy3Nhi6igwalDEOJEjPKP7CA2aFNK8Bkyy3fdNCg7r9/fW3jgpVJbDmy5+PB2IYp4MXFelQ7izPrhkPHB+P5/PjhD5gCgCenx+VR/dODEwD+A3T7nqbxwf1HAAAAAElFTkSuQmCC';
                break;
            case 'time':
                return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAKrSURBVDjLpdPbT9IBAMXx/qR6qNbWUy89WS5rmVtutbZalwcNgyRLLMyuoomaZpRQCt5yNRELL0TkBSXUTBT5hZSXQPwBAvor/fZGazlb6+G8nIfP0znbgG3/kz+Knsbb+xxNV63DLxVLHzqV0vCrfMluzFmw1OW8ePEwf8+WgM1UXDnapVgLePr5Nj9DJBJGFEN8+TzKqL2RzkenV4yl5ws2BXob1WVeZxXhoB+PP0xzt0Bly0fKTePozV5GphYQPA46as+gU5/K+w2w6Ev2Ol/KpNCigM01R2uPgDcQIRSJEYys4JmNoO/y0tbnY9JlxnA9M15bfHZHCnjzVN4x7TLz6fMSJqsPgLAoMvV1niSQBGIbUP3Ki93t57XhItVXjulTQHf9hfk5/xgGyzQTgQjx7xvE4nG0j3UsiiLR1VVaLN3YpkTuNLgZGzRSq8wQUoD16flkOPSF28/cLCYkwqvrrAGXC1UYWtuRX1PR5RhgTJTI1Q4wKwzwWHk4kQI6a04nQ99mUOlczMYkFhPrBMQoN+7eQ35Nhc01SvA7OEMSFzTv8c/0UXc54xfQcj/bNzNmRmNy0zctMpeEQFSio/cdvqUICz9AiEPb+DLK2gE+2MrR5qXPpoAn6mxdr1GBwz1FiclDcAPCEkTXIboByz8guA75eg8WxxDtFZloZIdNKaDu5rnt9UVHE5POep6Zh7llmsQlLBNLSMTiEm5hGXXDJ6qb3zJiLaIiJy1Zpjy587ch1ahOKJ6XHGGiv5KeQSfFun4ulb/josZOYY0di/0tw9YCquX7KZVnFW46Ze2V4wU1ivRYe1UWI1Y1vgkDvo9PGLIoabp7kIrctJXSS8eKtjyTtuDErrK8jIYHuQf8VbK0RJUsLfEg94BfIztkLMvP3v3XN/5rfgIYvAvmgKE6GAAAAABJRU5ErkJggg==';
                break;
            case 'memory':
                return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAGvSURBVDjLpZO7alZREEbXiSdqJJDKYJNCkPBXYq12prHwBezSCpaidnY+graCYO0DpLRTQcR3EFLl8p+9525xgkRIJJApB2bN+gZmqCouU+NZzVef9isyUYeIRD0RTz482xouBBBNHi5u4JlkgUfx+evhxQ2aJRrJ/oFjUWysXeG45cUBy+aoJ90Sj0LGFY6anw2o1y/mK2ZS5pQ50+2XiBbdCvPk+mpw2OM/Bo92IJMhgiGCox+JeNEksIC11eLwvAhlzuAO37+BG9y9x3FTuiWTzhH61QFvdg5AdAZIB3Mw50AKsaRJYlGsX0tymTzf2y1TR9WwbogYY3ZhxR26gBmocrxMuhZNE435FtmSx1tP8QgiHEvj45d3jNlONouAKrjjzWaDv4CkmmNu/Pz9CzVh++Yd2rIz5tTnwdZmAzNymXT9F5AtMFeaTogJYkJfdsaaGpyO4E62pJ0yUCtKQFxo0hAT1JU2CWNOJ5vvP4AIcKeao17c2ljFE8SKEkVdWWxu42GYK9KE4c3O20pzSpyyoCx4v/6ECkCTCqccKorNxR5uSXgQnmQkw2Xf+Q+0iqQ9Ap64TwAAAABJRU5ErkJggg==';
                break;
            case 'logo':
                return 'data:image/gif;base64,R0lGODlhEAAQAPcAAPb7/ef2+VepAGKzAIC8SavSiYS9Stvt0uTx4fX6+ur1632+QMLgrGOuApDIZO738drs0Ofz5t7v2MfjtPP6+t7v12SzAcvnyX2+PaPRhH2+Qmy3H3K5LPP6+cXkwIHAR2+4JHi7NePz8YC/Rc3ozfH49XK5KXq9OrzdpNzu1YrEUqrVkdzw5uTw4d/v2dDow5zOeO3279Hq0m+4JqrUhpnMbeHw3N3w6Mflwm22HmazBODy7tfu3un06r7gsuXy4sTisIzGXvH59ny9PdPr1rXZpMzlu36/Q5bLb+Pw3tDnxNHr1Lfbm+b199/x62q1Fp3NcdjszqTPh/L599vt04/GWmazCPb7/LHZnW63I3W6MXa7MmGuAt/y7Gq1E2m0Eb7cp9frzZLJaO/489bu3HW3N7rerN/v2q7WjIjEVuLx343FVrDXj9nt0cTjvW2zIoPBSNjv4OT09IXDUpvLeeHw3dPqyNLpxs/nwHe8OIvFWrPaoGe0C5zMb83mvHm8Oen06a3Xl9XqyoC/Qr/htWe0DofDU4nFWbPYk7ndqZ/PfYPBTMPhrqHRgoLBSujz55PKadHpxfX6+6LNeqPQfNXt2pPIYH2+O7vcoHi4OOf2+PL5+NTs2N3u1mi1E7XZl4zEVJjLaZHGauby5KTShmSzBO/38s/oz3i7MtbrzMHiuYTCT4fDTtXqye327uDv3JDHXu328JnMcu738LLanvD49ZTJYpPKauX19tvv44jBWo7GWpfKZ+Dv27XcpcrluXu8ONTs16zXleT08qfUjKzUlc7pzm63HaTRfZXKZuj06HG4KavViGe0EcDfqcjmxaDQgZrNdOHz77/ep4/HYL3esnW6LobCS3S5K57OctDp0JXKbez17N7x6cbkwLTZlbXXmLrcnrvdodHr06PQe8jkt5jIa93v13m8OI7CW3O6L3a7Nb7gs6nUjmu2GqjTgZjKaKLQeZnMc4LAReL08rTbopbLbuTx4KDOdtbry7DYmrvfrrPaoXK5K5zOegAAACH5BAEAAAAALAAAAAAQABAAAAhMAAEIHEiwoMGDCBMOlCKgoUMuHghInEiggEOHAC5eJNhQ4UAuAjwIJLCR4AEBDQS2uHiAYLGOHjNqlCmgYAONApQ0jBGzp8+fQH8GBAA7';
                break;
            case 'config':
                return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAFWSURBVBgZBcE/SFQBAAfg792dppJeEhjZn80MChpqdQ2iscmlscGi1nBPaGkviKKhONSpvSGHcCrBiDDjEhOC0I68sjvf+/V9RQCsLHRu7k0yvtN8MTMPICJieaLVS5IkafVeTkZEFLGy0JndO6vWNGVafPJVh2p8q/lqZl60DpIkaWcpa1nLYtpJkqR1EPVLz+pX4rj47FDbD2NKJ1U+6jTeTRdL/YuNrkLdhhuAZVP6ukqbh7V0TzmtadSEDZXKhhMG7ekZl24jGDLgtwEd6+jbdWAAEY0gKsPO+KPy01+jGgqlUjTK4ZroK/UVKoeOgJ5CpRyq5e2qjhF1laAS8c+Ymk1ZrVXXt2+9+fJBYUwDpZ4RR7Wtf9u9m2tF8Hwi9zJ3/tg5pW2FHVv7eZJHd75TBPD0QuYze7n4Zdv+ch7cfg8UAcDjq7mfwTycew1AEQAAAMB/0x+5JQ3zQMYAAAAASUVORK5CYII=';
                break;
            case 'arrow_down':
                return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAABbSURBVCjPY/jPgB8yDCkFB/7v+r/5/+r/i/7P+N/3DYuC7V93/d//fydQ0Zz/9eexKFgtsejLiv8b/8/8X/WtUBGrGyZLdH6f8r/sW64cTkdWSRS+zpQbgiEJAI4UCqdRg1A6AAAAAElFTkSuQmCC';
                break;
            case 'arrow_left':
                return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAAOZpQ0NQSUNDIFByb2ZpbGUAAHgBY2BgrEgsKMhhEmBgyM0rKXIPcoyMiIxSYL/KwM7AyAAGicnFBY4BAT4QHjby2zWI2su6ILMcE+ueGbjdZztZ41/7ehrTCmw6kMRYUlKLk4H8LUBcmlxQVMLAwKgDZKtnhwQ5A9khQDZfeUkBSDwFyBaBqgcyGaSdE3Myk4oSS1JTFNyLEisVnPNz8ouKCxKTU0HS1AUlqRVANzAwOOcXVBZlpmeUKDgCfZsKtDO3oLQktUhHwTMvWY+BIbm0qAxqMyOTJQMDKDwh/M+B4HBiFDsDAG8LPGRX4aBMAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAaklEQVQoFWNgGAKAEdmNvd8+3XwbNOU+shgTMoebU9aA92qaHLIYigIuBnEGBU7+G8hKUBRwMrAxSDDIc3KfRZiBooADqOA7w5cfLLo4FLAz/Gb49ZVJsfsFDgVsDH++caiUIUkjFA4kCwAZfxXcw7TN/gAAAABJRU5ErkJggg==';
                break;
			case 'exclamation':
				return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJPSURBVDjLpZPLS5RhFMYfv9QJlelTQZwRb2OKlKuINuHGLlBEBEOLxAu46oL0F0QQFdWizUCrWnjBaDHgThCMoiKkhUONTqmjmDp2GZ0UnWbmfc/ztrC+GbM2dXbv4ZzfeQ7vefKMMfifyP89IbevNNCYdkN2kawkCZKfSPZTOGTf6Y/m1uflKlC3LvsNTWArr9BT2LAf+W73dn5jHclIBFZyfYWU3or7T4K7AJmbl/yG7EtX1BQXNTVCYgtgbAEAYHlqYHlrsTEVQWr63RZFuqsfDAcdQPrGRR/JF5nKGm9xUxMyr0YBAEXXHgIANq/3ADQobD2J9fAkNiMTMSFb9z8ambMAQER3JC1XttkYGGZXoyZEGyTHRuBuPgBTUu7VSnUAgAUAWutOV2MjZGkehgYUA6O5A0AlkAyRnotiX3MLlFKduYCqAtuGXpyH0XQmOj+TIURt51OzURTYZdBKV2UBSsOIcRp/TVTT4ewK6idECAihtUKOArWcjq/B8tQ6UkUR31+OYXP4sTOdisivrkMyHodWejlXwcC38Fvs8dY5xaIId89VlJy7ACpCNCFCuOp8+BJ6A631gANQSg1mVmOxxGQYRW2nHMha4B5WA3chsv22T5/B13AIicWZmNZ6cMchTXUe81Okzz54pLi0uQWp+TmkZqMwxsBV74Or3od4OISPr0e3SHa3PX0f3HXKofNH/UIG9pZ5PeUth+CyS2EMkEqs4fPEOBJLsyske48/+xD8oxcAYPzs4QaS7RR2kbLTTOTQieczfzfTv8QPldGvTGoF6/8AAAAASUVORK5CYII=';
				break;
			case 'error':
			    return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIsSURBVDjLpVNLSJQBEP7+h6uu62vLVAJDW1KQTMrINQ1vPQzq1GOpa9EppGOHLh0kCEKL7JBEhVCHihAsESyJiE4FWShGRmauu7KYiv6Pma+DGoFrBQ7MzGFmPr5vmDFIYj1mr1WYfrHPovA9VVOqbC7e/1rS9ZlrAVDYHig5WB0oPtBI0TNrUiC5yhP9jeF4X8NPcWfopoY48XT39PjjXeF0vWkZqOjd7LJYrmGasHPCCJbHwhS9/F8M4s8baid764Xi0Ilfp5voorpJfn2wwx/r3l77TwZUvR+qajXVn8PnvocYfXYH6k2ioOaCpaIdf11ivDcayyiMVudsOYqFb60gARJYHG9DbqQFmSVNjaO3K2NpAeK90ZCqtgcrjkP9aUCXp0moetDFEeRXnYCKXhm+uTW0CkBFu4JlxzZkFlbASz4CQGQVBFeEwZm8geyiMuRVntzsL3oXV+YMkvjRsydC1U+lhwZsWXgHb+oWVAEzIwvzyVlk5igsi7DymmHlHsFQR50rjl+981Jy1Fw6Gu0ObTtnU+cgs28AKgDiy+Awpj5OACBAhZ/qh2HOo6i+NeA73jUAML4/qWux8mt6NjW1w599CS9xb0mSEqQBEDAtwqALUmBaG5FV3oYPnTHMjAwetlWksyByaukxQg2wQ9FlccaK/OXA3/uAEUDp3rNIDQ1ctSk6kHh1/jRFoaL4M4snEMeD73gQx4M4PsT1IZ5AfYH68tZY7zv/ApRMY9mnuVMvAAAAAElFTkSuQmCC';
			    break;
            default:
                # code...
                break;
        }
    }
        
    protected function headerOutput() {
        return ('
            <style type="text/css" media="screen">
                #scienta_debug { font: 11px/1.4em Lucida Grande, Lucida Sans Unicode, sans-serif; position:fixed; bottom:5px; left:5px; color:#000; }
                #scienta_debug ol {margin:10px 0px; padding:0 25px}
                #scienta_debug li {margin:0 0 10px 0;}
                #scienta_debug .clickable {cursor:pointer}
                #scienta_toggler { font-weight:bold; background:#BFBFBF; }
                .scienta_span { border: 1px solid #999; border-right:0px; background:#DFDFDF; padding: 3px 5px; }
                .scienta_last { border: 1px solid #999; }
                .scienta_panel { position:absolute;bottom:19px;width:600px; max-height:400px; overflow:auto; display:none; background:#E8E8E8; padding:5px; border: 1px solid #999; }
                .scienta_panel .pre {font: 11px/1.4em Monaco, Lucida Console, monospace; margin:0 0 0 22px}
				#scienta_exception { border:1px solid #CD0A0A;display: block; }
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
                
                function scientaPanel(name) {
                    jQuery(".scienta_panel").each(function(i){
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
                
                function scientaSlideBar() {
                    if (jQuery("#scienta_debug").position().left > 0) {
                        scientaPanel();
                        jQuery("#scienta_toggler").html("&#187;");
                        return jQuery("#scienta_debug").animate({left:"-"+parseInt(jQuery("#scienta_debug").outerWidth()-jQuery("#scienta_toggler").outerWidth())+"px"}, "normal", "swing");
                    } else {
                        jQuery("#scienta_toggler").html("&#171;");
                        return jQuery("#scienta_debug").animate({left:"5px"}, "normal", "swing");
                    }
                }
                
				function scientaToggleElement(name, whenHidden, whenVisible){
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
    
    protected function output($html) 
    {
        $response = $this->getResponse();
        $response->setBody(str_ireplace('</head>', $this->headerOutput().'</head>', $response->getBody()));
        $response->setBody(str_ireplace('</body>', '<div id="scienta_debug">'.$html.'</div></body>', $response->getBody()));
    }
    
    protected function cleanData($values, $first = 0)
    {
        if ($this->_options['sort_view_vars'])
            ksort($values);
            
        $retVal = '<div class="pre">';
        foreach ($values as $key => $value)
        {
            $key = htmlentities($key);
            if (is_numeric($value)) {
                $retVal .= $key.' => '.$value.'<br>';
            }
            else if (is_string($value)) {
                $retVal .= $key.' => \''.htmlentities($value).'\'<br>';
            }
            else if (is_array($value))
            {
                $retVal .= $key.' => '.self::cleanData($value);
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

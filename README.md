# ZFDebug - a debug bar for Zend Framework
ZFDebug is a plugin for the Zend Framework for PHP5, providing useful debug information displayed in a small bar at the bottom of every page.

Time spent, memory usage and number of database queries are presented at a glance. Additionally, included files, a listing of available view variables and the complete SQL command of all queries are shown in separate panels:

![](http://jokke.dk/media/2011-zfdebug.png)

The available plugins at this point are:

  * Cache: Information on Zend_Cache and APC.
  * Database: Full listing of SQL queries from Zend_Db and the time for each.
  * Exception: Error handling of errors and exceptions.
  * File: Number and size of files included with complete list.
  * Html: Number of external stylesheets and javascripts. Link to validate with W3C.
for custom memory measurements.
  * Log: Timing information of current request, time spent in action controller and custom timers. Also average, min and max time for requests.
  * Variables: View variables, request info and contents of `$_COOKIE`, `$_POST` and `$_SESSION`
  
Installation & Usage
------------
To install, place the folder 'ZFDebug' in your library path, next to the Zend
folder. Then add the following method to your bootstrap class (in ZF1.8+):

	protected function _initZFDebug()
	{
	    $autoloader = Zend_Loader_Autoloader::getInstance();
	    $autoloader->registerNamespace('ZFDebug');

	    $options = array(
	        'plugins' => array('Variables', 
	                           'Database' => array('adapter' => $db), 
	                           'File' => array('basePath' => '/path/to/project'),
	                           'Cache' => array('backend' => $cache->getBackend()), 
	                           'Exception')
	    );
	    $debug = new ZFDebug_Controller_Plugin_Debug($options);

	    $this->bootstrap('frontController');
	    $frontController = $this->getResource('frontController');
	    $frontController->registerPlugin($debug);
	}

Further documentation will follow as the github move progresses.
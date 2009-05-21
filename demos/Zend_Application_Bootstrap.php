<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initZFDebug()
    {
        // Setup autoloader with namespace
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('ZFDebug');

        // Ensure the front controller is initialized
        $this->bootstrap('FrontController');

        // Retrieve the front controller from the bootstrap registry
        $front = $this->getResource('FrontController');

        // Only enable zfdebug if options have been specified for it
        if ($this->hasOption('zfdebug'))
        {
            // Create ZFDebug instance
            $zfdebug = new ZFDebug_Controller_Plugin_Debug($this->getOption('zfdebug'));

            // Register ZFDebug with the front controller
            $front->registerPlugin($zfdebug);
        }
        // In application.ini do the following:
        // 
        // [development : production]
        // zfdebug.plugins.Variables = null
        // zfdebug.plugins.Time = null
        // zfdebug.plugins.Memory = null
        // ...
        
        // Plugins that take objects as parameters like Database and Cache
        // need to be registered manually:
        
        // $zfdebug->registerPlugin(new ZFDebug_Controller_Plugin_Debug_Plugin_Database($db));
        
        
        // Alternative configuration without application.ini
        // $options = array(
        //     'plugins' => array('variables', 'database', 
        //                        'file' => array('basePath' => '/Library/WebServer/Documents/budget', 'myLibrary' => 'Scienta'),
        //                        'memory', 'time', 'registry', 
        //                        //'auth',
        //                        //'cache' => array('backend' => $cache->getBackend()), 
        //                        'exception')
        // );
        // $zfdebug = new ZFDebug_Controller_Plugin_Debug($options);
        // Register ZFDebug with the front controller
        // $front->registerPlugin($zfdebug);
    }

}


<?php
/**
 * ZFDebug Zend Additions
 *
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Controller_Exception
 */
require_once 'Zend/Controller/Exception.php';

/**
 * @see Zend_Version
 */
require_once 'Zend/Version.php';

/**
 * @see ZFDebug_Controller_Plugin_Debug_Plugin_Text
 */
require_once 'ZFDebug/Controller/Plugin/Debug/Plugin/Text.php';

/**
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class ZFDebug_Controller_Plugin_Debug extends Zend_Controller_Plugin_Abstract
{
    /**
     * Contains registered plugins
     *
     * @var array
     */
    protected $_plugins = array();

    /**
     * Contains options to change Debug Bar behavior
     */
    protected $_options = array(
        'plugins'           => array (
            'variables' => null,
            'time' => null,
            'memory' => null),
        'z-index'           => 255,
        'jquery_path'       => 'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js',
        'image_path'        => null
    );

    /**
     * Debug Bar Version Number
     * for internal use only
     *
     * @var string
     */
    protected $_version = '1.5';

    /**
     * Creates a new instance of the Debug Bar
     *
     * @param array|Zend_Config $options
     * @throws Zend_Controller_Exception
     * @return void
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        /*
         * Verify that adapter parameters are in an array.
         */
        if (!is_array($options)) {
            throw new Zend_Controller_Exception('Debug parameters must be in an array or a Zend_Config object');
        }

        if (isset($options['jquery_path'])) {
            $this->_options['jquery_path'] = $options['jquery_path'];
        }

        if (isset($options['z-index'])) {
            $this->_options['z-index'] = $options['z-index'];
        }

        if (isset($options['image_path'])) {
            $this->_options['image_path'] = $options['image_path'];
        }

        if (isset($options['plugins'])) {
        	$this->_options['plugins'] = $options['plugins'];
        }

        /**
         * Creating ZF Version Tab always shown
         */
        $version = new ZFDebug_Controller_Plugin_Debug_Plugin_Text();
        $version->setPanel($this->_getVersionPanel())
                ->setTab($this->_getVersionTab())
                ->setIdentifier('copyright');
        $this->registerPlugin($version);

        /**
         * Loading aready defined plugins
         */
        $this->_loadPlugins();
    }

    /**
     * Register a new plugin in the Debug Bar
     *
     * @param ZFDebug_Controller_Plugin_Debug_Plugin_Interface
     * @return ZFDebug_Controller_Plugin_Debug
     */
    public function registerPlugin(ZFDebug_Controller_Plugin_Debug_Plugin_Interface $plugin)
    {
        $this->_plugins[] = $plugin;
        return $this;
    }

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     */
    public function dispatchLoopShutdown()
    {
        $html = '';

        if ($this->getRequest()->isXmlHttpRequest())
            return;

        /**
         * Creating menu tab for all registered plugins
         */
        foreach ($this->_plugins as $plugin)
        {
            $panel = $plugin->getPanel();
            if ($panel == '') {
                continue;
            }

            /* @var $plugin ZFDebug_Controller_Plugin_Debug_Plugin_Interface */
            $html .= '<div id="ZFDebug_' . $plugin->getIdentifier()
                  . '" class="ZFDebug_panel">' . $panel . '</div>';
        }

        $html .= '<div id="ZFDebug_info">';

        /**
         * Creating panel content for all registered plugins
         */
        foreach ($this->_plugins as $plugin)
        {
            $tab = $plugin->getTab();
            if ($tab == '') {
                continue;
            }

            /* @var $plugin ZFDebug_Controller_Plugin_Debug_Plugin_Interface */
            $html .= '<span class="ZFDebug_span clickable" onclick="ZFDebugPanel(\'ZFDebug_' . $plugin->getIdentifier() . '\');">';
            $html .= '<img src="' . $this->_icon($plugin->getIdentifier()) . '" style="vertical-align:middle" alt="' . $plugin->getIdentifier() . '" title="' . $plugin->getIdentifier() . '" /> ';
            $html .= $tab . '</span>';
        }

        $html .= '<span class="ZFDebug_span ZFDebug_last clickable" id="ZFDebug_toggler" onclick="ZFDebugSlideBar()">&#171;</span>';

        $html .= '</div>';
        $this->_output($html);
    }

    ### INTERNAL METHODS BELOW ###

    /**
     * Load plugins set in config option
     *
     * @return void;
     */
    protected function _loadPlugins()
    {
    	foreach($this->_options['plugins'] as $plugin => $options) {
    		switch ($plugin) {
    			case 'variables':
    				/**
    				 * @see ZFDebug_Controller_Plugin_Debug_Plugin_Variables
    				 */
    				require_once 'ZFDebug/Controller/Plugin/Debug/Plugin/Variables.php';
    				$object = new ZFDebug_Controller_Plugin_Debug_Plugin_Variables();
    				break;
    			case 'file':
    				/**
                     * @see ZFDebug_Controller_Plugin_Debug_Plugin_File
                     */
                    require_once 'ZFDebug/Controller/Plugin/Debug/Plugin/File.php';
                    if(isset($options['basePath']) && isset($options['myLibrary'])) {
                        $object = new ZFDebug_Controller_Plugin_Debug_Plugin_File($options['basePath'],$options['myLibrary']);
                    } else {
                    	$object = new ZFDebug_Controller_Plugin_Debug_Plugin_File();
                    }
                    break;
                case 'cache':
                    /**
                     * @see ZFDebug_Controller_Plugin_Debug_Plugin_Cache
                     */
                    require_once 'ZFDebug/Controller/Plugin/Debug/Plugin/Cache.php';
                    if(isset($options['backend'])) {
                        $object = new ZFDebug_Controller_Plugin_Debug_Plugin_Cache($options['backend']);
                    } else {
                    	$object = new ZFDebug_Controller_Plugin_Debug_Plugin_Cache();
                    }
                    break;
                case 'database':
                    /**
                     * @see ZFDebug_Controller_Plugin_Debug_Plugin_Database
                     */
                    require_once 'ZFDebug/Controller/Plugin/Debug/Plugin/Database.php';
                    if(isset($options['adapter'])) {
                        $object = new ZFDebug_Controller_Plugin_Debug_Plugin_Database($options['adapter']);
                    } else {
                        $object = new ZFDebug_Controller_Plugin_Debug_Plugin_Database();
                    }
                    break;
                case 'exception':
                    /**
                     * @see ZFDebug_Controller_Plugin_Debug_Plugin_Exception
                     */
                    require_once 'ZFDebug/Controller/Plugin/Debug/Plugin/Exception.php';
                    $object = new ZFDebug_Controller_Plugin_Debug_Plugin_Exception();
                    break;
                case 'memory':
                    /**
                     * @see ZFDebug_Controller_Plugin_Debug_Plugin_Memory
                     */
                    require_once 'ZFDebug/Controller/Plugin/Debug/Plugin/Memory.php';
                    $object = new ZFDebug_Controller_Plugin_Debug_Plugin_Memory();
                    break;
                case 'text':
                    /**
                     * @see ZFDebug_Controller_Plugin_Debug_Plugin_Text
                     */
                    require_once 'ZFDebug/Controller/Plugin/Debug/Plugin/Text.php';
                    if(isset($options['tab']) && isset($options['panel'])) {
                        $object = new ZFDebug_Controller_Plugin_Debug_Plugin_Text($options['tab'],$options['panel']);
                    } else {
                    	$object = new ZFDebug_Controller_Plugin_Debug_Plugin_Text();
                    }
                    break;
                case 'auth':
                    /**
                     * @see ZFDebug_Controller_Plugin_Debug_Plugin_Auth
                     */
                    require_once 'ZFDebug/Controller/Plugin/Debug/Plugin/Auth.php';
                    if(isset($options['user']) && isset($options['role'])) {
                        $object = new ZFDebug_Controller_Plugin_Debug_Plugin_Text($options['user'],$options['role']);
                    } else {
                        $object = new ZFDebug_Controller_Plugin_Debug_Plugin_Text();
                    }
                    break;
                case 'time':
                    /**
                     * @see ZFDebug_Controller_Plugin_Debug_Plugin_Time
                     */
                    require_once 'ZFDebug/Controller/Plugin/Debug/Plugin/Time.php';
                    $object = new ZFDebug_Controller_Plugin_Debug_Plugin_Time();
                    break;
                default:
                	throw new Zend_Controller_Exception('Plugin ZFDebug: Debug plugin "' . $plugin . '" unknown');
                	;
    		}
    		$this->registerPlugin($object);
    	}
    }

    /**
     * Return version tab
     *
     * @return string
     */
    protected function _getVersionTab()
    {
        return ' ' . Zend_Version::VERSION;
    }

    /**
     * Returns version panel
     *
     * @return string
     */
    protected function _getVersionPanel()
    {
        return '<h4>ZFDebug v'.$this->_version.'</h4>' .
               '<p>©2008-2009 <a href="http://jokke.dk">Joakim Nygård</a> & <a href="http://www.bangal.de">Andreas Pankratz</a></p>' .
               'The project is hosted at <a href="http://code.google.com/p/zfdebug/">http://zfdebug.googlecode.com</a> and released under the BSD License</p>' .
               '<p>Includes images from the <a href="http://www.famfamfam.com/lab/icons/silk/">Silk Icon set</a> by Mark James</p>';
    }

    /**
     * Returns path to the specific icon
     *
     * @return string
     */
    protected function _icon($kind)
    {
        switch ($kind) {
            case 'database':
                if (null === $this->_options['image_path'])
                    return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEYSURBVBgZBcHPio5hGAfg6/2+R980k6wmJgsJ5U/ZOAqbSc2GnXOwUg7BESgLUeIQ1GSjLFnMwsKGGg1qxJRmPM97/1zXFAAAAEADdlfZzr26miup2svnelq7d2aYgt3rebl585wN6+K3I1/9fJe7O/uIePP2SypJkiRJ0vMhr55FLCA3zgIAOK9uQ4MS361ZOSX+OrTvkgINSjS/HIvhjxNNFGgQsbSmabohKDNoUGLohsls6BaiQIMSs2FYmnXdUsygQYmumy3Nhi6igwalDEOJEjPKP7CA2aFNK8Bkyy3fdNCg7r9/fW3jgpVJbDmy5+PB2IYp4MXFelQ7izPrhkPHB+P5/PjhD5gCgCenx+VR/dODEwD+A3T7nqbxwf1HAAAAAElFTkSuQmCC';

                return $this->_options['image_path'] . '/database.png';
                break;
            case 'time':
                if (null === $this->_options['image_path'])
                    return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAKrSURBVDjLpdPbT9IBAMXx/qR6qNbWUy89WS5rmVtutbZalwcNgyRLLMyuoomaZpRQCt5yNRELL0TkBSXUTBT5hZSXQPwBAvor/fZGazlb6+G8nIfP0znbgG3/kz+Knsbb+xxNV63DLxVLHzqV0vCrfMluzFmw1OW8ePEwf8+WgM1UXDnapVgLePr5Nj9DJBJGFEN8+TzKqL2RzkenV4yl5ws2BXob1WVeZxXhoB+PP0xzt0Bly0fKTePozV5GphYQPA46as+gU5/K+w2w6Ev2Ol/KpNCigM01R2uPgDcQIRSJEYys4JmNoO/y0tbnY9JlxnA9M15bfHZHCnjzVN4x7TLz6fMSJqsPgLAoMvV1niSQBGIbUP3Ki93t57XhItVXjulTQHf9hfk5/xgGyzQTgQjx7xvE4nG0j3UsiiLR1VVaLN3YpkTuNLgZGzRSq8wQUoD16flkOPSF28/cLCYkwqvrrAGXC1UYWtuRX1PR5RhgTJTI1Q4wKwzwWHk4kQI6a04nQ99mUOlczMYkFhPrBMQoN+7eQ35Nhc01SvA7OEMSFzTv8c/0UXc54xfQcj/bNzNmRmNy0zctMpeEQFSio/cdvqUICz9AiEPb+DLK2gE+2MrR5qXPpoAn6mxdr1GBwz1FiclDcAPCEkTXIboByz8guA75eg8WxxDtFZloZIdNKaDu5rnt9UVHE5POep6Zh7llmsQlLBNLSMTiEm5hGXXDJ6qb3zJiLaIiJy1Zpjy587ch1ahOKJ6XHGGiv5KeQSfFun4ulb/josZOYY0di/0tw9YCquX7KZVnFW46Ze2V4wU1ivRYe1UWI1Y1vgkDvo9PGLIoabp7kIrctJXSS8eKtjyTtuDErrK8jIYHuQf8VbK0RJUsLfEg94BfIztkLMvP3v3XN/5rfgIYvAvmgKE6GAAAAABJRU5ErkJggg==';

                return $this->_options['image_path'] . '/time.png';
            case 'memory':
                if (null === $this->_options['image_path'])
                    return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAGvSURBVDjLpZO7alZREEbXiSdqJJDKYJNCkPBXYq12prHwBezSCpaidnY+graCYO0DpLRTQcR3EFLl8p+9525xgkRIJJApB2bN+gZmqCouU+NZzVef9isyUYeIRD0RTz482xouBBBNHi5u4JlkgUfx+evhxQ2aJRrJ/oFjUWysXeG45cUBy+aoJ90Sj0LGFY6anw2o1y/mK2ZS5pQ50+2XiBbdCvPk+mpw2OM/Bo92IJMhgiGCox+JeNEksIC11eLwvAhlzuAO37+BG9y9x3FTuiWTzhH61QFvdg5AdAZIB3Mw50AKsaRJYlGsX0tymTzf2y1TR9WwbogYY3ZhxR26gBmocrxMuhZNE435FtmSx1tP8QgiHEvj45d3jNlONouAKrjjzWaDv4CkmmNu/Pz9CzVh++Yd2rIz5tTnwdZmAzNymXT9F5AtMFeaTogJYkJfdsaaGpyO4E62pJ0yUCtKQFxo0hAT1JU2CWNOJ5vvP4AIcKeao17c2ljFE8SKEkVdWWxu42GYK9KE4c3O20pzSpyyoCx4v/6ECkCTCqccKorNxR5uSXgQnmQkw2Xf+Q+0iqQ9Ap64TwAAAABJRU5ErkJggg==';

                return $this->_options['image_path'] . '/memory.png';
                break;
            case 'copyright':
                if (null === $this->_options['image_path'])
                    return 'data:image/gif;base64,R0lGODlhEAAQAPcAAPb7/ef2+VepAGKzAIC8SavSiYS9Stvt0uTx4fX6+ur1632+QMLgrGOuApDIZO738drs0Ofz5t7v2MfjtPP6+t7v12SzAcvnyX2+PaPRhH2+Qmy3H3K5LPP6+cXkwIHAR2+4JHi7NePz8YC/Rc3ozfH49XK5KXq9OrzdpNzu1YrEUqrVkdzw5uTw4d/v2dDow5zOeO3279Hq0m+4JqrUhpnMbeHw3N3w6Mflwm22HmazBODy7tfu3un06r7gsuXy4sTisIzGXvH59ny9PdPr1rXZpMzlu36/Q5bLb+Pw3tDnxNHr1Lfbm+b199/x62q1Fp3NcdjszqTPh/L599vt04/GWmazCPb7/LHZnW63I3W6MXa7MmGuAt/y7Gq1E2m0Eb7cp9frzZLJaO/489bu3HW3N7rerN/v2q7WjIjEVuLx343FVrDXj9nt0cTjvW2zIoPBSNjv4OT09IXDUpvLeeHw3dPqyNLpxs/nwHe8OIvFWrPaoGe0C5zMb83mvHm8Oen06a3Xl9XqyoC/Qr/htWe0DofDU4nFWbPYk7ndqZ/PfYPBTMPhrqHRgoLBSujz55PKadHpxfX6+6LNeqPQfNXt2pPIYH2+O7vcoHi4OOf2+PL5+NTs2N3u1mi1E7XZl4zEVJjLaZHGauby5KTShmSzBO/38s/oz3i7MtbrzMHiuYTCT4fDTtXqye327uDv3JDHXu328JnMcu738LLanvD49ZTJYpPKauX19tvv44jBWo7GWpfKZ+Dv27XcpcrluXu8ONTs16zXleT08qfUjKzUlc7pzm63HaTRfZXKZuj06HG4KavViGe0EcDfqcjmxaDQgZrNdOHz77/ep4/HYL3esnW6LobCS3S5K57OctDp0JXKbez17N7x6cbkwLTZlbXXmLrcnrvdodHr06PQe8jkt5jIa93v13m8OI7CW3O6L3a7Nb7gs6nUjmu2GqjTgZjKaKLQeZnMc4LAReL08rTbopbLbuTx4KDOdtbry7DYmrvfrrPaoXK5K5zOegAAACH5BAEAAAAALAAAAAAQABAAAAhMAAEIHEiwoMGDCBMOlCKgoUMuHghInEiggEOHAC5eJNhQ4UAuAjwIJLCR4AEBDQS2uHiAYLGOHjNqlCmgYAONApQ0jBGzp8+fQH8GBAA7';

                return $this->_options['image_path'] . '/copyright.gif';
                break;
            case 'variables':
                if (null === $this->_options['image_path'])
                    return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAFWSURBVBgZBcE/SFQBAAfg792dppJeEhjZn80MChpqdQ2iscmlscGi1nBPaGkviKKhONSpvSGHcCrBiDDjEhOC0I68sjvf+/V9RQCsLHRu7k0yvtN8MTMPICJieaLVS5IkafVeTkZEFLGy0JndO6vWNGVafPJVh2p8q/lqZl60DpIkaWcpa1nLYtpJkqR1EPVLz+pX4rj47FDbD2NKJ1U+6jTeTRdL/YuNrkLdhhuAZVP6ukqbh7V0TzmtadSEDZXKhhMG7ekZl24jGDLgtwEd6+jbdWAAEY0gKsPO+KPy01+jGgqlUjTK4ZroK/UVKoeOgJ5CpRyq5e2qjhF1laAS8c+Ymk1ZrVXXt2+9+fJBYUwDpZ4RR7Wtf9u9m2tF8Hwi9zJ3/tg5pW2FHVv7eZJHd75TBPD0QuYze7n4Zdv+ch7cfg8UAcDjq7mfwTycew1AEQAAAMB/0x+5JQ3zQMYAAAAASUVORK5CYII=';

                return $this->_options['image_path'] . '/variables.png';
                break;
            case 'exception':
                if (null === $this->_options['image_path'])
    				return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJPSURBVDjLpZPLS5RhFMYfv9QJlelTQZwRb2OKlKuINuHGLlBEBEOLxAu46oL0F0QQFdWizUCrWnjBaDHgThCMoiKkhUONTqmjmDp2GZ0UnWbmfc/ztrC+GbM2dXbv4ZzfeQ7vefKMMfifyP89IbevNNCYdkN2kawkCZKfSPZTOGTf6Y/m1uflKlC3LvsNTWArr9BT2LAf+W73dn5jHclIBFZyfYWU3or7T4K7AJmbl/yG7EtX1BQXNTVCYgtgbAEAYHlqYHlrsTEVQWr63RZFuqsfDAcdQPrGRR/JF5nKGm9xUxMyr0YBAEXXHgIANq/3ADQobD2J9fAkNiMTMSFb9z8ambMAQER3JC1XttkYGGZXoyZEGyTHRuBuPgBTUu7VSnUAgAUAWutOV2MjZGkehgYUA6O5A0AlkAyRnotiX3MLlFKduYCqAtuGXpyH0XQmOj+TIURt51OzURTYZdBKV2UBSsOIcRp/TVTT4ewK6idECAihtUKOArWcjq/B8tQ6UkUR31+OYXP4sTOdisivrkMyHodWejlXwcC38Fvs8dY5xaIId89VlJy7ACpCNCFCuOp8+BJ6A631gANQSg1mVmOxxGQYRW2nHMha4B5WA3chsv22T5/B13AIicWZmNZ6cMchTXUe81Okzz54pLi0uQWp+TmkZqMwxsBV74Or3od4OISPr0e3SHa3PX0f3HXKofNH/UIG9pZ5PeUth+CyS2EMkEqs4fPEOBJLsyske48/+xD8oxcAYPzs4QaS7RR2kbLTTOTQieczfzfTv8QPldGvTGoF6/8AAAAASUVORK5CYII=';

                return $this->_options['image_path'] . '/exception.png';
                 break;
            case 'error':
                if (null === $this->_options['image_path'])
    			    return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIsSURBVDjLpVNLSJQBEP7+h6uu62vLVAJDW1KQTMrINQ1vPQzq1GOpa9EppGOHLh0kCEKL7JBEhVCHihAsESyJiE4FWShGRmauu7KYiv6Pma+DGoFrBQ7MzGFmPr5vmDFIYj1mr1WYfrHPovA9VVOqbC7e/1rS9ZlrAVDYHig5WB0oPtBI0TNrUiC5yhP9jeF4X8NPcWfopoY48XT39PjjXeF0vWkZqOjd7LJYrmGasHPCCJbHwhS9/F8M4s8baid764Xi0Ilfp5voorpJfn2wwx/r3l77TwZUvR+qajXVn8PnvocYfXYH6k2ioOaCpaIdf11ivDcayyiMVudsOYqFb60gARJYHG9DbqQFmSVNjaO3K2NpAeK90ZCqtgcrjkP9aUCXp0moetDFEeRXnYCKXhm+uTW0CkBFu4JlxzZkFlbASz4CQGQVBFeEwZm8geyiMuRVntzsL3oXV+YMkvjRsydC1U+lhwZsWXgHb+oWVAEzIwvzyVlk5igsi7DymmHlHsFQR50rjl+981Jy1Fw6Gu0ObTtnU+cgs28AKgDiy+Awpj5OACBAhZ/qh2HOo6i+NeA73jUAML4/qWux8mt6NjW1w599CS9xb0mSEqQBEDAtwqALUmBaG5FV3oYPnTHMjAwetlWksyByaukxQg2wQ9FlccaK/OXA3/uAEUDp3rNIDQ1ctSk6kHh1/jRFoaL4M4snEMeD73gQx4M4PsT1IZ5AfYH68tZY7zv/ApRMY9mnuVMvAAAAAElFTkSuQmCC';

                return $this->_options['image_path'] . '/error.png';
                break;
            case 'cache':
                if (null === $this->_options['image_path'])
                    return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAI/SURBVDjLjZPbS9NhHMYH+zNidtCSQrqwQtY5y2QtT2QGrTZf13TkoYFlzsWa/tzcoR3cSc2xYUlGJfzAaIRltY0N12H5I+jaOxG8De+evhtdOP1hu3hv3sPzPO/z4SsBIPnfuvG8cbBlWiEVO5OUItA0VS8oxi9EdhXo+6yV3V3UGHRvVXHNfNv6zRfNuBZVoiFcB/3LdnQ8U+Gk+bhPVKB3qUOuf6/muaQR/qwDkZ9BRFdCmMr5EPz6BN7lMYylLGgNNaKqt3K0SKDnQ7us690t3rNsxeyvaUz+8OJpzo/QNzd8WTtcaQ7WlBmPvxhx1V2Pg7oDziIBimwwf3qAGWESkVwQ7owNujk1ztvk+cg4NnAUTT4FrrjqUKHdF9jxBfXr1rgjaSk4OlMcLrnOrJ7latxbL1V2lgvlbG9MtMTrMw1r1PImtfyn1n5q47TlBLf90n5NmalMtUdKZoyQMkLKlIGLjMyYhFpmlz3nGEVmFJlRZNaf7pIaEndM24XIjCOzjX9mm2S2JsqdkMYIqbB1j5C6yWzVk7YRFTsGFu7l+4nveExIA9aMCcOJh6DIoMigyOh+o4UryRWQOtIjaJtoziM1FD0mpE4uZcTc72gBaUyYKEI6khgqINXO3saR7kM8IZUVCRDS0Ucf+xFbCReQhr97MZ51wpWxYnhpCD3zOrT4lTisr+AJqVx0Fiiyr4/vhP4VyyMFIUWNqRrV96vWKXKckBoIqWzXYcoPDrUslDJoopuEVEpIB0sR+AuErIiZ6OqMKAAAAABJRU5ErkJggg==';

                return $this->_options['image_path'] . '/cache.png';
                break;
            case 'text':
                if (null === $this->_options['image_path'])
                    return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHhSURBVDjLpZI9SJVxFMZ/r2YFflw/kcQsiJt5b1ije0tDtbQ3GtFQYwVNFbQ1ujRFa1MUJKQ4VhYqd7K4gopK3UIly+57nnMaXjHjqotnOfDnnOd/nt85SURwkDi02+ODqbsldxUlD0mvHw09ubSXQF1t8512nGJ/Uz/5lnxi0tB+E9QI3D//+EfVqhtppGxUNzCzmf0Ekojg4fS9cBeSoyzHQNuZxNyYXp5ZM5Mk1ZkZT688b6thIBenG/N4OB5B4InciYBCVyGnEBHO+/LH3SFKQuF4OEs/51ndXMXC8Ajqknrcg1O5PGa2h4CJUqVES0OO7sYevv2qoFBmJ/4gF4boaOrg6rPLYWaYiVfDo0my8w5uj12PQleB0vcp5I6HsHAUoqUhR29zH+5B4IxNTvDmxljy3x2YCYUwZVlbzXJh9UKeQY6t2m0Lt94Oh5loPdqK3EkjzZi4MM/Y9Db3MTv/mYWVxaqkw9IOATNR7B5ABHPrZQrtg9sb8XDKa1+QOwsri4zeHD9SAzE1wxBTXz9xtvMc5ZU5lirLSKIz18nJnhOZjb22YKkhd4odg5icpcoyL669TAAujlyIvmPHSWXY1ti1AmZ8mJ3ElP1ips1/YM3H300g+W+51nc95YPEX8fEbdA2ReVYAAAAAElFTkSuQmCC';

                return $this->_options['image_path'] . '/text.png';
                break;
            case 'file':
                if (null === $this->_options['image_path'])
                    return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADPSURBVCjPdZFNCsIwEEZHPYdSz1DaHsMzuPM6RRcewSO4caPQ3sBDKCK02p+08DmZtGkKlQ+GhHm8MBmiFQUU2ng0B7khClTdQqdBiX1Ma1qMgbDlxh0XnJHiit2JNq5HgAo3KEx7BFAM/PMI0CDB2KNvh1gjHZBi8OR448GnAkeNDEDvKZDh2Xl4cBcwtcKXkZdYLJBYwCCFPDRpMEjNyKcDPC4RbXuPiWKkNABPOuNhItegz0pGFkD+y3p0s48DDB43dU7+eLWes3gdn5Y/LD9Y6skuWXcAAAAASUVORK5CYII=';

                return $this->_options['image_path'] . '/file.png';
                break;
            case 'auth':
                if (null === $this->_options['image_path'])
                    return '';

                return $this->_options['image_path'] . '/auth.png';
                break;
            default:
                if (null === $this->_options['image_path'])
                    return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHhSURBVDjLpZI9SJVxFMZ/r2YFflw/kcQsiJt5b1ije0tDtbQ3GtFQYwVNFbQ1ujRFa1MUJKQ4VhYqd7K4gopK3UIly+57nnMaXjHjqotnOfDnnOd/nt85SURwkDi02+ODqbsldxUlD0mvHw09ubSXQF1t8512nGJ/Uz/5lnxi0tB+E9QI3D//+EfVqhtppGxUNzCzmf0Ekojg4fS9cBeSoyzHQNuZxNyYXp5ZM5Mk1ZkZT688b6thIBenG/N4OB5B4InciYBCVyGnEBHO+/LH3SFKQuF4OEs/51ndXMXC8Ajqknrcg1O5PGa2h4CJUqVES0OO7sYevv2qoFBmJ/4gF4boaOrg6rPLYWaYiVfDo0my8w5uj12PQleB0vcp5I6HsHAUoqUhR29zH+5B4IxNTvDmxljy3x2YCYUwZVlbzXJh9UKeQY6t2m0Lt94Oh5loPdqK3EkjzZi4MM/Y9Db3MTv/mYWVxaqkw9IOATNR7B5ABHPrZQrtg9sb8XDKa1+QOwsri4zeHD9SAzE1wxBTXz9xtvMc5ZU5lirLSKIz18nJnhOZjb22YKkhd4odg5icpcoyL669TAAujlyIvmPHSWXY1ti1AmZ8mJ3ElP1ips1/YM3H300g+W+51nc95YPEX8fEbdA2ReVYAAAAAElFTkSuQmCC';

                return $this->_options['image_path'] . '/unknown.png';
                break;
        }
    }

    /**
     * Returns html header for the Debug Bar
     *
     * @return string
     */
    protected function _headerOutput() {
        return ('
            <style type="text/css" media="screen">
                #ZFDebug_debug { font: 11px/1.4em Lucida Grande, Lucida Sans Unicode, sans-serif; position:fixed; bottom:5px; left:5px; color:#000; z-index: ' . $this->_options['z-index'] . ';}
                #ZFDebug_debug ol {margin:10px 0px; padding:0 25px}
                #ZFDebug_debug li {margin:0 0 10px 0;}
                #ZFDebug_debug .clickable {cursor:pointer}
                #ZFDebug_toggler { font-weight:bold; background:#BFBFBF; }
                .ZFDebug_span { border: 1px solid #999; border-right:0px; background:#DFDFDF; padding: 3px 5px; }
                .ZFDebug_last { border: 1px solid #999; }
                .ZFDebug_panel { text-align:left; position:absolute;bottom:19px;width:600px; max-height:400px; overflow:auto; display:none; background:#E8E8E8; padding:5px; border: 1px solid #999; }
                .ZFDebug_panel .pre {font: 11px/1.4em Monaco, Lucida Console, monospace; margin:0 0 0 22px}
                #ZFDebug_exception { border:1px solid #CD0A0A;display: block; }
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

                function ZFDebugPanel(name) {
                    jQuery(".ZFDebug_panel").each(function(i){
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

                function ZFDebugSlideBar() {
                    if (jQuery("#ZFDebug_debug").position().left > 0) {
                        ZFDebugPanel();
                        jQuery("#ZFDebug_toggler").html("&#187;");
                        return jQuery("#ZFDebug_debug").animate({left:"-"+parseInt(jQuery("#ZFDebug_debug").outerWidth()-jQuery("#ZFDebug_toggler").outerWidth())+"px"}, "normal", "swing");
                    } else {
                        jQuery("#ZFDebug_toggler").html("&#171;");
                        return jQuery("#ZFDebug_debug").animate({left:"5px"}, "normal", "swing");
                    }
                }

                function ZFDebugToggleElement(name, whenHidden, whenVisible){
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

    /**
     * Appends Debug Bar html output to the original page
     *
     * @param string $html
     * @return void
     */
    protected function _output($html)
    {
        $response = $this->getResponse();
        $response->setBody(str_ireplace('<head>', '<head>' . $this->_headerOutput(), $response->getBody()));
        $response->setBody(str_ireplace('</body>', '<div id="ZFDebug_debug">'.$html.'</div></body>', $response->getBody()));
    }
}
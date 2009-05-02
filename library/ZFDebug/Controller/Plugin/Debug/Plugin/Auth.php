<?php
/**
 * ZFDebug Zend Additions
 *
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 * @version    $Id: $
 */

/**
 * @category   ZFDebug
 * @package    ZFDebug_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class ZFDebug_Controller_Plugin_Debug_Plugin_Auth implements ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'auth';

    /**
     * Contains Zend_Auth object
     *
     * @var Zend_Auth
     */
    protected $_auth;

    /**
     * Contains "column name" for the username
     *
     * @var string
     */
    protected $_user;

    /**
     * Contains "column name" for the role
     *
     * @var string
     */
    protected $_role;

    /**
     * Contains Acls for this application
     *
     * @var Zend_Acl
     */
    protected $_acl;

    /**
     * Create ZFDebug_Controller_Plugin_Debug_Plugin_Auth
     *
     * @var string $user
     * @var string $role
     * @return void
     */
    public function __construct($user = 'user', $role = 'role')
    {
        $this->_auth = Zend_Auth::getInstance();
        $this->_user = $user;
        $this->_role = $role;
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
    	$username = 'Not Authed';
    	$role = 'Unknown Role';

    	if($this->_auth->hasIdentity()) {
    		$username = $this->_auth->getIdentity()->{$this->_user};
    		$role = $this->_auth->getIdentity()->{$this->_role};
    	}

        return $username . ' (' . $role . ')';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        return '';
    }
}
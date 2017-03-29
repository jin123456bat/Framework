<?php
namespace vendor;

use core\base;

class user extends base
{
    /**
     * 用户属性
     * @var unknown
     */
    private $_attributes;
    
    /**
     * 是否自动登陆
     * @var unknown
     */
    public $_autologin = true;
    
    /**
     * 自动登陆的有效期 0代表不限制
     * @var integer
     */
    public $_logintimeout = 0;
    
    /**
     * 是否使用cookie作为验证信息
     * @var unknown
     */
    public $_cookie = false;
    
    /**
     * 是否使用session作为验证信息
     * @var unknown
     */
    public $_session = true;
    
    /**
     * 登陆地址
     * @var unknown
     */
    public $_loginurl = null;
    
    function __construct()
    {
    }
    
    /**
     * 判断用户是否已经登陆
     */
    function isLogin()
    {
    }
    
    function isAnyone()
    {
        return true;
    }
    
    /**
     * 刷新用户信息，
     */
    function refresh($callback = null)
    {
        if (is_callable($callback)) {
            call_user_func_array($callback, [$this]);
        }
    }
    
    /**
     * 消除用户信息
     */
    function logout()
    {
    }
    
    /**
     * 记录用户信息
     */
    function login()
    {
    }
}

<?php

/**
 * 用户业务仓库
 * @category PhalconCMS
 * @copyright Copyright (c) 2016 PhalconCMS team (http://www.marser.cn)
 * @license GNU General Public License 2.0
 * @link www.marser.cn
 */

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

class Users extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 登录态检测
     * @return bool
     */
    public function login_check(){
        if($this -> getDI() -> get('session') -> has('user')){
            if(!empty($this -> getDI() -> get('session') -> get('user')['uid'])){
                return true;
            }
        }
        return false;
    }

    /**
     * 登录处理
     * @param $username
     * @param $password
     * @throws \Exception
     */
    public function login($username, $password){
        /** 获取用户信息 */
        $user = $this -> detail($username);
        if(!$user){
            throw new \Exception('用户名或密码错误');
        }
        $userinfo = $user -> toArray();
        /** 校验密码 */
        if(!$this -> getDI() -> get('security') -> checkHash($password, $userinfo['password'])){
            throw new \Exception('密码错误，请重新输入');
        }
        /** 设置session */
        unset($userinfo['password']);
        $this -> getDI() -> get('session') -> set('user', $userinfo);
    }

    /**
     * 重置密码
     * @param $oldpwd
     * @param $newpwd
     * @return bool
     * @throws \Exception
     */
    public function set_pwd($oldpwd, $newpwd){
        /** 校验旧密码是否正确 */
        $user = $this -> detail($this -> getDI() -> get('session') -> get('user')['username']);
        if(!$user){
            throw new \Exception('密码错误');
        }
        $userinfo = $user -> toArray();
        if(!$this -> getDI() -> get('security') -> checkHash($oldpwd, $userinfo['password'])){
            throw new \Exception('密码错误，请重新输入');
        }
        /** 密码更新 */
        $password = $this -> getDI() -> get('security') -> hash($newpwd);
        $affectedRows = $this -> update_record(array(
            'password' => $password,
        ), $this -> getDI() -> get('session') -> get('user')['uid']);
        if(!$affectedRows){
            throw new \Exception('修改密码失败，请重试');
        }
        return true;
    }

    /**
     * 变更个人配置
     * @param array $data
     * @param null $uid
     * @return bool
     * @throws \Exception
     */
    public function set_profile(array $data, $uid = null){
        $uid = intval($uid);
        empty($uid) && $uid = $this -> getDI() -> get('session') -> get('user')['uid'];
        $affectedRows = $this -> update_record($data, $uid);
        if(!$affectedRows){
            throw new \Exception('修改个人设置失败');
        }
        return true;
    }

    /**
     * 用户数据
     * @param string $username
     * @param array $ext
     * @return \Phalcon\Mvc\Model
     * @throws \Exception
     */
    public function detail($username, array $ext=array()){
        $user = $this -> get_model('UsersModel') -> detail($username, $ext);
        return $user;
    }

    /**
     * 更新用户数据
     * @param array $data
     * @param $uid
     * @return int
     * @throws \Exception
     */
    public function update_record(array $data, $uid){
        if(!isset($data['modify_time']) || empty($data['modify_time'])){
            $data['modify_time'] = time();
        }
        $affectedRows = $this -> get_model('UsersModel') -> update_record($data, $uid);
        return $affectedRows;
    }
}
<?php

/**
 * 通行证
 * @category PhalconCMS
 * @copyright Copyright (c) 2016 PhalconCMS team (http://www.marser.cn)
 * @license GNU General Public License 2.0
 * @link www.marser.cn
 */

namespace Marser\App\Backend\Controllers;
use \Marser\App\Core\PhalBaseController,
    \Marser\App\Backend\Repositories\Repository;

class PassportController extends PhalBaseController{

    /**
     * 用户数据仓库
     * @var \Marser\App\Backend\Repositories\Users
     */
    protected $repository;

    /**
     * 模块在URL中的pathinfo路径名
     * @var
     */
    private $_module_pathinfo;

    public function initialize(){
        parent::initialize();
        $this -> repository = Repository::get_repository('Users');
        $this -> _module_pathinfo = $this -> systemConfig -> get('app', 'backend', 'module_pathinfo');
    }

    /**
     * 登录页
     */
    public function indexAction(){
        $this -> login_check();
        $this -> view -> setVars(array(
            'title' => $this -> systemConfig -> get('app', 'app_name'),
            'assetsUrl' => $this -> systemConfig -> get('app', 'backend', 'assets_url'),
            'assetsVersion' => strtotime(date('Y-m-d H', time()) . ":00:00"),
            'modulePathinfo' => $this -> _module_pathinfo,
        ));
        $this -> view -> setMainView('passport/login');
    }

    /**
     * 登录处理
     * @throws \Exception
     */
    public function loginAction(){
        $this -> login_check();
        try {
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $username = $this -> request -> getPost('username', 'trim');
            $password = $this -> request -> getPost('password', 'trim');

            /** 添加验证规则 */
            $this -> validator -> add_rule('username', 'required', '请输入用户名')
                -> add_rule('username', 'alpha_dash', '用户名由4-20个英文字符、数字、中下划线组成')
                -> add_rule('username', 'min_length', '用户名由4-20个英文字符、数字、中下划线组成', 4)
                -> add_rule('username', 'max_length', '用户名由4-20个英文字符、数字、中下划线组成', 20);
            $this -> validator -> add_rule('password', 'required', '请输入密码')
                -> add_rule('password', 'min_length', '密码由6-20个字符组成', 6)
                -> add_rule('password', 'max_length', '密码由6-20个字符组成', 20);
            /** 截获验证异常 */
            if ($error = $this -> validator -> run(array('username'=>$username, 'password'=>$password))) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            /** 获取用户信息 */
            $user = $this -> repository -> detail($username);
            if(!$user){
                throw new \Exception('用户名或密码错误');
            }
            $userinfo = $user -> toArray();
            /** 校验密码 */
            if(!$this -> security -> checkHash($password, $userinfo['password'])){
                throw new \Exception('密码错误，请重新输入');
            }
            /** 设置session */
            unset($userinfo['password']);
            $this -> session -> set('user', $userinfo);

            $url = $this -> url -> get("{$this -> _module_pathinfo}/index/test");
            $this -> response -> redirect($url);
        }catch(\Exception $e){
            $this -> write_exception_log($e);

            $this -> flashSession -> error($e -> getMessage());

            $url = $this -> url -> get("{$this -> _module_pathinfo}/passport/index");
            $this -> response -> redirect($url);
        }
    }

    /**
     * 注销登录
     */
    public function logoutAction(){
        if($this -> session -> destroy()){
            $url = $this -> url -> get("{$this -> _module_pathinfo}/passport/index");
            $this -> response -> redirect($url);
        }
    }

    /**
     * 是否已登录
     * @return bool
     */
    protected function login_check(){
        if($this -> session -> has('user')){
            if(!empty($this -> session -> get('user')['uid'])){
                $url = $this -> url -> get("{$this -> _module_pathinfo}/index/test");
                return $this -> response -> redirect($url);
            }
        }
        return false;
    }
}
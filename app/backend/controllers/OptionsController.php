<?php

/**
 * 设置
 * @category PhalconCMS
 * @copyright Copyright (c) 2016 PhalconCMS team (http://www.marser.cn)
 * @license GNU General Public License 2.0
 * @link www.marser.cn
 */

namespace Marser\App\Backend\Controllers;
use \Marser\App\Backend\Controllers\BaseController;

class OptionsController extends BaseController{

    public function initialize(){
        parent::initialize();
    }

    /**
     * 站点基础配置
     */
    public function baseAction(){
        try {
            $key = "'site_name', 'site_url', 'site_description', 'site_keywords'";
            $options = $this -> get_repository('Options') -> get_list($key, array(
                'columns' => 'op_key, op_value',
            ));
            if(is_array($options) && count($options) > 0){
                foreach($options as $ok=>$ov){
                    $options[$ov['op_key']] = $ov;
                    unset($options[$ok]);
                }
            }
        }catch(\Exception $e){
            $this -> write_exception_log($e);

            $this -> flashSession -> error($e -> getMessage());
        }
        $this -> view -> setVars(array(
            'options' => $options,
        ));

        $this -> view -> pick('options/base');
    }

    /**
     * 站点基础配置变更
     */
    public function savebaseAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $siteName = $this -> request -> getPost('siteName', 'trim');
            $siteUrl = $this -> request -> getPost('siteUrl', 'trim');
            $description = $this -> request -> getPost('description', 'trim');
            $keywords = $this -> request -> getPost('keywords', 'trim');
            $timezone = $this -> request -> getPost('timezone', 'trim');
            /** 添加验证规则 */
            $this -> validator -> add_rule('siteName', 'required', '请填写站点名称')
                -> add_rule('siteName', 'chinese_alpha_numeric_dash', '站点名称由中英文字符、数字和中下划线组成');
            $this -> validator -> add_rule('siteUrl', 'required', '请填写一个合法的URL地址')
                -> add_rule('siteUrl', 'url', '站点网址格式错误');
            $this -> validator -> add_rule('description', 'xss_check', '请不要在站点描述中使用特殊字符');
            $this -> validator -> add_rule('keywords', 'xss_check', '请不要在关键词中使用特殊字符');
            /** 截获验证异常 */
            if ($error = $this -> validator -> run(array(
                'siteName' => $siteName,
                'siteUrl' => $siteUrl,
                'description' => $description,
                'keywords' => $keywords,
            ))) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            /** 保存配置项 */
            $data = array(
                'site_name' => $siteName,
                'site_url' => $siteUrl,
                'site_description' => $description,
                'site_keywords' => $keywords,
            );
            !empty($timezone) && $data['site_timezone'] = $timezone;
            $this -> get_repository('Options') -> save($data);

            $this -> flashSession -> success('更新成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);

            $this -> flashSession -> error($e -> getMessage());
        }
        $url = $this -> get_module_uri('options/base');
        return $this -> response -> redirect($url);
    }
}

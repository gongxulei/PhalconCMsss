<?php

/**
 * 文章业务仓库
 * @category PhalconCMS
 * @copyright Copyright (c) 2016 PhalconCMS team (http://www.marser.cn)
 * @license GNU General Public License 2.0
 * @link www.marser.cn
 */

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class Articles extends  BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 获取文章列表
     * @param int $status
     * @param int $pagesize
     * @param array $ext
     * @return mixed
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $paginator = $this -> get_model('ArticlesModel') -> get_list($page, $pagesize, $ext);
        return $paginator;
    }

    /**
     * 获取文章数据
     * @param $aid
     * @return mixed
     */
    public function detail($aid){
        $article = $this -> get_model('ArticlesModel') -> detail($aid);
        $article = $article -> toArray()[0];
        return $article;
    }

    /**
     * 获取文章所属分类
     * @param array $aids
     * @return mixed
     */
    public function get_categorys_by_aids(array $aids){
        $categorys = $this -> get_model('ArticlesCategorysModel') -> get_categorys_by_aids($aids);
        return $categorys;
    }

    /**
     * 获取文章关联的标签数据
     * @param $aid
     * @return mixed
     */
    public function get_tags_by_aids(array $aids){
        $tags = $this -> get_model('ArticlesTagsModel') -> get_tags_by_aids($aids);
        return $tags;
    }

    /**
     * 获取置顶文章
     * @param $pagesize
     * @return mixed
     */
    public function get_top_articles($pagesize=2){
        $result = $this -> get_model('ArticlesModel') -> get_top_articles($pagesize);
        return $result;
    }

    /**
     * 获取推荐阅读文章
     * @param int $pagesize
     * @return mixed
     */
    public function get_recommend_articles($pagesize=10){
        $result = $this -> get_model('ArticlesModel') -> get_recommend_articles($pagesize);
        return $result;
    }

    /**
     * 获取前一篇文章
     * @param $aid
     * @return mixed
     */
    public function get_prev_article($aid){
        $article = $this -> get_model('ArticlesModel') -> get_prev_article($aid);
        $article = $article -> toArray()[0];
        return $article;
    }

    /**
     * 获取后一篇文章
     * @param $aid
     * @return mixed
     */
    public function get_next_article($aid){
        $article = $this -> get_model('ArticlesModel') -> get_next_article($aid);
        $article = $article -> toArray()[0];
        return $article;
    }

    /**
     * 获取文章数量
     * @return int
     */
    public function get_count(){
        $count = $this -> get_model('ArticlesModel') -> get_count();
        $count = intval($count);
        return $count;
    }

}
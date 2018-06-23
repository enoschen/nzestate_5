<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/22
 * Time: 0:54
 */

namespace app\common\model;


use think\Model;

class WxMaterial extends Model
{

    //目前素材类型
    const TYPE_TEXT = 'text';
    const TYPE_NEWS = 'news';
    const TYPE_IMAGE = 'image';
    const TYPE_NEWS_IMAGE = 'news_image';

    public function wxNews()
    {
        return $this->hasMany('WxNews', 'material_id', 'news_id');
    }

    public function getDataAttr($value)
    {
        if (!$value) {
            return [];
        }
        return json_decode($value, true) ?: [];
    }

    public function setDataAttr($value)
    {
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return $value;
    }
}
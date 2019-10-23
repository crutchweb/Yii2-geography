<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\widgets\geography;

use yii\web\AssetBundle;
/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
use Yii;

class AppAsset extends AssetBundle
{
    public $jsOptions  = [
    ];
    public $basePath   = '@webroot';
    public $baseUrl    = '@web';
    public $css        = [
        'css/widgets/geography/geography'.((YII_DEBUG) ? '' : '.min').'.css?v1.00',
    ];
    public $cssOptions = [
        'onload' => "if(media!='all') media='all'"
    ];
    public $js         = [
        'js/enscroll'.((YII_DEBUG) ? '' : '.min').'.js?v1.0',
        'js/widgets/geography/geography'.((YII_DEBUG) ? '' : '.min').'.js?v1.0',
    ];
    public $depends    = [
        'yii\jui\JuiAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];

}
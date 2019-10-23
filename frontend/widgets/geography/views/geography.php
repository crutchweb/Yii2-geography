<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\jui\AutoComplete;
use yii\web\JsExpression;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use frontend\widgets\geography\AppAsset;

AppAsset::register($this);
?>

<div class="content geography" itemscope itemtype="http://schema.org/Organization">
    <h1><?= $title; ?></h1>
    <div class="row">
        <div class="col-md-6">
            <?=
            AutoComplete::widget([
                'clientOptions' => [
                    'minLength'    => '2',
                    'source'       => '/geography/autocomplete?is_active=1',
                    'response'     => new JsExpression("function(event, ui) {
                                $('.city__finder').autocomplete('instance')._renderItem = function( ul, item ) {
                                    return $('<li></li>').data('item.autocomplete', item ).append( item.value).appendTo( ul );
                                }
                            }"),
                    'select'       => new JsExpression("function(event, ui) {
                                  map.setZoom(10);
                                  map.setCenter([ui.item.lat, ui.item.lon]);
                            }")
                ],
                'options' => [
                    'placeholder' => 'Введите название города...',
                    'class'       => 'city__finder'
                ],
            ]);
            ?>
        </div>
        <div class="col-sm-6 col-md-3">
            <? $form = ActiveForm::begin();
            $params = [
                'prompt'      => 'Выберите страну...',
                'onchange'    => "getBranches(this);",
                'data-type'   => 'select',
                'class'       => 'counrty__change form-control'
            ];
            echo $form->field($model_country, 'code', ['enableLabel' => false])
                ->dropDownList(ArrayHelper::map($model_country->find()->select(['name','code'])->asArray()->all(), 'code', 'name'), $params);
            ActiveForm::end();
            ?>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="citylist__block">
                <span></span>
                <?
                $params = [
                    'class'       => 'city__list-btn',
                ];
                echo Html::button('Список', $params)
                ?>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
<div class="mapper widget">
    <div id="map__container"></div>
</div>

<!--faqs block-->
<div class="faq__wrap">
    <div class="content block-voprosov">
        <div class="row">
            <div class="col-sm-4">
                <div class="row">
                    <div class="col-sm-12 block-min-height">
                        <?=
                        Html::img('', [
                            'title' => 'Задать вопрос',
                            'alt' => 'Задать вопрос',
                            'class' => 'lazy',
                            'data-original' => Url::to('/images/static/bg/services/block-voprosov-question.svg')

                        ])
                        ?>
                    </div>
                    <div class="col-sm-12">
                        <h4 class="ne-khai-grustit">Задать вопрос</h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis pellentesque, nisl sed dapibus dictum</p>
                    </div>
                    <div class="col-sm-12">
                        <button class="btn btn-block btn-default text-uppercase">Задать вопрос</button>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="row">
                    <div class="col-sm-12 block-min-height">
                        <?=
                        Html::img('', [
                            'title' => 'Получить тарифы',
                            'alt' => 'Получить тарифы',
                            'class' => 'lazy',
                            'data-original' => Url::to('/images/static/bg/services/block-voprosov-calculator.svg')
                        ])
                        ?>
                    </div>
                    <div class="col-sm-12 block-min-height">
                        <h4 class="ne-khai-grustitr">Узнать стоимость</h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis pellentesque, nisl sed dapibus dictum</p>
                    </div>
                    <div class="col-sm-12">
                        <button class="btn btn-block btn-default text-uppercase">Получить тарифы</button>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="row">
                    <div class="col-sm-12 block-min-height">
                        <?=
                        Html::img('', [
                            'title' => 'Заказать',
                            'alt' => 'Заказать',
                            'class' => 'lazy',
                            'data-original' => Url::to('/images/static/bg/services/block-voprosov-shipping-truck.svg')
                        ])
                        ?>
                    </div>
                    <div class="col-sm-12 block-min-height">
                        <h4 class="ne-khai-grustit">Заказать перевозку</h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis pellentesque, nisl sed dapibus dictum</p>
                    </div>
                    <div class="col-sm-12">
                        <button class="btn btn-block btn-default text-uppercase">Заказать</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<? $this->registerJsFile("https://api-maps.yandex.ru/2.1/?lang=ru_RU"); ?>




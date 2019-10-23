<?
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\jui\AutoComplete;
use yii\web\JsExpression;
use common\models\geography\Address;
?>
<?
$title = (isset(Yii::$app->geography->kladrCity->name)) ? preg_replace('/\([\d\w\s\-]+/u', '', preg_replace('/\([\d\w\s\-]+\)/u', '', Yii::$app->geography->kladrCity->name)) : "Екатеринбург";

$this->title                   = ($this->title) ? $this->title : $title;
$this->params['header']        = ($this->params['header']) ? $this->params['header'] : $title;
$this->params['breadcrumbs'][] = ["label" => "О компании", "url" => "about"];
$this->params['breadcrumbs'][] = "Контакты";
$this->params['breadcrumbs'][] = $this->params['header'];
?>

<div class="content" itemscope itemtype="http://schema.org/Organization">
    <h1><?= $title; ?></h1>
    <div class="row">
        <div class="col-md-6">
            <?= Html::beginForm(['#'], 'post', ['class' => 'branches__autocomplite']); ?>
            <?=
            AutoComplete::widget([
                'clientOptions' => [
                    'minLength'    => '2',
                    'source'       => '/site/autocomplete-city?is_active=1&tzone_only=1',
                    'response'     => new JsExpression("function(event, ui) {
                                $('#branches_finder').autocomplete('instance')._renderItem = function( ul, item ) {
                                    console.log(item);
                                    return $('<li></li>').data('item.autocomplete', item ).append( item.value).appendTo( ul );
                                }
                            }"),
                    'select'       => new JsExpression("function(event, ui) {
                                $('#finder_city').val(ui.item.value);
                            }")
                ],
                'options' => [
                    'placeholder' => 'Введите название города...',
                    'class'       => 'branches_finder',
                    'id'          => 'branches_finder'
                ],
            ]);
            ?>
            <?= Html::submitButton('Найти', ['class' => 'search__submit', 'name' => 'branches__city']) ?>
            <?= Html::endForm() ?>
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
<div class="bstabs__wrap">
    <div class="content">
        <ul class="nav nav-tabs nav-justified no--margin">
            <? $i = 0; ?>
            <? foreach ($model as $branch){ ?>
                <? $address = preg_replace('/\(.+$/', '', $branch["value"]); ?>
                <li class="<?= ($i == 0) ? "active" : "" ?>">
                    <a data-toggle="tab" class="placemark" data-placemark="#placemark<?= $i+1;?>" data-value="<?= $branch["value"]; ?>" data-lat="<?= $branch["lat"]; ?>" data-lon="<?= $branch["lon"]; ?>" href="#<?= $branch['id']; ?>"><?= $address; ?></a>
                </li>
            <? $i++; ?>
            <? }; ?>
        </ul>
    </div>
</div>
<div class="mapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6 col-lg-5">
                <div class="bstabscontent__wrap">
                    <div class="tab-content no--padding">
                        <? $i = 0; ?>
                        <? foreach ($model as $branch){ ?>
                            <div id="<?= $branch['id']; ?>" class="tab-pane fade <?= ($i == 0) ? "active in" : "" ?>">
                                <div class="data__block">
                                    <h3>Адрес:</h3>
                                    <p><?= $branch['value']; ?></p>
                                    <? foreach ($branch['phone'] as $phone){ ?>
                                        <p><?= ($phone['comment']) ? $phone['comment'] : "Телефон для справок" ?>: <a href="tel:<?= $phone['value']; ?>"><?= $phone['value']; ?></a></p>
                                    <? } ?>
                                    <? foreach ($branch['email'] as $email){ ?>
                                        <p><?= ($email['comment']) ? $email['comment'] : "Email для справок" ?>: <a href="mailto:<?= $email['value']; ?>"><?= $email['value']; ?></a></p>
                                    <? } ?>
                                </div>
                                <div class="data__block">
                                    <? if ($branch['other_information']){ ?>
                                        <h3>Ограничения по грузу:</h3>
                                        <p><?= $branch['other_information']; ?></p>
                                    <? } ?>
                                    <div class="schedule__block">
                                        <? foreach ($branch['schedule'] as $key => $group){ ?>
                                            <h3><?= Address::scheduleGroupName($key); ?></h3>
                                            <div class="schedule__flextable flex__row">
                                                <? foreach ($group as $key => $schedule){ ?>
                                                    <div class="flex__cell <?= Address::thisDay($key); ?> <?= ($schedule['freeday'] == 1) ? 'output' : ''; ?>">
                                                        <div><?= Address::dayOfTheWeek($key); ?></div>
                                                        <? if ($schedule['freeday'] == 1) { ?>
                                                            <div class="output__cell">Выхо</div>
                                                            <div class="output__cell">дной</div>
                                                        <? } elseif ($schedule['all_day'] == 1) { ?>
                                                            <div class="allday">24/7</div>
                                                        <? } else { ?>
                                                            <div><?= $schedule['from']; ?></div>
                                                            <div><?= $schedule['to']; ?></div>
                                                        <? } ?>
                                                    </div>
                                                <? } ?>
                                            </div>
                                        <? } ?>
                                    </div>
                                </div>
                            </div>
                        <? $i++; ?>
                        <? }; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="map__container" data-lat="<?= $mapcenter[0]['lat']; ?>" data-lon="<?= $mapcenter[0]['lon']; ?>"></div>
</div>
<div class="inform__block">
    <div class="content">
        <div class="row">
            <div class="block__h1">Заказать перевозку</div>
            <div class="col-sm-8 col-sm-offset-2 col-md-4 col-md-offset-4">
                <div class="block__action">
                    <div class="row">
                        <div class="col-sm-6">
                            <?= Html::a('Расчет цены', ['#'], ['class' => 'btn btn-block btn-primary']) ?>
                        </div>
                        <div class="col-sm-6">
                            <?= Html::a('Заказать онлайн', ['#'], ['class' => 'btn btn-block btn-primary btn-outline']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
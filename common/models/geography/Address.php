<?php

namespace common\models\geography;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * @property mediumint(9) UN $id
 * @property mediumint(9) UN $geography_city_id
 * @property varchar(255) $value
 * @property decimal(10,6) $lat
 * @property decimal(10,6) $lon
 * @property varchar(5) $from
 * @property varchar(5) $to
 * @property text $schedule
 * @property timestamp $timestamp
 * @property timestamp $timestamp_update
 *
 * @property-read Phone $geographyPhone
 * @property-read Email $geographyEmail
 * @property-read City $geographyCity
 */
class Address extends ActiveRecord
{
    const DISABLE         = 0;
    const ENABLE          = 1;
    const YANDEX_XML_NAME = 'address-yandex';

    public $_imagesPath = '/geography/address/';
    public $imageFile;

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return '{{%geography_address}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['id', 'geography_city_id', 'is_address_delivery', 'not_autoinformer', 'not_yandex'], 'integer'],
            ['sort', 'integer', 'min' => 0, 'max' => 65535],
            ['is_main', 'boolean'],
            ['is_main', 'validatorIsMainExist'],
            [['is_address_delivery'], 'default', 'value' => self::DISABLE],
            [['is_address_delivery'], 'in', 'range' => [self::ENABLE, self::DISABLE]],
            [['value', 'from', 'to', 'driving_directions_image'], 'trim'],
            [['street', 'value_inline'], 'trim'],
            [['geography_city_id', 'value', 'code', 'street', 'value_inline'], 'required'],
            [['schedule', 'additional_information', 'other_information'], 'string'],
            [['value', 'driving_directions_image'], 'string', 'min' => 1, 'max' => 255],
            [['name', 'code'], 'string', 'max' => 255],
            [['from', 'to'], 'string', 'min' => 0, 'max' => 5],
            [['lat', 'lon'], 'number'],
            [['timestamp', 'timestamp_update'], 'date', 'format' => 'php:Y-m-d H:i:s'],
            [['imageFile'], 'file', 'extensions' => 'gif, jpg, png', 'maxSize' => 512000, 'tooBig' => 'Максимальный размер изображения 500KB']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'geography_city_id' => 'Город географии',
            'code' => 'Код завода',
            'value' => 'Адресс',
            'is_main' => 'Главный склад',
            'not_yandex' => 'Не выгружать в Yandex',
            'not_autoinformer' => 'Не использовать этот адрес автоинформатором',
            'street' => 'Улица',
            'value_inline' => 'Адрес и дополнительная информация',
            'is_address_delivery' => 'Адресная доставка',
            'additional_information' => 'Дополнительная информация',
            'other_information' => 'Иная информация',
            'driving_directions_image' => 'Схема проезда',
            'imageFile' => 'Схема проезда',
            'lat' => 'Широта',
            'lon' => 'Долгота',
            'from' => 'Время работы "С"',
            'to' => 'Время работы "До"',
            'schedule' => 'Режим работы',
            'timestamp' => 'Дата создания',
            'timestamp_update' => 'Дата обновления',
            'sort' => 'Сортировка',
            'name' => 'Название склада',
        ];
    }

    public function validatorIsMainExist($attribute)
    {
        if ($this->is_main) {
            $isMain = self::findOne([
                    'geography_city_id' => $this->geography_city_id,
                    'is_main' => 1
            ]);

            if ($isMain && ($isMain->id !== $this->id)) {
                if ($isMain->value) {
                    $this->addError($attribute, 'Для данного города главный склад уже присвоен адресу: '.$isMain->value);
                } else {
                    $this->addError($attribute, 'Для данного города главный склад уже присвоен.');
                }
            }
        }
    }

    public function getSrc()
    {
        if (mb_strlen($this->driving_directions_image) > 0) {
            $file = Yii::getAlias('@webroot').Yii::$app->params["imageDir"].'/dynamic/'.$this->_imagesPath.$this->id.'/'.$this->driving_directions_image;
            if (file_exists($file)) {
                $link = Yii::$app->params["imageDir"].'/dynamic'.$this->_imagesPath.$this->id.'/'.$this->driving_directions_image;
                return $link;
            } else {
                return Yii::$app->params["noImage"];
            }
        } else {
            return Yii::$app->params["noImage"];
        }
    }

    private function delImage()
    {
        if (mb_strlen($this->driving_directions_image) > 0) {
            $path = Yii::getAlias('@webroot').Yii::$app->params["imageDir"].'/dynamic/'.$this->_imagesPath.$this->id;
            $file = $path.'/'.$this->driving_directions_image;
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    private function upload()
    {
        if ($this->imageFile) {
            $this->delImage();
            if ($this->validate()) {
                $modelName = md5($this->imageFile->baseName.time());
                $path      = Yii::getAlias('@webroot').Yii::$app->params["imageDir"].'/dynamic/'.$this->_imagesPath.$this->id.'/';
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                $this->imageFile->saveAs($path.$modelName.'.'.$this->imageFile->extension);
                $this->driving_directions_image = $modelName.'.'.$this->imageFile->extension;
            }
        }
    }

    public function getPhone()
    {
        return $this->hasMany(\common\models\geography\Phone::className(), ['geography_address_id' => 'id']);
    }

    public function getEmail()
    {
        return $this->hasMany(\common\models\geography\Email::className(), ['geography_address_id' => 'id']);
    }

    public function getSchedule2()
    {
        return $this->hasMany(\common\models\geography\Schedule::className(), ['geography_address_id' => 'id']);
    }

    //ilya
    public static function dayOfTheWeek(int $day)
    {
        $daysOfWeek = [
            1 => 'ПН',
            2 => 'ВТ',
            3 => 'СР',
            4 => 'ЧТ',
            5 => 'ПТ',
            6 => 'СБ',
            7 => 'ВС'
        ];

        $daysOfWeekFull = [
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
            7 => 'Воскресение'
        ];

        return '<div class="visible-md visible-lg">' . $daysOfWeek[$day] . '</div><div class="visible-xs visible-sm">' . $daysOfWeekFull[$day] . '</div>';
    }

    public static function scheduleGroupName(int $group_id)
    {
        $schedulesGroupNames =[
           1 => 'Выдача груза',
           2 => 'Прием груза',
        ];

        return $schedulesGroupNames[$group_id];
    }

    public static function thisDay(int $day)
    {
        $curr_day = date('N', strtotime(date('Y-m-d H:i:s')));
        $active_class = '';
        if ($day == $curr_day){
            $active_class = 'active';
        }
        return $active_class;
    }

    public function getMapCenter()
    {
        $mapCenter = \common\models\sxgeo\City::find()
            ->select(['lat', 'lon'])
            ->where(['id' => Yii::$app->geography->entity->sxgeo_city_id])
            ->asArray()
            ->all();

        return $mapCenter;
    }

    public function getContactData()
    {
        $branchesRows = Address::find()
            ->where([Address::tableName().'.geography_city_id' => Yii::$app->geography->entity->id])
            ->joinWith('phone')
            ->joinWith('email')
            ->orderBy(['sort' => SORT_ASC])
            ->asArray()
            ->all();

        $branchesIds = [];
        foreach ($branchesRows as $branchRow){
            $branchesIds[] = $branchRow['id'];
        }

        $branchesData = [];
        foreach ($branchesRows as $branchesRow) {
            $branchesData[$branchesRow['id']] = $branchesRow;
        }

        $scheduleRows = Schedule::find()
            ->joinWith('group')
            ->andWhere(['geography_address_id' => $branchesIds])
            ->orderBy(['group_id' => SORT_ASC, 'sort' => SORT_ASC])
            ->asArray()
            ->all();

        $scheduleData = [];
        foreach ($scheduleRows as $scheduleRow) {
            $scheduleData[$scheduleRow['geography_address_id']]['schedule'][$scheduleRow['group_id']][$scheduleRow['day']] = $scheduleRow;
        }

        foreach ($branchesData as $key => $id){
            $result[$key] = array_merge($branchesData[$key], $scheduleData[$key]);
        }

        //sorting schedule days
        foreach ($result as $key => $res) {
            foreach ($result[$key]['schedule'] as $group_id => $schedule_group) {
                ksort($schedule_group, SORT_REGULAR);
                $result[$key]['schedule'][$group_id] = $schedule_group;
            }
        }

        return $result;
    }

    public function getPlacemark()
    {

        $branchesRows = City::find()
            ->select('id , subdomain, sxgeo_city_id')
            ->where(['is_active' => 1])
            ->with(['sxgeoCity' => function ($query) {
                $query->select('id, name_ru');
            }])
            ->with(['geographyAddress' => function ($query){
                $query->select('id, geography_city_id, value, lat, lon');
            }])
            ->asArray()
            ->all();

        foreach ($branchesRows as $branchesRow) {
            foreach ($branchesRow['geographyAddress'] as $item){
                $data = array_merge($item, ['subdomain' => $branchesRow['subdomain']], ['city' => $branchesRow['sxgeoCity']['name_ru']]);
                $result[] = [
                    "type" => "Feature",
                    "id" => $data['id'],
                    "geometry" => [
                        "type" => "Point",
                        "coordinates" => [
                            $data['lat'],
                            $data['lon']
                        ]
                    ],
                    "properties" => [
                        "balloonContentHeader"  => $data['city'],
                        "balloonContentBody"    => $data['value'],
                        "balloonContentFooter"  => '<a href="'. \common\helper\Domain::host() . '://' . $data['subdomain'] . '.' . \common\helper\Domain::base() . '">Перейти</a>',
                        "hintContent"           => $data['value'],
                    ],
                ];
            }
        }

        return ["type" => "FeatureCollection", "features" => $result];
    }

    public function getBranchData($id){

        $phoneRows = Phone::find()
            ->select(['value', 'comment'])
            ->where(['geography_address_id' => $id])
            ->orderBy(['sort' => SORT_ASC])
            ->asArray()
            ->all();

        $phoneData = [];
        foreach ($phoneRows as $phoneRow){
            $phoneData[] = ['value' => $phoneRow['value'], 'comment' => $phoneRow['comment'], 'type' => 'phone'];
        }

        $mailRows = Email::find()
            ->select(['value', 'comment'])
            ->where(['geography_address_id' => $id])
            ->orderBy(['sort' => SORT_ASC])
            ->asArray()
            ->all();

        $mailData = [];
        foreach ($mailRows as $mailRow){
            $mailData[] = ['value' => $mailRow['value'], 'comment' => $mailRow['comment'], 'type' => 'mail'];
        }

        $result = array_merge($phoneData, $mailData);

        return $result;
    }

    public function getScheduleInline(bool $numberView = true, int $groupId = 0)
    {
        $modelFind = Schedule::find();
        $modelFind->select([
            'geography_city_id',
            'geography_address_id',
            'group_id',
            'day',
            'all_day',
            'freeday',
            'from',
            'to'
        ]);

        $modelFind->distinct();

        $modelFind->innerJoinWith(['group', 'city']);
        $modelFind->orderBy(['geography_city_id' => SORT_ASC, 'geography_address_id' => SORT_ASC, 'group_id' => SORT_ASC, 'day' => SORT_ASC, 'sort' => SORT_ASC]);
        $modelFind->andWhere([City::tableName().'.is_active' => 1]);
        $modelFind->andWhere([ScheduleGroup::tableName().'.is_active' => 1]);
        $modelFind->andWhere(['geography_address_id' => $this->id]);
        if ($groupId) {
            $modelFind->andWhere(['group_id' => 2]);
        }

        $modelFind->asArray();

        $schedules  = [];
        $hash       = null;
        $preHash    = null;
        $hashId     = -1;
        $preGroupId = null;

        $hours = [
            1 => 'часа',
            2 => 'двух',
            3 => 'трех',
            4 => 'четырех',
            5 => 'пяти',
            6 => 'шести',
            7 => 'семи',
            8 => 'восьми',
            9 => 'девяти',
            10 => 'десяти',
            11 => 'одинадцати',
            12 => 'двенадцати',
            13 => 'тренадцати',
            14 => 'четырнадцати',
            15 => 'пятнадцати',
            16 => 'шестнадцати',
            17 => 'семнадцати',
            18 => 'восемнадцати',
            19 => 'девятнадцати',
            20 => 'двадцати',
            21 => 'двадцати одного',
            22 => 'двадцати двух',
            23 => 'двадцати стрех',
            24 => 'двадцати четырех'
        ];

        $days = [
            1 => 'понедельник',
            2 => 'вторник',
            3 => 'среду',
            4 => 'четверг',
            5 => 'пятница',
            6 => 'суббота',
            7 => 'воскресенье',
        ];

        $daysFrom = [
            1 => 'понедельника',
            2 => 'вторника',
            3 => 'среды',
            4 => 'четверга',
            5 => 'пятницы',
            6 => 'субботы',
            7 => 'воскресенья',
        ];

        $daysTo = [
            1 => 'понедельник',
            2 => 'вторник',
            3 => 'среду',
            4 => 'четверг',
            5 => 'пятницу',
            6 => 'субботу',
            7 => 'воскресенье',
        ];

        foreach ($modelFind->each() as $schedule) {
            $hash = sha1($schedule["geography_address_id"].$schedule['group_id'].$schedule["all_day"].$schedule["freeday"].$schedule["from"].$schedule["to"]);

            if ($preGroupId != $schedule['group_id']) {
                $preGroupId = $schedule['group_id'];
                $hashId     = -1;
            }
            if ($preHash != $hash) {
                $preHash = $hash;
                $hashId ++;
            }

            $schedules[$schedule['group_id']]['geography_address_id']        = $schedule["geography_address_id"];
            $schedules[$schedule['group_id']]['name']                        = $schedule['group']['name'];
            $schedules[$schedule['group_id']]['schedule'][$hashId]['days'][] = (int) $schedule['day'];

            if ($numberView) {
                $schedules[$schedule['group_id']]['schedule'][$hashId]['from'] = $schedule['from'];
                $schedules[$schedule['group_id']]['schedule'][$hashId]['to']   = $schedule['to'];
            } else {
                $schedules[$schedule['group_id']]['schedule'][$hashId]['from'] = (int) str_replace([':00'], '', $schedule['from']);
                $schedules[$schedule['group_id']]['schedule'][$hashId]['to']   = (int) str_replace([':00'], '', $schedule['to']);
            }

            $schedules[$schedule['group_id']]['schedule'][$hashId]['all_day'] = (bool) $schedule['all_day'];
            $schedules[$schedule['group_id']]['schedule'][$hashId]['freeday'] = (bool) $schedule['freeday'];
        }


        unset($schedule);

        $daysText      = '';
        $groupDaysText = '';
        $fromText      = 'с';
        $toText        = 'по';
        $untilText     = 'до';

        foreach ($schedules as $groupId => $group) {

            $daysText      .= $group['name'].' ';
            $groupDaysText = '';
            $countTimePart = count($group['schedule']);
            $i             = 0;
            foreach ($group['schedule'] as $timePart) {
                $i++;
                if ($countTimePart > $i) {
                    $delimetr = ',';
                } else {
                    $delimetr = '.';
                }
                if (min($timePart['days']) == max($timePart['days'])) {
                    $groupDaysText .= $days[min($timePart['days'])].' ';
                } else {
                    $groupDaysText .= $fromText.' '.$daysFrom[min($timePart['days'])].' ';
                    $groupDaysText .= $toText.' '.$daysTo[max($timePart['days'])].' ';
                }

                if ($timePart['all_day']) {
                    $groupDaysText .= 'круглосуточно'.$delimetr.' ';
                }

                if ($timePart['freeday']) {
                    $groupDaysText .= 'выходной'.$delimetr.' ';
                }

                if (!$timePart['all_day'] && !$timePart['freeday']) {
                    if (isset($hours[$timePart['from']])) {
                        $groupDaysText .= $fromText.' '.$hours[$timePart['from']];
                    } else {
                        $groupDaysText .= $fromText.' '.$timePart['from'];
                    }

                    if (isset($hours[$timePart['from']])) {
                        $groupDaysText .= ' '.$untilText.' '.$hours[$timePart['to']].$delimetr.' ';
                    } else {
                        $groupDaysText .= ' '.$untilText.' '.$timePart['to'].$delimetr.' ';
                    }
                }
            }

            $daysText .= $groupDaysText;
        }
        return $daysText;
    }

    public function getScheduleInlineYandex(int $groupId = 0)
    {
        $modelFind = Schedule::find();
        $modelFind->select([
            'geography_city_id',
            'geography_address_id',
            'group_id',
            'day',
            'all_day',
            'freeday',
            'from',
            'to'
        ]);

        $modelFind->distinct();

        $modelFind->innerJoinWith(['group', 'city']);
        $modelFind->orderBy(['geography_city_id' => SORT_ASC, 'geography_address_id' => SORT_ASC, 'group_id' => SORT_ASC, 'day' => SORT_ASC, 'sort' => SORT_ASC]);
        $modelFind->andWhere([City::tableName().'.is_active' => 1]);
        $modelFind->andWhere([ScheduleGroup::tableName().'.is_active' => 1]);
        $modelFind->andWhere(['geography_address_id' => $this->id]);
        if ($groupId) {
            $modelFind->andWhere(['group_id' => 2]);
        }

        $modelFind->asArray();

        $schedules  = [];
        $hash       = null;
        $preHash    = null;
        $hashId     = -1;
        $preGroupId = null;

        $days = [
            1 => 'пн',
            2 => 'вт',
            3 => 'ср',
            4 => 'чт',
            5 => 'пт',
            6 => 'сб',
            7 => 'вс',
        ];

        foreach ($modelFind->each() as $schedule) {
            $hash = sha1($schedule["geography_address_id"].$schedule['group_id'].$schedule["all_day"].$schedule["freeday"].$schedule["from"].$schedule["to"]);

            if ($preGroupId != $schedule['group_id']) {
                $preGroupId = $schedule['group_id'];
                $hashId     = -1;
            }
            if ($preHash != $hash) {
                $preHash = $hash;
                $hashId ++;
            }

            $schedules[$schedule['group_id']]['geography_address_id']        = $schedule["geography_address_id"];
            $schedules[$schedule['group_id']]['name']                        = $schedule['group']['name'];
            $schedules[$schedule['group_id']]['schedule'][$hashId]['days'][] = (int) $schedule['day'];

            $schedules[$schedule['group_id']]['schedule'][$hashId]['from'] = $schedule['from'];
            $schedules[$schedule['group_id']]['schedule'][$hashId]['to']   = $schedule['to'];

            $schedules[$schedule['group_id']]['schedule'][$hashId]['all_day'] = (bool) $schedule['all_day'];
            $schedules[$schedule['group_id']]['schedule'][$hashId]['freeday'] = (bool) $schedule['freeday'];
        }


        unset($schedule);

        $daysText      = '';
        $groupDaysText = '';
        $fromText      = '';
        $toText        = '';
        $untilText     = '-';
        foreach ($schedules as $groupId => $group) {
            $groupDaysText = '';
            $countTimePart = count($group['schedule']);
            $i             = 0;
            foreach ($group['schedule'] as $timePart) {
                $i++;

                if ($timePart['all_day']) {
                    $groupDaysText .= 'круглосуточно';
                    continue;
                }

                if ($timePart['freeday']) {
                    continue;
                }
                $delimetr = ', ';
                if ($i === 1) {
                    $delimetr = '';
                }


                $groupDaysText .= $delimetr;

                if (min($timePart['days']) == max($timePart['days'])) {
                    $groupDaysText .= $days[min($timePart['days'])].' ';
                } else {
                    $groupDaysText .= $fromText.''.$days[min($timePart['days'])].'-';
                    $groupDaysText .= $toText.''.$days[max($timePart['days'])].' ';
                }

                if (!$timePart['all_day'] && !$timePart['freeday']) {
                    $groupDaysText .= $fromText.' '.$timePart['from'];
                    $groupDaysText .= ''.$untilText.''.$timePart['to'].'';
                }
            }

            $daysText .= $groupDaysText;
        }
        return trim($daysText);
    }

    public function getCity()
    {
        return $this->hasOne(\common\models\geography\City::className(), ['id' => 'geography_city_id']);
    }

    public function beforeDelete()
    {
        \yii\helpers\FileHelper::removeDirectory(Yii::getAlias('@webroot').Yii::$app->params["imageDir"].'/dynamic/'.$this->_imagesPath.$this->id);
        \common\models\geography\Phone::deleteAll(['geography_address_id' => $this->id]);
        \common\models\geography\Email::deleteAll(['geography_address_id' => $this->id]);
        Schedule::deleteAll(['geography_address_id' => $this->id]);
        return parent::beforeDelete();
    }

    public function beforeSave($insert)
    {
        $this->imageFile = UploadedFile::getInstance($this, 'imageFile');
        $this->upload();
        return parent::beforeSave($insert);
    }

    public static function exportYandexXml()
    {
        $lang = 'ru';

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><companies/>');

        $addresses = self::find()
            ->joinWith('city')
            ->where([
                City::tableName().'.is_active' => 1,
                City::tableName().'.kladr_country_code' => 'RU',
                self::tableName().'.not_yandex' => 0,
            ])
            ->each();

        foreach ($addresses as $address) {

            $companyElement = $xml->addChild('company');
            $companyElement->addChild('company-id', $address->id); // ???
            $companyElement->addChild('name', 'GTD')->addAttribute('lang', $lang);
//            $companyElement->addChild('shortname ', 'GTD')->addAttribute('lang', 'en');
            $companyElement->addChild('address', $address->city->kladrCity->type.' '.$address->city->kladrCity->name.', '.$address->value)->addAttribute('lang', $lang);
            if ($address->additional_information) {
                $companyElement->addChild('address-add', $address->additional_information)->addAttribute('lang', $lang);
            }

            if ($address->lat && $address->lon) {
                $coordinates = $companyElement->addChild('coordinates');
                $coordinates->addChild('lon', $address->lon);
                $coordinates->addChild('lat', $address->lat);
            }

            $companyElement->addChild('country ', $address->city->kladrCountry->name)->addAttribute('lang', $lang);

            foreach ($address->phone as $phone) {
                $phoneElement = $companyElement->addChild('phone');
                $phoneElement->addChild('number', $phone->value);
                $phoneElement->addChild('ext');
                $phoneElement->addChild('info', $phone->comment);
            }

            foreach ($address->email as $email) {
                $emailElement = $companyElement->addChild('email', $email->value);
            }

            $companyElement->addChild('url ', 'https://'.$address->city->subdomain.'.gtdel.com');

            $companyElement->addChild('add-url', 'https://vk.com/tkglobaltruckdelivery');
            $companyElement->addChild('add-url', 'https://ok.ru/globaltruckdelivery');
            $companyElement->addChild('add-url', 'https://www.facebook.com/tkglobaltruckdelivery');
            $companyElement->addChild('add-url', 'https://www.instagram.com/tk_gtd');

            $companyElement->addChild('info-page', 'https://'.$address->city->subdomain.'.gtdel.com/contacts');


            $companyElement->addChild('working-time', $address->getScheduleInlineYandex(true, 2))->addAttribute('lang', $lang);

            $companyElement->addChild('rubric-id', 184108201);
            $companyElement->addChild('rubric-id', 184108175);
            $companyElement->addChild('rubric-id', 184108185);


            if (!($address->city->kladrCity->country_code == 'RU' && $address->city->kladrCity->region_code == '91')) {
                $companyElement->addChild('inn', 6679113421);
                $companyElement->addChild('ogrn', 1186658000484);
            }

            $companyElement->addChild('actualization-date', Yii::$app->formatter->asTimestamp($address->timestamp_update));

            $photos = $companyElement->addChild('photos');
            $photos->addChild('photo')->addAttribute('url', 'https://'.$address->city->subdomain.'.gtdel.com/images/static/bg/yandex-address.jpg');

//            $modularCargo = $companyElement->addChild('feature-boolean');
//            $modularCargo->addAttribute('name', 'modular cargo');
//            $modularCargo->addAttribute('value', 1);
//
//            $refrigeratedTransport = $companyElement->addChild('feature-boolean');
//            $refrigeratedTransport->addAttribute('name', 'refrigerated_transport');
//            $refrigeratedTransport->addAttribute('value', 1);
//
//            $paymentByCreditCard = $companyElement->addChild('feature-boolean');
//            $paymentByCreditCard->addAttribute('name', 'payment_by_credit_card');
//            $paymentByCreditCard->addAttribute('value', 1);
        }


        file_put_contents(Yii::getAlias('@frontend-webroot').'/'.self::YANDEX_XML_NAME.'.xml', $xml->asXML());
    }
}
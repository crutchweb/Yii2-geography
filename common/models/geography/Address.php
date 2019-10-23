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

    public function getPhone()
    {
        return $this->hasMany(\common\models\geography\Phone::className(), ['geography_address_id' => 'id']);
    }

    public function getEmail()
    {
        return $this->hasMany(\common\models\geography\Email::className(), ['geography_address_id' => 'id']);
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
}

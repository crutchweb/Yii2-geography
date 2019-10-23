<?php

namespace common\models\geography;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property mediumint(9) $id
 * @property mediumint(9) $geography_city_id
 * @property mediumint(9) $geography_address_id
 * @property varchar(100) $value
 * @property varchar(255) $comment
 * @property timestamp $timestamp
 * @property timestamp $timestamp_update

 * @property-read City $geographyCity
 * @property-read Address $geographyAddress
 */
class Schedule extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public $day_from;
    public $day_to;

    public static function tableName()
    {
        return '{{%geography_schedule}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['id', 'site'], 'safe'],
            ['sort', 'integer', 'min' => 0, 'max' => 65535],
            [['from', 'to'], 'match', 'pattern' => '/^(2[0-3]|[01][0-9]):[0-5][0-9]$/', 'message' => 'Неверный формат времени (правильный пример 09:00 или 19:00)'],
            [['id', 'geography_city_id', 'geography_address_id', 'freeday', 'all_day'], 'integer'],
            [['group_id', 'day', 'geography_address_id'], 'unique', 'targetAttribute' => ['group_id', 'day', 'geography_address_id'], 'message' => 'Такой вид графика и день уже существует для данного графика'],
            [['geography_city_id', 'geography_address_id', 'group_id', 'day', 'from', 'to'], 'required'],
            [['timestamp', 'timestamp_update'], 'date', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'geography_address_id' => 'Адрес уточненый',
            'geography_city_id' => 'Адрес географии',
            'freeday' => 'Выходной',
            'day' => 'Дни недели',
            'all_day' => 'Круглосуточно',
            'from' => 'Начало рабочего дня',
            'group_id' => 'Вид графика',
            'to' => 'Конец рабочего дня',
            'site' => 'Сайт',
            'timestamp' => 'Дата создания',
            'timestamp_update' => 'Дата обновления',
            'sort' => 'Сортировка'
        ];
    }

    public function getCity()
    {
        return $this->hasOne(\common\models\geography\City::className(), ['id' => 'geography_city_id']);
    }

    public function getGroup()
    {
        return $this->hasOne(\common\models\geography\ScheduleGroup::className(), ['id' => 'group_id']);
    }

    public function getAddress()
    {
        return $this->hasOne(Address::className(), ['id' => 'geography_address_id']);
    }
}
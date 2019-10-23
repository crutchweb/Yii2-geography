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
class Phone extends ActiveRecord
{

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return '{{%geography_phone}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['id'], 'safe'],
            ['sort', 'integer', 'min' => 0, 'max' => 65535],
            [['id', 'geography_city_id', 'geography_address_id'], 'integer'],
            [['value', 'comment'], 'trim'],
            [['geography_city_id', 'geography_address_id', 'value'], 'required'],
            [['value'], 'string', 'min' => 1, 'max' => 100],
            [['comment'], 'string', 'min' => 1, 'max' => 255],
            [['timestamp', 'timestamp_update'], 'date', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'geography_city_id' => 'Город географии',
            'geography_address_id' => 'Адрес географии',
            'value' => 'Телефон',
            'comment' => 'Комментарий',
            'timestamp' => 'Дата создания',
            'timestamp_update' => 'Дата обновления',
            'sort' => 'Сортировка'
        ];
    }

    public function getCity()
    {
        return $this->hasOne(\common\models\geography\City::className(), ['id' => 'geography_city_id']);
    }

    public function getAddress()
    {
        return $this->hasOne(\common\models\geography\Address::className(), ['id' => 'geography_address_id']);
    }
}
<?php

namespace common\models\geography;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property mediumint(8) UN $id
 * @property varchar(10) $site
 * @property tinyint(1) $is_active
 * @property varchar(255) $subdomain
 * @property int(11) UN $sxgeo_city_id
 * @property varchar(12) $kladr_city_code
 * @property varchar(12) $kladr_region_code
 * @property varchar(12) $kladr_country_code
 * @property tinyint(1) $is_new
 * @property varchar(512) $name
 * @property varchar(10) $zone
 * @property timestamp $timestamp
 * @property timestamp $timestamp_update
 *
 * @property-read SXGeoCity $sypexCity
 * @property-read KladrCity $kladrCity
 * @property-read KladrRegion $kladrRegion
 * @property-read KladrCountry $kladrCountry
 * @property-read Address $geographyAddress
 * @property-read Phone $geographyPhone
 * @property-read Email $geographyEmail
 */
class City extends ActiveRecord
{
    const ACTIVE   = 1;
    const DISABLED = 0;

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return '{{%geography_city}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['id'], 'safe'],
            [['id', 'sxgeo_city_id', 'is_new', 'is_active'], 'integer'],
            [['subdomain', 'name', 'site', 'zone'], 'trim'],
            [['subdomain'], 'match', 'pattern' => '/^[\-\w]{1,255}$/'],
            [['is_active', 'is_new'], 'default', 'value' => self::DISABLED],
            [['is_active', 'is_new'], 'in', 'range' => [self::ACTIVE, self::DISABLED]],
            [['is_active', 'is_new', 'subdomain', 'sxgeo_city_id', 'kladr_city_code'], 'required'],
            [['subdomain'], 'string', 'min' => 1, 'max' => 255],
            [['name'], 'string', 'min' => 1, 'max' => 512],
            [['kladr_city_code', 'kladr_region_code', 'kladr_country_code'], 'string', 'min' => 0, 'max' => 12],
            [['site', 'zone'], 'string', 'min' => 1, 'max' => 10],
            [['timestamp', 'timestamp_update'], 'date', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'site' => 'Сайт',
            'is_active' => 'Активность',
            'subdomain' => 'Поддомен',
            'sxgeo_city_id' => 'Город SYPEX Geo',
            'kladr_city_code' => 'Город (SAP)',
            'kladr_region_code' => 'Регион (SAP)',
            'kladr_country_code' => 'Страна (SAP)',
            'is_new' => 'Новый',
            'name' => 'Город (название для сайта)',
            'zone' => 'Язык',
            'timestamp' => 'Дата создания',
            'timestamp_update' => 'Дата обновления',
        ];
    }

    public function getSxgeoCity()
    {
        return $this->hasOne(\common\models\sxgeo\City::className(), ['id' => 'sxgeo_city_id']);
    }

    public function getKladrCity()
    {
        return $this->hasOne(\common\models\kladr\City::className(), ['code' => 'kladr_city_code', 'region_code' => 'kladr_region_code', 'country_code' => 'kladr_country_code']);
    }

    public function getKladrRegion()
    {
        return $this->hasOne(\common\models\kladr\Region::className(), ['code' => 'kladr_region_code', 'country_code' => 'kladr_country_code']);
    }

    public function getKladrCountry()
    {
        return $this->hasOne(\common\models\kladr\Country::className(), ['code' => 'kladr_country_code']);
    }

    public function getGeographyAddress()
    {
        return $this->hasMany(\common\models\geography\Address::className(), ['geography_city_id' => 'id']);
    }

    public function getGeographyPhone()
    {
        return $this->hasMany(\common\models\geography\Phone::className(), ['geography_city_id' => 'id']);
    }

    public function getGeographyEmail()
    {
        return $this->hasMany(\common\models\geography\Email::className(), ['geography_city_id' => 'id']);
    }

    public function beforeSave($insert)
    {
        $this->site = Yii::$app->id;
        return parent::beforeSave($insert);
    }

    public function beforeDelete()
    {
        \common\models\geography\Address::deleteAll(['geography_city_id' => $this->id]);
        \common\models\geography\Email::deleteAll(['geography_city_id' => $this->id]);
        \common\models\geography\Phone::deleteAll(['geography_city_id' => $this->id]);
        \common\models\geography\Schedule::deleteAll(['geography_city_id' => $this->id]);
        return parent::beforeDelete();
    }
}
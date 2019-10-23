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
class ScheduleGroup extends ActiveRecord
{

    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return '{{%geography_schedule_group}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['id'], 'safe'],
            [['id', 'is_active'], 'integer'],
            [['name'], 'required'],
            [['timestamp', 'timestamp_update'], 'date', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'name' => 'Вид графика',
            'is_active' => 'Активен',
            'timestamp' => 'Дата создания',
            'timestamp_update' => 'Дата обновления',
        ];
    }

    public function getAddress()
    {
        return $this->hasOne(\common\models\geography\Address::className(), ['id' => 'geography_address_id']);
    }

    public function getSchedule()
    {
        return $this->hasOne(\common\models\geography\Schedule::className(), ['group_id' => 'id']);
    }
}
<?php

namespace app\modules\shop\models;

use Yii;
use yii\caching\TagDependency;
use \devgroup\TagDependencyHelper\ActiveRecordHelper;

/**
 * This is the model class for table "special_price_list".
 *
 * @property integer $id
 * @property integer $object_id
 * @property string $class
 * @property integer $active
 * @property string $type_id
 * @property integer $sort_order
 * @property string $params
 * @property string $handler
 */
class SpecialPriceList extends \yii\db\ActiveRecord
{

    const TYPE_CORE = 'core';
    const TYPE_DISCOUNT ='discount';
    const TYPE_DELIVERY ='delivery';
    const TYPE_PROJECT = 'project';


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%special_price_list}}';
    }

    /**
     * @inheritdoc
     */
    public static function getModel($class, $object_id)
    {

        $cacheKey = self::className() . $class .$object_id;

        if (!$result = Yii::$app->cache->get($cacheKey)) {
            $result = self::find()
                ->where(
                    [
                        'class' => $class,
                        'object_id'=>$object_id
                    ]
                )->one();

            Yii::$app->cache->set(
                $cacheKey,
                $result,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(static::className())
                        ]
                    ]
                )
            );

        }
        return  $result;

    }


    public static function getModelsByKey($key)
    {
        $cacheKey = self::className() . $key;

        if (!$results = Yii::$app->cache->get($cacheKey)) {
            $results = self::find()
                ->leftJoin(
                    SpecialPriceListType::tableName(),
                    SpecialPriceListType::tableName().'.id = '.self::tableName().'.type_id'
                )
                ->where(
                    [
                        SpecialPriceListType::tableName().'.key' =>$key
                    ]
                )->all();

            Yii::$app->cache->set(
                $cacheKey,
                $results,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(static::className())
                        ]
                    ]
                )
            );

        }
        return  $results;

    }
    public function beforeSave($insert)
    {
        TagDependency::invalidate(
            Yii::$app->cache,
            [
                ActiveRecordHelper::getCommonTag($this->className()),
                'SpecialPriceList:' . $this->id . ':0'
            ]
        );

        TagDependency::invalidate(
            Yii::$app->cache,
            [
                ActiveRecordHelper::getCommonTag($this->className()),
                'SpecialPriceList:' . $this->id . ':1'
            ]
        );

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'class', 'type_id', 'handler'], 'required'],
            [['object_id', 'active', 'sort_order', 'type_id'], 'integer'],
            [['params'], 'string'],
            [['class'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'class' => Yii::t('app', 'Class'),
            'active' => Yii::t('app', 'Active'),
            'type_id' => Yii::t('app', 'Type'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'params' => Yii::t('app', 'Params'),
        ];
    }
}
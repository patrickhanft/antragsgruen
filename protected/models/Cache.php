<?php

/**
 * This is the model class for table "cache".
 *
 * The followings are the available columns in table 'cache':
 * @property string $id
 * @property string $datum
 * @property string $daten
 */
class Cache extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Cache the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @param string $key
	 * @return null|mixed
	 */
	public static function getObject($key)
	{
		$entry = self::model()->findByPk($key);
		if (is_null($entry)) return null;

		/** @var Cache $entry */
		return unserialize($entry->daten);
	}

	/**
	 * @param string $key
	 * @param mixed $val
	 * @return Cache
	 */
	public static function setObject($key, $val)
	{
		$cache        = new Cache();
		$cache->id    = $key;
		$cache->daten = serialize($val);
		$cache->datum = new CDbExpression('NOW()');
		$cache->save();
		return $cache;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'cache';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id', 'required'),
			array('id', 'length', 'max' => 32),
			array('datum, daten', 'safe'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'    => 'ID',
			'datum' => 'Datum',
			'daten' => 'Daten',
		);
	}
}
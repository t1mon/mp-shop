<?php

namespace shop\forms\Shop\Order;

use shop\entities\User\User;
use yii\base\Model;

class CustomerForm extends Model
{
    public $phone;
    public $name;

    public function __construct($config = [])
    {
        if (!\Yii::$app->user->isGuest){
            $user = User::findOne(\Yii::$app->user->id);
            $this->phone = $user->phone;
            $this->name = $user->getSurnameName();
        }

        parent::__construct($config);
    }

    public function beforeValidate()
    {
        $this->phone = mb_substr(preg_replace('/[^\d]/', '', $this->phone),1);
        //$this->phone = preg_replace('/[^\d]/', '', $this->phone);
        return parent::beforeValidate();
    }

    public function afterValidate()
    {
        $this->phone = preg_replace('/[^\d]/', '', $this->phone);
        //$this->phone = mb_substr(preg_replace('/[^\d]/', '', $this->phone),1);
        parent::afterValidate();
    }

    public function rules(): array
    {
        return [
            [['phone', 'name'], 'required'],
            [['phone', 'name'], 'string', 'max' => 255],
            ['phone','match', 'pattern' => '/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'ФИО',
            'phone' => 'Номер телефона',
        ];
    }
}
<?php

/**
 * Class SmsruModule
 */
class SmsruModule extends \yupe\components\WebModule
{
    /**
     * @var string
     */
    public $smsruAuthID = '';

    /**
     * @var string
     */
    public $sourceName;

    /**
     * @var string
     */
    public $smsruTo = '';
    /**
     * @var string
     */
    public $smsruFrom = '';

    /**
     * @var boolean
     */
    public $orderCreateEvent = self::NO;
    /**
     * @var boolean
     */
    public $callbackAddEvent = self::NO;

    /**
     *
     */
    const NO = 0;
    /**
     *
     */
    const YES = 1;
    /**
     *
     */
    const VERSION = '1.0';

    /**
     * @return array
     */
    public function getEditableParams()
    {
        return [
            'smsruAuthID',
            'smsruTo',
            'smsruFrom',
            'orderCreateEvent' => [self::NO => 'Нет', self::YES => 'Да'],
            'callbackAddEvent' => [self::NO => 'Нет', self::YES => 'Да'],
            'sourceName'
        ];
    }

    /**
     * @return array
     */
    public function getParamsLabels()
    {
        return [
            'smsruAuthID' => Yii::t('SmsruModule.smsru', 'AUTH_ID'),
            'smsruTo' => Yii::t('SmsruModule.smsru', 'Recipients of notifications (Phone)'),
            'smsruFrom' => Yii::t('SmsruModule.smsru', 'Sender of notifications (name)'),
            'orderCreateEvent' => Yii::t('SmsruModule.smsru', 'Send message when order will be created'),
            'callbackAddEvent' => Yii::t('SmsruModule.smsru', 'Send message when callback will be add'),
            'sourceName' => 'Источник'
        ];
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return ['queue'];
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return Yii::t('SmsruModule.smsru', 'module_category_name');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return Yii::t('SmsruModule.smsru', 'module_name');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return Yii::t('SmsruModule.smsru', 'module_description');
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'fa fa-fw fa-envelope-o';
    }

    /**
     * @return mixed
     */
    public function getAdminPageLink()
    {
        return Yii::app()->createUrl('/backend/modulesettings?module=smsru');
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return Yii::t('SmsruModule.smsru', 'omg.im team');
    }

    /**
     * @return string
     */
    public function getAuthorEmail()
    {
        return Yii::t('SmsruModule.smsru', 'dev@omg.im');
    }

    /**
     * @return array
     */
    public function getEditableParamsGroups()
    {
        return [
            '0.account' => [
                'label' => Yii::t('SmsruModule.smsru', 'module_params_group_account'),
                'items' => [
                    'smsruAuthID',
                ],
            ],
            '1.fields' => [
                'label' => Yii::t('SmsruModule.smsru', 'module_params_group_fields'),
                'items' => [
                    'smsruTo',
                    'smsruFrom',
                ],
            ],
            '2.fields' => [
                'label' => Yii::t('SmsruModule.smsru', 'module_params_group_sets'),
                'items' => [
                    'orderCreateEvent',
                    'callbackAddEvent',
                ],
            ],
            '3.fields' => [
                'label' => 'Свои значения полей в смс',
                'items' => [
                    'sourceName',
                ],
            ],
        ];
    }
}

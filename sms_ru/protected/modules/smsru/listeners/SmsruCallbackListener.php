<?php
/**
 * Created by PhpStorm.
 * User: Rushan Garipov
 * Date: 19.03.2018
 * Time: 14:20
 * Заюзан код из доков sms.ru в пользу отказа от вендоров
 */

/**
 * Class SmsruCallbackListener
 */
class SmsruCallbackListener
{
    public static function onCallbackAdd(CallbackAddEvent $event)
    {
        $module = Yii::app()->getModule('smsru');

        if ($module->callbackAddEvent == $module::YES) {

            $callback = $event->getModel();
            $api_id = $module->smsruAuthID;
            $to = $module->smsruTo;
            $from = $module->smsruFrom;
            $msg = '#' . $callback->id . ' ' . $callback->getType(). '
' . ($module->sourceName ?: $callback->url) . '
' . date('Y.m.d H:m') . '
' . $callback->phone . '
' . $callback->name;

            $send_array = [
                "api_id" => $api_id,
                "to" => $to, // До 100 штук до раз
                "msg" => $msg,
                "json" => 1, // Для получения более развернутого ответа от сервера
            ];
            if ($from) {
                $send_array['from'] = $from; // Имя отправителя
            }

            $ch = curl_init("https://sms.ru/sms/send");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($send_array));
            $body = curl_exec($ch);
            curl_close($ch);

            $json = json_decode($body);
            if ($json) { // Получен ответ от сервера
                if ($json->status == "OK") { // Запрос выполнился
                    foreach ($json->sms as $phone => $data) { // Перебираем массив СМС сообщений
                        if ($data->status == "OK") { // Сообщение отправлено
                            Yii::log('Сообщение на номер ' . $phone . ' успешно отправлено. ID сообщения: ' . $data->sms_id, 'info', 'Smsru.SmsruCallbackListener.onCallbackAdd');
                        } else { // Ошибка в отправке
                            Yii::log('Сообщение на номер ' . $phone . ' не отправлено. Код ошибки: ' . $json->status_code . 'Текст ошибки: ' . $json->status_text, 'error', 'Smsru.SmsruCallbackListener.onCallbackAdd');
                        }
                    }
                    Yii::log('Callback was added. id: ' . $callback->id . ' Баланс после отправки: ' . $json->balance, 'info', 'Smsru.SmsruCallbackListener.onCallbackAdd');
                } else { // Запрос не выполнился (возможно ошибка авторизации, параметрах, итд...)
                    Yii::log('Запрос не выполнился. Код ошибки: ' . $json->status_code . ' Текст ошибки: ' . $json->status_text, 'error', 'Smsru.SmsruCallbackListener.onCallbackAdd');
                }
            } else {
                Yii::log('Запрос не выполнился. Не удалось установить связь с сервером', 'error', 'Smsru.SmsruCallbackListener.onCallbackAdd');
            }
        }
    }
}


<?php
/**
 * Class SmsruOrderListener
 *
 *
/* @var $order Order */
class SmsruOrderListener
{
    public static function onOrderAdd(OrderEvent $event)
    {
        $module = Yii::app()->getModule('smsru');

        if ($module->orderCreateEvent == $module::YES) {

            $order = $event->getOrder();
            $api_id = $module->smsruAuthID;
            $to = $module->smsruTo;
            $from = $module->smsruFrom;
            $msg = Yii::t('SmsruModule.smsru', 'Order create') . ' #' . $order->id . '
' . date('Y.m.d H:m') . '
' . $order->name . '
' . $order->phone . '
' . $order->total_price . ' руб.';

            $send_array = [
                "api_id" => $api_id,
                "to" => $to, // До 100 штук до раз
                "msg" => $msg,
                "json" => 1, // Для получения более развернутого ответа от сервера
            ];
            if ($from) {
                $send_array['from'] = $from;
            }

            $ch = curl_init("https://sms.ru/sms/send");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $send_array ));
            $body = curl_exec($ch);
            curl_close($ch);

            $json = json_decode($body);
            if ($json) { // Получен ответ от сервера
                if ($json->status == "OK") { // Запрос выполнился
                    foreach ($json->sms as $phone => $data) { // Перебираем массив СМС сообщений
                        if ($data->status == "OK") { // Сообщение отправлено
                            Yii::log('Сообщение на номер '. $phone .' успешно отправлено. ID сообщения: '. $data->sms_id, 'info', 'Smsru.SmsruOrderListener.onOrderAdd');
                        } else { // Ошибка в отправке
                            Yii::log('Сообщение на номер '. $phone .' не отправлено. Код ошибки: '. $json->status_code. 'Текст ошибки: '. $json->status_text, 'error', 'Smsru.SmsruOrderListener.onOrderAdd');
                        }
                    }
                    Yii::log('Order sms was added. id: '. $order->id .' Баланс после отправки: '. $json->balance, 'info', 'Smsru.SmsruOrderListener.onOrderAdd');
                } else { // Запрос не выполнился (возможно ошибка авторизации, параметрах, итд...)
                    Yii::log('Запрос не выполнился. Код ошибки: '. $json->status_code. ' Текст ошибки: '. $json->status_text, 'error', 'Smsru.SmsruOrderListener.onOrderAdd');
                }
            } else {
                Yii::log('Запрос не выполнился. Не удалось установить связь с сервером', 'error', 'Smsru.SmsruOrderListener.onOrderAdd');
            }
        }
    }
}

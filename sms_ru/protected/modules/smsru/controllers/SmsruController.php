<?php
Yii::import('application.modules.callback.models.Callback');

class SmsruController extends yupe\components\controllers\FrontController
{

    public function actionSend()
    {
        $generatedCode = rand(1000,9999);

        $to = Yii::app()->getRequest()->getPost('to');
        $name = Yii::app()->getRequest()->getPost('name');

        Yii::app()->session['sms_ver-code-'.$to] = $generatedCode;
        Yii::app()->session['sms_ver-name-'.$to] = $name;

        $module = Yii::app()->getModule('smsru');
        $api_id = $module->smsruAuthID;
        $from = $module->smsruFrom;
        $msg = $generatedCode;

        $send_array = [
            "api_id" => $api_id,
            "to" => $to, // До 100 штук до раз
            "msg" => $msg,
            "json" => 1, // Для получения более развернутого ответа от сервера,
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
                        Yii::app()->ajax->success("Вам отправлено сообщение с кодом");
                    } else { // Ошибка в отправке
                        Yii::log('Сообщение на номер ' . $phone . ' не отправлено. Код ошибки: ' . $json->status_code . 'Текст ошибки: ' . $json->status_text, 'error', 'Smsru.SmsruCallbackListener.onCallbackAdd');
                        Yii::app()->ajax->failure("К сожалению мы не смогли отправить вам код");
                    }
                }
                Yii::log('Код успешно отправлен Баланс после отправки: ' . $json->balance, 'info', 'Smsru.SmsruCallbackListener.onCallbackAdd');
            } else { // Запрос не выполнился (возможно ошибка авторизации, параметрах, итд...)
                Yii::log('Запрос не выполнился. Код ошибки: ' . $json->status_code . ' Текст ошибки: ' . $json->status_text, 'error', 'Smsru.SmsruCallbackListener.onCallbackAdd');
            }
        } else {
            Yii::log('Запрос не выполнился. Не удалось установить связь с сервером', 'error', 'Smsru.SmsruCallbackListener.onCallbackAdd');
        }
    }

    public function actionVerify()
    {
        $code = Yii::app()->getRequest()->getPost('code');
        $phone = Yii::app()->getRequest()->getPost('phone');

        $codeInSession = Yii::app()->session['sms_ver-code-'.$phone];
        $nameInSession = Yii::app()->session['sms_ver-name-'.$phone];

        if(!isset($codeInSession)) {
            Yii::app()->ajax->failure("Сессии нет");
            return;
        }

        if ($codeInSession == $code) {
            $_POST['Callback']['agree'] = 1;
            $_POST['Callback']['phone'] = $phone;
            $_POST['Callback']['name'] = $nameInSession;
            $_POST['Callback']['comment'] = 'GetPrice';
            $_POST['Callback']['type'] = Callback::TYPE_PRICE_REQUEST;
            Yii::app()->callbackManager->add($_POST['Callback'], Yii::app()->getRequest()->getUrlReferrer());

            Yii::app()->ajax->success("Номер подтвержден");
        } else {
            Yii::app()->ajax->failure("Не удалось подтвердить номер");
        }
    }
}

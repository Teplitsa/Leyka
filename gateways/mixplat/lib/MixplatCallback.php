<?php

namespace MixplatClient;

use MixplatClient\Notify\PaymentCheck;
use MixplatClient\Notify\PaymentStatus;
use MixplatClient\Notify\RefundStatus;
use MixplatClient\Notify\SmsNotify;

class MixplatCallback
{
    public $postData;

    /**
     * @param string|null $postdata
     * @return MixplatCallback
     */
    public function init($postdata = null)
    {
        if ($postdata) {
            $this->postData = $postdata;
        } else {
            $this->postData = file_get_contents("php://input");
        }
        return $this;
    }

    /**
     * @return PaymentCheck|PaymentStatus|RefundStatus|SmsNotify|null
     */
    public function getNotify()
    {
        $data = json_decode($this->postData, true);
        switch ($data['request']) {
            case MixplatVars::NOTIFY_REQUEST_PAYMENT_STATUS:
                $notify = new PaymentStatus();
                return $notify->setParams($data);
                break;

            case MixplatVars::NOTIFY_REQUEST_REFUND_STATUS:
                $notify = new RefundStatus();
                return $notify->setParams($data);
                break;

            case MixplatVars::NOTIFY_REQUEST_PAYMENT_CHECK:
                $notify = new PaymentCheck();
                return $notify->setParams($data);
                break;

            case MixplatVars::NOTIFY_REQUEST_SMS:
                $notify = new SmsNotify();
                return $notify->setParams($data);
                break;

            default:
                return null;
        }
    }

    /**
     * Вернуть "успешно"
     */
    public function returnSuccess($message = null)
    {
        echo json_encode(array(
            'result' => MixplatVars::RESULT_OK,
            'message' => $message,
        ));
    }

    /**
     * Вернуть ошибку
     * @param string $errorText
     */
    public function returnError($errorText, $errorDescription = null, $message = null)
    {
        echo json_encode(array(
            'result' => $errorText,
            'error_description' => $errorDescription,
            'message' => $message,
        ));
    }

}

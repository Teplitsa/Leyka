<?php

namespace YooKassa\Model\Deal;

use YooKassa\Common\AbstractEnum;

/**
 * Class FeeMoment
 *
 * @package YooKassa
 */
class FeeMoment extends AbstractEnum
{
    /** Вознаграждение после успешной оплаты */
    const PAYMENT_SUCCEEDED = 'payment_succeeded';
    /** Вознаграждение при закрытии сделки после успешной выплаты */
    const DEAL_CLOSED = 'deal_closed';

    protected static $validValues = array(
        self::PAYMENT_SUCCEEDED => true,
        self::DEAL_CLOSED => true,
    );
}
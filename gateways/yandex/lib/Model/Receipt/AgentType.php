<?php


namespace YooKassa\Model\Receipt;


use YooKassa\Common\AbstractEnum;

class AgentType extends AbstractEnum
{
    const BANKING_PAYMENT_AGENT = 'banking_payment_agent';
    const BANKING_PAYMENT_SUBAGENT = 'banking_payment_subagent';
    const PAYMENT_AGENT = 'payment_agent';
    const PAYMENT_SUBAGENT = 'payment_subagent';
    const ATTORNEY = 'attorney';
    const COMMISSIONER = 'commissioner';
    const AGENT = 'agent';

    protected static $validValues = array(
        self::BANKING_PAYMENT_AGENT    => true,
        self::BANKING_PAYMENT_SUBAGENT => true,
        self::PAYMENT_AGENT            => true,
        self::PAYMENT_SUBAGENT         => true,
        self::ATTORNEY                 => true,
        self::COMMISSIONER             => true,
        self::AGENT                    => true,
    );
}
<?php
namespace Learning\Util;

class Logger
{
    private const LOG_FORMAT = "time:%s\tpid:%s\tmessage:%s\tcontext:%s\n";
    private const TIME_FORMAT = 'Y-m-d H:i:s.u';

    /**
     * output log.
     *
     * @param string $message
     * @param array $context
     */
    public static function output(string $message, array $context = []): void
    {
        $currentDatetime = (new \DateTime())->format(self::TIME_FORMAT);
        echo sprintf(self::LOG_FORMAT, $currentDatetime, getmypid(), $message, json_encode($context));
    }
}
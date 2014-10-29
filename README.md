#toplogger

Log Levels

Monolog supports the logging levels described by RFC 5424.

DEBUG (100): Detailed debug information.

INFO (200): Interesting events. Examples: User logs in, SQL logs.

NOTICE (250): Normal but significant events.

WARNING (300): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.

ERROR (400): Runtime errors that do not require immediate action but should typically be logged and monitored.

CRITICAL (500): Critical conditions. Example: Application component unavailable, unexpected exception.

ALERT (550): Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.

EMERGENCY (600): Emergency: system is unusable.

All log levels above can be used as follows addDebug(), addInfo(), addNotice(), addWarning() etc.

Sample line:

$logger->addInfo('Very informative text', array('something' => 'something else'));

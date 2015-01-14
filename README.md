#toplogger

#### Standards for our code:

- Major steps (Iplom, Transform Deecompose, etc) when they start AND end should be at level **200**
- Any steps **inside** a major step should be **250**
- Always ask "could this log line show up a million times in a row?" If the answer is yes, log at **100** , also, try and have an **550** message just show up once when that starts to happen, if possible.

#### Log Levels

Monolog supports the logging levels described by RFC 5424.

**DEBUG (100):** Detailed debug information.

**INFO (200):** Interesting events. Examples: User logs in, SQL logs.

**NOTICE (250):** Normal but significant events.

**WARNING (300):** Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.

**ERROR (400):** Runtime errors that do not require immediate action but should typically be logged and monitored.

**CRITICAL (500):** Critical conditions. Example: Application component unavailable, unexpected exception.

**ALERT (550):** Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.

**EMERGENCY (600):** Emergency: system is unusable.

All log levels above can be used as follows addDebug(), addInfo(), addNotice(), addWarning() etc.

#### Sample line:

```$logger->addInfo('Very informative text', array('something' => 'something else'));```

Toplogger uses different log levels and settings for different environments. At any time, environmental variable ENV should be set to one of the values: production, staging or development

#### Default settings for different environments:

**Production**

Debug disabled

Slack enabled

Log levels to use for writing to a file: 200,400,550

Log levels to be sent to Slack: 200,550

**Staging**

Debug enabled

Slack enabled

Log levels to use for writing to a file: 100,200,250,300,400,500,550,600

Log levels to be sent to Slack: 200,550

**Development**

Debug enabled

Slack disabled

Log levels to use for writing to a file: 100,200,250,300,400,500,550,600

Log levels to be sent to Slack: null

Additionaly, regardless of the environment, log levels can always be overridden using the variables: ```LOGLEVELS``` and ```SLACKLEVELS```. The log levels should be written seperated with commes WITHOUT any spaces.

For example: ```LOGLEVELS=200,350,500```

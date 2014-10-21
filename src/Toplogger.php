<?php namespace TopLog\Toplogger;

use Monolog\ErrorHandler;
use Monolog\Logger;
use Monolog\Handler\SlackHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use TopLog\Toplogger\Processors\TopLogProcessor;

class Toplogger extends Logger
{
    private $slack;
    private $slackEnabled;
    protected $handlers;
    private $debug;
    protected $name;

    public function __construct($name = 'TOPLOG', $logFile = 'toplog_app.log', $slackToken = null, $slackChannel = null)
    {
        $this->logFile = $logFile;
        $this->name = $name;

        // Check if the env is production, if not, turn debug mode on
        $this->debug = getenv("ENV") !== "production";

        //Check if Slack is enabled
        if (!$slackToken || !$slackChannel)
        {
            $this->slackEnabled = false;
        }
        else
        {
            $this->slackEnabled = true;
        }

        $streamHandler = new StreamHandler(getenv('TOPLOG_LOGDIR') . $logFile, Logger::INFO, true, 0644);
        $streamHandler->setFormatter($this->formatter());
        $this->handlers = [$streamHandler];

        // Setup pushing to Slack if required
        if($this->slackEnabled && $slackToken !== null && $slackChannel !== null)
        {
            $this->setupSlack($slackToken, $slackChannel);
        }

        if($this->debug)
        {
            $this->setupDebug($this->slackEnabled);
        }

        parent::__construct($name, $this->handlers, [new TopLogProcessor]);
    }

    private function setupDebug($slackEnabled)
    {
        $debugStreamHandler = new StreamHandler(getenv('TOPLOG_LOGDIR') . $this->logFile, Logger::DEBUG, true, 0644);
        $debugStreamHandler->setFormatter($this->formatter());
        $debugLogger = new Logger('DEBUG');

        if($debugStreamHandler !== null) {
            $debugLogger->pushHandler($debugStreamHandler);
        }

        if ($slackEnabled && $this->slack !== null)
        {
            $debugLogger->pushHandler($this->slack);
        }

        $debugLogger->pushProcessor(new TopLogProcessor);
        ErrorHandler::register($debugLogger);
    }

    private function setupSlack($token, $room)
    {
        $this->slack = new SlackHandler($token, $room, $this->name, false, null, Logger::DEBUG);
        $this->slack->setFormatter($this->formatter());

        array_push($this->handlers, $this->slack);
    }

    private function formatter()
    {
        if ($this->debug)
        {
            $formatter = new LineFormatter("%datetime% %channel% %level_name% %message% %context%\n", "[d/M/Y:H:i:s O]");
        }
        else
        {
            $formatter = new LineFormatter("%datetime% %channel% %level_name% %message%\n", "[d/M/Y:H:i:s O]");
        }

        return $formatter;
    }
}

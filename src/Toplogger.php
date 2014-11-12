<?php namespace TopLog\Toplogger;

use Monolog\Logger;
use Monolog\ErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FilterHandler;
use Monolog\Formatter\LineFormatter;
use TopLog\Toplogger\Processors\TopLogProcessor;
use TopLog\Toplogger\Handlers\SlackHandler;

class Toplogger extends Logger
{
    private $env;
    private $logLevels;
    private $slackLevels;
    private $slack;
    private $slackEnabled;
    private $debug;
    protected $name;
    protected $handlers;

    public function __construct($name = 'TOPLOG', $logFile = 'toplog_app.log', $slackToken = null, $slackChannel = null)
    {
        $this->logFile = $logFile;
        $this->name = $name;

        detectEnvAndConfig();

        $streamHandler = new StreamHandler(getenv('TOPLOG_LOGDIR') . $logFile, Logger::INFO, true, 0666);
        $streamHandler->setFormatter($this->formatter());

        filterLevelsAndPush($streamHandler, $this->logLevels);

        // Setup pushing to Slack if required
        if($this->slackEnabled && $slackToken !== null && $slackChannel !== null)
        {
            $this->setupSlack($slackToken, $slackChannel);
        }

        parent::__construct($name, $this->handlers, [new TopLogProcessor]);
    }

    private function setupDebug()
    {
        $debugStreamHandler = new StreamHandler(getenv('TOPLOG_LOGDIR') . $this->logFile, Logger::DEBUG, true, 0666);
        $debugStreamHandler->setFormatter($this->formatter());
        $debugLogger = new Logger('DEBUG');

        if($debugStreamHandler !== null) {
            $debugLogger->pushHandler($debugStreamHandler);
        }

        $debugLogger->pushProcessor(new TopLogProcessor);
        ErrorHandler::register($debugLogger);
    }

    private function setupSlack($token, $room)
    {
        $this->slack = new SlackHandler($token, $room, $this->name, true, null, Logger::DEBUG, false);

        filterLevelsAndPush($this->slack, $this->slackLevels);
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

    private function detectEnvAndConfig() 
    {
        //get the env variables

        $this->env = getenv('ENV');
        $this->logLevels = array_map('intval', explode(',', getenv('LOGLEVELS')));
        $this->slackLevels = array_map('intval', explode(',', getenv('SLACKLEVELS')));

        // Check if the env is production, if not, turn debug mode on
        // debug is always on for dev and staging
        $this->debug = $this->env !== "production";

        //slack in enabled for prod and staging
        if($this->env === "dev")
        {
            $this->slackEnabled = false;
        }
        else
        {
            $this->slackEnabled = true;
        }

        //if debug is enabled, setup the handler with or without slack depending on the arg passed
        if($this->debug)
        {
            $this->setupDebug();
        }
    }

    private function filterLevelsAndPush($handler, $loglevels)
    {
        //Now we are filtering the levels of logs so we are wrapping the handler with filterhandler
        $filterHandler = new FilterHandler($handler, $loglevels);

        array_push($this->handlers, $filterHandler);
    }
}

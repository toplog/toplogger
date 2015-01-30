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

    public function __construct($name = 'TOPLOG', $logFile = 'toplog_app.log', $logDir = '/var/log/toplog/', $env = 'development', $slackToken = null, $slackChannel = null, $slackLevels = null, $logLevels = null)
    {
        $this->logFile = $logFile;
        $this->name = $name;
        $this->env = $env;
        $this->logDir = $logDir;

        $this->detectEnvAndConfig();

        $streamHandler = new StreamHandler($logDir . $logFile, Logger::INFO, true, 0666);
        $streamHandler->setFormatter($this->formatter());

        $this->filterLevelsAndPush($streamHandler, $this->logLevels);

        // Setup pushing to Slack if required
        if($this->slackEnabled && $slackToken !== null && $slackChannel !== null)
        {
            $this->setupSlack($slackToken, $slackChannel);
        }

        parent::__construct($name, $this->handlers, [new TopLogProcessor]);
    }

    private function setupDebug()
    {
        $debugStreamHandler = new StreamHandler($this->logDir . $this->logFile, Logger::DEBUG, true, 0666);
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

        $this->filterLevelsAndPush($this->slack, $this->slackLevels);
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

        $this->handlers = [];

        //default values
        if ($this->env === "production")
        {
             $this->debug = false;
             $this->slackEnabled = true;
             $this->logLevels = [200,400,550];
             $this->slackLevels = [200,550];
        } 
        elseif ($this->env === "staging")
        {
            $this->debug = true;
            $this->slackEnabled = true;
            $this->logLevels = [100,200,250,300,400,500,550,600];
            $this->slackLevels = [200,550];
        }
        elseif($this->env === "development")
        {
            $this->debug = true;
            $this->slackEnabled = false;
            $this->logLevels = [100,200,250,300,400,500,550,600];
            $this->slackLevels = [];
        }
        else
        {
            $this->debug = true;
            $this->slackEnabled = false;
            $this->logLevels = [100,200,250,300,400,500,550,600];
            $this->slackLevels = [];
        }

        //override the log levels if they are specified as an env var
        if($this->logLevels !== null)
        {
            $this->logLevels = array_map('intval', $this->logLevels);
        }

        if($this->slackLevels !== null)
        {
            $this->slackLevels = array_map('intval', $this->slackLevels);
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

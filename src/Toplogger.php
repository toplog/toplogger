<?php namespace TopLog\Toplogger;

use Monolog\ErrorHandler;
use Monolog\Logger;
use Monolog\Handler\HipChatHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use TopLog\Toplogger\Processors\TopLogProcessor;

class Toplogger extends Logger
{
    private $hipchat;
    private $hipchatEnabled;
    protected $handlers;
    private $debug;
    protected $name;
    private $fallback;

    public function __construct($name = 'TOPLOG', $logFile = 'toplog_app.log', $hipchatToken = null, $hipchatRoom = null)
    {
        $this->logFile = $logFile;
        $this->name = $name;

        // Check if the env is production, if not, turn debug mode on
        $this->debug = getenv("ENV") !== "production";

        //Check if hipchat is enabled
        if (!$hipchatToken || !$hipchatRoom)
        {
            $this->hipchatEnabled = false;
        }
        else
        {
            $this->hipchatEnabled = true;
        }

        try
        {
            $streamHandler = new StreamHandler(getenv('TOPLOG_LOGDIR') . $logFile, Logger::INFO, true);
            $streamHandler->setFormatter($this->formatter());
            $this->handlers = [$streamHandler];

            // Setup pushing to Hipchat if required
            if($this->hipchatEnabled && $hipchatToken !== null && $hipchatRoom !== null)
            {
                $this->setupHipChat($hipchatToken, $hipchatRoom);
            }

            if($this->debug)
            {
                $this->setupDebug($this->hipchatEnabled);
            }

        }
        catch (Exception $e)
        {
            $this->fallback = true;
        }


        //if the $streamHandler failed due to permission error, switch to syslog
        if($this->fallback)
        {
            $syslogHandler = new SyslogHandler('topLog app', 'topLog app');
            $this->handlers = [$syslogHandler];
        }

        parent::__construct($name, $this->handlers, [new TopLogProcessor]);
    }

    private function setupDebug($hipchatEnabled)
    {
        $debugStreamHandler = new StreamHandler(getenv('TOPLOG_LOGDIR') . $this->logFile, Logger::DEBUG, true);
        $debugStreamHandler->setFormatter($this->formatter());
        $debugLogger = new Logger('DEBUG');

        if($debugStreamHandler !== null) {
            $debugLogger->pushHandler($debugStreamHandler);
        }

        if ($hipchatEnabled && $this->hipchat !== null)
        {
            $debugLogger->pushHandler($this->hipchat);
        }

        $debugLogger->pushProcessor(new TopLogProcessor);
        ErrorHandler::register($debugLogger);
    }

    private function setupHipChat($token, $room)
    {
        $this->hipchat = new HipChatHandler($token, $room, $this->name, false, 100);
        $this->hipchat->setFormatter($this->formatter());

        array_push($this->handlers, $this->hipchat);
    }

    private function formatter()
    {
        if ($this->debug)
        {
            $formatter = new LineFormatter("%datetime% %channel% %level_name% %message% %context%\n", "[d/M/Y:h:i:s O]");
        }
        else
        {
            $formatter = new LineFormatter("%datetime% %channel% %level_name% %message%\n", "[d/M/Y:h:i:s O]");
        }

        return $formatter;
    }
}

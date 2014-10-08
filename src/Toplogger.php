<?php namespace TopLog\Toplogger;

use Monolog\ErrorHandler;
use Monolog\Logger;
use Monolog\Handler\HipChatHandler;
use Monolog\Handler\SyslogHandler;
use TopLog\Toplogger\Handlers\topLogStreamHandler;
use Monolog\Formatter\LineFormatter;
use TopLog\Toplogger\Processors\TopLogProcessor;

class Toplogger extends Logger
{
    private $hipchat;
    private $hipchatEnabled;
    protected $handlers;
    private $debug;
    protected $name;

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

        $streamHandler = new topLogStreamHandler(getenv('TOPLOG_LOGDIR') . $logFile, Logger::INFO;
        $streamHandler->setFormatter($this->formatter());

        try //Here it checks whether streamHandler can write/create the log file using a mock message
        {
            $mockMessage = array("foo");
            $streamHandler->write($mockMessage);
            $this->handlers = [$streamHandler];
        }        
        catch (\Exception $e) //if the $streamHandler fails due to permission error, switch to syslog
        {
            $syslogHandler = new SyslogHandler('topLog');
            $syslogHandler->setFormatter($this->formatter());
            $this->handlers = [$syslogHandler];
        }

        // Setup pushing to Hipchat if required
        if($this->hipchatEnabled && $hipchatToken !== null && $hipchatRoom !== null)
        {
            $this->setupHipChat($hipchatToken, $hipchatRoom);
        }

        if($this->debug)
        {
            $this->setupDebug($this->hipchatEnabled);
        }

        parent::__construct($name, $this->handlers, [new TopLogProcessor]);
    }

    private function setupDebug($hipchatEnabled)
    {
        $debugStreamHandler = new topLogStreamHandler(getenv('TOPLOG_LOGDIR') . $this->logFile, Logger::DEBUG);
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

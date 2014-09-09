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

    public function __construct($name = 'TOPLOG', $logFile = 'toplog_app.log', $hipchatEnabled = false, $hipchatToken = null, $hipchatRoom = null)
    {
        // Check if the env is production, if not, turn debug mode on
        $this->debug = getenv("ENV") !== "production";

        $this->logFile = $logFile;
        $this->name = $name;

        $streamHandler = new StreamHandler(getenv('TOPLOG_LOGDIR') . $logFile, Logger::INFO);
        $streamHandler->setFormatter($this->formatter());
        $this->handlers = [$streamHandler];

        // Setup pushing to Hipchat if required
        if($hipchatEnabled && $hipchatToken !== null && $hipchatRoom !== null)
        {
            $this->setupHipChat($hipchatToken, $hipchatRoom);
        }

        if($this->debug)
        {
            $this->setupDebug();
        }

        parent::__construct($name, $this->handlers, [new TopLogProcessor]);
    }

    private function setupDebug()
    {
        $debugStreamHandler = new StreamHandler(getenv('TOPLOG_LOGDIR') . $this->logFile, Logger::DEBUG);
        $debugStreamHandler->setFormatter($this->formatter());
        $debugLogger = new Logger('DEBUG');
        $debugLogger->pushHandler($debugStreamHandler);

        if ($hipchatEnabled)
        {
            $debugLogger->pushHandler($this->hipchat);
        }

        $debugLogger->pushProcessor(new TopLogProcessor);
        ErrorHandler::register($debugLogger);
    }

    private function setupHipChat($token, $room, $productionRoom = null)
    {
        if (getenv("ENV") === 'production')
        {
            $room = $productionRoom;
        }

        $this->hipchat = new HipChatHandler($token, $room, $this->name, false, 100);
        $this->hipchat->setFormatter($this->formatter());

        array_push($this->handlers, $this->hipchat);
    }

    private function formatter()
    {
        if ($this->debug)
        {
            $formatter = new LineFormatter("%datetime% %channel% %level_name% %message% %context%\n", "[d/M/Y:h:m:s O]");
        }
        else
        {
            $formatter = new LineFormatter("%datetime% %channel% %level_name% %message%\n", "[d/M/Y:h:m:s O]");
        }

        return $formatter;
    }
}

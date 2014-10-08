<?php namespace TopLog\Toplogger\Handlers;

use Monolog\Handler\StreamHandler;

class topLogStreamHandler extends StreamHandler
{
	public function __construct($stream, $level = Logger::DEBUG, $bubble = true, $filePermission = null, $useLocking = false)
	{
		parent::__construct($stream, $level, $bubble, $filePermission, $useLocking);
	}

	public function write(array $record)
	{
		try //Here it checks whether streamHandler can write/create the log file using a mock message
	    {
			parent::write($record);
		}
		catch (\UnexpectedValueException $e)
		{
			throw new Exception("StreamHandler couldn't write into log file. Permission denied.");
		}
	}
}
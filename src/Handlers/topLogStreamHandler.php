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
		parent::write($record);
	}
}
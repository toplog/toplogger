<?php namespace TopLog\Toplogger\Processors;

class TopLogProcessor
{
    public function __invoke(array $record)
    {
        $channel = $record['channel'];

        $channel = isset($record['context']['run_id']) ? "{$record['context']['run_id']} {$channel}" : "NA {$channel}";
        $channel = gethostname()." {$channel}";

        $record['channel'] = $channel;

        return $record;
    }
}

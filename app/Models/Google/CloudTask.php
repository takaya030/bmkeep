<?php
declare(strict_types=1);

namespace App\Models\Google;

use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\Task;
use Google\Protobuf\Timestamp;

class CloudTask
{
    protected $client = null;
    protected $queueName = null;

    public function __construct( CloudTasksClient $ctc, $projectId, $locationId, $queueId )
    {
        $this->client = $ctc;
        $this->queueName = $this->client->queueName($projectId, $locationId, $queueId);
    }

    public function createHttpRequestGet( string $url, array $params = [] ): HttpRequest
    {
        if( !empty($params) )
        {
            $url .= '?' . http_build_query($params);
        }

        $httpRequest = new HttpRequest();
        $httpRequest->setUrl($url);
        $httpRequest->setHttpMethod(HttpMethod::GET);

        return $httpRequest;
    }

    public function createTask( string $url, array $params = [], int $wait_time = 5 ): Task
    {
        $task = new Task();

        $future_time_seconds = time() + $wait_time;
        $future_timestamp = new Timestamp();
        $future_timestamp->setSeconds($future_time_seconds);
        $future_timestamp->setNanos(0);
        $task->setScheduleTime($future_timestamp);

        $httpRequest = $this->createHttpRequestGet( $url, $params );
        $task->setHttpRequest($httpRequest);

        return $task;
    }
}

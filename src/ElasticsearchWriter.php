<?php namespace Zipzoft\HttpLogger;

use Elasticsearch\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchWriter implements Writer
{
    /**
     * @var Client
     */
    private Client $client;

    /**
     * ElasticsearchLoggerDriver constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param Request $request
     * @param \Illuminate\Http\Response $response
     */
    public function handle(Request $request, $response)
    {
        $body = [
            'path' => $request->path(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'request' => [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
            ],
            'user_agent' => $request->userAgent(),
            'created_at' => now()->toIso8601String(),
        ];

        if ($request->user()) {
            $body = array_merge($body, [
                'user' => $this->mapUser($request, $body),
            ]);
        }

        if ($response instanceof Response) {
            $body = array_merge($body, [
                'response' => $this->mapResponse($response, $body),
            ]);
        }

        $this->client->bulk(
            $params = $this->createBulkParameterFromBody($body)
        );
    }

    /**
     * @param $request
     * @param array $body
     * @return array
     */
    private function mapUser($request, array $body)
    {
        return [
            'type' => get_class($request->user()),
            'id' => $request->user()->id,
            'name' => $request->user()->name,
        ];
    }

    /**
     * @param Response $response
     * @param array $body
     * @return array
     */
    private function mapResponse(Response $response, array $body)
    {
        return [
            'status' => $response->getStatusCode(),
        ];
    }

    /**
     * @return string
     */
    private function getIndexName()
    {
        return Str::snake(config('app.name') . '_http_logs');
    }

    /**
     * @param array $body
     * @return array[]
     */
    private function createBulkParameterFromBody(array $body)
    {
        return [
            'body' => [
                ['index' => [
                    '_index' => $this->getIndexName(),
                    'pipeline' => 'geoip',
                ]],
                $body,
            ],
        ];
    }
}

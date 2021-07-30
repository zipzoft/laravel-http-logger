<?php namespace Zipzoft\HttpLogger;

use Elasticsearch\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Jenssegers\Agent\Agent;

class ElasticsearchWriter implements Writer
{
    /**
     * @var Client
     */
    protected Client $engine;

    /**
     * @var array
     */
    private array $config;

    /**
     * @var Agent
     */
    private Agent $agent;

    /**
     * ElasticsearchLoggerDriver constructor.
     * @param Client $client
     */
    public function __construct(Client $client, array $config = [])
    {
        $this->engine = $client;
        $this->config = $config;

        $this->agent = new Agent();
    }

    /**
     * @param Request $request
     * @param \Illuminate\Http\Response $response
     */
    public function handle(Request $request, $response)
    {
        $this->agent->setUserAgent($request->userAgent());
        $this->agent->setHttpHeaders($request->headers->all());

        $body = [
            'path' => $request->path(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'request' => [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
            ],
            'created_at' => now()->toIso8601String(),
            'agent' => [
                'user_agent' => $request->userAgent(),
                'languages' => $this->agent->languages(),
                'device' => $this->agent->device(),
                'platform' => [
                    'name' => $this->agent->platform(),
                    'version' => $this->agent->version($this->agent->platform()),
                ],
                'browser' => [
                    'name' => $this->agent->browser(),
                    'version' => $this->agent->version($this->agent->browser()),
                ],
                'robot' => $this->agent->robot(),
            ],
        ];

        if ($request->user()) {
            $body = array_merge($body, [
                'user' => $this->mapUser($request),
            ]);
        }

        if ($response instanceof Response) {
            $body = array_merge($body, [
                'response' => $this->mapResponse($response, $body),
            ]);
        }

        $param = array_merge([
            'index' => $this->getIndexName(),
            'body' => $body,
        ]);

        return $this->engine->index(array_merge(
            $param, $this->withOptions($param)
        ));
    }

    /**
     * @param $request
     * @return array
     */
    protected function mapUser($request)
    {
        return [
            'type' => $request->user()->getMorphClass(),
            'id' => $request->user()->id,
            'name' => $request->user()->name,
        ];
    }

    /**
     * @param Response $response
     * @param array $body
     * @return array
     */
    protected function mapResponse(Response $response, array $body)
    {
        return [
            'status' => $response->getStatusCode(),
        ];
    }

    /**
     * @return string
     */
    protected function getIndexName()
    {
        return $this->config['index'] ?? Str::snake(config('app.name') . '_http_logs');
    }

    /**
     * @param array $body
     * @return array
     */
    protected function withOptions(array $body)
    {
        return [];
    }
}

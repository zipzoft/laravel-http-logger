<?php namespace Zipzoft\HttpLogger;

use Closure;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

class LogRequestMiddleware
{

    /**
     * @var Writer
     */
    private Writer $writer;

    /**
     * @var Application
     */
    private Application $app;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * LogAnyRequestMiddleware constructor.
     * @param Application $app
     * @param Writer $writer
     */
    public function __construct(Application $app, Writer $writer)
    {
        $this->app = $app;
        $this->writer = $writer;
        $this->config = $app['config'];
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return tap($next($request), function($response) use ($request) {
            if ($this->shouldLogRequest($request)) {
                $this->log($request, $response);
            }
        });
    }

    /**
     * @param Exception $exception
     * @param \Illuminate\Http\Request  $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    protected function handleExceptions(Exception $exception, $request, $response)
    {
        //
    }

    /**
     * @param \Illuminate\Http\Request  $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    protected function log($request, $response)
    {
        try {
            $this->writer->handle($request, $response);
        } catch (\Exception $exception) {
            $this->handleExceptions($exception, $request, $response);
        }
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function shouldLogRequest(Request $request)
    {
        foreach ($this->config->get('http-logger.ignore.methods') ?: [] as $method) {
            if ($request->isMethod($method)) {
                return false;
            }
        }

        foreach ($this->config->get('http-logger.ignore.paths') ?: [] as $path) {
            if ($path !== '/') {
                $path = trim($path, '/');
            }

            if ($request->fullUrlIs($path) || $request->is($path)) {
                return false;
            }
        }

        return true;
    }
}

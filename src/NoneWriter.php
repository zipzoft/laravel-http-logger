<?php namespace Zipzoft\HttpLogger;

use Illuminate\Http\Request;

class NoneWriter implements Writer
{
    /**
     * @param Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function handle(Request $request, $response)
    {
        //
    }
}

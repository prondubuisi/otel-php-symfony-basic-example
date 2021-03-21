<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{
    /**
     * @Route("/hello", name="hello")
     */
    public function index(): Response
    {   
        global $zipkinTracer;
        if ($zipkinTracer) {
            /** @var Span $span */
            $span = $zipkinTracer->getActiveSpan();
            
            $span->setAttribute('foo', 'bar');
            $span->updateName('New name');

            $zipkinTracer->startAndActivateSpan('Child span');
            try {
                throw new \Exception('Exception Example');
            } catch (\Exception $exception) {
                $span->setSpanStatus($exception->getCode(), $exception->getMessage());
            }
            $zipkinTracer->endActiveSpan();
        }
        return new Response('Hello World');
    }
}
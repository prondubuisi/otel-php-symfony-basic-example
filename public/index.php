<?php

use App\Kernel;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Sdk\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Trace as API;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

$sampler = new AlwaysOnSampler();
$samplingResult = $sampler->shouldSample(
    null,
    md5((string) microtime(true)),
    substr(md5((string) microtime(true)), 16),
    'io.opentelemetry.example',
    API\SpanKind::KIND_INTERNAL
);

$jaegarExporter = new JaegerExporter(
    'Hello World Web Server Jaegar',
    'http://localhost:9412/api/v2/spans'
);

$zipkinExporter = new ZipkinExporter(
    'Hello World Web Server Zipkin',
    'http://localhost:9411/api/v2/spans'
);

if (SamplingResult::RECORD_AND_SAMPLED === $samplingResult->getDecision()) {

    $jaegarTracer = (new TracerProvider())
        ->addSpanProcessor(new BatchSpanProcessor($jaegarExporter, Clock::get()))
        ->getTracer('io.opentelemetry.contrib.php');

    $zipkinTracer = (new TracerProvider())
    ->addSpanProcessor(new BatchSpanProcessor($zipkinExporter, Clock::get()))
    ->getTracer('io.opentelemetry.contrib.php');

    $request = Request::createFromGlobals();
    $jaegarSpan = $jaegarTracer->startAndActivateSpan($request->getUri());
    $zipkinSpan = $zipkinTracer->startAndActivateSpan($request->getUri());

}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

if (SamplingResult::RECORD_AND_SAMPLED === $samplingResult->getDecision()) {
    $zipkinTracer->endActiveSpan();
    $jaegarTracer->endActiveSpan();
}

# otel-php-symfony-basic-example
A basic example on using the OpenTelemetry-PHP Library within your Symfony Application

# Installation

1. Download [Symfony CLI](https://symfony.com/download)

2. Run
```
composer install
```

3. Run
```
docker-compose up -d
```

4. Run
```
symfony server:start
```
5. App available at [http://localhost:8000/hello](http://localhost:8000/hello)  
    Jaeger available at [http://localhost:16686/search](http://localhost:16686/search)   
    Zipkin available at [http://localhost:9411/](http://localhost:9411/)


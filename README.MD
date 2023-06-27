## Библиотека Laravel для взаимодействия с RabbitMQ














Установка

    sail composer require ex3mm/laravel-rabbitmq

Добавление сервис провайдера

    Ex3mm\LaravelRabbitmq\LaravelRabbitmqServiceProvider::class,

Выбрать публикуемый файл

    sail artisan vendor:publish

Опубликовать только конфиг

    sail artisan vendor:publish --provider="Ex3mm\LaravelRabbitmq\LaravelRabbitmqServiceProvider" --tag=config

Добавить в композер для локальной разработке

    "Ex3mm\\LaravelRabbitmq\\": "packages/ex3mm/laravel-rabbitmq/src"

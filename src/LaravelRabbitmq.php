<?php

namespace Ex3mm\LaravelRabbitmq;

use Exception;
use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class LaravelRabbitmq
{
    private AMQPStreamConnection $connection;
    private AbstractChannel|AMQPChannel $channel;

    private string $exchange;
    private string $queue;           // set
    private string $routing_key = '';           // set
    private string $consumer_tag = '';           // set

    private string $type = AMQPExchangeType::DIRECT;
    private bool $auto_delete = false;
    private bool $durable = true;
    private bool $passive = false;
    private bool $exclusive = false;


    /**
     * @throws Exception
     */
    // TODO добавить конфиг данные
    public function __construct()
    {
        // Создаем соединение
        $this->connection = new AMQPStreamConnection(
            config('rabbitmq.host', 'localhost'),
            config('rabbitmq.port', 5672),
            config('rabbitmq.username'),
            config('rabbitmq.password'));

        // Создание канала
        $this->channel = $this->connection->channel();
    }

    /**
     * @return AbstractChannel|AMQPChannel
     */
    public function getChannel(): AMQPChannel|AbstractChannel
    {
        return $this->channel;
    }

    /**
     * @return AMQPStreamConnection
     */
    public function getConnection(): AMQPStreamConnection
    {
        return $this->connection;
    }

    /**
     * @throws Exception
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param string $exchange
     * @return $this
     */
    public function setExchange(string $exchange): static
    {
        $this->exchange = $exchange;
        return $this;
    }

    /**
     * @param string $queue
     * @return $this
     */
    public function setQueue(string $queue): static
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * @param $routing_key
     * @return $this
     */
    public function setRoutingKey($routing_key): static
    {
        $this->routing_key = $routing_key;
        return $this;
    }

    /**
     * @param string $consumer_tag
     * @return LaravelRabbitmq
     */
    public function setConsumerTag(string $consumer_tag): static
    {
        $this->consumer_tag = $consumer_tag;
        return $this;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @param bool $auto_delete
     */
    public function setAutoDelete(bool $auto_delete): void
    {
        $this->auto_delete = $auto_delete;
    }

    /**
     * @param bool $durable
     */
    public function setDurable(bool $durable): void
    {
        $this->durable = $durable;
    }

    /**
     * @param bool $passive
     */
    public function setPassive(bool $passive): void
    {
        $this->passive = $passive;
    }

    /**
     * @param bool $exclusive
     */
    public function setExclusive(bool $exclusive): void
    {
        $this->exclusive = $exclusive;
    }

    /**
     * Закрытие канала и соединения
     *
     * @return void
     * @throws Exception
     */
    public function close(): void
    {
        // Закрытие канала
        $this->channel->close();

        // Закрытие соединения
        $this->connection->close();
    }

    /**
     * Обработка сообщения
     *
     * @param $message
     * @return false|string
     */
    public function convertorMessage($message): bool|string
    {
        return json_encode($message, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Декларирование обменника с настройками
     *
     * @return void
     */
    public function declareExchange(): void
    {
        $this->channel->exchange_declare(
            $this->exchange,
            $this->type,
            $this->passive,
            $this->durable,
            $this->auto_delete);
    }

    /**
     * Декларирование очереди
     *
     * @return void
     */
    public function declareQueue(): void
    {
        $this->channel->queue_declare(
            $this->queue,
            $this->passive,
            $this->durable,
            $this->exclusive,
            $this->auto_delete);
    }

    /**
     * Декларирование и биндинг обменников и очередей
     *
     * @return void
     */
    public function declareBinding(): void
    {
        // Декларирование обменника с настройками
        $this->declareExchange();

        // Декларирование очереди
        $this->declareQueue();

        // Биндинг очереди к обменнику с указанием имени очереди и имени обменника
        $this->channel->queue_bind($this->queue, $this->exchange, $this->routing_key);

        // Обработка по 1 сообщению
        $this->channel->basic_qos(null, 1, null);

    }

    /**
     * Отправка сообщения
     *
     * @param $message
     * @return void
     * @throws Exception
     */
    public function send($message): void
    {
        // Декларирование и биндинг
        $this->declareBinding();

        // Создание сообщения для отправки
        $msg = new AMQPMessage(
            $this->convertorMessage($message), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT // Установка режима доставки в сохранное сообщение
        ]);

        // Отправка сообщения на обменник с указанием сообщения и имени обменника
        $this->channel->basic_publish($msg, $this->exchange, $this->routing_key);

        // Закрываем соединение
        $this->close();
    }

    /**
     * Прослушивание очередей
     *
     * @throws Exception
     */
    public function listen($callback): void
    {
        // Декларирование и биндинг
        $this->declareBinding();

        // Прослушивание очереди
        $this->channel->basic_consume(
            $this->queue,
            $this->consumer_tag,
            false,
            true,
            false,
            false, $callback);

        while ($this->channel->is_open()) {
            $this->channel->wait();
        }

        $this->close();
    }
    
    /**
     * Узнать количество сообщений в очереди
     *
     * @return int
     */
    public function getCountMessages(): int
    {
        try {
            return ($this->channel->queue_declare($this->queue, true))[1] ?? 0;

        } catch (AMQPProtocolChannelException $e) {
            return 0;
        }
    }
}
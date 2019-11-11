<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpKernel\KernelInterface;

class ReplyController extends AbstractController
{
    private $rootPath;

    public function __construct(KernelInterface $kernel){
        $this->rootPath = $kernel->getProjectDir();
    }

    public function send_message($msg,$exchange,$queue) {
        $dotenv = new Dotenv();
        $dotenv->load($this->rootPath.'/.env');
        $connection = new AMQPStreamConnection($_ENV['RABBITMQ_HOST'], $_ENV['RABBITMQ_PORT'], $_ENV['RABBITMQ_DEFAULT_USER'], $_ENV['RABBITMQ_DEFAULT_PASS'], $_ENV['RABBITMQ_DEFAULT_VHOST']);
        $channel = $connection->channel();
        $channel->queue_declare($queue, false, true, false, false);
        $channel->exchange_declare($exchange, 'direct', false, true, false);
        $channel->queue_bind($queue, $exchange);
        $message = new AMQPMessage(json_encode($msg), array('content_type' => 'application/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        $channel->basic_publish($message, $exchange);
        $channel->close();
        $connection->close();
    }
}
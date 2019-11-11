<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Symfony\Component\Validator\Validation;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpKernel\KernelInterface;

class AddUserController extends AbstractController implements ConsumerInterface
{
    private $rootPath;

    public function __construct(KernelInterface $kernel){
        $this->rootPath = $kernel->getProjectDir();
    }

    public function validate_user($user) {
        $validator = Validation::createValidator();
        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            $formattedErrors = [];
            for ($i = 0; $i < $errors->count(); $i++) {
                $violation = $errors->get($i);
                $formattedErrors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return $formattedErrors;
        }

        return [];
    }

    public function execute(AMQPMessage $msg){
        $response = json_decode($msg->body, true);
        $action = $response['action'] ?? "";
        $name = $response['name'] ?? "";
        $email = $response['email'] ?? "";
        $location = $response['location'] ?? "";
        $reply_to = $response['reply_to'] ?? ["queue"=>'undefined_queue','exchange'=>'undefined_exchange'];

        $errors=[];
        $user=new User();

        if ($action!="add_user") $errors['action']="wrong action";
        $user->setName($name);
        $user->setEmail($email);
        $user->setLocation($location);

        $errors+= $this->validate_user($user);

        $response=[
            "id"=> null,
            "error_code"=>1,
            "error_msg"=>''
        ];

        if (!empty($errors)) {
            $response["error_msg"]=implode(";",$errors);
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $response["id"]=$user->getId();
            $response["error_code"]=0;
        }

        $this->reply_manual($response,$reply_to['exchange'],$reply_to['queue']);
    }

    public function reply_manual($msg,$exchange,$queue) {
        $dotenv = new Dotenv();
        $dotenv->load($this->rootPath.'/.env');
        $connection = new AMQPStreamConnection($_ENV['RABBITMQ_HOST'], $_ENV['RABBITMQ_PORT'], $_ENV['RABBITMQ_DEFAULT_USER'], $_ENV['RABBITMQ_DEFAULT_PASS'], $_ENV['RABBITMQ_DEFAULT_VHOST']);
        $channel = $connection->channel();
        $channel->queue_declare($queue, false, true, false, false);
        $channel->exchange_declare($exchange, 'direct', false, true, false);
        $channel->queue_bind($queue, $exchange);
        $message = new AMQPMessage(json_encode($msg), array('content_type' => 'application/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        $channel->basic_publish($message, $exchange);
    }
}

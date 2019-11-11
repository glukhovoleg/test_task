<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use PhpAmqpLib\Message\AMQPMessage;

class AddUserController extends AbstractController implements ConsumerInterface
{

    private $reply;
    private $validator;

    public function __construct(ReplyController $reply,ValidatorInterface $validator){
        $this->reply=$reply;
        $this->validator=$validator;
    }

    public function validate_user($user) {
        $errors = $this->validator->validate($user);
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

        $this->reply->send_message($response,$reply_to['exchange'],$reply_to['queue']);

    }

}

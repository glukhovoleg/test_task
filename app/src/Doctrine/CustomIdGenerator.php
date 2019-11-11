<?php


namespace App\Doctrine;

use Doctrine\ORM\Id\AbstractIdGenerator;
class CustomIdGenerator extends AbstractIdGenerator{
    public function randString($length) {
        $char = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $char = str_shuffle($char);
        for($i = 0, $rand = '', $l = strlen($char) - 1; $i < $length; $i ++) {
            $rand .= $char{mt_rand(0, $l)};
        }
        return $rand;
    }

    public function generate(\Doctrine\ORM\EntityManager $em, $entity)
    {
        $entity_name = $em->getClassMetadata(get_class($entity))->getName();

        while (true)
        {
            $id = $this->randString(10);
            $item = $em->find($entity_name, $id);
            if (!$item)
            {
                return $id;
            }
        }

        throw new \Exception('RandomIdGenerator worked hard, but could not generate unique ID :(');
    }

}
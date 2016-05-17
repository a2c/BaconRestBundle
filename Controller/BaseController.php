<?php

namespace Bacon\Bundle\RestBundle\Controller;

use JMS\Serializer\SerializationContext;
use FOS\RestBundle\Controller\FOSRestController;
use Datetime;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Language controller.
 *
 * @version 0.1
 *
 */
class BaseController extends FOSRestController
{
    protected function getContext($groups)
    {
        $groups[] = 'base';
        $context = SerializationContext::create();
        return $context->setGroups($groups);
    }

    protected function takeOutAccents($string)
    {
        $patterns[0] = '/[Ã¡|Ã¢|Ã |Ã¥|Ã¤]/';
        $patterns[1] = '/[Ã°|Ã©|Ãª|Ã¨|Ã«]/';
        $patterns[2] = '/[Ã­|Ã®|Ã¬|Ã¯]/';
        $patterns[3] = '/[Ã³|Ã´|Ã²|Ã¸|Ãµ|Ã¶]/';
        $patterns[4] = '/[Ãº|Ã»|Ã¹|Ã¼]/';
        $patterns[5] = '/Ã¦/';
        $patterns[6] = '/Ã§/';
        $patterns[7] = '/ÃŸ/';
        $replacements[0] = 'a';
        $replacements[1] = 'e';
        $replacements[2] = 'i';
        $replacements[3] = 'o';
        $replacements[4] = 'u';
        $replacements[5] = 'ae';
        $replacements[6] = 'c';
        $replacements[7] = 'ss';
        
        return preg_replace($patterns, $replacements,$string);
    }
    
    protected function saveEntity($entityName, $request, $id = null)
    {
        if ($id) {
           $request->request->add(array('id' => $id));
        }

        $data = json_encode($request->request->all());
        $serializer = $this->container->get('jms_serializer');
        $validator = $this->container->get('validator');

        try {
            $entity = $serializer->deserialize($data, $entityName, 'json');
        } catch (RuntimeException $e) {
            throw new HttpException($e->getMessage(), 400);
        }

        if(count($errors = $validator->validate($entity))){
            return $errors;
        }
        
        if(!$entity->getCreatedAt())
        {
            $entity->setCreatedAt(new Datetime());
        }
        
        $entity->setUpdatedAt(new Datetime());

        return $entity;
    }
}

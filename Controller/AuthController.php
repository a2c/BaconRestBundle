<?php

namespace Bacon\Bundle\RestBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Post;
use Uecode\Bundle\ApiKeyBundle\Util\ApiKeyGenerator;

class AuthController extends BaseController
{
    /**
     * Rest Login
     *
     * @Post("/login", name="test_update")
     */
    public function loginAction(Request $request)
    {
        $username = $request->get('username',NULL);
        $password = $request->get('password',NULL);

        if (!isset($username) || !isset($password)){
            return $this->errorBadParameters();
        }

        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByUsernameOrEmail($username);

        if (!$user instanceof \Bacon\Custom\UserBundle\Entity\User) {
            return $this->errorUserNotFound();
        }

        return $this->registerUser($user, $password);
    }

    private function errorBadParameters()
    {
        $return = array(
            'type' => 'error',
            'message' => "You must pass username and password fields"
        );

        return $this->view($return, 400);
    }

    private function errorUserNotFound()
    {
        $return = array(
            'type' => 'error',
            'message' => "No matching user account found"
        );

        return $this->view($return, 404);
    }

    private function errorPasswordMatch()
    {
        $return = array(
            'type' => 'error',
            'message' => "Password does not match password on record"
        );

        return $this->view($return, 400);
    }

    private function registerUser($user, $password)
    {
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);

        $bool = ($encoder->isPasswordValid($user->getPassword(),$password,$user->getSalt())) ? true : false;
        if (!$bool) {

            return $this->errorPasswordMatch();
        }

        $generator = new ApiKeyGenerator();
        $user->setApiKey($generator->generateApiKey());
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $return = array(
            "type" => "success",
            "apiKey" => $user->getApiKey()
        );

        return $this->view($return, 200);
    }

}

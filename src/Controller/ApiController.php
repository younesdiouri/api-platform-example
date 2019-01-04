<?php
/**
 * Created by PhpStorm.
 * User: younesdiouri
 * Date: 22/10/2018
 * Time: 18:14
 */
namespace App\Controller;
use App\Entity\Book;
use App\Entity\User;
use App\Repository\UserRepository;
use function GuzzleHttp\Psr7\str;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ApiController extends AbstractController
{
    /**
     * @Route("/login/register", name="api_login_register", methods="POST")
     */
    public function login(Request $request,  UserPasswordEncoderInterface $passwordEncoder)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $data = json_decode($request->getContent());
        $em = $this->getDoctrine()->getManager();
        if( $user = $em->getRepository(User::class)->findByFacebookId((int) $data->provider_id) ){
            return new JsonResponse([
                "userData" => [
                    "id" => $user[0]['id'],
                    "email" => $user[0]['email'],
                    "firstname" => $user[0]['firstname'],
                    "lastname" => $user[0]['lastname'],
                    "picture_url" => $user[0]['picture_url'],
                    "facebook_id" => $user[0]['facebook_id']
                ]
            ]);
        }
        $user = new User();
        $password = $passwordEncoder->encodePassword($user,$data->provider_id);
        $user->setPassword($password);
        $user->setFirstname(explode(' ', $data->name)[0]);
        $user->setLastname(explode(' ', $data->name)[1]);
        $user->setEmail($data->email);
        $user->setFacebookId($data->provider_id);
        $user->setPictureUrl($data->provider_pic);
        $user->setAccessToken($data->token);
        $user->setExpiresIn($data->expiresIn);
        $user->setReauthorizeRequiredIn($data->reauthorize_required_in);
        $em->persist($user);
        $em->flush();
        return new JsonResponse([
            "userData" => [
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "firstname" => $user->getFirstname(),
                "lastname" => $user->getLastname(),
                "picture_url" => $user->getPictureUrl(),
                "facebook_id" => $user->getFacebookId()
            ]
        ]);
    }
    /**
     * @Route("/api/private", name="", methods={"GET","POST"})
     */
    public function api(Request $request): JsonResponse
    {
        return new JsonResponse([
            "success" => "Logged in as " . $this->getUser()->getUsername()
        ]);
    }
}
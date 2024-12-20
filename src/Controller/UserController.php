<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/user', name: 'app_api_user_')]
class UserController extends AbstractController
{
    public $userNotFound = [
        'status' => 'User not found!',
        Response::HTTP_NOT_FOUND,
    ];

    public function __construct(
        private EntityManagerInterface $manager,
        private UserRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/', methods: 'POST', name: 'new')]
    public function new(Request $request): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($user);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($user, 'json');
        $location = $this->urlGenerator->generate('app_api_user_show', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($responseData, Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: 'GET', name: 'show')]
    public function show(int $id): JsonResponse
    {
        $user = $this->repository->findOneBy(['id' => $id]);

        if ($user) {
            $responseData = $this->serializer->serialize($user, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK);
        }

        return new JsonResponse(
            $this->userNotFound
        );

    }

    #[Route('/{id}', methods: 'PUT', name: 'update')]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->repository->findOneBy(['id' => $id]);

        if ($user) {
            $user = $this->serializer->deserialize($request->getContent(), User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);
            $user->setUpdatedAt(new \DateTimeImmutable());

            $this->manager->flush();

            $responseData = $this->serializer->serialize($user, 'json');
            $location = $this->urlGenerator->generate('app_api_user_show', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            return new JsonResponse($responseData, Response::HTTP_OK, ['Location' => $location], true);
        }

        return new JsonResponse(
            $this->userNotFound
        );

    }

    #[Route('/{id}', methods: 'DELETE', name: 'delete')]
    public function delete(int $id): JsonResponse
    {
        $user = $this->repository->findOneBy(['id' => $id]);

        if ($user) {
            $this->manager->remove($user);
            $this->manager->flush();

            return new JsonResponse(
                [
                    'status' => 'User deleted!',
                    Response::HTTP_OK,
                ]
            );
        }

        return new JsonResponse(
            $this->userNotFound
        );
    }
}

<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Chat;
use App\Repository\ChatRepository;


#[Route('api/chat', name: 'app_api_chat_')]
class ChatController extends AbstractController
{
    public $chatNotFound = [
        'status' => 'Chat not found!',
        Response::HTTP_NOT_FOUND,
    ];

    public function __construct(
        private EntityManagerInterface $manager,
        private ChatRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/', methods: 'POST', name: 'new')]
    public function new(Request $request): JsonResponse
    {
        $chat = $this->serializer->deserialize($request->getContent(), Chat::class, 'json');
        $chat->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($chat);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($chat, 'json');
        $location = $this->urlGenerator->generate('app_api_chat_show', ['id' => $chat->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($responseData, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('/{id}', methods: 'GET', name: 'show')]
    public function show(int $id): JsonResponse
    {
        $chat = $this->repository->findOneBy(['id' => $id]);

        if ($chat) {
            $responseData = $this->serializer->serialize($chat, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(
            $this->chatNotFound
        );
    }

    #[Route('/{id}', methods: 'PUT', name: 'update')]
    public function update(int $id, Request $request): JsonResponse
    {
        $chat = $this->repository->findOneBy(['id' => $id]);

        if ($chat) {
            $chat = $this->serializer->deserialize($request->getContent(), Chat::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $chat]);
            $chat->setUpdatedAt(new \DateTimeImmutable());

            $this->manager->flush();

            $responseData = $this->serializer->serialize($chat, 'json');
            $location = $this->urlGenerator->generate('app_api_chat_show', ['id' => $chat->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            return new JsonResponse($responseData, Response::HTTP_OK, ['Location' => $location], true);
        }

        return new JsonResponse(
            $this->chatNotFound
        );

    }

    #[Route('/{id}', methods: 'DELETE', name: 'delete')]
    public function delete(int $id): JsonResponse
    {
        $chat = $this->repository->findOneBy(['id' => $id]);

        if ($chat) {
            $this->manager->remove($chat);
            $this->manager->flush();

            return new JsonResponse(
                [
                    'status' => 'Chat deleted!',
                    Response::HTTP_OK,
                ],
            );
        }

        return new JsonResponse(
            $this->chatNotFound
        );
    }
}
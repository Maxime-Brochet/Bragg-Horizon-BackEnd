<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Chat;
use App\Repository\ChatRepository;


#[Route('api/chat', name: 'app_api_chat_')]
class ChatController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private ChatRepository $repository)
    {
    }

    #[Route('/', methods: 'POST', name: 'new')]
    public function new(): Response
    {
        $chat = new Chat();
        $chat->setDiscussion([]);
        $chat->setCreatedAt(new \DateTimeImmutable());
        $chat->setOwner($this->getUser());

        $this->manager->persist($chat);
        $this->manager->flush();

        return $this->json(
            [
                'status' => 'Chat created!',
                'id' => $chat->getId(),
                'createdAt' => $chat->getCreatedAt(),
                'owner' => $chat->getOwner(),
                Response::HTTP_CREATED,
            ],
        );
    }

    #[Route('/{id}', methods: 'GET', name: 'show')]
    public function show(int $id): Response
    {
        $chat = $this->repository->find($id);

        if ($chat) {
            return $this->json(
                [
                    'id' => $chat->getId(),
                    'discussion' => $chat->getDiscussion(),
                    'createdAt' => $chat->getCreatedAt(),
                    'updatedAt' => $chat->getUpdatedAt(),
                    'owner' => $chat->getOwner(),
                    Response::HTTP_OK,
                ],
            );
        }

        return $this->json(
            [
                'status' => 'Chat not found!',
                Response::HTTP_NOT_FOUND,
            ],
        );
    }

    #[Route('/{id}', methods: 'PUT', name: 'update')]
    public function update(int $id): Response
    {
        $chat = $this->repository->find($id);

        if ($chat) {
            $chat->setDiscussion(['discussion']);
            $chat->setUpdatedAt(new \DateTimeImmutable());

            $this->manager->flush();

            return $this->redirectToRoute('app_api_chat_show', ['id' => $chat->getId()]);
        }

        return $this->json(
            [
                'status' => 'Chat not found!',
                Response::HTTP_NOT_FOUND,
            ],
        );

    }

    #[Route('/{id}', methods: 'DELETE', name: 'delete')]
    public function delete(int $id): Response
    {
        $chat = $this->repository->find($id);

        if ($chat) {
            $this->manager->remove($chat);
            $this->manager->flush();

            return $this->json(
                [
                    'status' => 'Chat deleted!',
                    Response::HTTP_OK,
                ],
            );
        }

        return $this->json(
            [
                'status' => 'Chat not found!',
                Response::HTTP_NOT_FOUND,
            ],
        );
    }
}
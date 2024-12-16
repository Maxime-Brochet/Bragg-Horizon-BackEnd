<?php

namespace App\Controller;

use App\Entity\Tags;
use App\Repository\TagsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('api/tags', name: 'app_api_tags_')]
class TagsController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private TagsRepository $repository)
    {
    }

    #[Route('/', methods: 'POST', name: 'new')]
    public function new(): Response
    {
        $tags = new Tags();
        $tags->setName('Tags 1');
        $tags->setColor('red');
        $tags->setIcon('');

        $this->manager->persist($tags);
        $this->manager->flush();

        return $this->json(
            [
                'status' => 'Chat created!',
                'id' => $tags->getId(),
                Response::HTTP_CREATED,
            ],
        );
    }

    #[Route('/{id}', methods: 'GET', name: 'show')]
    public function edit(int $id): Response
    {
        $tags = $this->repository->find($id);

        if (!$tags) {
            throw new \Exception('no tags found for id ' . $id);
        }

        return $this->json(
            [
                'id' => $tags->getId(),
                'name' => $tags->getName(),
                'color' => $tags->getColor(),
                'icon' => $tags->getIcon(),
                Response::HTTP_OK,
            ],
        );
    }

    #[Route('/{id}', methods: 'PUT', name: 'update')]
    public function update(int $id): Response
    {
        $tags = $this->repository->find($id);

        if (!$tags) {
            throw new \Exception('no tags found for id ' . $id);
        }

        $tags->setName('Tags 1');
        $tags->setColor('red');
        $tags->setIcon('');

        $this->manager->flush();

        return $this->redirectToRoute('app_api_tags_show', ['id' => $tags->getId()]);
    }

    #[Route('/{id}', methods: 'DELETE', name: 'delete')]
    public function delete(int $id): Response
    {
        $tags = $this->repository->find($id);

        if (!$tags) {
            throw new \Exception('no tags found for id ' . $id);
        }

        $this->manager->remove($tags);
        $this->manager->flush();

        return $this->json(
            [
                'status' => 'Tags deleted!',
                Response::HTTP_OK,
            ],
        );
    }
}

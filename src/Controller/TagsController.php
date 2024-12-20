<?php

namespace App\Controller;

use App\Entity\Tags;
use App\Repository\TagsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/tags', name: 'app_api_tags_')]
class TagsController extends AbstractController
{
    public $tagsNotFound = [
        'status' => 'Tags not found!',
        Response::HTTP_NOT_FOUND,
    ];

    public function __construct(
        private EntityManagerInterface $manager,
        private TagsRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/', methods: 'POST', name: 'new')]
    public function new(Request $request): JsonResponse
    {
        $tags = $this->serializer->deserialize($request->getContent(), Tags::class, 'json');

        $this->manager->persist($tags);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($tags, 'json');
        $location = $this->urlGenerator->generate('app_api_tags_show', ['id' => $tags->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($responseData, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('/{id}', methods: 'GET', name: 'show')]
    public function edit(int $id): JsonResponse
    {
        $tags = $this->repository->findOneBy(['id' => $id]);

        if ($tags) {
            $responseData = $this->serializer->serialize($tags, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(
            $this->tagsNotFound
        );
    }

    #[Route('/{id}', methods: 'PUT', name: 'update')]
    public function update(int $id, Request $request): JsonResponse
    {
        $tags = $this->repository->findOneBy(['id' => $id]);

        if ($tags) {
            $tags = $this->serializer->deserialize($request->getContent(), Tags::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $tags]);

            $this->manager->flush();

            $responseData = $this->serializer->serialize($tags, 'json');
            $location = $this->urlGenerator->generate('app_api_tags_show', ['id' => $tags->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            return new JsonResponse($responseData, Response::HTTP_OK, ['Location' => $location], true);
        }

        return new JsonResponse(
            $this->tagsNotFound
        );
    }

    #[Route('/{id}', methods: 'DELETE', name: 'delete')]
    public function delete(int $id): JsonResponse
    {
        $tags = $this->repository->findOneBy(['id' => $id]);

        if ($tags) {
            $this->manager->remove($tags);
            $this->manager->flush();

            return new JsonResponse(
                [
                    'status' => 'Tags deleted!',
                    Response::HTTP_OK,
                ],
            );
        }

        return new JsonResponse(
            $this->tagsNotFound
        );
    }
}

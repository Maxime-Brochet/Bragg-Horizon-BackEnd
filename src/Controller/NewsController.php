<?php

namespace App\Controller;

use App\Entity\News;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/news', name: 'app_api_news_')]
class NewsController extends AbstractController
{
    public $newsNotFound = [
        'status' => 'News not found!',
        Response::HTTP_NOT_FOUND,
    ];

    public function __construct(
        private EntityManagerInterface $manager,
        private NewsRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/', methods: 'POST', name: 'new')]
    public function new(Request $request): JsonResponse
    {
        $news = $this->serializer->deserialize($request->getContent(), News::class, 'json');
        $news->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($news);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($news, 'json');
        $location = $this->urlGenerator->generate('app_api_news_show', ['id' => $news->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($responseData, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('/{id}', methods: 'GET', name: 'show')]
    public function show(int $id): JsonResponse
    {
        $news = $this->repository->findOneBy(['id' => $id]);

        if ($news) {
            $responseData = $this->serializer->serialize($news, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(
            $this->newsNotFound
        );
    }

    #[Route('/{id}', methods: 'PUT', name: 'update')]
    public function update(int $id, Request $request): JsonResponse
    {
        $news = $this->repository->findOneBy(['id' => $id]);

        if ($news) {
            $news = $this->serializer->deserialize($request->getContent(), News::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $news]);
            $news->setUpdatedAt(new \DateTimeImmutable());

            $this->manager->flush();

            $responseData = $this->serializer->serialize($news, 'json');
            $location = $this->urlGenerator->generate('app_api_news_show', ['id' => $news->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            return new JsonResponse($responseData, Response::HTTP_OK, ['Location' => $location], true);
        }

        return new JsonResponse(
            $this->newsNotFound
        );
    }

    #[Route('/{id}', methods: 'DELETE', name: 'delete')]
    public function delete(int $id): JsonResponse
    {
        $news = $this->repository->findOneBy(['id' => $id]);

        if ($news) {
            $this->manager->remove($news);
            $this->manager->flush();

            return new JsonResponse(
                [
                    'status' => 'News deleted!',
                    Response::HTTP_OK,
                ],
            );
        }

        return new JsonResponse(
            $this->newsNotFound
        );
    }
}

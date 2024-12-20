<?php

namespace App\Controller;

use App\Entity\News;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('api/news', name: 'app_api_news_')]
class NewsController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private NewsRepository $repository)
    {
    }

    #[Route('/', methods: 'POST', name: 'new')]
    public function new(): Response
    {
        $news = new News();
        $news->setName('News 1');
        $news->setSlug('news-1');
        $news->setArticle(['content' => 'Article 1']);
        $news->setCreatedAt(new \DateTimeImmutable());
        $news->setOwner($this->getUser());

        $this->manager->persist($news);
        $this->manager->flush();

        return $this->json(
            [
                'status' => 'News created!',
                'id' => $news->getId(),
                'createdAt' => $news->getCreatedAt(),
                'owner' => $news->getOwner(),
                Response::HTTP_CREATED,
            ],
        );
    }

    #[Route('/{id}', methods: 'GET', name: 'show')]
    public function show(int $id): Response
    {
        $news = $this->repository->find($id);

        if ($news) {
            return $this->json(
                [
                    'id' => $news->getId(),
                    'name' => $news->getName(),
                    'slug' => $news->getSlug(),
                    'article' => $news->getArticle(),
                    'createdAt' => $news->getCreatedAt(),
                    'updatedAt' => $news->getUpdatedAt(),
                    'owner' => $news->getOwner(),
                    Response::HTTP_OK,
                ],
            );
        }

        return $this->json(
            [
                'status' => 'News not found!',
                Response::HTTP_NOT_FOUND,
            ],
        );
    }

    #[Route('/{id}', methods: 'PUT', name: 'update')]
    public function update(int $id): Response
    {
        $news = $this->repository->find($id);

        if ($news) {
            $news->setName('name');
            $news->setSlug('slug');
            $news->setArticle(['article']);
            $news->setUpdatedAt(new \DateTimeImmutable());

            $this->manager->flush();

            return $this->redirectToRoute('app_api_news_show', ['id' => $news->getId()]);
        }

        return $this->json(
            [
                'status' => 'News not found!',
                Response::HTTP_NOT_FOUND,
            ],
        );
    }

    #[Route('/{id}', methods: 'DELETE', name: 'delete')]
    public function delete(int $id): Response
    {
        $news = $this->repository->find($id);

        if ($news) {
            $this->manager->remove($news);
            $this->manager->flush();

            return $this->json(
                [
                    'status' => 'News deleted!',
                    Response::HTTP_OK,
                ],
            );
        }

        return $this->json(
            [
                'status' => 'News not found!',
                Response::HTTP_NOT_FOUND,
            ],
        );

    }
}

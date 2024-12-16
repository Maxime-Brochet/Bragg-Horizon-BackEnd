<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('api/user', name: 'app_api_user_')]
class UserController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private UserRepository $repository)
    {
    }

    #[Route('/', methods: 'POST', name: 'new')]
    public function new(): Response
    {
        $user = new User();
        $user->setFirstName('User');
        $user->setLastName('Name');
        $user->setEmail('user@name.fr');
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($user);
        $this->manager->flush();

        return $this->json(
            [
                'status' => 'success',
                'message' => 'User created!',
                'id' => $user->getId(),
                'createdAt' => $user->getCreatedAt(),
                Response::HTTP_CREATED,
            ]
        );
    }

    #[Route('/{id}', methods: 'GET', name: 'show')]
    public function show(int $id): Response
    {
        $user = $this->repository->find($id);

        if (!$user) {
            throw new \Exception('No user found for id ' . $id);
        }

        return $this->json(
            [
                'status' => 'success',
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'password' => $user->getPassword(),
                'roles' => $user->getRoles(),
                'createdAt' => $user->getCreatedAt(),
                'updatedAt' => $user->getUpdatedAt(),
                Response::HTTP_OK,
            ]
        );
    }

    #[Route('/{id}', methods: 'PUT', name: 'update')]
    public function update(int $id): Response
    {
        $user = $this->repository->find($id);

        if (!$user) {
            throw new \Exception('No user found for id ' . $id);
        }

        $user->setFirstName('User');
        $user->setLastName('Name');
        $user->setEmail('user@name.fr');
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);
        $user->setUpdatedAt(new \DateTimeImmutable());

        $this->manager->flush();

        return $this->redirectToRoute('app_api_user_show', ['id' => $user->getId()]);
    }

    #[Route('/{id}', methods: 'DELETE', name: 'delete')]
    public function delete(int $id): Response
    {
        $user = $this->repository->find($id);

        if (!$user) {
            throw new \Exception('No user found for id ' . $id);
        }

        $this->manager->remove($user);
        $this->manager->flush();

        return $this->json(
            [
                'status' => 'User deleted!',
                Response::HTTP_OK,
            ]
        );
    }
}

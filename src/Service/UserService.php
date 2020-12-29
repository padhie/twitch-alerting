<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Padhie\TwitchApiBundle\Model\TwitchUser;

final class UserService
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    public function getOrCreateUserByTwitchUser(TwitchUser $twitchUser): User
    {
        $twitchUuid = $twitchUser->getId();
        $twitchLogin = $twitchUser->getName();

        $user = $this->userRepository->findOneBy([
            'twitchUuid' => $twitchUuid,
        ]);

        if ($user !== null) {
            return $user;
        }

        $user = $this->userRepository->findOneBy([
            'twitchLogin' => $twitchLogin,
        ]);

        if ($user !== null) {
            return $user;
        }

        $user = new User($twitchUuid, $twitchLogin);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
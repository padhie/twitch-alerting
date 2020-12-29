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

    public function getUserByTwitchUser(TwitchUser $twitchUser): ?User
    {
        $twitchId = $twitchUser->getId();
        $twitchLogin = $twitchUser->getName();

        $user = $this->userRepository->findOneBy([
            'twitchId' => $twitchId,
        ]);

        if ($user !== null) {
            return $user;
        }

        $user = $this->userRepository->findOneBy([
            'twitchLogin' => $twitchLogin,
        ]);

        return $user ?? null;
    }

    public function getOrCreateUserByTwitchUser(TwitchUser $twitchUser, string $oAuth): User
    {
        $user = $this->getUserByTwitchUser($twitchUser);
        if ($user !== null) {
            return $user;
        }

        $user = new User(
            $twitchUser->getId(),
            $twitchUser->getName(),
            $oAuth
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
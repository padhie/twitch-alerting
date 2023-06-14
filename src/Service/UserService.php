<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Padhie\TwitchApiBundle\Response\Users\User as TwitchUser;

final class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository
    ) {
    }

    public function getUserByTwitchUser(TwitchUser $twitchUser): ?User
    {
        $twitchId = $twitchUser->getId();
        $twitchLogin = $twitchUser->getLogin();

        return $this->getUserByTwitchUserData($twitchId, $twitchLogin);
    }

    public function getUserByTwitchUserData(?int $twitchId, ?string $twitchLogin): ?User
    {
        $user = $this->userRepository->findOneBy([
            'twitchId' => $twitchId ?? '',
        ]);

        if ($user !== null) {
            return $user;
        }

        $user = $this->userRepository->findOneBy([
            'twitchLogin' => $twitchLogin ?? '',
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
            $twitchUser->getLogin(),
            $oAuth
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
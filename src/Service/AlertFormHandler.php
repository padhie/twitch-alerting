<?php

namespace App\Service;

use App\Entity\Alert as AlertEntity;
use App\Entity\User;
use App\Form\AlertForm;
use App\Form\Model\Alert as AlertFormModel;
use App\Repository\AlertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

final class AlertFormHandler
{
    private EntityManagerInterface $entityManager;
    private AlertRepository $alertRepository;
    private Filesystem $filesystem;
    private string $fileSaveDir;

    public function __construct(
        EntityManagerInterface $entityManager,
        AlertRepository $alertRepository,
        Filesystem $filesystem,
        EnvironmentContainer $environmentContainer
    ) {
        $this->entityManager = $entityManager;
        $this->alertRepository = $alertRepository;
        $this->filesystem = $filesystem;
        $this->fileSaveDir = $environmentContainer->getDataDirectory() . DIRECTORY_SEPARATOR . 'sound' . DIRECTORY_SEPARATOR;
    }

    public function save(User $user, FormInterface $form, SluggerInterface $slugger, AlertFormModel $alertFormModel): void
    {
        for($i=0; $i<AlertForm::MAX_ITEMS; $i++) {
            $new = false;

            $nameField = 'name_' . $i;
            $activeField = 'active_' . $i;
            $soundField = 'sound_' . $i;

            if ($alertFormModel->{$nameField} === null) {
                continue;
            }

            $entity = $this->alertRepository->findOneBy([
                'user' => $user->getId(),
                'name' => $alertFormModel->{$nameField}
            ]);

            if ($entity === null) {
                $new = true;
                $entity = new AlertEntity($user, $alertFormModel->{$nameField});
                $this->entityManager->persist($entity);
            }

            $this->saveFile($user, $form, $slugger, $i, $alertFormModel);

            $entity->setActive($alertFormModel->{$activeField});

            if ($alertFormModel->{$soundField} !== null) {
                if ($new !== true) {
                    $this->deleteFile($user, $entity);
                }

                $entity->setFile($alertFormModel->{$soundField});
            }
        }

        dump($alertFormModel);

        $this->entityManager->flush();

        $this->removeOldAlerts($user, $alertFormModel);
    }

    private function removeOldAlerts(User $user, AlertFormModel $alertFormModel): void
    {
        $currentAlertNames = [];
        for($i=0; $i<AlertForm::MAX_ITEMS; $i++) {
            $nameField = 'name_' . $i;

            $currentAlertNames[] = $alertFormModel->{$nameField};
        }

        $userAlerts = $this->alertRepository->findBy([
            'user' => $user->getId(),
        ]);

        $oldAlerts = array_filter(
            $userAlerts,
            static function(AlertEntity $alertEntity) use ($currentAlertNames): bool {
                return !in_array($alertEntity->getName(), $currentAlertNames, true);
            }
        );

        foreach ($oldAlerts as $oldAlert) {
            $this->deleteFile($user, $oldAlert);

            $this->entityManager->remove($oldAlert);
        }

        $this->entityManager->flush();
    }

    private function saveFile(User $user, FormInterface $form, SluggerInterface $slugger, int $index, AlertFormModel $alertFormModel): void
    {
        $soundFile = $form->get('sound_' . $index)->getData();
        if (!$soundFile) {
            return;
        }

        $originalFilename = pathinfo($soundFile->getClientOriginalName(), PATHINFO_FILENAME);

        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = sprintf('%s-%s.%s', $safeFilename, uniqid('', true), $soundFile->guessExtension());

        try {
            $result = $soundFile->move(
                $this->fileSaveDir . DIRECTORY_SEPARATOR . $user->getId(),
                $newFilename
            );
        } catch (FileException $exception) {
            dump($exception);
            return;
        }
        dump(
            $result,
            $this->fileSaveDir . DIRECTORY_SEPARATOR . $user->getId(),
            $newFilename,
            $soundFile
        );

        $alertFormModel->{'sound_' . $index} = $newFilename;
    }

    private function deleteFile(User $user, AlertEntity $alert): void
    {
        $soundFile = $alert->getFile();

        $fullSoundFile = $this->fileSaveDir . DIRECTORY_SEPARATOR . $user->getId()  . DIRECTORY_SEPARATOR . $soundFile;

        $this->filesystem->remove([$fullSoundFile]);
    }
}
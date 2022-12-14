<?php

namespace App\Service;

use App\Entity\Alert as AlertEntity;
use App\Entity\User;
use App\Form\AlertForm;
use App\Form\Model\Alert as AlertFormModel;
use App\Model\Notification;
use App\Model\NotificationCollection;
use App\Repository\AlertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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
        $this->fileSaveDir = $environmentContainer->getDataDirectory() . DIRECTORY_SEPARATOR . 'sound';
    }

    public function save(User $user, FormInterface $form, SluggerInterface $slugger, AlertFormModel $alertFormModel, NotificationCollection $notificationCollection): void
    {
        $savedCounter = 0;
        for($i=0; $i<AlertForm::MAX_ITEMS; $i++) {
            $new = false;

            $nameField = 'name_' . $i;
            $activeField = 'active_' . $i;
            $soundField = 'sound_' . $i;

            if ($alertFormModel->{$nameField} === null) {
                continue;
            }

            $this->saveFile($user, $form, $slugger, $i, $alertFormModel, $notificationCollection);

            $nameValue = $alertFormModel->{$nameField};
            $activeValue = $alertFormModel->{$activeField};
            $soundValue = $alertFormModel->{$soundField};


            $entity = $this->alertRepository->findOneBy([
                'user' => $user->getId(),
                'name' => $nameValue
            ]);

            if ($entity === null) {
                $new = true;
                $entity = new AlertEntity($user, $nameValue);
                $this->entityManager->persist($entity);

                if ($soundValue) {
                    $notificationCollection->addWithDuplicate(
                        new Notification('error', 'error.error_creating', ['name' => $nameValue])
                    );

                    continue;
                }
            }
            $entity->setActive($activeValue);

            if ($soundValue !== null) {
                if ($new !== true) {
                    $this->deleteFile($user, $entity);
                }

                $entity->setFile($soundValue);
            }

            $savedCounter++;
        }

        dump($alertFormModel);

        $this->entityManager->flush();

        $this->removeOldAlerts($user, $alertFormModel);

        $notificationCollection->addWithDuplicate(new Notification('success', 'success.alert_saved', ['success_count' => $savedCounter]));
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

    private function saveFile(User $user, FormInterface $form, SluggerInterface $slugger, int $index, AlertFormModel $alertFormModel, NotificationCollection $notificationCollection): void
    {
        $soundFile = $form->get('sound_' . $index)->getData();
        if (!$soundFile) {
            return;
        }

        $userDirectory = $this->fileSaveDir . DIRECTORY_SEPARATOR . $user->getId();
        try {
            if (is_dir($userDirectory) === false) {
                $this->filesystem->mkdir($userDirectory);
            }
        } catch (Exception $exception) {
            dump($exception);
            $notificationCollection->add(new Notification('error', 'error.user_directory'));
        }

        $originalFilename = pathinfo($soundFile->getClientOriginalName(), PATHINFO_FILENAME);

        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = sprintf('%s-%s.%s', $safeFilename, uniqid('', true), $soundFile->guessExtension());

        try {
            $soundFile->move(
                $userDirectory,
                $newFilename
            );
        } catch (FileException $exception) {
            dump($exception);
            $notificationCollection->addWithDuplicate(new Notification('error', 'error.file_upload', ['filename' => $safeFilename]));

            return;
        }

        $alertFormModel->{'sound_' . $index} = $newFilename;
    }

    private function deleteFile(User $user, AlertEntity $alert): void
    {
        $soundFile = $alert->getFile();

        $fullSoundFile = $this->fileSaveDir . DIRECTORY_SEPARATOR . $user->getId()  . DIRECTORY_SEPARATOR . $soundFile;

        $this->filesystem->remove([$fullSoundFile]);
    }
}
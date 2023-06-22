<?php

namespace App\Service;

use App\Entity\Alert as AlertEntity;
use App\Entity\User;
use App\Form\AlertListForm;
use App\Form\Model\AlertList as AlertFormModel;
use App\Form\Model\Alert;
use App\Model\Notification;
use App\Model\NotificationCollection;
use App\Repository\AlertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

final class AlertListFormHandler
{
    private string $fileSaveDir;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AlertRepository $alertRepository,
        private readonly Filesystem $filesystem,
        EnvironmentContainer $environmentContainer
    ) {
        $this->fileSaveDir = $environmentContainer->getDataDirectory() . DIRECTORY_SEPARATOR . 'sound';
    }

    public function save(
        User $user,
        FormInterface $form,
        SluggerInterface $slugger,
        AlertFormModel $alertFormModel,
        NotificationCollection $notificationCollection
    ): void {
        $savedCounter = 0;
        for($i=0; $i<AlertListForm::MAX_ITEMS; $i++) {
            $new = false;

            $item = $alertFormModel->getItem($i);
            if ($item->name == null) {
                continue;
            }

            $this->saveFile($user, $form, $slugger, $i, $item, $notificationCollection);

            $entity = $this->alertRepository->findOneBy([
                'user' => $user->getId(),
                'name' => $item->name
            ]);

            if ($entity === null) {
                $new = true;
                $entity = new AlertEntity($user, $item->name);
                $this->entityManager->persist($entity);

                if (!$item->sound) {
                    $notificationCollection->addWithDuplicate(
                        new Notification('error', 'error.error_creating', ['name' => $item->name])
                    );

                    continue;
                }
            }
            $entity->setActive($item->active);

            if ($item->sound !== null) {
                if ($new !== true) {
                    $this->deleteFile($user, $entity);
                }

                $entity->setFile($item->sound);
            }

            $savedCounter++;
        }

        $this->entityManager->flush();

        $this->removeOldAlerts($user, $alertFormModel);

        $notificationCollection->addWithDuplicate(new Notification('success', 'success.alert_saved', ['success_count' => $savedCounter]));
    }

    private function removeOldAlerts(User $user, AlertFormModel $alertFormModel): void
    {
        $currentAlertNames = [];
        for($i=0; $i<AlertListForm::MAX_ITEMS; $i++) {
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

    private function saveFile(
        User $user,
        FormInterface $form,
        SluggerInterface $slugger,
        int $index,
        Alert $alertItem,
        NotificationCollection $notificationCollection
    ): void {
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
            $notificationCollection->addWithDuplicate(new Notification('error', 'error.file_upload', ['filename' => $safeFilename]));

            return;
        }

        $alertItem->sound = $newFilename;
    }

    private function deleteFile(User $user, AlertEntity $alert): void
    {
        $soundFile = $alert->getFile();
        if ($soundFile === '') {
            return;
        }

        $fullSoundFile = $this->fileSaveDir . DIRECTORY_SEPARATOR . $user->getId()  . DIRECTORY_SEPARATOR . $soundFile;

        $this->filesystem->remove([$fullSoundFile]);
    }
}
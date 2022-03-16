<?php

namespace whatwedo\SwissZip\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use whatwedo\SwissZip\Entity\SwissZipTrait;

class SwissZipTraitListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            'loadClassMetadata',
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $cm = $eventArgs->getClassMetadata();
        $class = $cm->getName();
        $uses = class_uses($class);

        if (in_array(SwissZipTrait::class, $uses)) {
            $cm->table['indexes'][] = [
                'columns' => [
                    'postleitzahl',
                ],
            ];
            $cm->table['indexes'][] = [
                'columns' => [
                    'plz_zz',
                ],
            ];
            $cm->table['indexes'][] = [
                'columns' => [
                    'ortbez18',
                ],
            ];
            $cm->table['indexes'][] = [
                'columns' => [
                    'ortbez27',
                ],
            ];
        }
    }
}

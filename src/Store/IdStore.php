<?php
namespace App\Store;

use App\Entity\IdEntry;
use Doctrine\ORM\EntityManagerInterface;
use LightSaml\Provider\TimeProvider\TimeProviderInterface;
use LightSaml\Store\Id\IdStoreInterface;

class IdStore implements IdStoreInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /** @var  TimeProviderInterface */
    private $timeProvider;

    /**
     * @param EntityManagerInterface $manager
     * @param TimeProviderInterface $timeProvider
     */
    public function __construct(EntityManagerInterface $manager, TimeProviderInterface $timeProvider)
    {
        $this->manager = $manager;
        $this->timeProvider = $timeProvider;
    }

    /**
     * @param string    $entityId
     * @param string    $id
     * @param \DateTime $expiryTime
     *
     * @return void
     */
    public function set($entityId, $id, \DateTime $expiryTime)
    {
        $idEntry = $this->manager->find(IdEntry::class, ['entityId'=>$entityId, 'id'=>$id]);
        if (null == $idEntry) {
            $idEntry = new IdEntry();
        }
        $idEntry->setEntityId($entityId)
            ->setId($id)
            ->setExpiryTime($expiryTime);
        $this->manager->persist($idEntry);
        $this->manager->flush($idEntry);
    }

    /**
     * @param string $entityId
     * @param string $id
     *
     * @return bool
     */
    public function has($entityId, $id)
    {
        /** @var IdEntry $idEntry */
        $idEntry = $this->manager->find(IdEntry::class, ['entityId'=>$entityId, 'id'=>$id]);
        if (null == $idEntry) {
            return false;
        }
        if ($idEntry->getExpiryTime()->getTimestamp() < $this->timeProvider->getTimestamp()) {
            return false;
        }

        return true;
    }
}

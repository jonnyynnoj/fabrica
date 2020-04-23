<?php declare(strict_types=1);

namespace Noj\Fabrica\Store;

use Doctrine\ORM\EntityManager;

class DoctrineStore implements StoreInterface
{
	private $entityManager;

	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	public function save($entity)
	{
		$this->entityManager->persist($entity);
		$this->entityManager->flush();
	}
}

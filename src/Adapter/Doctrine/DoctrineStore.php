<?php declare(strict_types=1);

namespace Noj\Fabrica\Adapter\Doctrine;

use Doctrine\ORM\EntityManager;
use Noj\Fabrica\Adapter\StoreInterface;
use Noj\Fabrica\Builder\Result;

class DoctrineStore implements StoreInterface
{
	public function __construct(public EntityManager $entityManager)
	{
	}

	/**
	 * @param Result[] $results
	 */
	public function save(array $results): void
	{
		foreach ($results as $result) {
			$metaData = $this->entityManager->getClassMetadata($result->definition->class);

			if (!$metaData->isEmbeddedClass) {
				$this->entityManager->persist($result->entity);
			}
		}

		$this->entityManager->flush();
	}
}

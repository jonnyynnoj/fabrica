<?php declare(strict_types=1);

namespace Noj\Fabrica\Store;

use Doctrine\ORM\EntityManager;
use Noj\Fabrica\Builder\Result;

class DoctrineStore implements StoreInterface
{
	private $entityManager;

	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	/**
	 * @param Result[] $results
	 */
	public function save(array $results)
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

<?php

/**
 * @file
 * Contains Quda\Queue\Job\Collection
 */

namespace Quda\Queue\Job;

use Quda\Vendor\Doctrine\Collection\AbstractCollection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Collection for returning all Jobs
 *
 * @package Application\Module\Queue\Persistence\Doctrine\Collection
 */
class Collection
	extends AbstractCollection
{
	/**
	 * The table we will be using
	 *
	 * @var string
	 */
	protected $table = 'queue_jobs';

	/**
	 * The fields used for counting
	 * @var string
	 */
	protected $countField = 'id';

	/**
	 * The fields to select
	 *
	 * @var string
	 */
	protected $fields = '*';

	/**
	 * {@inheritdoc}
	 */
	protected function applyCriteria(QueryBuilder $queryBuilder, $criteria)
	{
		if (isset($criteria['queue'])) {
			$queryBuilder->andWhere('queue = :queue');
			$queryBuilder->setParameter('queue', $criteria['queue']);
		}

		if (isset($criteria['locked'])) {
			$queryBuilder->andWhere('lock_id IS NOT NULL');
//			$queryBuilder->setParameter('lock_id', $criteria['lock']);
		}

		if (isset($criteria['delay_until'])) {
			if (stripos($criteria['delay_until'], 'NOT') === false) {
				$queryBuilder->andWhere('delay_until IS NULL');
			}
			else {
				$queryBuilder->andWhere('delay_until IS NOT NULL');
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function filterResults($results)
	{
		return array_map([Repository::class, 'createFromDatabase'], $results);
	}

}
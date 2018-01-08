<?php

/**
 * @file
 * Contains Quda\Output\Helper
 */

namespace Quda\Output\Helper;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Quda\Queue\Job\Collection;
use Quda\Vendor\Pagerfanta\CollectionAdapter;
use Quda\Vendor\Pagerfanta\Paginator;

/**
 * Helper functions to deal with pagination
 *
 */
class Paginate
{
	public function __invoke($collection)
	{
		if ($collection instanceof Collection) {
			$adapter = new CollectionAdapter($collection);
		}
		elseif (is_array($collection)) {
			$adapter = new ArrayAdapter($collection);
		}
		else {
			throw new \InvalidArgumentException("The collection must be an instance of Brooks\\Domain\\Shared\\Collection or an array, Received: " . gettype($collection));
		}

		return new Paginator(new Pagerfanta($adapter));
	}

}
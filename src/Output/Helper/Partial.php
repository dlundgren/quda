<?php

/**
 * @file
 * Contains Quda\Output\Helper\Partial
 */

namespace Quda\Output\Helper;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Phrender\Template\Factory;
use Quda\Queue\Job\Collection;
use Quda\Vendor\Pagerfanta\CollectionAdapter;
use Quda\Vendor\Pagerfanta\Paginator;

/**
 * Helper functions to deal with pagination
 *
 */
class Partial
{
	private $templateFactory;

	public function __construct(Factory $templateFactory)
	{
		$this->templateFactory = $templateFactory;
	}

	public function __invoke($template, array $data = [])
	{
		$this->templateFactory->load($template, $data);
	}

}
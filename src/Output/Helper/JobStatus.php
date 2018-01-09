<?php

/**
 * @file
 * Contains Quda\Output\Helper
 */

namespace Quda\Output\Helper;

/**
 * Helper functions to deal with pagination
 *
 */
class JobStatus
{
	public function __invoke($job)
	{
		$status = 'p';
		if ($job->expiresAt() || $job->delayUntil()) {
			$status = 'd';
		}
		elseif ($job->lockId() == -1) {
			$status =  'f';
		}
		elseif ($job->lockId()) {
			$status = 'r';
		}


		switch ($status) {
			case 'f': return 'Failed';
			case 'p': return 'Pending';
			case 'r' : return 'In Progress';
			case 'd': return 'Delayed';
			default: return 'Unknown';
		}
	}

}
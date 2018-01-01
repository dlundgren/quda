<?php

namespace Quda;

use Phrender\Engine;

class Presenter
	extends Engine
{
	protected $level = 0;
	protected $layout = 'default';

	public function withLayout($layout)
	{
		$this->layout = $layout;
	}

	public function render($template, $data)
	{
		$this->level++;
		try {
			$output = parent::render("views/{$template}", $data);
		}
		catch (\Exception $e) {
			$this->level--;
			throw $e;
		}

		if (--$this->level === 0) {
			return parent::render("layouts/{$this->layout}", ['content' => $output]);
		}

		return $output;
	}

}
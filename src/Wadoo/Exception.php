<?php

	namespace Wadoo;

	/**
	 * Wadoo Exception
	 * Nothing fancy but useful because captures all XML errors.
	 */
	class Exception extends \Exception
	{
		protected $errors = array();

		public function __construct($errors)
		{
			$msg = 'Unknown Error';

			if (is_array($errors))
				$this->errors = $errors;
			else
				$msg = $errors;

			parent::__construct($msg);
		}

		public function getErrors()
		{
			return $this->errors;
		}

		/**
		 * Return a message and a list of xml errors (if any).
		 * @return string
		 */
		public function render()
		{
			$html  = '<h3>Something went wrong</h3>';
			$html .= "<p>{$this->getMessage()}</p>";

			$errors = $this->getErrors();
			$errors = self::formatErrors($errors);

			$html .= $errors;

			return $html;
		}

		protected static function formatErrors($errors = array())
		{
			if (!$errors) return '';

			$ret = array();
			foreach ($errors as $e)
			{
				extract((array) $e);
				$file = ltrim(str_replace(getcwd(), '', $file), '/');
				if ($file) $file = "{$file}:{$line}";

				$ret[] = "{$file} {$message}";
			}

			return '<ul><li>'. join('</li><li>', $ret). '</li></ul>';
		}
	}

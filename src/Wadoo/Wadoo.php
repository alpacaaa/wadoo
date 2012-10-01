<?php

	namespace Wadoo;

	/**
	 * Wadoo helper class
	 */
	class Wadoo
	{
		/**
		 * Holds the XSLTProcessor instance used in the transformations
		 */
		protected static $proc;
		protected $app;

		public function __construct(App $app = null)
		{
			$this->app = $app;
		}

		/**
		 * Load an XML file into a DOMDocument
		 * @param string $file the filename
		 * @return DOMDocument
		 */
		public function loadDoc($file)
		{
			$doc = new \DOMDocument();
			$doc->formatOutput = true;
			$doc->preserveWhiteSpace = false;

			$ret = $doc->load($file);

			if (!$ret) return self::throwEx();

			$context = array('file' => $file, 'document' => $doc);
			$context = $this->fire('load.document', $context);
			return $context['document'];
		}

		/**
		 * Transform an xsl stylesheet
		 * @param DOMDocument $xml
		 * @param DOMDocument $xsl
		 * @param array $params additional parameters used in the transformation
		 * @return string
		 */
		public function transform(\DOMDocument $xml, \DOMDocument $xsl, $params = array())
		{
			$proc = self::getProcessor();
			$proc->importStylesheet($xsl);

			if ($params) $proc->setParameter('', $params);
			$ret = @$proc->transformToXML($xml);

			if ($ret) return $ret;
			self::throwEx();
		}

		/**
		 * Merge all the XML files within a folder in a single DOMDocument
		 * @param string $folder
		 * @param DOMDocument $dom the document to populate
		 * @return DOMDocument
		 */
		public function mergeXml($folder, $dom, $dir = null)
		{
			$iterator = new \DirectoryIterator($folder);
			foreach ($iterator as $file)
			{
				$filename = $folder. '/'. $file->getFilename();
				$hidden = substr($file->getFilename(), 0, 1);

				if ($file->isDot() || $hidden == '.') continue;

				if ($file->isDir()) 
				{
					$path = explode('/', $filename);
					array_shift($path);
					$path = join('/', $path);

					$node = $dom->createElement('folder');
					$node->setAttribute('path', $path);

					$this->mergeXml($filename, $dom, $node);
					$target = $dir ?: $dom->documentElement;
					$target->appendChild($node);

					continue;
				}

				$new = $this->loadDoc($filename);
				$new = $dom->importNode($new->documentElement, $deep = true);

				$file = $dom->createElement('file');
				$file->setAttribute('filename', basename($filename));
				$file->appendChild($new);

				$target = $dir ?: $dom->documentElement;
				$target->appendChild($file);

			}

			return $dom;
		}

		protected function fire($event, $context)
		{
			if (!$this->app) return $context;

			return $this->app->fire($event, $context);
		}

		protected static function throwEx()
		{
			$errors = libxml_get_errors();
			libxml_clear_errors();

			throw new Exception($errors);
		}

		protected static function getProcessor()
		{
			if (self::$proc) return self::$proc;
			return self::$proc = new \XSLTProcessor();
		}
	}

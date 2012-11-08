<?php

	namespace Wadoo\Filters;
	use dflydev\markdown\MarkdownParser;

	class Markdown
	{
		protected $markdown;
		protected $extensions = array(
			'.markdown', '.md', '.mdown', '.mkdn', '.mkd', '.mdwn', '.mdtxt', '.mdtext'
		);

		private $uniq;

		public function __construct()
		{
			$this->markdown = new MarkdownParser();
			$this->uniq = uniqid();
		}

		public function register()
		{
			return array(
				'before.load.document' => 'processMarkdownFile',
				'load.document' => 'processXML'
			);
		}

		public function processMarkdownFile($context)
		{
			$file = $context['file'];
			if (!$this->getSupportedExtension($file))
				return $context;

			$content = file_get_contents($file);
			$html = $this->markdown->transformMarkdown($content);
			$html = '<markdown processed="true">'. $html. '</markdown>';

			$context['document']->loadXML($html);

			$context['done'] = true;
			return $context;
		}

		public function processXML($context)
		{
			$doc = $context['document'];
			$xpath =  new \DOMXPath($doc);
			$nodes = $xpath->query('//*[@markdown-process and not(@processed)]');

			if (!$nodes->length) return $context;

			$processed = $doc->createAttribute('processed');
			$processed->value = 'true';

			foreach ($nodes as $node)
			{
				$html = $this->markdown->transformMarkdown($node->nodeValue);
				$node->nodeValue = '';

				$new  = new \DOMDocument();
				$new->loadXML('<dummy>'. $html. '</dummy>');
				$nodes = $new->documentElement->childNodes;

				foreach ($nodes as $el)
					$node->appendChild($doc->importNode($el, true));

				$node->appendChild($processed);
			}

			return $context;
		}

		protected function getSupportedExtension($file)
		{
			$file = str_replace($this->extensions, $this->uniq, $file);
			return substr($file, -strlen($this->uniq)) == $this->uniq;
		}
	}

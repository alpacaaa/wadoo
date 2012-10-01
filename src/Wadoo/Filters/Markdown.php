<?php

	namespace Wadoo\Filters;
	use dflydev\markdown\MarkdownParser;

	class Markdown implements \Wadoo\Filter
	{
		protected $markdown;

		public function __construct()
		{
			$this->markdown = new MarkdownParser();
		}

		public function register()
		{
			return 'load.document';
		}

		public function fire($context)
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
	}

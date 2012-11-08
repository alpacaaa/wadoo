<?php

	namespace Wadoo\Filters;

	class HTML5
	{
		public function register()
		{
			return array(
				'compile.after' => 'fixDoctype'
			);
		}

		// code stolen from the html5_doctype extension from symphony cms
		// https://github.com/domain7/html5_doctype/
		public function fixDoctype($context)
		{
			// Parse only if $context['output'] exists and it's an HTML document
			if(substr($context['output'], 0, 14) !== '<!DOCTYPE html') return $context;

			$html = $context['output'];

			// Split the HTML output into two variables:
			// $html_doctype contains the first four lines of the HTML document
			// $html_doc contains the rest of the HTML document
			$html_array = explode("\n", $html, 15);
			$html_doc = array_pop($html_array);
			$html_doctype = implode("\n", $html_array);

			// Parse the doctype to convert XHTML syntax to HTML5
			$html_doctype = preg_replace("/<!DOCTYPE [^>]+>/", "<!DOCTYPE html>", $html_doctype);
			$html_doctype = preg_replace('/ xmlns=\"http:\/\/www.w3.org\/1999\/xhtml\"| xml:lang="[^\"]*\"/', '', $html_doctype);
			$html_doctype = preg_replace('/<meta http-equiv=\"Content-Type\" content=\"text\/html; charset=(.*[a-z0-9-])\" \/>/i', '<meta charset="\1" />', $html_doctype);

			// Concatenate the fragments into a complete HTML5 document
			$html = $html_doctype . "\n" . $html_doc;

			$context['output'] = $html;
			$html = $context['output'];
			return $context;
		}

	}

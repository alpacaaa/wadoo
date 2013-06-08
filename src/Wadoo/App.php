<?php


	namespace Wadoo;

	/**
	 * Wadoo little app
	 */
	class App {

		protected $wadoo;

		/**
		 * Holds registered filters
		 * [event] => array(
		 *    array('object' => filter1, 'callback' => 'method'),
		 *    array('object' => filter2, 'callback' => 'method')
		 * )
		 */
		protected $filters = array();

		/**
		 * Wadoo configuration
		 * can be overridden in the constructor
		 */
		protected $config  = array();

		/**
		 * Wadoo base path
		 */
		protected $path;

		public function __construct($params = array())
		{
			$root = rtrim($_SERVER['HTTP_HOST'], '/'). dirname($_SERVER['PHP_SELF']);
			$root = 'http://'. rtrim($root, '/');

			$default = array(
				'data.folder' => 'data',
				'data.file' => 'data.xml',
				'sitemap.file' => 'sitemap.xml',
				'public.folder' => 'public',
				'root.url' => $root
			);

			$this->path = realpath(__DIR__. '/../..'). '/';
			$this->config = array_merge($default, $params);
			$this->wadoo = new Wadoo($this);
		}

		/**
		 * App dispatcher
		 * setting $_GET['echo'] will cause most actions to print
		 * the result instead of writing it to a file (useful for testing)
		 *
		 * @param string $action the method to invoke
		 * @return string the message/html returned by the method
		 */
		public function run($action = 'index')
		{
			$action = str_replace('-', '', $action);
			$method = 'action'. ucfirst($action);

			if (!method_exists($this, $method))
				throw new Exception('Unknow action '. $action);

			return $this->$method($_GET);
		}

		public function actionIndex()
		{
			return "This is wadoo. Please take a look at 
			the docs if you're unsure what to do next.";
		}

		/**
		 * Merge all the files found in the `data.folder` defined
		 * in the configuration into a single `data.file`
		 * @see Wadoo::mergeXML()
		 */
		public function actionMergeData($options = array())
		{
			$dir = $this->getPath($this->get('data.folder'));

			if (!is_dir($dir))
				throw new Exception('Folder <code>'. $dir. '</code> does not exist.');

			$dom = new \DomDocument();
			$dom->formatOutput = true;
			$dom->preserveWhiteSpace = false;
			$dom->loadXML("<data />");

			$this->wadoo->mergeXml($dir, $dom);

			$options['format'] = true;
			return $this->send($dom, $this->get('data.file'), $options);
		}

		/**
		 * Creates a sitemap using the given template.
		 * Sitemap must have the following structure:
		 * <sitemap>
		 *	<resource uri="index.html" template="home.xsl" />
		 *	<resource uri="blog/my-post.html" template="blog.xsl" />
		 * </sitemap>
		 */
		public function actionSitemap($options = array())
		{
			$tpl = isset($options['template']) ? $this->getPath($options['template']) : '';

			if (!$tpl || !file_exists($tpl))
				throw new Exception(
				'Please specify a valid stylesheet in the url 
				<code>?action=sitemap&amp;template=file.xsl</code>'
				);

			$params = $this->getTransformationParams(array(), $options);
			$dataFile = $this->getPath($this->get('data.file'));

			$xml = $this->wadoo->loadDoc($dataFile);
			$xsl = $this->wadoo->loadDoc($tpl);
			$ret = $this->wadoo->transform($xml, $xsl, $params);

			$options['format'] = true;
			return $this->send($ret, $this->get('sitemap.file'), $options);
		}

		/**
		 * Compiles a single resource or the whole website.
		 * The resource can be defined with $options['uri'].
		 */
		public function actionCompile($options = array())
		{
			$public  = $this->getPath(trim($this->get('public.folder'), '/')). '/';
			$sitemapFile = $this->getPath($this->get('sitemap.file'));
			$data = $this->getPath($this->get('data.file'));

			if (!file_exists($data))
				throw new Exception('<code>'. $data. '</code> not found.');

			if (!file_exists($sitemapFile))
				throw new Exception('<code>'. $sitemapFile. '</code> not found.');

			$sitemap = $this->wadoo->loadDoc($sitemapFile);
			$xml = $this->wadoo->loadDoc($data);
			$xpath = new \DOMXPath($sitemap);

			$uri = isset($options['uri']) ? $options['uri'] : null;

			if ($uri)
			{
				$xsl = $xpath->query('//resource[@uri = "'. $uri. '"]');
				if (!$xsl->length)
				{
					// try appending /index.html
					$new = rtrim($uri, '/'). '/index.html';
					$xsl = $xpath->query('//resource[@uri = "'. $new. '"]');

					if (!$xsl->length)
						throw new Exception('No resource with uri: <code>'. $uri. '</code>');

					// found with /index.html
					$uri = $new;
				}

				$xsl = $xsl->item($xsl->length -1);
				$ret = $this->compileResource($xsl, $xml, $options);
				return $this->send($ret, $public. $uri, $options);
			}

			$resources = $xpath->query('//resource');

			if (!$resources->length)
				throw new Exception('No resource defined in <code>'. $sitemapFile. '</code>');

			foreach ($resources as $r)
			{
				$ret = $this->compileResource($r, $xml, $options);
				self::writeFile($public. $r->getAttribute('uri'), $ret);
			}

			return 'Site compiled succesfully!';
		}

		/**
		 * Useful when modifying constantly modifying data.
		 * Will first merge the data again and the recompile either 
		 * a single resource or the whole website.
		 *
		 * @see App::actionMergeData()
		 * @see App::actionCompile()
		 */
		public function actionMergeDataAndCompile($options = array())
		{
			return $this->actionFullCompile($options, $skipSitemap = true);
		}

		/**
		 * Merge data + Sitemap compilation + Resource/Website compilation
		 * @see actionMergeDataAndCompile
		 * @see actionSitemap
		 */
		public function actionFullCompile($options = array(), $skipSitemap = false)
		{
			$dontEcho = $options;
			unset($dontEcho['echo']);

			$this->actionMergeData($dontEcho);
			if (!$skipSitemap)
				$this->actionSitemap($dontEcho);

			return $this->actionCompile($options);
		}

		/**
		 * Register filters that will be invoked during app execution.
		 * Filters can alter pretty much anything and are used as extensions to the core.
		 *
		 * @param array $filters an array of objects with a `register()` method
		 */
		public function registerFilters($filters = array())
		{
			foreach ($filters as $f)
			{
				if (!method_exists($f, 'register'))
					throw new Exception(get_class($f). ' does not have a register method.');

				$events = $f->register();

				foreach ($events as $e => $cb)
				{
					if (!isset($this->filters[$e]))
						$this->filters[$e] = array();

					$this->filters[$e][] = array('object' => $f, 'callback' => $cb);
				}
			}
		}

		/**
		 * Fire an event so that registered filters can alter application data/behaviour.
		 *
		 * @param string $event
		 * @param array $context
		 * @return $context
		 */
		public function fire($event, $context)
		{
			if (!isset($this->filters[$event])) return $context;

			foreach ($this->filters[$event] as $data)
			{
				$f  = $data['object'];
				$cb = $data['callback'];
				if (!method_exists($f, $cb))
					throw new Exception('Method '. $cb. ' not implemented in '. get_class($f));

				$ret = $f->$cb($context);
				if ($ret) $context = $ret;
			}

			return $context;
		}

		/**
		 * Accessor to Wadoo configuration.
		 *
		 * @param string $key
		 * @param string $default will be returned if key is not found
		 * @return string configuration value or $default
		 */
		public function get($key, $default = null)
		{
			if (isset($this->config[$key]))
				return $this->config[$key];

			return $default;
		}

		/**
		 * Return file path relative to Wadoo root.
		 *
		 * @param string $append path to append
		 * @return string full path
		 */
		public function getPath($append = '')
		{
			return $this->path. $append;
		}

		protected function compileResource(\DOMElement $r, \DOMDocument $xml, $options = array())
		{
			$uri = $r->getAttribute('uri');
			$xsl = $r->getAttribute('template');

			if (!$uri || !$xsl) {
				$missing = $uri ? 'template' : 'uri';
				throw new Exception(sprintf(
					'Resource defined on line %s is missing <code>%s</code>',
					$r->getLineNo(), $missing
				));
			}

			$params = $this->getTransformationParams($r->attributes, $options);

			$xsl = $this->wadoo->loadDoc($this->getPath($xsl));
			$ret = $this->wadoo->transform($xml, $xsl, $params);

			$context = array(
				'uri' => $uri, 'output' => $ret
			);

			$context = $this->fire('compile.after', $context);
			return $context['output'];
		}

		protected function send($data, $file = null, $options = array(), $msg = null)
		{
			if ($file && !isset($options['echo']))
			{
				if (!$msg)
					$msg = "<code>{$file}</code> compiled succesfully.";

				self::writeFile($this->getPath($file), $data);
				return $msg;
			}

			if ($data instanceof \DomDocument)
				$data = $data->saveXML();

			if (isset($options['format']) && $options['format'])
				$data = sprintf('<pre>%s</pre>', htmlentities($data));

			return $data;
		}

		protected function getTransformationParams($attributes = array(), $options = array())
		{
			$root = $this->get('root.url');
			if (php_sapi_name() !== 'cli-server')
				$root .= '/'. $this->get('public.folder');

			$params = array(
				'root'  => $root,
				'today' => date('Y-m-d')
			);

			foreach ($attributes as $k => $v)
				$params[$k] = $v->value;

			$exclude = array('action', 'template', 'uri', 'echo');
			foreach ($options as $k => $v)
			{
				if (in_array($k, $exclude) || is_array($v)) continue;

				$params[$k] = $v;
			}

			return $params;
		}

		protected static function writeFile($path, $data)
		{
			if (substr($path, -1) == '/') $path .= 'index.html';
			$dir = explode('/', $path);

			array_pop($dir);
			$dir = join('/', $dir);
			if ($dir && !is_dir($dir))
				mkdir($dir, 0777, $recursive = true);

			if ($data instanceof \DomDocument)
				$data = $data->saveXML();

			if (!file_put_contents($path, $data))
				throw new Exception(
					'Cannot write file <code>'. $path. '</code>. Check permissions'
				);
		}
	}


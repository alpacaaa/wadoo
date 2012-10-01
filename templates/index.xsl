<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:import href="utils.xsl" />

<xsl:output method="xml"
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
	omit-xml-declaration="yes"
	encoding="UTF-8"
	indent="yes" />

<xsl:param name="assets" select="concat($root, '/assets/')" />

<xsl:variable name="config" select="/data/file[@filename = 'config.xml']/config" />

<xsl:template match="/">
	<html>
		<head>
		   <title>
		   	<xsl:apply-templates select="data" mode="head-title" />
		   </title>
		   <meta name="author" content="{$config/author/name}" />

		   <link rel="stylesheet" href="{$assets}css/style.css" type="text/css" />
		</head>
		<body>

		  <div class="site">
			<div class="title">
			  <a href="{$root}">
			  	<xsl:value-of select="$config/site/title" />
			  </a>

			  <xsl:apply-templates select="data/folder[@path = 'pages']/file/entry[@show-in-menu = 'true']" mode="pages-menu" />
			</div>
		  
			<xsl:apply-templates select="data" />
		  
			<div class="footer">
			  <div class="contact">
				<p>
				  <xsl:value-of select="$config/author/name" /><br />
				  <xsl:value-of select="$config/site/tagline" />
				</p>
			  </div>
			  <div class="contact">
				<p>
				  <a href="https://github.com/{$config/author/github}/">
				  	github.com/<xsl:value-of select="$config/author/github" />
				  </a><br />

				  <!-- <a href="mailto:{$config/author/email}">
				  	<xsl:value-of select="$config/author/email" />
				  </a> -->
				  Theme based on <a href="https://github.com/jekyllbootstrap/theme-tom">theme-tom</a> 
				  from <a href="http://jekyllbootstrap.com/">Jekyll Bootstrap</a>
				</p>
			  </div>
			</div>
		  </div>
		  <a href="https://github.com/alpacaaa/wadoo">
		  	<img style="position: absolute; top: 0; right: 0; border: 0;" src="http://s3.amazonaws.com/github/ribbons/forkme_right_red_aa0000.png" alt="Fork me on GitHub" />
		 </a>

		</body>
	</html>
</xsl:template>

<xsl:template match="data" mode="head-title">
	<xsl:value-of select="$config/site/title" />
</xsl:template>

<xsl:template match="data">
	<h1>My blog posts</h1>

	<ul class="posts">
		<xsl:apply-templates select="folder[@path = 'posts']/file/entry" mode="post-listing">
			<xsl:sort select="substring(date,1,4)" order="descending" /> <!-- year sort -->
			<xsl:sort select="substring(date,4,2)" order="descending" /> <!-- day sort -->
			<xsl:sort select="substring(date,7,2)" order="descending" /> <!-- month sort -->
		</xsl:apply-templates>
	</ul>
</xsl:template>

<xsl:template match="entry" mode="post-listing">
	<xsl:variable name="href">
		<xsl:call-template name="get-post-url" />
	</xsl:variable>

	<li>
		<span>
			<xsl:call-template name="format-date">
				<xsl:with-param name="date" select="date" />
			</xsl:call-template>
		</span>
		 » 
		<a href="{$href}">
			<xsl:value-of select="title" />
		</a>
	</li>
</xsl:template>

<xsl:template match="entry" mode="pages-menu">
	<a class="extra" href="{$root}/{title/@handle}">
		<xsl:value-of select="title" />
	</a>
</xsl:template>

</xsl:stylesheet>

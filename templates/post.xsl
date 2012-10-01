<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:import href="index.xsl" />

<xsl:variable name="post" select="/data/folder[@path = 'posts']/file[@filename = $filename]/entry" />

<xsl:template match="data">
	<xsl:apply-templates select="$post" mode="post-detail" />
</xsl:template>

<xsl:template match="data" mode="head-title">
	<xsl:value-of select="$post/title" /> - 
	<xsl:apply-imports />
</xsl:template>

<xsl:template match="entry" mode="post-detail">
	<div id="post">
		<h1><xsl:value-of select="title" /></h1>

		<p class="meta">
			<xsl:call-template name="format-date">
				<xsl:with-param name="date" select="date" />
			</xsl:call-template>

			<xsl:if test="city">
				 - <xsl:value-of select="city" />
			</xsl:if>
		</p>

		<xsl:copy-of select="content/*" />
	</div>

	<xsl:apply-templates select="related" />
</xsl:template>

<xsl:template match="related">
	<div id="related">
		<h2>Related Posts</h2>

		<ul class="posts">
			<xsl:apply-templates select="item" />
		</ul>
	</div>
</xsl:template>

<xsl:template match="related/item">
	<xsl:apply-templates 
		select="/data/folder[@path = 'posts']/file[@filename = concat(current()/@handle, '.xml')]/entry" 
		mode="related-post" />
</xsl:template>

<xsl:template match="entry" mode="related-post">
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

</xsl:stylesheet>

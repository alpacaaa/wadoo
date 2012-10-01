<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:import href="utils.xsl" />

<xsl:output method="xml"
	encoding="UTF-8"
	indent="yes" />


<xsl:template match="/">
	<sitemap>
		<xsl:apply-templates select="data" />
	</sitemap>
</xsl:template>

<xsl:template match="data">
	<resource uri="index.html" template="templates/index.xsl" />

	<xsl:apply-templates select="folder[@path = 'posts']/file/entry" mode="post" />
	<xsl:apply-templates select="folder[@path = 'pages']/file/entry" mode="page" />
</xsl:template>

<xsl:template match="entry" mode="post">
	<xsl:variable name="href">
		<xsl:call-template name="get-post-url">
			<xsl:with-param name="root" select="''" />
			<xsl:with-param name="suffix" select="'/index.html'" />
		</xsl:call-template>
	</xsl:variable>

	<resource uri="{$href}" template="templates/post.xsl" filename="{../@filename}" />
</xsl:template>

<xsl:template match="entry" mode="page">
	<resource uri="{title/@handle}/index.html" template="templates/page.xsl" handle="{title/@handle}" />
</xsl:template>

</xsl:stylesheet>

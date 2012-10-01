<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:import href="index.xsl" />

<xsl:variable name="page" select="/data/folder[@path = 'pages']/file/entry[title/@handle = $handle]" />

<xsl:template match="data">
	<div id="post">
		<xsl:copy-of select="$page/content/*" />
	</div>
</xsl:template>

<xsl:template match="data" mode="head-title">
	<xsl:value-of select="$page/title" /> - 
	<xsl:apply-imports />
</xsl:template>

</xsl:stylesheet>

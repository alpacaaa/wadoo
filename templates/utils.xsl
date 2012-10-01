<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:import href="date-time.xsl" />

<xsl:template name="get-post-url">
	<xsl:param name="entry" select="." />
	<xsl:param name="root" select="concat($root, '/')" />
	<xsl:param name="suffix" select="''" />

	<xsl:variable name="year" select="substring($entry/date,1,4)" />
	<xsl:variable name="handle" select="substring-before($entry/../@filename, '.xml')" />

	<xsl:value-of select="concat($root, 'blog/', $year, '/', $handle, $suffix)" />
</xsl:template>

</xsl:stylesheet>

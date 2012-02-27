<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<xsl:output omit-xml-declaration="yes"/>
	<xsl:output indent="yes"/>
	
	<xsl:template match="module[@name ='snippet' and @action='show' and @mode='posts_updates_last']" mode="p-module">
		<div class="snippet posts_updates_last">
			<div>
				<xsl:value-of select="snippet/@posts_fetched" />
				<xsl:text> записей в рейтинге по данным на </xsl:text>
				<xsl:value-of select="snippet/@time" />
			</div>
		</div>
	</xsl:template>
	
	
</xsl:stylesheet>

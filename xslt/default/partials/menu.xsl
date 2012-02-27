<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<xsl:output omit-xml-declaration="yes"/>
	<xsl:output indent="yes"/>

	<xsl:template match="module[@name ='menu' and @action='list' and @mode='rating']" mode="p-module">
		<ul class="p-menu-rating">
			<xsl:apply-templates select="menu/item" mode="p-menu-rating" />
		</ul>
	</xsl:template>
	
	<xsl:template match="*" mode="p-menu-rating">
		<li class="{@class}">
			<a onfocus="this.blur()"  title="{@title}" alt="{@title}">
				<xsl:if test="@class = ''">
					<xsl:attribute name="href">
						<xsl:value-of select="@path" />
					</xsl:attribute>
				</xsl:if>
				<xsl:value-of select="@title" />	
			</a>	
		</li>
	</xsl:template>
	
</xsl:stylesheet>

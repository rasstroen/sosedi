<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<xsl:output omit-xml-declaration="yes"/>
	<xsl:output indent="yes"/>

	<xsl:template match="*" mode="p-misc-condition">
		<ul class="p-misc-condition">
			<xsl:if test="@mode='sorting'">
				<div class="p-misc-condition-title">Сортировать по</div>
			</xsl:if>
			<xsl:apply-templates select="options/item" mode="p-misc-condition-option"/>
		</ul>
	</xsl:template>

	<xsl:template match="*" mode="p-misc-condition-option">
		<li>
			<xsl:attribute name="class">
				<xsl:text>p-misc-condition-option</xsl:text>
				<xsl:if test="@current=1"> current</xsl:if>
			</xsl:attribute>
			<xsl:apply-templates select="." mode="h-variant-link"/>
		</li>
	</xsl:template>

</xsl:stylesheet>

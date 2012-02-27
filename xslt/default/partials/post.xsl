<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<xsl:output omit-xml-declaration="yes"/>
	<xsl:output indent="yes"/>

	<xsl:template match="module[@name ='posts' and @action='list']" mode="p-module">
		<xsl:apply-templates select="posts/item" mode="posts-list">
			<xsl:with-param name="authors" select="authors" />
		</xsl:apply-templates>
		
	</xsl:template>
	
	<xsl:template match="module[@name ='posts' and @action='show']" mode="p-module">
		<xsl:apply-templates select="post" mode="posts-show">
			<xsl:with-param name="authors" select="authors" />
		</xsl:apply-templates>
	</xsl:template>

	<xsl:template match="*" mode="posts-list">
		<xsl:param name="authors" select="authors"/>
		<xsl:param name="post" select="." />
		<xsl:variable name="author" select="$authors/item[@id = $post/@id_author]" />
		<div class="post-list">
			
			<xsl:attribute name="class">
				<xsl:choose>
					<xsl:when test="position() mod 2=1">post-list odd</xsl:when>
					<xsl:otherwise>post-list</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<div class="num">
				<xsl:value-of select="@num" />
			</div>
			<xsl:apply-templates select="$post" mode="posts-title-plank">
				<xsl:with-param name="author" select="$author" />
			</xsl:apply-templates>
			<div class="text">
				<xsl:if test="@has_pic=1">
					<div class="pic">
						<img src="{@pic}" />
					</div>
				</xsl:if>
				<xsl:value-of select="@short" disable-output-escaping="yes" />
			</div>
			<div class="break" />
			<div class="counters">
				<xsl:value-of select="@visits24" /> посетителей,
				<xsl:value-of select="@comments24" /> комментариев,
				<xsl:value-of select="@links24" /> ссылок за 24 часа
			</div>
			<div class="break" />
		</div>
	</xsl:template>
	
	<xsl:template match="*" mode="posts-title-plank">
		<xsl:param name="author" />
		<div class="title">
			<div class="author">
				<a href="{$author/@path}">
					<xsl:value-of select="$author/@username" />
				</a>
			</div>
			<em>:</em>
			<div class="title">
				<a href="{@path}">
					<xsl:value-of select="@title" />
				</a>
			</div>
		</div>
	</xsl:template>
	
	<xsl:template match="*" mode="posts-show">
		<xsl:param name="authors" select="." />
		<xsl:param name="post" select="." />
		<xsl:variable name="author" select="$authors/item[@id = $post/@id_author]" />		
		<div class="post-show">
			<xsl:apply-templates select="$post" mode="posts-title-plank">
				<xsl:with-param name="author" select="$author" />
			</xsl:apply-templates>
			<div class="author">
				<img src="{$author/@avatar}" />
			</div>
			<div class="text">
				<xsl:value-of select="@text" disable-output-escaping="yes" />
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>

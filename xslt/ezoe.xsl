<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl"
	exclude-result-prefixes="php">
	<xsl:output omit-xml-declaration="yes" method="html" indent="yes"/>

	<!-- remove /html/body/ wrapper -->
	<xsl:template match="/">
		<xsl:apply-templates select="/html/body/node()" />
	</xsl:template>

	<!-- copy everything -->
	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:template>

	<!-- translate big to b -->
	<xsl:template match="big">
		<b><xsl:apply-templates select="@*|node()"/></b>
	</xsl:template>

	<!-- strip tags -->
	<xsl:template match="font|span|hr|wbr|tbody|center">
		<xsl:value-of select="php:function( 'Html2XmlTextEWeek::xsltLog', concat( 'Tag removed: ', name(.) ) )"/>
		<xsl:apply-templates select="node()"/>
	</xsl:template>

	<!-- remove attributes -->
	<xsl:template match="h1/@style|h2/@style|h3/@style|h4/@style|h5/@style" />

	<!-- remove attributes and log -->
	<xsl:template match="*/@onclick|p/@class|p/@style|a/@name|li/@class|a/@class|a/@style|a/@rel|table/@class|table/@style|li/@style|table/@bgcolor|table/@height|table/@cellpadding|table/@cellspacing|td/@height|td/@class|tr/@bgcolor|td/@bgcolor|ul/@style|ul/@class|td/@bordercolor|td/@style|p/@id|ul/@dir|table/@valign|table/@bordercolor|tr/@bordercolor|tr/@class|h1/@style|h2/@style|h3/@style|h4/@style|h5/@style">
		<xsl:value-of select="php:function( 'Html2XmlTextEWeek::xsltLog', concat( 'Removing attribute: ', name( ./.. ), '/', name(.) ) )"/>
	</xsl:template>

	<!-- img to custom tag -->
	<xsl:template match="img">
		<custom name="img" custom:alt="{string( @alt )}" custom:src="{string( @src )}" custom:style="{string( @style )}" custom:height="{string( @height )}" custom:width="{string( @width )}">
			<xsl:choose>
				<xsl:when test="string( @alt ) != ''">
					<xsl:value-of select="@alt" />
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="@src" />
				</xsl:otherwise>
			</xsl:choose>
		</custom>
	</xsl:template>

	<!-- q|blockquote to custom tag -->
	<xsl:template match="q|blockquote">
		<custom name="blockquote"><xsl:apply-templates select="@*|node()"/></custom>
	</xsl:template>

	<!-- u to custom tag -->
	<xsl:template match="u">
		<custom name="underline"><xsl:apply-templates select="@*|node()"/></custom>
	</xsl:template>

	<!-- sup to custom tag -->
	<xsl:template match="sup|sub">
		<custom name="{name(.)}"><xsl:apply-templates select="@*|node()"/></custom>
	</xsl:template>

	<!-- strike to custom tag -->
	<xsl:template match="strike|del">
		<custom name="strike"><xsl:apply-templates select="@*|node()"/></custom>
	</xsl:template>

	<!-- script to literal tag -->
	<xsl:template match="script">
		<xsl:value-of select="php:function( 'Html2XmlTextEWeek::xsltLog', 'Script tag embedded in literal' )"/>
		<literal class="html">
			<script><xsl:apply-templates select="@*|node()"/></script>
		</literal>
	</xsl:template>

	<!-- wrap some tags into literal -->
	<xsl:template match="object|form|iframe|code|pre">
		<xsl:value-of select="php:function( 'Html2XmlTextEWeek::xsltLog', concat( name(.), ' embedded in literal' ) )"/>
		<literal class="html">
			<xsl:element name="{name( . )}">
				<xsl:apply-templates select="@*|node()"/>
			</xsl:element>
		</literal>
	</xsl:template>

	<!-- remove empty links -->
	<xsl:template match="a[normalize-space()='']">
		<xsl:apply-templates select="node()"/>
		<xsl:value-of select="php:function( 'Html2XmlTextEWeek::xsltLog', concat( 'Empty link tag (a) removed: ', string(./@href) ) )"/>
	</xsl:template>

	<!-- remove empty tags -->
	<xsl:template match="p[normalize-space()='']|strong[normalize-space()='']|li[normalize-space()='']|ul[normalize-space()='']|div[normalize-space()='']|table[normalize-space()='']">
		<xsl:apply-templates select="node()"/>
		<xsl:value-of select="php:function( 'Html2XmlTextEWeek::xsltLog', concat( 'Empty tag removed: ', name(.) ) )"/>
	</xsl:template>

</xsl:stylesheet>
<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="/">
		<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE html&gt;</xsl:text>
		<html>
			<head>
				<meta charset="UTF-8"/>
				<title>
					<xsl:call-template name="title"/>
				</title>
				<style type="text/css">
					html {
					background-color: #ddd;
					}
					body {
					width: 80%;
					margin: 20px auto;
					padding: 15px;
					border: 1px solid #888;
					background-color: #fff;
					-webkit-border-radius: 15px;
					-moz-border-radius: 15px;
					border-radius: 15px;
					-webkit-box-shadow: 0 0 10px #999;
					-moz-box-shadow: 0 0 10px #999;
					box-shadow: 0 0 10px #999;
					}
					table {
					width: 100%;
					}
					th {
					background-color: #b0c4de;
					padding: 1px 10px;
					}
					td {
					background-color: #ddd;
					}
				</style>
			</head>
			<body>
				<h1>
					<xsl:call-template name="title"/>
				</h1>
				<p>
					<xsl:value-of select="/exception/message"/>
				</p>

				<p>
					<xsl:text>Thrown in: </xsl:text>
					<code>
						<xsl:value-of select="/exception/file"/>
						<xsl:text> @ line </xsl:text>
						<xsl:value-of select="/exception/file/@line"/>
					</code>
				</p>

				<h2>Data</h2>
				<table>
					<tr>
						<th>Name:</th>
						<th>Value:</th>
					</tr>
					<xsl:for-each select="/exception/dataList/data">
						<tr>
							<td>
								<code>
									<xsl:value-of select="@name"/>
								</code>
							</td>
							<td>
								<code>
									<xsl:value-of select="."/>
								</code>
							</td>
						</tr>
					</xsl:for-each>
				</table>

				<h2>Stacktrace</h2>
				<table>
					<tr>
						<th>File:</th>
						<th colspan="3">Class:</th>
						<th>Arguments:</th>
					</tr>
					<xsl:for-each select="/exception/traceList/trace">
						<tr>
							<td>
								<code>
									<xsl:value-of select="file"/>
									<xsl:text> @ line </xsl:text>
									<xsl:value-of select="file/@line"/>
								</code>
							</td>
							<td>
								<code>
									<xsl:value-of select="class"/>
								</code>
							</td>
							<td>
								<code>
									<xsl:value-of select="@type"/>
								</code>
							</td>
							<td>
								<code>
									<xsl:value-of select="class/@function"/>
								</code>
							</td>
							<td>
								<code>
									<xsl:value-of select="args"/>
								</code>
							</td>
						</tr>
					</xsl:for-each>
				</table>
			</body>
		</html>
	</xsl:template>

	<xsl:template name="title">
		<xsl:text>Uncaught </xsl:text>
		<xsl:value-of select="/exception/@name"/>
	</xsl:template>

</xsl:stylesheet>
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
    <xsl:template match="/">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <title>Sitemap</title>
            <style type="text/css">
                body {
                    font-family: Arial, sans-serif;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                th, td {
                    padding: 8px;
                    border: 1px solid #ddd;
                    text-align: left;
                }
                th {
                    background-color: #f4f4f4;
                }
                h1 {
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <h1>XML Sitemap</h1>
            <table>
                <thead>
                <tr>
                    <th>URL</th>
                    <th>Last Modified</th>
                </tr>
                </thead>
                <tbody>
                <xsl:for-each select="urlset/url">
                    <tr>
                        <td><xsl:value-of select="loc"/></td>
                        <td><xsl:value-of select="lastmod"/></td>
                    </tr>
                </xsl:for-each>
                </tbody>
            </table>
        </body>
        </html>
    </xsl:template>
</xsl:stylesheet>

<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="html"/>

  <xsl:template match="/response">
    <div>
    <h2><xsl:value-of select="result/@numFound"/> Items Returned</h2>
    <table class="sortable">
        <tr>
          <th>Time</th>
          <th>IP</th>
          <th>EPerson Id</th>
          <th>Type</th>
          <th>Object</th>
          <th>Referrer</th>
        </tr>
      <xsl:for-each select="result/doc">
        <tr>
          <td><xsl:value-of select="date[@name='time']"/></td>
          <td><xsl:value-of select="str[@name='ip']"/></td>
          <td><xsl:value-of select="int[@name='epersonid']"/></td>
          <td>
          <xsl:variable name="type" select="int[@name='type']"/>
          <xsl:choose>
            <xsl:when test="$type=2">Item</xsl:when>
            <xsl:when test="$type=3">Collection</xsl:when>
            <xsl:when test="$type=4">Community</xsl:when>
            <xsl:when test="$type=5">Bitstream</xsl:when> 
            <xsl:otherwise><xsl:value-of select="$type"/></xsl:otherwise>
          </xsl:choose>
          </td>
          <td>
          <a>
              <xsl:choose>
                <!-- pre-DSpace 6 -->
                <xsl:when test="int[@name='id']">
                  <xsl:attribute name="href">
                    <xsl:value-of select="concat('getHandle.php?type=',int[@name='type'],'&amp;id=',int[@name='id'])"/>
                  </xsl:attribute>
                  <xsl:value-of select="int[@name='id']"/>
                </xsl:when>
                <!-- DSpace 6 and later -->
                <xsl:otherwise>
                  <xsl:attribute name="href">
                    <xsl:value-of select="concat('getHandle.php?type=',int[@name='type'],'&amp;id=',str[@name='uid'])"/>
                  </xsl:attribute>
                  <xsl:value-of select="str[@name='uid']"/>
                </xsl:otherwise>
              </xsl:choose>
          </a>
          </td>
          <td class="referrer">
          <a>
            <xsl:attribute name="href">
              <xsl:value-of select="str[@name='referrer']"/>
            </xsl:attribute>
            <xsl:value-of select="str[@name='referrer']"/>
          </a>
          </td>
        </tr>
      </xsl:for-each>
    </table>
    </div>
  </xsl:template>

</xsl:stylesheet>
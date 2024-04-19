<?php
/*
Filename:         GoogleXmlSitemap.php
Author:           Francis Tsao
Date Created:     08/01/2008
Purpose:          Creates a Google XML <urlset> files and a <sitemapindex> for the number of URLs
                  added.
History:          04/09/2024 - modernized from PHP 5.6 to PHP 8.2 and using XMLWriter interface [ft]
                  12/06/2011 - commented out <changefreq> tag as Google does not pay
                               attention to this according to N*** B* B*** [ft]

TODO: support/checking for MAX_FILESIZE
*/


/**
 * GoogleXmlSitemap - create Google XML Sitemap (sitemapindex and urlset file(s))
 *
 * Sample usage
 * <code>
 * $my_sitemap = new Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap($http_hostname = 'www.testdomain.com');
 * $my_sitemap->setUseHttpsUrls(true); // use "https" mode for your URLs or plain "http"
 * $my_sitemap->setSitemapFilenamePrefix('mysitemap'); // set name of sitemap file minus ".xml" (e.g. mysitemap.xml)
 * foreach ($url_array as $url)
 * {
 *    $my_sitemap->addUrl($url = "$query_data->url/", $lastmod = '', $changefreq = '', $priority = '');
 * }
 * 
 * // signal when done adding URLs, so we can generate the sitemap index file (table of contents)
 *  $my_sitemap->endXmlDoc();
 * </code>
 *
 * @author Francis Tsao
 */
namespace Dialeleven\PhpGoogleXmlSitemap;

use Exception;
use InvalidArgumentException;
use XMLWriter;


require_once 'AbstractGoogleSitemap.php';



class GoogleXmlSitemap extends GoogleSitemap
{
   /**
     * Start our <url> element and child tags 'loc,' 'lastmod,' 'changefreq,' and 'priority' as needed
     * 
     * e.g.
     *    <url>
     *       <loc>http://www.mydomain.com/someurl/</loc>
     *       <lastmod>2024-04-06</lastmod>
     *       <changefreq>weekly</changefreq>
     *       <priority>1.0</priority>
     *    </url>
     * @param string $loc
     * @param array $tags_arr
     * @param array $special_tags_arr
     * @access public
     * @return bool
     */
    //public function addUrl(string $loc, string $lastmod = '', string $changefreq = '', string $priority = ''): bool
    public function addUrl(string $loc, array $tags_arr = array(), array $special_tags_arr = array()): bool
    {
      // safety check for extra param not needed for XML
      if (is_array($special_tags_arr) AND count($special_tags_arr) > 0)
         throw new Exception("\$special_tags_arr is unsupported for sitemap type '$this->sitemap_type' and should not be passed as an argument");

      
      // check if we need a new XML file
      $this->startNewUrlsetXmlFile();

      // Start the 'url' element
      $this->xml_writer->startElement('url');

      if (empty($loc))
      throw new Exception("ERROR: url cannot be empty");

      // TODO: strip/add leading trailing slash after http host like https://www.domain.com/?


      $this->xml_writer->writeElement('loc', $this->url_scheme_host . $loc);

      if (array_key_exists('lastmod', $tags_arr))
         $this->xml_writer->writeElement('lastmod', $tags_arr['lastmod']);

      if (array_key_exists('changefreq', $tags_arr))
         $this->xml_writer->writeElement('changefreq', $tags_arr['changefreq']);

      if (array_key_exists('priority', $tags_arr))
         $this->xml_writer->writeElement('priority', $tags_arr['priority']);

      // end </url> element
      $this->endUrl();
      
  
       return true;
   }
}
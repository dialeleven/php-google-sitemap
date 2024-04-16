<?php
/*
Filename:         GoogleImageSitemap.php
Author:           Francis Tsao
Date Created:     04/14/2024
Purpose:          Creates a Google image sitemap <urlset> files and a <sitemapindex> for the number of URLs
                  added.

TODO: support/checking for MAX_FILESIZE
*/


/**
 * GoogleImageSitemap - create Google XML Sitemap (sitemapindex and urlset file(s))
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



class GoogleImageSitemap extends GoogleSitemap
{
   /**
     * Add our image:image and image:loc tags
     * 
     * e.g.
     *    <url>
     *       <loc>https://example.com/sample1.html</loc>
     *       <image:image>
     *          <image:loc>https://example.com/image.jpg</image:loc>
     *       </image:image>
     *    </url>
     * @param string $image_loc (e.g. https://example.com/image.jpg)
     * @access public
     * @return bool
     */   
   public function addImage(string $image_loc): bool
   {
      $this->xml_writer->startElement('image:image'); // Start '<image:image>'
      $this->xml_writer->writeElement('image:loc', $image_loc);
      $this->xml_writer->endElement(); // End the '</image:image>' element

      return true;
   }
}
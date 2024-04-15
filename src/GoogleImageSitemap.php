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
     * Start our <url> element and child tag <loc> only as we don't know how 
     * many image(s) are inside the url tag
     * 
     * e.g.
     *    <url>
     *       <loc>https://example.com/sample1.html</loc>
     *       <image:image>
     *          <image:loc>https://example.com/image.jpg</image:loc>
     *       </image:image>
     *       <image:image>
     *          <image:loc>https://example.com/photo.jpg</image:loc>
     *       </image:image>
     *    </url>
     * @access public
     * @return bool
     */   
    public function addUrl(string $url, string $lastmod = '', string $changefreq = '', string $priority = ''): bool
    {
       // check if we need a new XML file
       $this->startNewUrlsetXmlFile();

       // Start the 'url' element
       $this->xml_writer->startElement('url');
 
      if (empty($url))
        throw new Exception("ERROR: url cannot be empty");

      // TODO: strip/add leading trailing slash after http host like https://www.domain.com/

      // <loc> is required among all sitemap types (xml, image, video, news)
      $this->xml_writer->writeElement('loc', $this->url_scheme_host . $url);
 
      return true;
   }


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


   public function endUrl(): bool
   {
      // End the 'url' element
      $this->xml_writer->endElement();

      // increment URL count so we can start a new <urlset> XML file if needed
      ++$this->url_count_current;
      ++$this->url_count_total;

      return true;
   }
}
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
 * GoogleImageSitemap - create Google Image Sitemap (sitemapindex and urlset file(s))
 *
 * TODO: Update example below
 * 
 * Sample usage
 * <code>
 * $my_sitemap = new Dialeleven\PhpGoogleSitemap\GoogleImageSitemap($http_hostname = 'www.testdomain.com');
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
namespace Dialeleven\PhpGoogleSitemap;

use Exception;
use InvalidArgumentException;
use XMLWriter;


require_once 'AbstractGoogleSitemap.php';

/*
// sample usage - single image on a page
$mysitemap->addUrl($loc = 'http://example.com/page.html');
$mysitemap->addImage($loc = 'http://example.com/single_image.jpg');


// sample usage - multiple images on a page
$mysitemap->addUrl($loc = 'http://example.com/page.html');
$mysitemap->addImage($loc = 'http://example.com/multiple_images.jpg');
$mysitemap->addImage($loc = 'http://example.com/another_image.jpg');
*/

class GoogleImageSitemap extends GoogleSitemap
{
   // Separate counter to track how many <url>s we have. Usual 'url_count_current' property
   // won't work since the image sitemaps can have multiple images causing endURL() to be
   // called later on and not incrementing the counter until later on.
   protected $image_sitemap_url_count = 0;

   /**
     * Start our <url> element and child tags for image sitemap
     * 
     * e.g.
     *    <url>
     *       <loc>https://example.com/sample1.html</loc>
     *       <image:image>
     *          <image:loc>https://example.com/image.jpg</image:loc>
     *       </image:image>
     *    </url>
     * @param string $loc
     * @param array $tags_arr
     * @param array $special_tags_arr
     * @access public
     * @return bool
     */
    public function addUrl(string $loc, array $tags_arr = array(), array $special_tags_arr = array()): bool
    {
      // check for special_tags_arr which is FOR VIDEO SITEMAPS ONLY with special child tag handling
      if (is_array($special_tags_arr) AND count($special_tags_arr) > 0)
         throw new Exception("\$special_tags_arr is unsupported for sitemap type '$this->sitemap_type' and should not be passed as an argument");
      // image sitemap doesn't have to pass tags_arr
      else if (is_array($tags_arr) AND count($tags_arr) > 0)
         throw new Exception("\$tags_arr is unsupported for sitemap type '$this->sitemap_type' and should not be passed as an argument");
      
      // loc is empty
      if (empty($loc))
         throw new Exception("ERROR: loc cannot be empty");
      
         
      // auto close </url> element for subsequent <url>s being added to simplify using the class
      if ($this->image_sitemap_url_count > 0)
         $this->endUrl();

      // check if we need a new XML file
      $this->startNewUrlsetXmlFile();

      // Start the 'url' element
      $this->xml_writer->startElement('url');

      // TODO: strip/add leading trailing slash after http host like https://www.domain.com/?


      $this->xml_writer->writeElement('loc', $this->url_scheme_host . $loc);

      // keep track of how many <url> elements we have to auto close the </url> on subsequent call
      ++$this->image_sitemap_url_count;
  
       return true;
   }


   /**
     * Add our image:image and image:loc tags
     * 
     * TODO: IDEA - pass images as an array to "addUrl()" method or a addImageUrl($loc, $img_arr) method?
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
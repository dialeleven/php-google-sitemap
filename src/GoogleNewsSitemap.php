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



class GoogleNewsSitemap extends GoogleSitemap
{
   /**
     * Start our <url> element and child tags for a news sitemap
     * 
     * e.g.
     *    <url>
     *       <loc>http://www.example.org/business/article55.html</loc>
     *       <news:news>
     *          <news:publication>
     *             <news:name>The Example Times</news:name>
     *             <news:language>en</news:language>
     *          </news:publication>
     *          <news:publication_date>2008-12-23</news:publication_date>
     *          <news:title>Companies A, B in Merger Talks</news:title>
     *       </news:news>
     *    </url>
     * @param string $loc
     * @param array $tags_arr
     * @param array $special_tags_arr
     * @access public
     * @return bool
     */
   public function addUrl(string $loc, array $tags_arr = array(), array $special_tags_arr = array()): bool
   {
      if (empty($loc))
         throw new Exception("ERROR: loc cannot be empty");

      // safety check for special_tags_arr which is FOR VIDEO SITEMAPS ONLY with special child tag handling
      if (is_array($special_tags_arr) AND count($special_tags_arr) > 0)
         throw new Exception("\$special_tags_arr is unsupported for sitemap type '$this->sitemap_type' and should not be passed as an argument");

      
      // date formats - regular exp matches for allowed formats per Google documentation
      $formats = array(
                        '/^\d{4}-\d{2}-\d{2}$/',                                      // YYYY-MM-DD
                        '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}[+-]\d{2}:\d{2}$/',           // YYYY-MM-DDThh:mmTZD
                        '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/',     // YYYY-MM-DDThh:mm:ssTZD
                        '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d+[+-]\d{2}:\d{2}$/' // YYYY-MM-DDThh:mm:ss.sTZD
                      );      
      
      // list of required child tags within <url>
      $required_tags_arr = array('name', 'language', 'publication_date', 'title');

      // verify each of our required child tags for news exists in the passed tags array
      foreach ($required_tags_arr AS $required_key => $value)
      {
         $value = trim($value);

         // child tag name does not exist in our required list of elements
         if (!array_key_exists($required_key, $tags_arr))
            throw new Exception("A required child tag '$required_key' was not found in the passed array for '\$tags_arr' - " . print_r($tags_arr, true));
         // disallow empty strings
         else if (empty($value))
            throw new Exception("A value is required for '$required_key' - value passed was '$value'");
         // check for valid publication_date
         else if ($required_key == 'publication_date')
         {
            // Check if the input string matches any of the specified formats
            foreach ($formats AS $format) {
               if (preg_match($format, $value)) {
                  $valid_date_string_found = true;
               }
            }

            if (!$valid_date_string_found)
               throw new Exception("Invalid publication_date passed '$value' - publication_date should 
                                    follow 'YYYY-MM-DD,' 'YYYY-MM-DDThh:mmTZD,' 'YYYY-MM-DDThh:mm:ssTZD,' 
                                    or 'YYYY-MM-DDThh:mm:ss.sTZD' format.");
         }
      }
      
      // check if we need a new XML file
      $this->startNewUrlsetXmlFile();

      // Start the 'url' element
      $this->xml_writer->startElement('url');

      // TODO: strip/add leading trailing slash after http host like https://www.domain.com/?


      $this->xml_writer->writeElement('loc', $this->url_scheme_host . $loc); // Start <loc>
      $this->xml_writer->startElement('news:news'); // Start '<news:news>'
      $this->xml_writer->startElement('news:publication'); // Start '<news:publication>'


      if (array_key_exists('name', $tags_arr))
         $this->xml_writer->writeElement('news:name', $tags_arr['name']);

      if (array_key_exists('language', $tags_arr))
         $this->xml_writer->writeElement('news:language', $tags_arr['language']);

      $this->xml_writer->endElement(); // end </news:publication>
      
      if (array_key_exists('publication_date', $tags_arr))
         $this->xml_writer->writeElement('news:publication_date', $tags_arr['publication_date']);

      if (array_key_exists('title', $tags_arr))
         $this->xml_writer->writeElement('news:title', $tags_arr['title']);


      $this->xml_writer->endElement(); // End the '</news:news>' element

      // for XML, news and video(?) sitemaps, we can end the </url> tag at this point since there
      // is only one group of child elements vs image sitemaps which can have 
      // one or more child elements (i.e. multiple images on a page)
      if ( in_array($this->sitemap_type, array('xml', 'news', 'video')) )
         $this->endUrl();
  
       return true;
   }
}
<?php
/*
Filename:         GoogleNewsSitemap.php
Author:           Francis Tsao
Date Created:     04/14/2024
Purpose:          Creates a Google news sitemap <urlset> files and a <sitemapindex> for the number of URLs
                  added.

TODO: support/checking for MAX_FILESIZE
*/


/**
 * GoogleNewsSitemap - create Google News Sitemap (sitemapindex and urlset file(s))
 * 
 * Sample usage
 * <code>
 * $my_sitemap = new Dialeleven\PhpGoogleSitemap\GoogleNewsSitemap($sitemap_type = 'video', 
 *                                                                 $http_hostname = 'www.testdomain.com', 
 *                                                                 $xml_files_dir = $_SERVER['DOCUMENT_ROOT'] . '/public/sitemaps');
 * $my_sitemap->setUseHttpsUrls(true); // use "https" mode for your URLs or plain "http"
 * $my_sitemap->setSitemapFilenamePrefix('mynews_sitemap'); // set name of sitemap file minus ".xml" (e.g. mysitemap.xml)
 * $my_sitemap->setUseGzip($use_gzip = false); // gzip the urlset files to reduce file sizes (true/false)
 * 
 * foreach ($url_array as $url)
 * {
 *    $my_sitemap->addUrl($url = "url-to-your-page-minus-hostname/", 
 *                        $tags_arr = array(
 *                                              'name' => "The Example Times", 
 *                                              'language' => 'en', 
 *                                              'publication_date' => '2024-04-19',
 *                                              'title' => "Example Article Title #$i"
 *                                          ));
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



class GoogleNewsSitemap extends GoogleSitemap
{
   // list of required child tags within <url>
   protected $required_tags_arr = array('name', 'language', 'publication_date', 'title');

   
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
      if ( empty ( trim($loc) ) )
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

      // verify each of our required child tags for news exists in the passed tags array
      foreach ($this->required_tags_arr AS $required_key)
      {

         // child tag name does not exist in our required list of elements
         if (!array_key_exists($required_key, $tags_arr))
            throw new Exception("A required child tag '$required_key' was not found in the passed array for '\$tags_arr' - " . print_r($tags_arr, true));
         // disallow empty strings
         else if (empty( trim($tags_arr[$required_key] ?? '') ))
            throw new Exception("A value is required for '$required_key' - value passed was '{$tags_arr[$required_key]}'");
         // check for valid publication_date
         else if ($required_key == 'publication_date')
         {
            // Check if the input string matches any of the specified formats
            foreach ($formats AS $format) {
               if ( preg_match ( $format, trim($tags_arr[$required_key] ?? '') ) ) {
                  $valid_date_string_found = true;
               }
            }

            if (!$valid_date_string_found)
               throw new Exception("Invalid publication_date passed '{$tags_arr[$required_key]}' - publication_date should 
                                    follow 'YYYY-MM-DD,' 'YYYY-MM-DDThh:mmTZD,' 'YYYY-MM-DDThh:mm:ssTZD,' 
                                    or 'YYYY-MM-DDThh:mm:ss.sTZD' format.");
         }
      }
      
      // check if we need a new XML file
      $this->startNewUrlsetXmlFile();

      // Start the '<url>' element
      $this->xml_writer->startElement('url');

         $this->xml_writer->writeElement('loc', $this->url_scheme_host . $loc); // Start <loc>
         $this->xml_writer->startElement('news:news'); // Start '<news:news>'
      
            $this->xml_writer->startElement('news:publication'); // Start '<news:publication>'

               // 'news:name' passed with value, write xml tag/elm
               if (array_key_exists('name', $tags_arr) AND $tags_arr['name'])
                  $this->xml_writer->writeElement('news:name', $tags_arr['name']);

               // 'news:name' passed with value, write xml tag/elm
               if (array_key_exists('language', $tags_arr) AND $tags_arr['language'])
                  $this->xml_writer->writeElement('news:language', $tags_arr['language']);

            $this->xml_writer->endElement(); // end </news:publication>
      
         // 'news:publication_date' passed with value, write xml tag/elm
         if (array_key_exists('publication_date', $tags_arr) AND $tags_arr['publication_date'])
            $this->xml_writer->writeElement('news:publication_date', $tags_arr['publication_date']);

         // 'news:title' passed with value, write xml tag/elm
         if (array_key_exists('title', $tags_arr) AND $tags_arr['title'])
            $this->xml_writer->writeElement('news:title', $tags_arr['title']);


      $this->xml_writer->endElement(); // End the '</news:news>' element

      // end </url> element
      $this->endUrl();
  
      return true;
   }
}
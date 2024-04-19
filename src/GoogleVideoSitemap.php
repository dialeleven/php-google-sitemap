<?php
/*
Filename:         GoogleVideoSitemap.php
Author:           Francis Tsao
Date Created:     04/19/2024
Purpose:          Creates a Google video sitemap <urlset> files and a <sitemapindex> for the number of URLs
                  added.

TODO: support/checking for MAX_FILESIZE
*/


/**
 * GoogleVideoSitemap - create Google Video Sitemap (sitemapindex and urlset file(s))
 * 
 * Sample usage
 * <code>
 * $my_sitemap = new Dialeleven\PhpGoogleSitemap\GoogleVideoSitemap($sitemap_type = 'video', 
 *                                                                  $http_hostname = 'www.testdomain.com', 
 *                                                                  $xml_files_dir = $_SERVER['DOCUMENT_ROOT'] . '/public/sitemaps'
 * $my_sitemap->setUseHttpsUrls(true); // use "https" mode for your URLs or plain "http"
 * $my_sitemap->setSitemapFilenamePrefix('myvideo_sitemap'); // set name of sitemap file minus ".xml" (e.g. mysitemap.xml)
 * $my_sitemap->setUseGzip($use_gzip = false); // gzip the urlset files to reduce file sizes (true/false)
 * 
 * foreach ($url_array as $url)
 * {
 *    $my_sitemap->addUrl($url = "$query_data->url/", 
 *                        $tags_arr = array(
 *                                            // these 5 are required (check class properties for required vs. optional tags)
 *                                            'thumbnail_loc' => "https://example.com/thumbs/$i.jpg", 
 *                                            'title' => "Video Title #$i", 
 *                                            'description' => "Video description #$i",
 *                                            'content_loc' => "http://streamserver.example.com/video$1.mp4",
 *                                            'player_loc' => "https://example.com/videoplayer.php?video=$i"
 *                                            ),
 *                       $special_tags_arr = array(
 *                                                  array('restriction', 'relationship', 'allow', 'IE GB US CA'),
 *                                                  array('price', 'currency', 'EUR', '1.99'), 
 *                                                  array('uploader', 'info', "https://example.com/users/user$i", "Username$i")
 *                                                  ));
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



class GoogleVideoSitemap extends GoogleSitemap
{
   // required video element tags
   protected $required_tags_arr = array('thumbnail_loc', 'title', 'description', 'content_loc', 'player_loc');

   // allowed regular element/tags
   protected $allowed_tags_arr = array('thumbnail_loc', 'title', 'description', 'content_loc', 'player_loc',
                                       'duration', 'rating', 'view_count', 'publication_date', 'family_friendly',
                                       'requires_subscription', 'live');
   
   // allowed special element/tags
   protected $allowed_special_tags_arr = array('restriction', 'price', 'uploader');

   /**
     * Add our <video:video> and child news tags
     * https://developers.google.com/search/docs/crawling-indexing/sitemaps/video-sitemaps
     * 
     * e.g.
     *    <url>
     *       <!-- required video tags -->
     *       <video:video>
     *          <video:thumbnail_loc>https://www.example.com/thumbs/345.jpg</video:thumbnail_loc>
     *          <video:title>Grilling steaks for winter</video:title>
     *          <video:description>
     *            In the freezing cold, Roman shows you how to get perfectly done steaks every time.
     *          </video:description>
     *          <video:content_loc>
     *            http://streamserver.example.com/video345.mp4
     *          </video:content_loc>
     *          <video:player_loc>
     *            https://www.example.com/videoplayer.php?video=345
     *          </video:player_loc>
     *       </video:video>
     * 
     *       <!-- optional video tags -->
     *       <video:video>
     *          <video:duration>600</video:duration>
     *          <video:expiration_date>2021-11-05T19:20:30+08:00</video:expiration_date>
     *          <video:rating>4.2</video:rating>
     *          <video:view_count>12345</video:view_count>
     *          <video:publication_date>2007-11-05T19:20:30+08:00</video:publication_date>
     *          <video:family_friendly>yes</video:family_friendly>
     *          <!-- format for "restriction," "price," and "uploader" are different -->
     *          <video:restriction relationship="allow">IE GB US CA</video:restriction>
     *          <video:price currency="EUR">1.99</video:price>
     *          <video:requires_subscription>yes</video:requires_subscription>
     *          <video:uploader
     *            info="https://www.example.com/users/grillymcgrillerson">GrillyMcGrillerson
     *          </video:uploader>
     *          <video:live>no</video:live>
     *       </video:video>
     *    </url>
     * @param string $
     * @access public
     * @return bool
     */
    public function addUrl(string $loc, array $tags_arr = array(), array $special_tags_arr = array()): bool
    {
      if (empty($loc))
          throw new Exception("ERROR: loc cannot be empty");
 
       
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
         $value = trim($tags_arr[$required_key] ?? '');

         // child tag name does not exist in our required list of elements
         if (!array_key_exists($required_key, $tags_arr))
            throw new Exception("A required child tag '$required_key' was not found in the passed array for '\$tags_arr' - " . print_r($tags_arr, true));
         // disallow empty strings
         else if (empty($value))
            throw new Exception("A value is required for '$required_key' - value passed was '$value'");
      }
       
      // check if we need a new XML file
      $this->startNewUrlsetXmlFile();

      // Start the 'url' element
      $this->xml_writer->startElement('url');

      // TODO: strip/add leading trailing slash after http host like https://www.domain.com/?


      $this->xml_writer->writeElement('loc', $this->url_scheme_host . $loc); // Start <loc>
      $this->xml_writer->startElement('video:video'); // Start '<video:video>'

      // process the regular elements/tags array
      if (is_array($tags_arr))
      {
         foreach ($tags_arr AS $key => $val)
         {
            // we are expecting two (2) elements for each array
            if (empty(trim($key)) OR empty(trim($val)))
               throw new Exception("\$tags_arr expects each array to contain 2 elements. Passed values are key ($key) => value ($val)");
            
            // video element name does not exist in our allowed list
            if (!in_array($key, $this->allowed_tags_arr))
               throw new Exception("'$key' $val is not an allowed video element. Allowed values include: " . print_r($this->allowed_tags_arr, true));
            else
               $this->xml_writer->writeElement('video:' . $key, $val);
         }
      }

      // process the special elements/tags array
      if (is_array($special_tags_arr))
      {
         foreach ($special_tags_arr AS $arr)
         {
            // we are expecting four (4) elements for each array
            if (count($arr) != 4)
               throw new Exception("\$special_tags_arr expects each array to contain 4 elements. Passed array contains " . 
                                   count($arr) . " element(s) and contains " . print_r($arr, true));
            
            // video element name does not exist in our allowed list
            if (!in_array($arr[0], $this->allowed_special_tags_arr))
               throw new Exception("'{$arr[0]}' is not an allowed video element. Allowed values include: " . print_r($this->allowed_special_tags_arr, true));
            // write special video element tags
            else
            {
               $this->xml_writer->startElementNs('video', $arr[0], null);
                  $this->xml_writer->writeAttribute($arr[1], $arr[2]);
                  // Write the text content of the video:ELEMENT_NAME element
                  $this->xml_writer->text($arr[3]);
               // Close the video:ELEMENT_NAME element
               $this->xml_writer->endElement();
            }
         }
      }

 
       $this->xml_writer->endElement(); // End the '</video:video>' element
 
       // end </url> element
       $this->endUrl();
   
       return true;
   }
}
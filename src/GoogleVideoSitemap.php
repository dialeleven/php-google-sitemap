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
       foreach ($this->required_tags_arr AS $required_key => $value)
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
 
       // end </url> element
       $this->endUrl();
   
       return true;
   }


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
   /*
   $optional_vid_regular_attr_arr = [
                                       array('duration', '600'),
                                       array('expiration_date', '2021-11-05T19:20:30+08:00')
   ];

   $optional_vid_special_attr_arr = [
                                       array('restriction', 'relationship', 'allow', 'IE GB US CA'),
                                       array('price', 'currency', 'EUR', '1.99'),
                                       array('uploader', 'info', 'https://www.example.com/users/grillymcgrillerson', 'GrillyMcGrillerson')
                                    ];
   */

   public function addVideo(string $thumbnail_loc, string $title, string $description, string $content_loc, string $player_loc, 
                            array $optional_vid_regular_attr_arr = array(), array $optional_vid_special_attr_arr = array()): bool
   {
      // ensure required video elements are not blank
      if ( empty($thumbnail_loc) OR empty($title) OR empty($description) OR empty($content_loc) OR empty($player_loc) )
         throw new Exception("Required video element(s) are missing: thumbnail_loc ($thumbnail_loc), 
                              title ($title), description ($description), content_loc ($content_loc), 
                              player_loc ($player_loc)");
      
      $this->xml_writer->writeElement('video:thumbnail_loc', $thumbnail_loc);
      $this->xml_writer->writeElement('video:title', $title);
      $this->xml_writer->writeElement('video:description', $description);
      $this->xml_writer->writeElement('video:content_loc', $content_loc);
      $this->xml_writer->writeElement('video:player_loc', $player_loc);
      
      if (is_array($optional_vid_regular_attr_arr))
      {
         foreach ($optional_vid_regular_attr_arr AS $arr)
         {
            // we are expecting two (2) elements for each array
            if (count($arr) != 2)
               throw new Exception("\$optional_vid_regular_attr_arr expects each array to contain 2 elements. Passed array contains " . 
                                  count($arr) . " element(s) and contains " . print_r($arr, true));
            
            $this->xml_writer->writeElement('video:' . $arr[0], $arr[1]);
         }
         // do something
      }

      if (is_array($optional_vid_special_attr_arr))
      {
         // do something
      }
      
      return true;
   }
}
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
       return true;
   }
}
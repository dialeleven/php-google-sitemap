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
     * Add our <news:news> and child news tags. ALL of the following are REQUIRED
     * (at the moment).
     * https://developers.google.com/search/docs/crawling-indexing/sitemaps/news-sitemap
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
     * @param string $news_name (e.g. The Example Times)
     * @param string $news_pubdate YYYY-MM-DD, YYYY-MM-DDThh:mmTZD, YYYY-MM-DDThh:mm:ssTZD, YYY-MM-DDThh:mm:ss.sTZD
     * @param string $news_title The title of the news article
     * @param string $news_lang 2 or 3 letter ISO 639 language code (e.g. 'en')
     * @access public
     * @return bool
     */   
   public function addNews(string $news_name, string $news_pubdate, string $news_title, string $news_lang = 'en'): bool
   {
      // check for empty news elements
      if (empty($news_name) OR empty($news_lang) OR empty($news_pubdate) OR empty($news_title))
         throw new Exception("News name ($news_name), news language ($news_lang), news pubdate ($news_pubdate), and news title ($news_title) are required");
      
      // Regular expressions for each date format
      $formats = array(
                        '/^\d{4}-\d{2}-\d{2}$/',                                      // YYYY-MM-DD
                        '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}[+-]\d{2}:\d{2}$/',           // YYYY-MM-DDThh:mmTZD
                        '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/',     // YYYY-MM-DDThh:mm:ssTZD
                        '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d+[+-]\d{2}:\d{2}$/' // YYYY-MM-DDThh:mm:ss.sTZD
                      );
      
      // Check if the input string matches any of the specified formats
      foreach ($formats as $format) {
         if (preg_match($format, $news_pubdate)) {
            $valid_date_string_found = true;
         }
      }

      // a valid date format was not found
      if (!$valid_date_string_found)
         throw new Exception("Invalid news pubdate passed '$news_pubdate' - pubdate should 
                              follow 'YYYY-MM-DD,' 'YYYY-MM-DDThh:mmTZD,' 'YYYY-MM-DDThh:mm:ssTZD,' 
                              or 'YYYY-MM-DDThh:mm:ss.sTZD' format.");

      $this->xml_writer->startElement('news:news'); // Start '<news:news>'

         $this->xml_writer->startElement('news:publication');
            $this->xml_writer->writeElement('news:name', $news_name);
            $this->xml_writer->writeElement('news:language', $news_lang);
         $this->xml_writer->endElement();

         $this->xml_writer->writeElement('news:publication_date', $news_pubdate);
         $this->xml_writer->writeElement('news:title', $news_title);
      $this->xml_writer->endElement(); // End the '</news:news>' element
 
       return true;
    }
}
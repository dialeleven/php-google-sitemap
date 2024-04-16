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
   
}
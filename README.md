# PHP Google Sitemap - Overview

A PHP class to generate a multiple types of sitemaps. This supports creating a [Google XML Sitemap](https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap), [image sitemap](https://developers.google.com/search/docs/crawling-indexing/sitemaps/image-sitemaps), [news sitemap](https://developers.google.com/search/docs/crawling-indexing/sitemaps/news-sitemap), and [video sitemap](https://developers.google.com/search/docs/crawling-indexing/sitemaps/video-sitemaps). Most likely you're after creating an XML sitemap, so we'll go over that here.

Briefly, a Google Sitemap (XML/image/news/video) contains two parts:

1. A [Sitemap Index](https://developers.google.com/search/docs/crawling-indexing/sitemaps/large-sitemaps) XML file - a table of contents listing each *urlset* file (this example is in the next bullet point). In the sample sitemap index XML file below, one *urlset* file is named *sitemap1.xml.gz*. Note that we're gzipping the resulting XML file in the example below to reduce file sizes. The XML file can be left uncompressed, but will get rather large with 50,000 URLs in one file which is the maximum per sitemap file (~20MB uncompressed for an XML sitemap). For example:

```
   <?xml version="1.0" encoding="UTF-8"?>
   <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
      <sitemap>
         <loc>http://www.mydomain.com/someurl/sitemap1.xml.gz</loc>
         <lastmod>2024-04-06T21:23:02+00:00</lastmod>
      </sitemap>
      <sitemap>
         <loc>http://www.mydomain.com/someurl/sitemap2.xml.gz</loc>
         <lastmod>2024-04-06T21:23:02+00:00</lastmod>
      </sitemap>
   </sitemapindex>
```

2. A **urlset** XML file(s) - a list of each of your website's URLs. For example sitemap1.xml may contain something like this for an XML sitemap listing your website's pages/URLs:

```
   <?xml version="1.0" encoding="UTF-8"?>
   <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
      <url>
         <loc>http://www.mydomain.com/someurl/</loc>
         <lastmod>2024-04-06</lastmod>
         <changefreq>weekly</changefreq>
         <priority>1.0</priority>
      </url>
      <url>
         <loc>http://www.mydomain.com/anotherurl/</loc>
         <lastmod>2024-04-07</lastmod>
         <changefreq>weekly</changefreq>
         <priority>1.0</priority>
      </url>
   </urlset>
```

As you can see the structure is quite similar with the differences being the 'sitemapindex' vs 'urlset' as our opening tag (attributes are identical). The tags contained in our sitemapindex/urlset will contain either a 'sitemap' container tag or 'url' container tag.

## Prerequisties

This project uses [composer](https://getcomposer.org/) to autoload class files, but have been manually **include**d to be on the safe side. 
One should be able to use this class without composer, but just a forewarning if you have any issues.


## How to use the PHP Google XML Sitemap Class

> [!IMPORTANT]
> Files you'll need:
> * /src/AbstractGoogleSitemap.php
> * /src/GoogleImageSitemap.php
> * /src/GoogleNewsSitemap.php
> * /src/GoogleVideoSitemap.php
> * /src/GoogleXmlSitemap.php

### Sample Usage
Start off with the required namespace (e.g. "use _____;") and include the appropriate class src for the sitemap type you are using. For an XML sitemap, use the GoogleXmlSitemap.php PHP class as shown below.
```
   use Dialeleven\PhpGoogleSitemap;

   // adjust the path to the PHP class depending on your site architecture
   include_once $_SERVER['DOCUMENT_ROOT'] . '/src/GoogleXmlSitemap.php';
```
For a news sitemap you'll change the *include_once* src to use '/src/GoogleNewsSitemap.php' instead. For example:
```
   use Dialeleven\PhpGoogleSitemap;

   // adjust the path to the PHP class depending on your site architecture
   include_once $_SERVER['DOCUMENT_ROOT'] . '/src/GoogleNewsSitemap.php';
```


**Create new instance of GoogleSitemap Class**

By default, resulting XML files will be created in the same path as your script using the PHP Google XML Sitemap class if $xml_files_dir is blank or not passed as an argument.

```
   // create new instance of the PHP Google XML Sitemap class (using default save dir - whatever script your path is in)
   /*
   SPECIFY YOUR SITEMAP TYPE:
      - xml (for most people, you'll use this unless you need a speciality sitemap type like images, etc..)
      - image
      - video
      - news
   */
   $my_sitemap = new Dialeleven\PhpGoogleSitemap\GoogleXmlSitemap($sitemap_type = 'xml', $http_hostname = $_SERVER['HTTP_HOST'], $xml_files_dir = '');
```

**OR**

To save the resulting XML files saved in a subdirectory, pass the full DOCUMENT_ROOT and directory path(s)

```
   // create new instance of the PHP Google Sitemap class (using specified XML save directory)
   $my_sitemap = new Dialeleven\PhpGoogleSitemap\GoogleXmlSitemap($sitemap_type = 'xml', $http_hostname = $_SERVER['HTTP_HOST'], $xml_files_dir = $_SERVER['DOCUMENT_ROOT'] . '/public/sitemaps');

```

Remaining logic for usage (please adjust the sample code depending on if you're retrieving the URLs from a database or you have it stored as an array):
```
   // Some configuratation methods for your sitemap file(s) to be generated.
   $my_sitemap->setUseHttpsUrls(true); // use "https" scheme (true) for your URLs or plain "http" (false)
   $my_sitemap->setSitemapFilenamePrefix('mysitemap'); // set name of sitemap file(s) minus ".xml" (e.g. mysitemap.xml)
   $my_sitemap->setUseGzip($use_gzip = false); // compress the urlset files to save space (true/false)

   // you might store your url arrays like this
   $url_md_arr = array(
      array('http://www.domain.com/url1/', '2024-01-01', 'weekly', '0.5'),
      array('http://www.domain.com/url2/', '2024-01-01', 'weekly', '0.5'),
      array('http://www.domain.com/url3/', '2024-01-01', 'weekly', '0.5')
   );

   // you probably want to pull your URLs from your database though (e.g. MySQL, Postgres, Mongo, etc...)
   /*
   INCLUDE YOUR DATABASE LOGIC HERE TO PULL YOUR URLs FROM THE REQUIRED TABLE(s)...
   */


   // loop through your URLs from your array or preferred database (array example for simplicity)
   foreach ($url_md_arr as $url_arr)
   {
      // the important part - adding each URL (replace sample values from your DB/array)
      $my_sitemap->addUrl($loc = $url_arr[0], $tags_arr = array('lastmod' => $url_arr[1], 'changefreq' => $url_arr[2], 'priority' => $url_arr[3]));
   }


   // signal when done adding URLs, so we can generate the sitemap index file (table of contents)
   $my_sitemap->endXmlDoc();
```
**That's all there is to it!**

## About addURL() Method
> [!NOTE]
> The **addURL()** method only requires **$loc** to be passed as an argument. 
> The other arguments lastmod, changefreq, and priority are optional and can
> be omitted.

Instead of calling the method like:
```
$my_sitemap->addUrl($loc = $url_arr[0], array('lastmod' = $url_arr[1], 'changefreq' = $url_arr[2], 'priority' = $url_arr[3]));
```

You can just use the following if you don't need lastmod/changefreq/priority:
```
$my_sitemap->addUrl($loc = $url_arr[0]);
```

## XML Tag Definitions for XML Sitemaps (e.g. lastmod)

> [!NOTE]
> If you choose to pass other arguments to addURL() like **lastmod**, **changefreq**, or **priority**, please refer to the following for valid values.

Taken from https://www.sitemaps.org/protocol.html#xmlTagDefinitions

```<lastmod>``` optional.
The date of last modification of the page. This **date should be in W3C Datetime format**. This format allows you to omit the time portion, if desired, and use **YYYY-MM-DD**.

Note that the date must be set to the date the linked page was last modified, not when the sitemap is generated.

Note also that this tag is separate from the If-Modified-Since (304) header the server can return, and search engines may use the information from both sources differently.

```<changefreq>``` optional.
How frequently the page is likely to change. This value provides general information to search engines and may not correlate exactly to how often they crawl the page. Valid values are:

* always
* hourly
* daily
* weekly
* monthly
* yearly
* never

```<priority>``` optional.
The priority of this URL relative to other URLs on your site. Valid values range from 0.0 to 1.0. This value does not affect how your pages are compared to pages on other sites—it only lets the search engines know which pages you deem most important for the crawlers.

The default priority of a page is 0.5.


## Summary

As you can see, the usage is pretty simple. 

1. Instantiate the class.
2. A couple configuration methods.
3. Set up your loop and iterate through your array or database records.
4. Call addUrl() method until you're out of URLs to add.
5. Wrap up by calling endXmlDoc() which will generate your sitemapindex TOC.
6. Submit your sitemapindex XML file to Google. Done!

This was rewritten from PHP 5.6 to 8 and greatly simplified from a class that
did too much and was rather confusing to read and maintain even though it worked.
It cut down the lines of code by about 200-300. Hope you find this class useful.


## Sample Scripts

The following sample scripts instantiating each type of sitemap class and basic logic can be found under /public to help get you started with each sitemap type supported (XML/image/video/news):
- 1google_image_sitemap_test.php
- 1google_news_sitemap_test.php
- 1google_video_sitemap_test.php
- 1google_xml_sitemap_test.php


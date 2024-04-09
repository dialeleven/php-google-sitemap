# PHP Google XML Sitemap Overview

A PHP class to generate a [Google XML Sitemap](https://developers.google.com/search/docs/crawling-indexing/sitemaps/overview).

Briefly, a Google XML Sitemap contains two parts:

1. A Sitemap Index XML file - a table of contents listing each 'urlset' file. Note that we're gzipping the resulting XML file in the example below (future version) to reduce file sizes. We could also leave the XML file uncompressed. For example:

```
   <?xml version="1.0" encoding="UTF-8"?>
   <sitemapindex xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
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

2. 'urlset' XML file(s) - a list of each of your website's URLs. For example sitemap1.xml may contain something like:

```
   <?xml version="1.0" encoding="UTF-8"?>
   <urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
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


## How to use the PHP Google XML Sitemap Class (using PHP XMLWriter extension)

Files you'll need:

- /src/GoogleXmlSitemap.php

### Sample Usage
```
   use Dialeleven\PhpGoogleXmlSitemap;

   // adjust the path to the PHP class depending on your site architecture
   include_once $_SERVER['DOCUMENT_ROOT'] . '/src/GoogleXmlSitemap.php';


   // create new instance of the PHP Google XML Sitemap class (XML files will be written to the same path as the script using the Google XML Sitemap class in the line below - uncomment if using)
   //$my_sitemap = new Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap($http_hostname = $_SERVER['HTTP_HOST'], $xml_files_dir = '');

   // create new instance of the PHP Google XML Sitemap class (using specified XML save directory)
   $my_sitemap = new Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap($http_hostname = $_SERVER['HTTP_HOST'], $xml_files_dir = $_SERVER['DOCUMENT_ROOT'] . '/public/sitemaps');

   /*
   Some configuratation methods for your sitemap file(s) to be generated.
   */
   $my_sitemap->setUseHttpsUrls(true); // use "https" scheme (true) for your URLs or plain "http" (false)
   $my_sitemap->setSitemapFilenamePrefix('mysitemap'); // set name of sitemap file minus ".xml" (e.g. mysitemap.xml)
   

   // you might store your arrays like this
   $url_md_arr = array(
      array('http://www.domain.com/url1/', '2024-01-01', 'weekly', '1.0'),
      array('http://www.domain.com/url2/', '2024-01-01', 'weekly', '1.0'),
      array('http://www.domain.com/url3/', '2024-01-01', 'weekly', '1.0')
   );

   // you might probably want to pull your URLs from your database though (e.g. MySQL, Postgres, Mongo, etc...)
   /*
   INCLUDE YOUR DATABASE LOGIC HERE TO PULL YOUR URLs FROM THE REQUIRED TABLE(s)...
   */


   // add your URLs
   foreach ($url_md_arr as $url_arr)
   {
      // the important part - adding each URL
      $my_sitemap->addUrl($url = $url_arr[0], $lastmod = $url_arr[1]', $changefreq = $url_arr[2', $priority = $url_arr[3]);
   }


   // signal when done adding URLs, so we can generate the sitemap index file (table of contents)
   $my_sitemap->endXmlDoc();
```

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
 <?php
use Dialeleven\PhpGoogleSitemap;


include_once $_SERVER['DOCUMENT_ROOT'] . '/src/GoogleImageSitemap.php';


/*
Instansiate the PHP Google Image Sitemap Class. Pass your hostname below as an 
argument using PHP's $_SERVER['HTTP_HOST'] or you can hard code your hostname
such as 'https://www.yourdomain.com' for example.

*** DO NOT INCLUDE A TRAILING SLASH AT THE END OF YOUR HOSTNAME! ***
*/
$my_sitemap = new Dialeleven\PhpGoogleSitemap\GoogleImageSitemap($sitemap_type = 'video', 
                                                                 $http_hostname = 'www.testdomain.com', 
                                                                 $xml_files_dir = $_SERVER['DOCUMENT_ROOT'] . '/public/sitemaps');



/*
Some configuratation methods for your sitemap file(s) to be generated.
*/
#$my_sitemap->setXmlMode($mode = 'file'); // For development purposes. mode = memory (browser), mode = file (save to XML file)
$my_sitemap->setUseHttpsUrls(true); // use "https" mode for your URLs or plain "http"
$my_sitemap->setSitemapFilenamePrefix('myimage_sitemap'); // set name of sitemap file minus ".xml" (e.g. mysitemap.xml)
$my_sitemap->setUseGzip($use_gzip = true); // gzip the urlset files to reduce file sizes (true/false)


// Start adding your URLs and image items
for ($i = 1; $i <= 100001; ++$i)
{
   echo $i . ' - ';

   /*
   Add URLs from your database or array (if preferred)
   1. $loc - Should not include the hostname. For example if the URL is https://www.yourdomain.com/somepath/, then
             the $loc should be "somepath/" if you want the trailing slash. Trailing slash is not enforced for
             flexibility as some sites may not use a trailing slash.
   2. $tags_arr - not used for image sitemap
   3. $special_tags_arr - not used for image sitemap
                     
   The class will create a new 'urlset' file if you reach the 50,000 URL limit and create
   the 'sitemapindex' file listing each urlset file that was generated.
   */
   $my_sitemap->addUrl(
                           $loc = "url-$i/",
                           $tags_arr = array(),
                           $special_tags_arr = array()
                      );
   
   // get random number of image tags to output (1-5)
   $num_images = random_int($min = 1, $max = 5);

   // output some image tags
   for ($j = 1; $j <= $num_images; ++$j)
   {
      $my_sitemap->addImage($loc = "http://example.com/images/loc{$i}_image$j.jpg");
   }
}

// signal when done adding URLs, so we can generate the sitemap index file (table of contents)
$my_sitemap->endXmlDoc();



#throw new Exception('Test exception here');
#throw new InvalidArgumentException('test');
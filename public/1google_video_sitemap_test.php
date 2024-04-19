 <?php
use Dialeleven\PhpGoogleSitemap;


include_once $_SERVER['DOCUMENT_ROOT'] . '/src/GoogleVideoSitemap.php';


/*
Instansiate the PHP Google Video Sitemap Class. Pass your hostname below as an 
argument using PHP's $_SERVER['HTTP_HOST'] or you can hard code your hostname
such as 'https://www.yourdomain.com' for example.

*** DO NOT INCLUDE A TRAILING SLASH AT THE END OF YOUR HOSTNAME! ***
*/
$my_sitemap = new Dialeleven\PhpGoogleSitemap\GoogleVideoSitemap($sitemap_type = 'video', 
                                                                 $http_hostname = 'www.testdomain.com', 
                                                                 $xml_files_dir = $_SERVER['DOCUMENT_ROOT'] . '/public/sitemaps');



/*
Some configuratation methods for your sitemap file(s) to be generated.
*/
#$my_sitemap->setXmlMode($mode = 'file'); // mode = memory (browser), mode = file (save to XML file)
$my_sitemap->setUseHttpsUrls(true); // use "https" mode for your URLs or plain "http"
$my_sitemap->setSitemapFilenamePrefix('myvideo_sitemap'); // set name of sitemap file minus ".xml" (e.g. mysitemap.xml)
$my_sitemap->setUseGzip($use_gzip = false); // gzip the urlset files to reduce file sizes (true/false)



/*
Start adding your URLs and news items
*/

for ($i = 1; $i <= 1000; ++$i)
{
   echo $i . ' - ';

   /*
   Add URLs from your database or array (if preferred)
   1. $loc - Should not include the hostname. For example if the URL is https://www.yourdomain.com/somepath/, then
             the $loc should be "somepath/" if you want the trailing slash. Trailing slash is not enforced for
             flexibility as some sites may not use a trailing slash.
   2. $tags_arr - Here pass an array of the video URL tags including the following:
                     - thumbnail_loc (required)
                     - title (required)
                     - description (required)
                     - content_loc (required)
                     - player_loc (required)
                     - thumbnail_loc
                     - title
                     - description
                     - content_loc
                     - player_loc
                     - duration
                     - rating
                     - view_count
                     - publication_date
                     - family_friendly
                     - requires_subscription
                     - live

   3. $special_tags_arr - Additional optional video tags which require passing 4 values instead of the usual 2 (name => value).
                          Allowed tags include:
                           - restriction
                           - price
                           - uploader
                     
   The class will create a new 'urlset' file if you reach the 50,000 URL limit and create
   the 'sitemapindex' file listing each urlset file that was generated.
   */
   $my_sitemap->addUrl(
                           $loc = "url-$i/",
                           $tags_arr = array(
                                                'thumbnail_loc' => "https://example.com/thumbs/$i.jpg", 
                                                'title' => "Video Title #$i", 
                                                'description' => "Video description #$i",
                                                'content_loc' => "http://streamserver.example.com/video$1.mp4",
                                                'player_loc' => "https://example.com/videoplayer.php?video=$i"
                                             ),
                           $special_tags_arr = array(
                                                      array('restriction', 'relationship', 'allow', 'IE GB US CA'),
                                                      array('price', 'currency', 'EUR', '1.99'), 
                                                      array('uploader', 'info', "https://example.com/users/user$i", "Username$i")
                                                    )
                      );
}

// signal when done adding URLs, so we can generate the sitemap index file (table of contents)
$my_sitemap->endXmlDoc();



#throw new Exception('Test exception here');
#throw new InvalidArgumentException('test');
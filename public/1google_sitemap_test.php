 <?php
use Dialeleven\PhpGoogleXmlSitemap;


include_once $_SERVER['DOCUMENT_ROOT'] . '/src/GoogleXmlSitemap.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/public/db_connect.inc.php';




$sql_total = $sql = 'SELECT COUNT(*) AS total FROM sample';

// mysql PDO query non-prepared statement
$stmt = $pdo->query($sql);
$totalrows = $stmt->rowCount();

while ($query_data = $stmt->fetch())
{
   // code here
   echo "Total Rows: $query_data->total<br>";
}

// user should create an array of all their URLs

/*
Instansiate the PHP Google XML Sitemap Class. Pass your hostname below as an 
argument using PHP's $_SERVER['HTTP_HOST'] or you can hard code your hostname
such as 'https://www.yourdomain.com' for example.
*/
$my_sitemap = new Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap($http_host = $_SERVER['HTTP_HOST']);


/*
Is this script not in the root/public dir? enter the number of directories deep we are in (e.g. /in/here/your_script_filename.php = "2").
This will adjust where your sitemap file gets written.
*/
#$my_sitemap->setPathAdjustmentToRootDir($path_adj = 0);


/*
Some configuratation methods for your sitemap file(s) to be generated.
*/
$my_sitemap->setUseMysqlDbModeFlag(true, $pdo, $sql_total); // generate URLs for sitemap from MySQL? true/false, your PDO object, basic SQL "COUNT(*) AS total"
$my_sitemap->setSitemapFilenamePrefix('mysitemap'); // set name of sitemap file minus ".xml" (e.g. mysitemap.xml)
$my_sitemap->setSitemapChangeFreq('weekly'); // set sitemap 'changefreq' how often the content is expected to change (always, hourly, daily, weekly, monthly, yearly, never)
$my_sitemap->setHostnamePrefixFlag(true); // 'true' to use "https://$_SERVER['HTTP_HOST]/"+REST-OF-YOUR-URL-HERE/. 'false' if using full URLs.

/*
Start adding your URLs
*/

$my_sitemap->openXml($mode = 'memory'); //$my_sitemap->openXml($mode = 'file');




#throw new Exception('Test exception here');
#throw new InvalidArgumentException('test');

$path = '../../';

echo (preg_match('#(\.\./){1,}#', $path)) ? 'match' : 'no match';
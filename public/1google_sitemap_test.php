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


$my_sitemap = new Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap($http_host = $_SERVER['HTTP_HOST']);

// is this script not in the root/public dir? enter the number of directories deep we are in (e.g. /in/here/google_sitemap.php = "2")
#$my_sitemap->setPathAdjustmentToRootDir($path_adj = 0);

$my_sitemap->setUseMysqlDbModeFlag(true, $pdo, $sql_total); // generate URLs for sitemap from MySQL? true/false, your PDO object, basic SQL "COUNT(*) AS total"
$my_sitemap->setSitemapFilenamePrefix('mysitemap'); // set name of sitemap file minus ".xml" (e.g. mysitemap.xml)
$my_sitemap->setSitemapChangeFreq('weekly'); // set sitemap 'changefreq' how often the content is expected to change (always, hourly, daily, weekly, monthly, yearly, never)
$my_sitemap->setHostnamePrefixFlag(true); // 'true' to use "https://$_SERVER['HTTP_HOST]/"+REST-OF-YOUR-URL-HERE/. 'false' if using full URLs.


#throw new Exception('Test exception here');
#throw new InvalidArgumentException('test');

$path = '../../';

echo (preg_match('#(\.\./){1,}#', $path)) ? 'match' : 'no match';
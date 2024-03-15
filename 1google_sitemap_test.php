<?php
use Dialeleven\PhpGoogleXmlSitemap;

include_once $_SERVER['DOCUMENT_ROOT'] . '/__google_sitemap_template.class.php';


$db_host = 'localhost';
$db_name = 'test';
$db_username = 'root';
$db_password = '';
$db_port = 3308;

/* Connection string, or "data source name" */
$dsn = 'mysql:host=' . $db_host . ';dbname=' . $db_name . ';port=' . $db_port;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    #PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    #PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_LAZY,

    PDO::ATTR_EMULATE_PREPARES   => false,
];

/* Connection inside a try/catch block */
try
{
   /* PDO object creation */
   $pdo = new PDO($dsn, $db_username,  $db_password, $options);
}
catch (PDOException $e)
{
   /* If there is an error an exception is thrown */
   echo 'Connection failed<br>';
   echo 'Error number: ' . $e->getCode() . '<br>';
   echo 'Error message: ' . $e->getMessage() . '<br>';
   die();
}


$sql_total = $sql = 'SELECT COUNT(*) AS total FROM sample';

// mysql PDO query non-prepared statement
$stmt = $pdo->query($sql);
$totalrows = $stmt->rowCount();

while ($query_data = $stmt->fetch())
{
   // code here
   echo "Total Rows: $query_data->total<br>";
}


$my_sitemap = new Dialeleven\PhpGoogleXmlSitemap\GoogleSitemap($http_host = $_SERVER['HTTP_HOST']);

// is this script not in the root/public dir? enter the number of directories deep we are in (e.g. /in/here/google_sitemap.php = "2")
#$my_sitemap->setPathAdjustmentToRootDir($path_adj = 0);

$my_sitemap->setUseMysqlDbModeFlag(true, $pdo, $sql_total); // generate URLs for sitemap from MySQL? true/false, your PDO object, basic SQL "COUNT(*) AS total"
$my_sitemap->setSitemapFilenamePrefix('mysitemap'); // set name of sitemap file minus ".xml" (e.g. mysitemap.xml)
$my_sitemap->setSitemapChangeFreq('weekly'); // set sitemap 'changefreq' how often the content is expected to change (always, hourly, daily, weekly, monthly, yearly, never)
$my_sitemap->setHostnamePrefixFlag(true); // 'true' to use "https://$_SERVER['HTTP_HOST]/"+REST-OF-YOUR-URL-HERE/. 'false' if using full URLs.
?>



<?php
/*
Ref: https://stackoverflow.com/a/66470138
*/
function interpolateSQL($pdo, $query, $params) {
   $s = chr(2); // Escape sequence for start of placeholder
   $e = chr(3); // Escape sequence for end of placeholder
   $keys = [];
   $values = [];

   // Make sure we use escape sequences that are not present in any value
   // to escape the placeholders.
   foreach ($params as $key => $value) {
      while( mb_stripos($value, $s) !== false ) $s .= $s;
      while( mb_stripos($value, $e) !== false ) $e .= $e;
   }


   foreach ($params as $key => $value) {
      // Build a regular expression for each parameter
      $keys[] = is_string($key) ? "/$s:$key$e/" : "/$s\?$e/";

      // Treat each value depending on what type it is.
      // While PDO::quote() has a second parameter for type hinting,
      // it doesn't seem reliable (at least for the SQLite driver).
      if( is_null($value) ){
         $values[$key] = 'NULL';
      }
      elseif( is_int($value) || is_float($value) ){
         $values[$key] = $value;
      }
      elseif( is_bool($value) ){
         $values[$key] = $value ? 'true' : 'false';
      }
      else{
         $value = str_replace('\\', '\\\\', $value);
         $values[$key] = $pdo->quote($value);
      }
   }

   // Surround placehodlers with escape sequence, so we don't accidentally match
   // "?" or ":foo" inside any of the values.
   $query = preg_replace(['/\?/', '/(:[a-zA-Z0-9_]+)/'], ["$s?$e", "$s$1$e"], $query ?? '');

   // Replace placeholders with actual values
   $query = preg_replace($keys, $values, $query, 1, $count);

   // Verify that we replaced exactly as many placeholders as there are keys and values
   if( $count !== count($keys) || $count !== count($values) ){
      throw new \Exception('Number of replacements not same as number of keys and/or values');
   }

   return $query;
}
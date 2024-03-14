<?php
$db_host = 'localhost';
$db_name = 'test';
$db_username = 'root';
$db_password = '';

/* Connection string, or "data source name" */
$dsn = 'mysql:host=' . $db_host . ';dbname=' . $db_name;

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


include_once $_SERVER['DOCUMENT_ROOT'] . '/__google_sitemap_template.class.php';


$sql_total = 'SELECT COUNT(*) AS total FROM demo WHERE 1 = 1';


$my_sitemap = new GoogleSitemap($pdo, $sql_total, $http_host = $_SERVER['HTTP_HOST'], $sitemap_filename_prefix = 'mysitemap', 
                                $sitemap_changefreq = 'weekly', $path_adj = 0);
echo 'hello world';
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
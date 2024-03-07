<?php
if (preg_match('/bcb\.test/', $_SERVER['HTTP_HOST']))
{
   $db_host = 'localhost';
   $db_name = 'bcb';
   $db_username = 'fsus_webadm';
   $db_password = '-=68.f0bgGG';
}
else if (preg_match('/dev\.businesscashback\.com/', $_SERVER['HTTP_HOST']))
{
   $db_host = 'localhost';
   $db_name = 'bcb_dev';
   $db_username = 'bcb_dev_user';
   $db_password = '9y9Pl2X3m9Y8fRu2Cf';
}
else
{
   $db_host = 'localhost';
   $db_name = 'bcb';
   $db_username = 'bcb_mysql';
   $db_password = 'D9iRC5NhBJDioGFCsd';
}

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


// end script if NOT being called from local server.
#if ($_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR']) { die; }


$http_host = $_SERVER['SERVER_NAME'];

// just recreate the sitemap
if ($_GET['regen_sitemap'] == 1)
{
   // don't allow script to timeout
   set_time_limit(0);
   
   //$http_host = 'boston.fabuloussavings.com';
   $sitemap_filename_prefix = "sitemap_bcb_vendors";
   $sitemap_changefreq = 'weekly';
   $path_adj = -1;


   $sql = "SELECT COUNT(*) AS total
              FROM vendors
              WHERE status = 1
              ORDER BY name";
   #echo interpolateSQL($pdo, $sql, $params = ['cat_name' => $cat_name, 'cat_description' => $cat_description, 'meta_title' => $meta_title, 'meta_description' => $meta_description, 'cat_id' => $cat_id]); // sql debugging
   $stmt = $pdo->prepare($sql);
   $stmt->execute([]);

   $json_output = json_encode($json_values, JSON_PRETTY_PRINT);

   // SQL for total number of links for cities hosted on FS.com
   $sql_total = "SELECT COUNT(*) AS total
                 FROM vendors
                 WHERE status = 'active'";
   
   // create new instance of GoogleSitemap passing required params to constructor
   $my_sitemap = new GoogleSitemap($sql_total, $http_host, $sitemap_filename_prefix, $sitemap_changefreq, $path_adj);

   #####################################################################################################
   # Create Sitemap for 1) Local (non-Toronto), 2) Local GD (non-Toronto), 3) Local Online, 4) Online US
   #####################################################################################################


   //--------------------- assemble sitemap file(s) -------------------//
   // vendors list
   $sql = "SELECT name, slug
           FROM vendors
           WHERE status = 'active'
           ORDER BY name";

   // add the FS.com home page URL to the first sitemap
   $url_arr = ($i == 0) ? array('http://' . $http_host . '|weekly') : '';

   // start creating each individual sitemap file
   $my_sitemap->createSitemapFile($sql, $db_field_name_arr = array('slug'),
                                  $loc_url_template = '/[slug]/', $url_arr = '');


   // build Sitemap index contents and write file once done building ALL the individual Sitemaps above
   $my_sitemap->buildSitemapIndexContents();
   $my_sitemap->writeSitemapIndexFile();
   
   $status_item = $my_sitemap->status_item;
   $error_msg .= $my_sitemap->error_msg;
   
   if ($status_item) { $status_msg = 'Successfully generated Sitemap(s)'; }
}


// display status message if set
if ($status_msg)
{
   echo '<div class="statusGreen">' . $status_msg . '</div>';
   echo ($status_item) ? '<ul class="statusMessage">' . $status_item . '</ul>' : '<br />';
}
// display error message if set
else if ($error_msg)
{
   echo '<div class="statusRed">There was an error processing your request:</div>';
   echo '<ul class="statusMessage">' . $error_msg . '</ul><br />';
}
?>

<p style="margin-top: 25px;">
   &gt; <a href="?regen_sitemap=1">Create Google Sitemap</a>
</p>

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
   $query = preg_replace(['/\?/', '/(:[a-zA-Z0-9_]+)/'], ["$s?$e", "$s$1$e"], $query);

   // Replace placeholders with actual values
   $query = preg_replace($keys, $values, $query, 1, $count);

   // Verify that we replaced exactly as many placeholders as there are keys and values
   if( $count !== count($keys) || $count !== count($values) ){
      throw new \Exception('Number of replacements not same as number of keys and/or values');
   }

   return $query;
}
?>
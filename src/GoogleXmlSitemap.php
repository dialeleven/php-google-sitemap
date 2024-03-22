<?php
/*
Filename:         google_sitemap_template.class.php
Author:           Francis Tsao
Date Created:     08/01/2008
Purpose:          Creates a gzipped google sitemap xml file with a list of URLs specified
                  by the passed SQL.
History:          12/06/2011 - commented out <changefreq> tag as Google does not pay
                               attention to this according to Nine By Blue [ft]
*/


/**
 * GoogleSitemap - create Google XML Sitemap from either a MySQL query or supplied list (array?) of URLs
 *
 * History: 
 *
 * Sample usage
 * <code>
 * $mysitemap = new GoogleSitemap($http_host);
 * 
 * // repeat this call as many times as required if assembling a sitemap that needs 
 * // to execute several different SQL statements
 * $mysitemap->createSitemapFile($sql, $db_field_name_arr, $loc_url_template, $url_arr);
 
 * $mysitemap->buildSitemapContents();
 * $mysitemap->buildSitemapIndexContents();
 * </code>
 *
 * @author Francis Tsao
 */
namespace Dialeleven\PhpGoogleXmlSitemap;

use Exception;
use InvalidArgumentException;


class GoogleXmlSitemap
{
   private $pdo;
   public $sql;
   public $http_host; // http hostname (minus the "http://" part - e.g. www.fabuloussavings.ca)
   private $sitemap_filename_prefix = 'sitemap_filename'; // YOUR_FILENAME_PREFIX1.xml.gz, YOUR_FILENAME_PREFIX2.xml.gz, etc
                                                      // (e.g. if prefix is "sitemap_clients" then you will get a sitemap index
                                                      // file "sitemap_clients.xml, and sitemap files "sitemap_clients1.xml.gz")
   private $sitemap_changefreq = 'weekly'; // Google Sitemap <changefreq> value (always, hourly, daily, weekly, monthly, yearly, never)
   
   public $total_links = 0;                   // total number of <loc> URL links
   private $max_sitemap_links = 50000;     // maximum is 50,000 URLs per file
   
   const MAX_SITEMAP_LINKS = 50000;

   #public $max_sitemap_links = 10;     // maximum is 50,000
   //public $max_filesize = 10485760;       // 10MB maximum (unsupported feature currently)
   private $num_sitemaps = 0;              // total number of Sitemap files
   public $sitemap_index_contents = '';        // contents of Sitemap index file
   public $sitemap_contents;              // contents of sitemap (URLs)
   private $status_item;                   // list item status messages
   private $error_msg;
   private $path_adj;                      // file path adjustment to root directory (e.g. "../../")
   public $use_hostname_prefix;           // flag to use supplied $http_host value for $http_host/whatever/is/passed/
                                       // in <url> tag or only the DB field supplied value which should contain http://www.domain.com
   
   public $db_field_name_arr;
   public $loc_url_template;
   public $url_arr;

   public $createSitemapFileWithDelayedWriteOptionCounter = 0;

   /**
     * Constructor gets HTTP host to use in <loc> to keep things simple. Call setter methods to set other props as needed.
     *
     * @param  string $http_host  http hostname to use for URLs - e.g. www.yourdomain.com or pass the $_SERVER['HTTP_HOST']

     * @access public
     * @return void
     */
   public function __construct(string $http_host)
   {  
      $this->http_host = $http_host;
   }

   // TODO: PHPUnit test- setUseMysqlDbModeFlag
   public function setUseMysqlDbModeFlag(bool $use_db_mode, object $pdo, string $sql_total): bool
   {
      if ($use_db_mode == true)
      {
         $this->pdo = $pdo;

         $this->setTotalLinksSQL($sql_total);
         return true;
      }
      else
         return false;
   }

   
   /**
     * Set the total number of links (URLs) that will be in the Google XML Sitemap.
     * The SQL query should look like 'SELECT COUNT(*) AS total FROM my_table_name' at a minimum.
     *
     * @param  
     * @access private
     * @return void
     */
   private function setTotalLinksSQL(string $sql_total)
   {      
      #echo $sql_total;
      #echo interpolateSQL($pdo, $sql_total, $params = []); // sql debugging

      $stmt = $this->pdo->prepare($sql_total);
      $stmt->execute([]);

      $query_data = $stmt->fetch();
      $this->total_links += $query_data->total;
   }


   /**
     * Set the relative path adjustment for writing the sitemap file(s) to the root directory
     * in case we are somewhere below the root (e.g. /admin/googlesitemapbuilder/)
     *
     * @param int $path_adj  number of steps up to the root directory from the CALLING script (not this one) to write the sitemap
     *                       file(s) to the root direcroy
     * @access private
     * @return bool
     */
   public function setPathAdjustmentToRootDir(int $path_adj)
   {      
      if ($path_adj > 0)
      {
         for ($i = 1; $i <= $path_adj; ++$i)
            $this->path_adj .= '../';

         return true;
      }
      // if zero is passed, set to empty string for PHPUnit
      else
      {
         $this->path_adj = '';
         return false;
      }
   }

   public function getPathAdjustmentToRootDir(): string
   {
      return $this->path_adj;
   }

   /**
     * @param string $sitemap_filename_prefix  name of the sitemap minus the file extension (e.g. [MYSITEMAP].xml)
     * @access public
     * @return bool
     */
   public function setSitemapFilenamePrefix(string $sitemap_filename_prefix): bool
   {
      $this->sitemap_filename_prefix = $sitemap_filename_prefix;

      return true;
   }

   public function getSitemapFilenamePrefix(): string
   {
      return $this->sitemap_filename_prefix;
   }


   /**
     * @param string $sitemap_changefreq  how often the content is expected to change (always, hourly, daily, weekly, monthly, yearly, never)
     * @access public
     * @return void
     */
   public function setSitemapChangefreq(string $sitemap_changefreq)
   {
      $this->sitemap_changefreq = $sitemap_changefreq;
   }

   public function getSitemapChangefreq(): string
   {
      return $this->sitemap_changefreq;
   }


   /**
     * @param bool $use_hostname_prefix  Flag to use default "https://$this->http_host" or leave blank if pulling a complete URL from DB
     * @access public
     * @return void
     */
   public function setHostnamePrefixFlag(bool $use_hostname_prefix)
   {
      $this->use_hostname_prefix = $use_hostname_prefix;
   }
   
   
   /**
     * Manually set the $total_links var in cases where passing the SQL to calculate the
     * total number of <loc> URLs is not possible (e.g. with calculating the total number of populated categories)
     *
     * @param  string $total_links  total number of links/URLs
     * @access public
     * @return void
     */
    public function setTotalLinks(int $total_links)
   {
      if ($total_links >= 0)
         $this->total_links = $total_links;
   }
   
   
   /**
     * Builds contents of the sitemap index file (similar to a table of contents).
     * This will list all of your sitemap files
     * 
     * Example:
     *   - http://www.domain.com/my_sitemap_file1.xml.gz
     *   - http://www.domain.com/my_sitemap_file2.xml.gz
     *   - etc...
     * @access public
     * @return void
     */
    public function buildSitemapIndexContents()
   {
      $this->sitemap_index_contents = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
      $this->sitemap_index_contents .= '<sitemapindex xmlns="http://www.google.com/schemas/sitemap/0.84"' . "\r\n";
      $this->sitemap_index_contents .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\r\n";
      $this->sitemap_index_contents .= 'xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84' . "\r\n";
      $this->sitemap_index_contents .= 'http://www.google.com/schemas/sitemap/0.84/siteindex.xsd">' . "\r\n";

      $lastmod = date('Y-m-d\TH:i:s+00:00', time());

      for ($i = 1; $i <= $this->num_sitemaps; ++$i)
      {
         $this->sitemap_index_contents .= '   <sitemap>' . "\r\n";
         $this->sitemap_index_contents .= "      <loc>https://$this->http_host/$this->sitemap_filename_prefix{$i}.xml.gz</loc>\r\n";
         $this->sitemap_index_contents .= '      <lastmod>' . $lastmod . '</lastmod>' . "\r\n";
         $this->sitemap_index_contents .= '   </sitemap>' . "\r\n";
      }
      
      $this->sitemap_index_contents .= '</sitemapindex>';
   }



   /**
     * Builds contents of the sitemap index file (similar to a table of contents).
     * @access public
     * @return void
     */
   public function buildSitemapIndexContentsUrlsOnly()
   {
      $lastmod = date('Y-m-d\TH:i:s+00:00', time());

      for ($i = 1; $i <= $this->num_sitemaps; ++$i)
      {
         // TODO: change 'sitemap_index_contents' to maybe sitemap_index_contents_urls_only?
         $this->sitemap_index_contents .= '   <sitemap>' . "\r\n";
         $this->sitemap_index_contents .= "      <loc>https://$this->http_host/$this->sitemap_filename_prefix{$i}.xml.gz</loc>\r\n";
         $this->sitemap_index_contents .= '      <lastmod>' . $lastmod . '</lastmod>' . "\r\n";
         $this->sitemap_index_contents .= '   </sitemap>' . "\r\n";
      }

      // if sitemap index contents are empty, set to empty string for PHPUnit to avoid null error
      if (empty($this->sitemap_index_contents))
         $this->sitemap_index_contents = '';
   }
   
   
   /**
     * Creates and writes required number of sitemap files.
     * @access public
     * @param  string $sql  SQL to build <loc> URLs from (the $sql var is "required" but you can pass an empty var if using $url_arr)
     * @param  array $db_field_name_arr  array of DB field name(s) to substitute in $loc_url_template
     * @param  string $loc_url_template  templated URL string to substitute the db_field_name_arr with.
     *                                   Make sure the substitue templated items have the SAME name 
     *                                   AND ORDER as the actual database table field names!!!
     *
     *                                   *** ENSURE you include the leading forward slash ***.
     *
     *                                   Example 1:
     *                                     db_field_name_arr -> array('city_name', 'oct_name', 'oct_id')
     *                                     loc_url_template  -> /online-[city_name]-coupons/category-[oct_name]-[oct_id]/
     *                                     
     *                                     Note how the db_field_name_arr has three (3) elements and the 
     *                                     loc_url_template also has three (3) templated strings enclosed
     *                                     in square brackets.
     * @param  array $url_arr  array of URLs (if you want to add more urls to the sitemap)
     * @return void
     */
   // TODO: PHPUnit test - createSitemapFile
   public function createSitemapFile(string $sql, array $db_field_name_arr, string $loc_url_template, array $url_arr = [])
   {
      $this->sql = $sql; // store this as we're calling buildSitemapContents() in a bit
      $this->db_field_name_arr = $db_field_name_arr;
      $this->loc_url_template = $loc_url_template;
      $this->url_arr = $url_arr;

      $offset = '';

      #print_r($this->db_field_name_arr);

      // if URL array (URL, changefreq) is passed, then adjust the total number of links per Sitemap.
      // Change it across all Sitemaps for simplicities sake.
      if (is_array($url_arr))
      {
         $total_urls = count($url_arr);
         $this->total_links += $total_urls; // increment total number of URL links
         $this->max_sitemap_links -= $total_urls;
      }
      
      // calculate SQL LIMIT clause offset
      $offset = ($this->num_sitemaps < 2)
              ? ($this->num_sitemaps * $this->max_sitemap_links) - (1  * $this->num_sitemaps) 
              : $offset + $this->max_sitemap_links;
      $sql_limit = "LIMIT $offset, " . $this->max_sitemap_links;
      
      // calculate number of sitemap files we need based on the max allowed number of links.
      // NOTE: This $num_sitemaps variable is ONLY for the current call to buildSitemapContents() and
      //       NOT a running total of the number of sitemaps we have.
      $num_sitemaps = ceil($this->total_links / $this->max_sitemap_links);
      
      // create X number of req'd sitemap files
      for ($i = 0; $i < $num_sitemaps; $i++)
      {
         $offset = ($i < 2) ? ($i * $this->max_sitemap_links) - (1  * $i) : $offset + $this->max_sitemap_links;
         
         $sql_limit = "LIMIT $offset, " . $this->max_sitemap_links;
         $sitemap_contents = $this->buildSitemapContents($sql_limit);
         
         // if SQL executed results in no records returned, then don't write a file
         // (e.g. SQL for open pages for Local Online is run, but there are no open page records)
         if (empty($sitemap_contents)) { continue; }
         
         $gz = gzopen("$this->path_adj$this->sitemap_filename_prefix" . ($this->num_sitemaps + 1) . '.xml.gz', 'w9');
         
         if ($bytes_written = gzwrite($gz, $sitemap_contents))
         {
            $this->status_item .= "<li>Wrote " . number_format($bytes_written) .
                                  " bytes to $this->path_adj$this->sitemap_filename_prefix" . ($this->num_sitemaps + 1) . '.xml.gz</li>';
            gzclose($gz);
         }

         // increment total number of sitemaps
         ++$this->num_sitemaps;
      }
   }


   /**
     * Creates and writes required number of sitemap files.
     * @access public
     * @param  string $sql  SQL to build <loc> URLs from (the $sql var is "required" but you can pass an empty var if using $url_arr)
     * @param  array $db_field_name_arr  array of DB field name(s) to substitute in $loc_url_template
     * @param  string $loc_url_template  templated URL string to substitute the db_field_name_arr with.
     *                                   Make sure the substitue templated items have the SAME name
     *                                   AND ORDER as the actual database table field names!!!
     *
     *                                   *** ENSURE you include the leading forward slash ***.
     *
     *                                   Example 1:
     *                                     db_field_name_arr -> array('city_name', 'oct_name', 'oct_id')
     *                                     loc_url_template  -> /online-[city_name]-coupons/category-[oct_name]-[oct_id]/
     *
     *                                     Note how the db_field_name_arr has three (3) elements and the
     *                                     loc_url_template also has three (3) templated strings enclosed
     *                                     in square brackets.
     * @param  array $url_arr  array of URLs (if you want to add more urls to the sitemap)
     * @return void
     */
    // TODO: PHPUnit test - createSitemapFileWithDelayedWriteOption
    public function createSitemapFileWithDelayedWriteOption(string $sql, array $db_field_name_arr, string $loc_url_template,
                                                           array $url_arr = [], bool $build_sitemap_contents = true)
   {
      $this->createSitemapFileWithDelayedWriteOptionCounter++;
      $this->sql = $sql; // store this as we're calling buildSitemapContents() in a bit
      $this->db_field_name_arr = $db_field_name_arr;
      $this->loc_url_template = $loc_url_template;
      $this->url_arr = $url_arr;
      $offset = '';

      // get total links for current SQL call
      #echo interpolateSQL($pdo, $sql, $params = ['cat_name' => $cat_name, 'cat_description' => $cat_description, 'meta_title' => $meta_title, 'meta_description' => $meta_description, 'cat_id' => $cat_id]); // sql debugging
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute([]);

      $totalrows_for_current_call = $stmt->rowCount();


      echo $this->sql . " has [<b style='color: blue;'>$totalrows_for_current_call</b>] rows.<p>Call [$this->createSitemapFileWithDelayedWriteOptionCounter] for createSitemapFileWithDelayedWriteOption()</p>" . '<hr>';
      #print_r($this->db_field_name_arr);

      // if URL array (URL, changefreq) is passed, then adjust the total number of links per Sitemap.
      // Change it across all Sitemaps for simplicities sake.
      if (is_array($url_arr))
      {
         $total_urls = count($url_arr);
         $this->total_links += $total_urls; // increment total number of URL links
         $this->max_sitemap_links -= $total_urls;
      }

      echo "\$this->total_links: $this->total_links<br>";

      // calculate SQL LIMIT clause offset
      $offset = ($this->num_sitemaps < 2)
              ? ($this->num_sitemaps * $this->max_sitemap_links) - (1  * $this->num_sitemaps)
              : $offset + $this->max_sitemap_links;
      $sql_limit = "LIMIT $offset, " . $this->max_sitemap_links;

      // calculate number of sitemap files we need based on the max allowed number of links.
      // NOTE: This $num_sitemaps variable is ONLY for the current call to buildSitemapContents() and
      //       NOT a running total of the number of sitemaps we have.
      #$num_sitemaps = ceil($this->total_links / $this->max_sitemap_links);
      $num_sitemaps = ceil($this->total_links / $this->max_sitemap_links);





      if ($build_sitemap_contents)
      {
         echo "BUILD THE ENTIRE FILE NOW<BR>";
         // create X number of req'd sitemap files
         for ($i = 0; $i < $num_sitemaps; $i++)
         {
            $sitemap_contents = $this->getXmlUrlsetTagStart();
            $sitemap_contents .= $this->sitemap_contents; // get the previous call's URLs

            $offset = ($i < 2) ? ($i * $this->max_sitemap_links) - (1  * $i) : $offset + $this->max_sitemap_links;

            $sql_limit = "LIMIT $offset, " . $this->max_sitemap_links;
            $sitemap_contents .= $this->buildSitemapContentsUrlsOnly($sql_limit);

            // if SQL executed results in no records returned, then don't write a file
            // (e.g. SQL for open pages for Local Online is run, but there are no open page records)
            if (empty($sitemap_contents)) { continue; }

            $sitemap_contents .= $this->getXmlUrlsetTagEnd();

            $gz = gzopen("$this->path_adj$this->sitemap_filename_prefix" . ($this->num_sitemaps + 1) . '.xml.gz', 'w9');

            if ($bytes_written = gzwrite($gz, $sitemap_contents))
            {
               $this->status_item .= "<li>Wrote " . number_format($bytes_written) .
                                     " bytes to $this->path_adj$this->sitemap_filename_prefix" . ($this->num_sitemaps + 1) . '.xml.gz</li>';
               gzclose($gz);
            }

            // increment total number of sitemaps
            ++$this->num_sitemaps;
         }
      }
      else
      {
         echo "########## JUST COLLECT THE URLs FOR NOW for <span style='font: 14px Courier'>[$sql]</span><BR><br>";
// create X number of req'd sitemap files
         for ($i = 0; $i < $num_sitemaps; $i++)
         {
            #$this->getXmlUrlsetTagStart();

            $offset = ($i < 2) ? ($i * $this->max_sitemap_links) - (1  * $i) : $offset + $this->max_sitemap_links;

            $sql_limit = "LIMIT $offset, " . $this->max_sitemap_links;
            $this->sitemap_contents = $this->buildSitemapContentsUrlsOnly($sql_limit);



            echo "\$this->sitemap_contents: $this->sitemap_contents<br>";

            // if SQL executed results in no records returned, then don't write a file
            // (e.g. SQL for open pages for Local Online is run, but there are no open page records)
            if (empty($sitemap_contents)) { continue; }
/*
            $this->getXmlUrlsetTagEnd();

            $gz = gzopen("$this->path_adj$this->sitemap_filename_prefix" . ($this->num_sitemaps + 1) . '.xml.gz', 'w9');

            if ($bytes_written = gzwrite($gz, $sitemap_contents))
            {
               $this->status_item .= "<li>Wrote " . number_format($bytes_written) .
                                     " bytes to $this->path_adj$this->sitemap_filename_prefix" . ($this->num_sitemaps + 1) . '.xml.gz</li>';
               gzclose($gz);
            }
*/
            // increment total number of sitemaps
            ++$this->num_sitemaps;
         }
      }

      echo "<hr><hr>=== <pre>$this->sitemap_contents</pre> ===<hr><hr>";
   }

   
   /**
     * Writes the sitemap index file listing all of the individual sitemap files used.
     * @access public
     * @return bool
     */
     public function writeSitemapIndexFile(): bool
   {
      $sitemap_index_filename = "{$this->sitemap_filename_prefix}.xml";
      
      // open file for writing, any exisint file content will be overwritten
      if ( !($fp = @fopen("$this->path_adj$sitemap_index_filename", 'w') ) )
      {
         $this->error_msg .= "<li>Could not open file $this->path_adj$sitemap_index_filename for writing</li>";

         return false;
         //throw new Exception("ERROR: Could not open file $this->path_adj$sitemap_index_filename for writing");
      }
      // write file contents and update last update date
      else
      {
         fwrite($fp, $this->sitemap_index_contents);
         fclose($fp);
         @chmod("../../$this->path_adj$sitemap_index_filename", 0755);
         $this->status_item .= "<li>Wrote <a href=\"../../$this->path_adj$sitemap_index_filename\">$sitemap_index_filename</a></li>";

         return true;
      }
   }
   
   
   /**
     * Builds the contents of a single sitemap file.
     * @param  string $sql_limit  SQL LIMIT clause
     * @access public
     * @return string $sitemap_contents
     */
   // TODO: PHPUnit test - buildSitemapContents
   public function buildSitemapContents($sql_limit): string
   {
      // start processing SQL if passed
      if ($this->sql)
      {
         // <loc> url template cannot be blank
         if (empty($this->loc_url_template))
            die("ERROR: \$this->loc_url_template cannot be empty. Line " . __LINE__ .
                ' in ' . __FILE__ . ' from function ' . __FUNCTION__);
         
         preg_match_all("/\[[^\[\]]+\]/", $this->loc_url_template, $matches);

         $loc_url_template_arr_size = count($matches[0]);
         $db_field_name_arr_size = count($this->db_field_name_arr);
         
         // start swapping data as long as array sizes match up
         if ($db_field_name_arr_size == $loc_url_template_arr_size)
         {
            $loc_url_template = $this->loc_url_template;
            
            // replace each [string] replacement with the appropriate db column.
            // *** IMPORTANT: USE ONLY $loc_url_template and NOT $this->loc_url_template as that will overwrite ***
            //     the template causing subsequent calls to the buildSitemapContents method to fail in cases
            //     where we have to split the Sitemap into several smaller files!
            foreach ($this->db_field_name_arr as $db_field_name)
               $loc_url_template = preg_replace("/\[[^\[\]]+\]/", "\$query_data->$db_field_name", $loc_url_template, 1);
         }
         // if array sizes don't match, then user has missed including some data
         else
         {
            die("ERROR: DB field name array and URL template array do not contain the same number of elements. \$db_field_name_arr_size: $db_field_name_arr_size, \$loc_url_template_arr_size: $loc_url_template_arr_size. Line " . __LINE__ . ' in file ' . __FILE__ . '.');
         }
         
         // assemble full SQL string
         $sql = "$this->sql $sql_limit";
         #echo interpolateSQL($pdo, $sql, $params = ['cat_name' => $cat_name, 'cat_description' => $cat_description, 'meta_title' => $meta_title, 'meta_description' => $meta_description, 'cat_id' => $cat_id]); // sql debugging
         $stmt = $this->pdo->prepare($sql);
         $stmt->execute([]);


         if ($stmt->rowCount() > 0)
         {
            // get opening <?xml> and <urlset> start tag
            $sitemap_contents = $this->getXmlUrlsetTagStart();

            // if url array is present, build the URL entries for them
            $sitemap_contents .= $this->getUrlArraySitemapUrlTags();

            while ($query_data = $stmt->fetch())
            {
               #$sitemap_contents .= "   <!-- $query_data->client_name - $query_data->page_name -->\r\n";
               $sitemap_contents .= "   <url>\r\n";

               // use supplied $http_host value or DB field template only
               $sitemap_contents .= ($this->use_hostname_prefix) ? "      <loc>https://$this->http_host" : "      <loc>";

               // evaluate the URL template to substitute the DB variables
               eval('$sitemap_contents .= "' . $loc_url_template . '";?>');

               $sitemap_contents .= "</loc>\r\n";
               //$sitemap_contents .= "      <changefreq>$this->sitemap_changefreq</changefreq>\r\n";
               $sitemap_contents .= "   </url>\r\n";
            }
            
            // get ending </urlset> tag
            $sitemap_contents .= $this->getXmlUrlsetTagEnd();
         }
         else
         {
            $error_msg .= '<li>ERROR: No sitemap file to create</li>';
         }
      }
      // no SQL passed so build sitemap from URL array only if present
      else if (is_array($this->url_arr))
      {
         // get opening <?xml> and <urlset> start tag
         $sitemap_contents = $this->getXmlUrlsetTagStart();
         
         // if url array is present, build the URL entries for them
         $sitemap_contents .= $this->getUrlArraySitemapUrlTags();
         
         // get ending </urlset> tag
         $sitemap_contents .= $this->getXmlUrlsetTagEnd();
      }
      
      return $sitemap_contents;
   }






   /**
     * Builds the contents of a single sitemap file.
     * @param  string $sql_limit  SQL LIMIT clause
     * @access public
     * @return string $sitemap_contents
     */
   public function buildSitemapContentsUrlsOnly($sql_limit): string
   {
      // start processing SQL if passed
      if ($this->sql)
      {
         echo "Processing $this->sql on line " . __LINE__ . ' in fn ' . __function__ . '<hr>';
         // <loc> url template cannot be blank
         if (empty($this->loc_url_template))
            die("ERROR: \$this->loc_url_template cannot be empty. Line " . __LINE__ .
                ' in ' . __FILE__ . ' from function ' . __FUNCTION__);

         preg_match_all("/\[[^\[\]]+\]/", $this->loc_url_template, $matches);

         $loc_url_template_arr_size = count($matches[0]);
         $db_field_name_arr_size = count($this->db_field_name_arr);

         // start swapping data as long as array sizes match up
         if ($db_field_name_arr_size == $loc_url_template_arr_size)
         {
            $loc_url_template = $this->loc_url_template;

            // replace each [string] replacement with the appropriate db column.
            // *** IMPORTANT: USE ONLY $loc_url_template and NOT $this->loc_url_template as that will overwrite ***
            //     the template causing subsequent calls to the buildSitemapContents method to fail in cases
            //     where we have to split the Sitemap into several smaller files!
            foreach ($this->db_field_name_arr as $db_field_name)
               $loc_url_template = preg_replace("/\[[^\[\]]+\]/", "\$query_data->$db_field_name", $loc_url_template, 1);
         }
         // if array sizes don't match, then user has missed including some data
         else
         {
            die("ERROR: DB field name array and URL template array do not contain the same number of elements. \$db_field_name_arr_size: $db_field_name_arr_size, \$loc_url_template_arr_size: $loc_url_template_arr_size. Line " . __LINE__ . ' in file ' . __FILE__ . '.');
         }

         // assemble full SQL string
         $sql = "$this->sql $sql_limit";
         #echo interpolateSQL($pdo, $sql, $params = ['cat_name' => $cat_name, 'cat_description' => $cat_description, 'meta_title' => $meta_title, 'meta_description' => $meta_description, 'cat_id' => $cat_id]); // sql debugging
         $stmt = $this->pdo->prepare($sql);
         $stmt->execute([]);


         if ($stmt->rowCount() > 0)
         {
            // if url array is present, build the URL entries for them
            $sitemap_contents .= $this->getUrlArraySitemapUrlTags();

            while ($query_data = $stmt->fetch())
            {
               #$sitemap_contents .= "   <!-- $query_data->client_name - $query_data->page_name -->\r\n";
               $sitemap_contents .= "   <url>\r\n";

               // use supplied $http_host value or DB field template only
               $sitemap_contents .= ($this->use_hostname_prefix) ? "      <loc>https://$this->http_host" : "      <loc>";


               // evaluate the URL template to substitute the DB variables
               eval('$sitemap_contents .= "' . $loc_url_template . '";?>');

               $sitemap_contents .= "</loc>\r\n";
               //$sitemap_contents .= "      <changefreq>$this->sitemap_changefreq</changefreq>\r\n";
               $sitemap_contents .= "   </url>\r\n";

               /////echo $sitemap_contents ."<br>";
            }

         }
         else
         {
            $error_msg .= '<li>ERROR: No sitemap file to create</li>';
         }
      }
      // no SQL passed so build sitemap from URL array only if present
      else if (is_array($this->url_arr))
      {
         // get opening <?xml> and <urlset> start tag
         $sitemap_contents = $this->getXmlUrlsetTagStart();

         // if url array is present, build the URL entries for them
         $sitemap_contents .= $this->getUrlArraySitemapUrlTags();

         // get ending </urlset> tag
         $sitemap_contents .= $this->getXmlUrlsetTagEnd();
      }

      return $sitemap_contents;
   }



   
   
   /**
     * Builds the contents of the <url> tags from an array.
     * @access public
     * @return string $sitemap_contents
     */
    protected function getUrlArraySitemapUrlTags(): string
   {
      // if url array is present, build the URL entries for them
      if (is_array($this->url_arr))
      {
         // URL array should come as URL|changefreq
         foreach ($this->url_arr as $val)
         {
            $val_arr = explode('|', $val);
            
            $sitemap_contents .= "   <url>\r\n";
            $sitemap_contents .= "      <loc>$val_arr[0]</loc>\r\n";
            //$sitemap_contents .= "      <changefreq>$val_arr[1]</changefreq>\r\n";
            $sitemap_contents .= "   </url>\r\n";
         }
      }
      
      return $sitemap_contents;
   }
   
   
   /**
     * Get the contents of the start of the XML and <urlset> Sitemap tag
     * @access public
     * @return string $sitemap_contents
     */
    protected function getXmlUrlsetTagStart(): string
   {
      $sitemap_contents = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
      $sitemap_contents .= '<urlset xmlns="http://www.google.com/schemas/sitemap/0.84"' . "\r\n";
      $sitemap_contents .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\r\n";
      $sitemap_contents .= 'xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84' . "\r\n";
      $sitemap_contents .= 'http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">' . "\r\n";
      
      return $sitemap_contents;
   }
   
   
   /**
     * Get the end </urlset> Sitemap tag
     * @access public
     * @return string $sitemap_contents
     */
   protected function getXmlUrlsetTagEnd(): string
   {
      $sitemap_contents = '</urlset>';

      return $sitemap_contents;
   }
}
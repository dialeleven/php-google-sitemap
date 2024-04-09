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
use XMLWriter;


class GoogleXmlSitemap
{
   private $pdo;

   public $xml_writer;

   private $url_count = 0;

   private $xml_mode = 'browser'; // send XML to 'broswer' or 'file'

   public $sql;
   public $http_host; // http hostname (minus the "http://" part - e.g. www.yourdomain.com)

   private $http_host_use_https = true;

   private $sitemap_filename_prefix = 'sitemap_filename'; // YOUR_FILENAME_PREFIX1.xml.gz, YOUR_FILENAME_PREFIX2.xml.gz, etc
                                                      // (e.g. if prefix is "sitemap_clients" then you will get a sitemap index
                                                      // file "sitemap_clients_index.xml, and sitemap files "sitemap_clients1.xml.gz")
   private $sitemap_changefreq = 'weekly'; // Google Sitemap <changefreq> value (always, hourly, daily, weekly, monthly, yearly, never)
   
   public $total_links = 0;                   // total number of <loc> URL links
   private $max_sitemap_links = 50000;     // maximum is 50,000 URLs per file
   
   const MAX_SITEMAP_LINKS = 50000;
   const SITEMAP_FILENAME_SUFFIX = '.xml';

   #public $max_sitemap_links = 10;     // maximum is 50,000
   //public $max_filesize = 10485760;       // 10MB maximum (unsupported feature currently)
   private $num_sitemaps = 0;              // total number of Sitemap files
   public $sitemap_index_contents = '';        // contents of Sitemap index file
   public $sitemap_contents;              // contents of sitemap (URLs)
   private $path_adj;                      // file path adjustment to root directory (e.g. "../../")
   public $use_hostname_prefix;           // flag to use supplied $http_host value for $http_host/whatever/is/passed/
                                       // in <url> tag or only the DB field supplied value which should contain http://www.domain.com

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
      
      // Create a new XMLWriter instance
      $this->xml_writer = new XMLWriter();
   }
   
   public function setUseHttpsUrls(bool $use_https_urls): void
   {
      $this->http_host_use_https = $use_https_urls;
   }

   /**
     * Set what mode to use for the XMLWriter interface. Either 'memory' (send to browser)
     * or 'file' (save to file). Memory mode should only be used for debugging/testing to
     * review the <urlset> XML contents easier than opening up the written XML file.
     *
     * @param  string $xml_mode  http hostname to use for URLs - e.g. www.yourdomain.com or pass the $_SERVER['HTTP_HOST']

     * @access public
     * @return void
     */
   public function setXmlMode(string $xml_mode)
   {
      // TODO: Validation for either 'memory' or 'file'

      $this->xml_mode = $xml_mode;
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
   public function setSitemapChangefreq(string $sitemap_changefreq): void
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
   public function setHostnamePrefixFlag(bool $use_hostname_prefix): void
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
    public function setTotalLinks(int $total_links): void
   {
      if ($total_links >= 0)
         $this->total_links = $total_links;
   }





   /////////////////////// NEW XMLwriter methods ///////////////////////////

   /**
     * Start the XML document. Use either 'memory' mode to send to browser or 'openURI()'
     * save as a file with the specified filename. Set our indentation and then of course
     * start with the <?xml version="1.0" encoding="UTF-8"?> tag.
     * @access protected
     * @param  string $mode  send the resulting XML to 'memory' (browser) or 'file'
     * @param  string $xml_ns_type  values ('urlset' or 'sitemapindex') create either a <urlset xmlns> tag or <sitemapindex> tag
     * @return bool
     */      
   protected function startXmlDoc($xml_ns_type = 'urlset'): bool
   {
      // Create a new XMLWriter instance
      #$this->xml_writer = new XMLWriter();

      // Set the output to memory (for testing mainly)
      if ($this->xml_mode == 'memory')
      {
         $this->xml_writer->openMemory();
      }
      // file writing mode
      else if ($this->xml_mode == 'file')
      {
         // sitemapindex will be "userspecifiedname_index.xml"
         if ($xml_ns_type == 'sitemapindex')
            $this->xml_writer->openURI("{$this->sitemap_filename_prefix}_index" . self::SITEMAP_FILENAME_SUFFIX);
         else
            $this->xml_writer->openURI($this->sitemap_filename_prefix . self::SITEMAP_FILENAME_SUFFIX);
      }

      // Set indentation and line breaks for readability
      $this->xml_writer->setIndent(true);
      $this->xml_writer->setIndentString('   '); // Adjust the number of spaces for indentation as desired


      // Start the document with XML declaration and encoding
      $this->xml_writer->startDocument('1.0', 'UTF-8');

      // open our cotainting tag either 'sitemapindex' or 'urlset'
      $this->startXmlNsElement($xml_ns_type = 'urlset');

      return true;
   }


   /**
     * Open the "xmlns" tag for either the Sitemap Index or 'urlset' list of
     * tags including the xmlns and xsi attributes needed. 
     * 
     * e.g. sitemap index follows:
     *   <sitemapindex xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
     * 
     * 'urlset' XML file container tag follows:
     *   <urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
     * @access protected
     * @return bool
     */      
   protected function startXmlNsElement(string $xml_ns_type = 'sitemapindex'): bool
   {
      // Start the XMLNS element according to what Google needs based on 'sitemapindex' vs. 'urlset'
      if ($xml_ns_type == 'sitemapindex')
         $this->xml_writer->startElementNS(null, 'sitemapindex', 'http://www.sitemaps.org/schemas/sitemap/0.9');
      // Start the 'urlset' element with namespace and attributes
      else
         $this->xml_writer->startElementNS(null, 'urlset', 'http://www.sitemaps.org/schemas/sitemap/0.9');

      // remaining 'xmlns' attributes for both sitemapindex and urlset files are the same
      $this->xml_writer->writeAttributeNS('xmlns', 'xsi', null, 'http://www.w3.org/2001/XMLSchema-instance');
      $this->xml_writer->writeAttributeNS('xsi', 'schemaLocation', null, 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');

      return true;
   }

   protected function startNewUrlsetXmlFile()
   {

      // start new XML file if we reach maximum number of URLs per urlset file
      if ($this->url_count >= self::MAX_SITEMAP_LINKS)
      {
         // end the XML document
         $this->endXmlDoc();

         // start new XML doc
         $this->startXmlDoc($mode = 'memory', $xml_ns_type = 'urlset');

         // increment number of sitemaps counter
         ++$this->num_sitemaps;
      }
      // first call to addURLNew2(), so open up the XML file
      else if ($this->url_count == 0)
      {
         // start new XML doc
         $this->startXmlDoc($mode = 'memory', $xml_ns_type = 'urlset');
         
         // increment number of sitemaps counter
         ++$this->num_sitemaps;
      }
   }


   /**
     * Start our <url> element and child tags 'loc,' 'lastmod,' 'changefreq,' and 'priority' as needed
     * 
     * e.g.
     *    <url>
     *       <loc>http://www.mydomain.com/someurl/</loc>
     *       <lastmod>2024-04-06</lastmod>
     *       <changefreq>weekly</changefreq>
     *       <priority>1.0</priority>
     *    </url>
     * @access public
     * @return bool
     */   
    public function addUrlNew2(string $url, string $lastmod = '', string $changefreq = '', string $priority = '')
    {
       // check if we need a new XML file
       $this->startNewUrlsetXmlFile();

       // Start the 'url' element
       $this->xml_writer->startElement('url');
 
      if (empty($url))
        throw new Exception("ERROR: url cannot be empty");

      // assemble full http(s) URL portion
      $http_host = (($this->http_host_use_https) ? 'https://' : 'http://') . $this->http_host . '/';

      // TODO: strip/add leading trailing slash after http host like https://www.domain.com/



      $this->xml_writer->writeElement('loc', $http_host . $url);

      if ($lastmod)
         $this->xml_writer->writeElement('lastmod', $lastmod);
 
      if ($changefreq)
         $this->xml_writer->writeElement('changefreq', $changefreq);

      if ($priority)
         $this->xml_writer->writeElement('priority', $priority);
 
      // End the 'url' element
      $this->xml_writer->endElement();

      // increment URL count so we can start a new <urlset> XML file if needed
      ++$this->url_count;
 
      return true;
   }



   /**
     * End the XML document. User has added all of their URLs and now we can
     * generate our sitemapindex XML file and send the generated XML to file
     * or browser (for testing/debugging).
     * 
     * @param $mode
     * @access public
     * @return bool
     */  
   public function endXmlDoc(): bool
   {
      // End the 'sitemapindex/urlset' element
      $this->xml_writer->endDocument();

      
      $this->outputXml();

      // create our sitemap index file
      $this->generateSitemapIndexFile();

      return true;
   }


   protected function generateSitemapIndexFile(): bool
   {
      #echo "num_sitemaps: $this->num_sitemaps, \$i = $i<br>";
      #die;

      // start XML doc <?xml version="1.0" ? > and 'sitemapindex' tag
      $this->startXmlDoc($xml_ns_type = 'sitemapindex');

      // generate X number of <sitemap> entries for each of the urlset sitemaps
      for ($i = 1; $i <= $this->num_sitemaps; ++$i)
      {
         // Start the 'sitemap' element
         
         $this->xml_writer->startElement('sitemap');

         #if ($this->http_host == true)
         
         $this->xml_writer->writeElement('loc', $url);
         $this->xml_writer->writeElement('lastmod', date('Y-m-d\TH:i:s+00:00'));
         $this->xml_writer->endElement();
         
         #echo "in for loop: \$this->num_sitemaps = $this->num_sitemaps, \$i = $i<br>";
      }

      return true;
   }

   protected function outputXml(): bool
   {
      #echo "<p>\$this->xml_mode: $this->xml_mode</p>";

      // Output the XML content
      if ($this->xml_mode == 'memory')
         echo '<pre>'.htmlspecialchars($this->xml_writer->outputMemory(), ENT_XML1 | ENT_COMPAT, 'UTF-8', true);
      else
         $this->xml_writer->outputMemory();

      return true;
   }
}
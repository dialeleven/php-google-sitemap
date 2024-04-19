<?php
/*
Abstracting the GoogleSitemap class for possible future support for various 
Sitemap extensions including Image sitemaps, News sitemaps, Video sitemaps and
alternatives (ref: https://developers.google.com/search/docs/crawling-indexing/sitemaps/image-sitemaps).

Currently it supports generating the required files for:
- Google XML Sitemaps (using /src/GoogleXmlSitemap.php)

*/
namespace Dialeleven\PhpGoogleSitemap;

use Exception;
use InvalidArgumentException;
use XMLWriter;


abstract class GoogleSitemap
{
   const MAX_SITEMAP_LINKS = 50000;
   //const MAX_SITEMAP_LINKS = 100; // for development testing
   const SITEMAP_FILENAME_SUFFIX = '.xml';
   //const MAX_FILESIZE = 10485760;       // 10MB maximum (unsupported feature currently)

   protected $sitemap_type;
   protected $xml_writer;
   protected $xml_mode = 'browser'; // send XML to 'browser' or 'file'
   protected $xml_files_dir; // directory where to save the XML files
   protected $url_count_current = 0; // total number of <loc> URL links for current <urlset> XML file
   protected $url_count_total = 0; // grand total number of <loc> URL links
   public $http_hostname; // http hostname (minus the "http://" part - e.g. www.yourdomain.com)
   protected $http_host_use_https = true; // flag to use either "https" or "http" as the URL scheme
   protected $url_scheme_host; // the combined scheme and host (e.g. 'https://' + 'www.domain.com')
   protected $use_gzip = false;
   protected $sitemap_filename_prefix = 'default_sitemap_filename'; // No file extension. 
                                                                    // Files will be named like YOUR_FILENAME_PREFIX1.xml.gz, YOUR_FILENAME_PREFIX2.xml.gz, etc
                                                                    // (e.g. if prefix is "sitemap_clients" then you will get a sitemap index
                                                                    // file "sitemap_clients_index.xml, and sitemap files "sitemap_clients1.xml.gz")
   protected $num_sitemaps = 0;              // total number of Sitemap files
   protected $urlset_xmlns_types_arr = array('xml'   => '', // XML doesn't have an additional XMLNS attribute like image/video/news
                                             'image' => 'http://www.google.com/schemas/sitemap-image/1.1',
                                             'video' => 'http://www.google.com/schemas/sitemap-video/1.1',
                                             'news'  => 'http://www.google.com/schemas/sitemap-news/0.9');

   
   // TODO: perhaps abstract the core methods and leave out setters/getters

   //abstract protected function startXmlNsElement(string $xml_ns_type = 'sitemapindex'): bool;
   //abstract protected function startNewUrlsetXmlFile(): void;
   //public function addUrl(string $url, string $lastmod = '', string $changefreq = '', string $priority = ''): bool;

   /*
   $tags_arr - additional child tags inside of <url>
   $special_tags_arr - for video sitemaps only
   */
   abstract function addUrl(string $loc, array $tags_arr = array(), array $special_tags_arr = array()): bool;


   //---------------------- CONCRETE METHODS - START ----------------------//
   /**
     * Constructor gets HTTP host to use in <loc> and where to save the XML files (optional).
     * By default, it will save to the script path that calls the GoogleXMLSitemap class.
     *
     * @param  string $http_hostname  http hostname to use for URLs - e.g. www.yourdomain.com or pass the $_SERVER['HTTP_HOST']
     * @param  string $xml_files_dir  full document root path and subdirectory path to save files

     * @access public
     * @return void
     */
   public function __construct(string $sitemap_type, string $http_hostname, string $xml_files_dir = '')
   {  
      $this->sitemap_type = $sitemap_type;
      $this->http_hostname = $http_hostname;
      $this->xml_files_dir = $xml_files_dir;
      
      // Create a new XMLWriter instance
      $this->xml_writer = new XMLWriter();

      $this->checkSitemapType($sitemap_type); // check for valid sitemap type (xml, image, video, news)
      $this->checkDirectoryTrailingSlash($xml_files_dir); // ensure directory includes trailing slash

      $this->setXmlMode('file'); // should be 'file' mode unless debugging in 'memory' (browser)

      $this->setUrlSchemeHost(); // assemble scheme+host (e.g. https://hostname.ext)
   }

   
   /**
     * Set flag for "use HTTPS" in host name. Assemble full URL scheme+host propery string.
     * @access protected
     * @return void
     */
   public function setUseHttpsUrls(bool $use_https_urls): void
   {
      $this->http_host_use_https = $use_https_urls;

      // update the URL scheme+host as we toggle http/https on or off
      $this->setUrlSchemeHost();
   }
 
 
    public function setUseGzip(bool $use_gzip): void
    {
       if ($use_gzip)
          if (function_exists('ob_gzhandler') && ini_get('zlib.output_compression'))
             $this->use_gzip = $use_gzip;
          else
             throw new Exception('Gzip compression is not enabled on this server. Please enable "zlib.output_compression" in php.ini.');
       else
          $this->use_gzip = false;
    }
 
 
    protected function getUseGzip(): bool
    {
       return $this->use_gzip;
    }
 
 
    /**
      * Assemble the URL scheme+host string (e.g. 'https://' + 'www.domain.com')
      * @access protected
      * @return void
      */
    protected function setUrlSchemeHost(): void
    {
       $this->url_scheme_host = (($this->http_host_use_https) ? 'https://' : 'http://') . $this->http_hostname . '/';
    }
 
 
   /**
     * Set what mode to use for the XMLWriter interface. Either 'memory' (send to browser)
     * or 'file' (save to file). Memory mode should only be used for debugging/testing to
     * review the <urlset> XML contents easier than opening up the written XML file.
     * 
     * Created for development purposes of viewing the urlset XML file in the browser
     * immediately. This would just output one XML file of course.
     *
     * @param  string $xml_mode  http hostname to use for URLs - e.g. www.yourdomain.com or pass the $_SERVER['HTTP_HOST']
     * @access public
     * @return void
     */
   public function setXmlMode(string $xml_mode): void
   {
      $valid_modes = array('memory', 'file');

      // Validation for either 'memory' or 'file'
      if ( !in_array($xml_mode, array('memory', 'file') ) )
         throw new Exception("\$xml_mode: '$xml_mode' is not a valid option. Valid modes are " . print_r($valid_modes, true));

      $this->xml_mode = $xml_mode;
   }
 
 
   /**
     * @param 
     * @access public
     * @return string  $xml_mode
     */
   public function getXmlMode(): string
   {
      return $this->xml_mode;
   }

   public function getXmlFilesDir(): string
   {
      return $this->xml_files_dir;
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

    
   // TODO: unit test
   protected function checkSitemapType($sitemap_type): bool
   {
      if (!array_key_exists($sitemap_type, $this->urlset_xmlns_types_arr))
      {
         throw new Exception("'$sitemap_type' not in allowed sitemap types. Valid values are " . print_r($this->urlset_xmlns_types_arr, true));
      }
      else
      {
         #echo "$sitemap_type key found in ";
         #print_r($this->urlset_xmlns_types_arr, true);
         return true;
      }
   }

   
   /**
     * Check if the specified sitemaps directory included a trailing slash.
     * Add one if not present to avoid "mysubdirsitemap.xml" vs "mysubdir/sitemap.xml"
     * to avoid confusion where the file(s) are.
     * @access protected
     * @return void
     */
    protected function checkDirectoryTrailingSlash(string $xml_files_dir): void
    {
       if ($xml_files_dir AND !preg_match('#\/$#', $xml_files_dir))
          $this->xml_files_dir = $xml_files_dir . '/';
    }


   /**
     * Start the XML document. Use either 'memory' mode to send to browser or 'openURI()'
     * save as a file with the specified filename. Set indentation. Start XML file which includes:
     *   <?xml version="1.0" encoding="UTF-8"?>
     *   <!-- URLSET TAG HERE according to formats listed below -->
     * 
     *   e.g. XML sitemap
     *      <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
     * 
     *   e.g. image sitemap
     *      <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
     *   
     *   e.g. video sitemap
     *      <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">
     * 
     *   e.g. news sitemap
     *      <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
     * @param  string $xml_ns_type  values ('urlset' or 'sitemapindex') create either a <urlset xmlns> tag or <sitemapindex> tag
     * @access protected
     * @return bool
     */      
   public function startXmlDoc(string $xml_ns_type = 'urlset'): bool
   {
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
         {
            $uri = $this->xml_files_dir . "{$this->sitemap_filename_prefix}_index" . self::SITEMAP_FILENAME_SUFFIX;
            $uri_return_val = $this->xml_writer->openURI($uri);
         }
         // urlset file
         else
         {
            $uri = $this->xml_files_dir . $this->sitemap_filename_prefix . ($this->num_sitemaps + 1) . self::SITEMAP_FILENAME_SUFFIX;
            $uri_return_val = $this->xml_writer->openURI($uri);
         }

         // error opening the URI - path error or directory doesn't exist
         if ($uri_return_val == false) { throw new Exception("Error opening '$uri.' Please check your directory path and that the directory exists.***"); }
      }


      // Set indentation and line breaks for readability
      $this->xml_writer->setIndent(true);
      $this->xml_writer->setIndentString('   '); // Adjust the number of spaces for indentation as desired


      // Start the document with XML declaration and encoding
      $this->xml_writer->startDocument('1.0', 'UTF-8');

      // open our cotainting tag either 'sitemapindex' or 'urlset'
      $this->startXmlNsElement($xml_ns_type = 'urlset');

      // add additional attribute(s) to the <urlset> tag (if needed)
      $this->urlsetAdditionalAttributes();

      return true;
   }


   /**
     * End our </url> element 
     * @access public
     * @return bool
     * TODO: Unit test
     */
   protected function endUrl(): bool
   {
      if ( in_array($this->sitemap_type, array('xml', 'news', 'video')) )
         // End the 'url' element
         $this->xml_writer->endElement();

      // increment URL count so we can start a new <urlset> XML file if needed
      ++$this->url_count_current;
      ++$this->url_count_total;

      return true;
   }

   
   /**
     * Check if we need to start a new urlset XML file based on how many urls
     * have been added.
     * @access protected
     * @return void
     */   
   protected function startNewUrlsetXmlFile(): void
   {
      // start new XML file if we reach maximum number of URLs per urlset file
      if ($this->url_count_current >= self::MAX_SITEMAP_LINKS)
      {
         // close the </urlset> element in the current XML doc
         $this->xml_writer->endElement();

         // start new XML doc
         $this->startXmlDoc($xml_ns_type = 'urlset');

         // reset counter for current urlset XML file
         $this->url_count_current = 0;

         // increment number of sitemaps counter
         ++$this->num_sitemaps;
      }
      // first call to addURL(), so open up the XML file
      else if ($this->url_count_current == 0)
      {
         // start new XML doc
         $this->startXmlDoc($xml_ns_type = 'urlset');
         
         // increment number of sitemaps counter
         ++$this->num_sitemaps;
      }
   } 

   
   /**
     * Open the "xmlns" tag for either the 'sitemapindex' or 'urlset' list of
     * tags including the xmlns and xsi attributes needed. 
     * 
     * e.g. sitemap index follows:
     *   <sitemapindex xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
     * 
     * 'urlset' XML file container tag follows:
     *   <urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
     * @param $xml_ns_type ('sitemapindex' or 'urlset')
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

      return true;
   }


   // TODO: unit test
   protected function urlsetAdditionalAttributes(): bool
   {
      // If the sitemap type array element contains a value (e.g. 'image' => 'URI'), then write the attribute.
      // XML sitemaps do not require an additional xmlns:TYPE_NAME attribute, so the value for XML will be null
      // as in 'xml' => ''.
      if ($this->urlset_xmlns_types_arr[$this->sitemap_type])
      {
         $this->xml_writer->writeAttributeNS('xmlns', "$this->sitemap_type", null, $this->urlset_xmlns_types_arr[$this->sitemap_type]);
         return true;
      }
      else
         return false;
   }


   /**
     * Generate the sitemapindex XML file based on the number of urlset files
     * that were created.
     * 
     * @access protected
     * @return bool
     */  
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

         // our "loc" URL to each urlset XML file
         $loc = $this->url_scheme_host .  $this->sitemap_filename_prefix . $i . self::SITEMAP_FILENAME_SUFFIX;
         
         // add ".gz" gzip extension to filename if compressing with gzip
         if ($this->getUseGzip()) { $loc .= '.gz'; }

         $this->xml_writer->writeElement('loc', $loc);
         $this->xml_writer->writeElement('lastmod', date('Y-m-d\TH:i:s+00:00'));
         $this->xml_writer->endElement();
         
         #echo "in for loop: \$this->num_sitemaps = $this->num_sitemaps, \$i = $i<br>";
      }

      // End the document (sitemapindex)
      $this->xml_writer->endDocument();

      // Output the XML content
      //echo '<pre>'.htmlspecialchars($xmlWriter->outputMemory(), ENT_XML1 | ENT_COMPAT, 'UTF-8', true);
      $this->xml_writer->outputMemory();

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

      // output XML from memory using outputMemory() and format for browser if needed
      $this->outputXml();

      // gzip files if needed
      if ($this->getUseGzip()) { $this->gzipXmlFiles(); }

      // create our sitemap index file
      $this->generateSitemapIndexFile();

      return true;
   }
 
 
    /**
      * Gzip the <urlset> XML files and discard the original urlset file after
      * 
      * @access protected
      * @return bool
      */  
    protected function gzipXmlFiles(): bool
    {
      for ($i = 1; $i <= $this->num_sitemaps; ++$i)
      {
         $gz = gzopen($this->xml_files_dir . $this->sitemap_filename_prefix . $this->num_sitemaps . '.xml.gz', 'w9');
         
         // uncompressed gzip filename
         $filename = $this->xml_files_dir . $this->sitemap_filename_prefix . $this->num_sitemaps . '.xml';
         $handle = fopen($filename, "r");
         $contents = fread($handle, filesize($filename));

         if ($bytes_written = gzwrite($gz, $contents))
         {
            gzclose($gz);
            unlink($filename); // remove original urlset XML file to avoid dir clutter
         }
      }
 
       return true;
   }

   
   /**
     * Done with the XML file, so output what's in memory to file/browser.
     * 
     * @access protected
     * @return bool
     */  
   protected function outputXml(): bool
   {
      // Output the XML content nicely for 'memory' (browser output)
      if ($this->xml_mode == 'memory')
         echo '<pre>'.htmlspecialchars($this->xml_writer->outputMemory(), ENT_XML1 | ENT_COMPAT, 'UTF-8', true);
      else
         $this->xml_writer->outputMemory();

      return true;
   }
}
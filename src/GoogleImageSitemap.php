<?php
/*
Filename:         GoogleImageSitemap.php
Author:           Francis Tsao
Date Created:     04/14/2024
Purpose:          Creates a Google image sitemap <urlset> files and a <sitemapindex> for the number of URLs
                  added.

TODO: support/checking for MAX_FILESIZE
*/


/**
 * GoogleImageSitemap - create Google XML Sitemap (sitemapindex and urlset file(s))
 *
 * Sample usage
 * <code>
 * $my_sitemap = new Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap($http_hostname = 'www.testdomain.com');
 * $my_sitemap->setUseHttpsUrls(true); // use "https" mode for your URLs or plain "http"
 * $my_sitemap->setSitemapFilenamePrefix('mysitemap'); // set name of sitemap file minus ".xml" (e.g. mysitemap.xml)
 * foreach ($url_array as $url)
 * {
 *    $my_sitemap->addUrl($url = "$query_data->url/", $lastmod = '', $changefreq = '', $priority = '');
 * }
 * 
 * // signal when done adding URLs, so we can generate the sitemap index file (table of contents)
 *  $my_sitemap->endXmlDoc();
 * </code>
 *
 * @author Francis Tsao
 */
namespace Dialeleven\PhpGoogleXmlSitemap;

use Exception;
use InvalidArgumentException;
use XMLWriter;


require_once 'AbstractGoogleSitemap.php';



class GoogleImageSitemap extends GoogleSitemap
{
   protected function checkSitemapType($sitemap_type): bool
   {
      if (!array_key_exists($sitemap_type, $this->urlset_xmlns_types_arr))
      {
         throw new Exception("$sitemap_type not in allowed sitemap types. Valid values are " . print_r($this->urlset_xmlns_types_arr, true));
         return false;
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


   protected function urlsetAdditionalAttributes($sitemap_type = 'xml'): bool
   {
      // If the sitemap type array element contains a value (e.g. 'image' => 'URI'), then write the attribute.
      // XML sitemaps do not require an additional xmlns:TYPE_NAME attribute, so the value for XML will be null
      // as in 'xml' => ''.
      if ($this->urlset_xmlns_types_arr[$sitemap_type])
      {
         $this->xml_writer->writeAttributeNS('xmlns', "$sitemap_type", null, $this->urlset_xmlns_types_arr[$sitemap_type]);
         return true;
      }
      else
         return false;
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
         throw new Exception("\$xml_mode: $xml_mode is not a valid option. Valid modes are " . print_r($valid_modes, true));

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

      // remaining 'xmlns' attributes for both sitemapindex and urlset files are the same
      $this->xml_writer->writeAttributeNS('xmlns', 'xsi', null, 'http://www.w3.org/2001/XMLSchema-instance');
      $this->xml_writer->writeAttributeNS('xsi', 'schemaLocation', null, 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');

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
      if ($this->url_count_current >= parent::MAX_SITEMAP_LINKS)
      {
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
    public function addUrl(string $url, string $lastmod = '', string $changefreq = '', string $priority = ''): bool
    {
       // check if we need a new XML file
       $this->startNewUrlsetXmlFile();

       // Start the 'url' element
       $this->xml_writer->startElement('url');
 
      if (empty($url))
        throw new Exception("ERROR: url cannot be empty");

      // TODO: strip/add leading trailing slash after http host like https://www.domain.com/


      $this->xml_writer->writeElement('loc', $this->url_scheme_host . $url);

      if ($lastmod)
         $this->xml_writer->writeElement('lastmod', $lastmod);
 
      if ($changefreq)
         $this->xml_writer->writeElement('changefreq', $changefreq);

      if ($priority)
         $this->xml_writer->writeElement('priority', $priority);
 
      // End the 'url' element
      $this->xml_writer->endElement();

      // increment URL count so we can start a new <urlset> XML file if needed
      ++$this->url_count_current;
      ++$this->url_count_total;
 
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
         $loc = $this->url_scheme_host .  $this->sitemap_filename_prefix . $i . parent::SITEMAP_FILENAME_SUFFIX;
         
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
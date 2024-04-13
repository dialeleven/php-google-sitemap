<?php
/*
Abstracting the GoogleSitemap class for possible future support for various 
Sitemap extensions including Image sitemaps, News sitemaps, Video sitemaps and
alternatives (ref: https://developers.google.com/search/docs/crawling-indexing/sitemaps/image-sitemaps).

Currently it supports generating the required files for:
- Google XML Sitemaps (using /src/GoogleXmlSitemap.php)

*/
namespace Dialeleven\PhpGoogleXmlSitemap;



abstract class GoogleSitemap
{
   const MAX_SITEMAP_LINKS = 50000;
   #const MAX_SITEMAP_LINKS = 5; // for development testing
   const SITEMAP_FILENAME_SUFFIX = '.xml';
   //const MAX_FILESIZE = 10485760;       // 10MB maximum (unsupported feature currently)

   
   protected $xml_writer;
   protected $xml_mode = 'browser'; // send XML to 'browser' or 'file'
   protected $xml_files_dir; // directory where to save the XML files
   protected $url_count_current = 0; // total number of <loc> URL links for current <urlset> XML file
   protected $url_count_total = 0; // grand total number of <loc> URL links
   public $http_hostname; // http hostname (minus the "http://" part - e.g. www.yourdomain.com)
   protected $http_host_use_https = true; // flag to use either "https" or "http" as the URL scheme
   protected $url_scheme_host; // the combined scheme and host (e.g. 'https://' + 'www.domain.com')
   protected $use_gzip = false;
   protected $sitemap_filename_prefix = 'sitemap_filename'; // YOUR_FILENAME_PREFIX1.xml.gz, YOUR_FILENAME_PREFIX2.xml.gz, etc
                                                          // (e.g. if prefix is "sitemap_clients" then you will get a sitemap index
                                                          // file "sitemap_clients_index.xml, and sitemap files "sitemap_clients1.xml.gz")
   protected $num_sitemaps = 0;              // total number of Sitemap files

   abstract protected function startXmlNsElement(string $xml_ns_type = 'sitemapindex'): bool;
   abstract protected function startNewUrlsetXmlFile(): void;
   abstract public function addUrl(string $url, string $lastmod = '', string $changefreq = '', string $priority = ''): bool;
   abstract protected function generateSitemapIndexFile(): bool;

   
   // TODO: move to concrete method(s)
   abstract protected function gzipXmlFiles(): bool;
   abstract public function endXmlDoc(): bool;
   abstract protected function outputXml(): bool;


   //---------------------- CONCRETE METHODS - START ----------------------//

   /**
     * Start the XML document. Use either 'memory' mode to send to browser or 'openURI()'
     * save as a file with the specified filename. Set our indentation and then of course
     * start with the <?xml version="1.0" encoding="UTF-8"?> tag.
     * @access protected
     * @param  string $xml_ns_type  values ('urlset' or 'sitemapindex') create either a <urlset xmlns> tag or <sitemapindex> tag
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

      return true;
   }
}
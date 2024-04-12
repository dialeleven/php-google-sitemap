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
   #const MAX_SITEMAP_LINKS = 5;
   const SITEMAP_FILENAME_SUFFIX = '.xml';
   //const MAX_FILESIZE = 10485760;       // 10MB maximum (unsupported feature currently)


   abstract protected function startXmlNsElement(string $xml_ns_type = 'sitemapindex'): bool;
   abstract protected function startNewUrlsetXmlFile(): void;
   abstract public function addUrl(string $url, string $lastmod = '', string $changefreq = '', string $priority = ''): bool;
   abstract public function endXmlDoc(): bool;
   abstract protected function gzipXmlFiles(): bool;
   abstract protected function generateSitemapIndexFile(): bool;
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
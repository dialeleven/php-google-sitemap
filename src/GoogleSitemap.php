<?php
/*
Abstracting the GoogleSitemap class for possible future support for various 
Sitemap extensions including Image sitemaps, News sitemaps, Video sitemaps and
alternatives. 

See https://developers.google.com/search/docs/crawling-indexing/sitemaps/image-sitemaps
*/
abstract class GoogleSitemap
{
   protected function startXmlDoc(string $xml_ns_type = 'urlset'): bool
   {
      return true;
   }

   abstract protected function startXmlNsElement(string $xml_ns_type = 'sitemapindex'): bool;
   abstract protected function startNewUrlsetXmlFile(): void;
   abstract public function addUrl(string $url, string $lastmod = '', string $changefreq = '', string $priority = ''): bool;
   abstract public function endXmlDoc(): bool;
   abstract protected function gzipXmlFiles(): bool;
   abstract protected function generateSitemapIndexFile(): bool;
   abstract protected function outputXml(): bool;
}
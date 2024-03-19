<?php
namespace Dialeleven\PhpGoogleXmlSitemap;

use PHPUnit\Framework\TestCase;

class GoogleXmlSitemapTest extends TestCase
{
   // tests go here
   public function testClassConstructor()
   {
      // Instantiate the GoogleXmlSitemap class
      $mysitemap = new GoogleXmlSitemap($http_host = 'https://phpgoogle-xml-sitemap.localhost/');

      // Assert that the instantiated object is an instance of GoogleXmlSitemap
      $this->assertInstanceOf(GoogleXmlSitemap::class, $mysitemap);
   }
}
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

   public function testSetSitemapFilenamePrefix()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = 'https://phpgoogle-xml-sitemap.localhost/');

      $this->assertTrue($mysitemap->setSitemapFilenamePrefix('my_sitemap_filename'));
      $this->assertIsString($mysitemap->getSitemapFilenamePrefix());
      $this->assertStringContainsString('my_sitemap_filename', $mysitemap->getSitemapFilenamePrefix());
   }

   public function testSetSitemapChangefreq()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = 'http://www.domain.com');
      $mysitemap->setSitemapChangefreq('weekly');

      $this->assertIsString('weekly', $mysitemap->getSitemapChangefreq());
      $this->assertStringContainsString('weekly', $mysitemap->getSitemapChangefreq());
   }

   public function testSetHostnamePrefixFlag()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = 'http://www.domain.com');
      $mysitemap->setHostnamePrefixFlag(true);

      $this->assertIsBool($mysitemap->use_hostname_prefix);
      $this->assertTrue($mysitemap->use_hostname_prefix);

      $mysitemap->setHostnamePrefixFlag(false);
      $this->assertFalse($mysitemap->use_hostname_prefix);
   }

   public function testSetTotalLinks()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = 'http://www.domain.com');
      $mysitemap->setTotalLinks(10);

      $this->assertIsInt(10, $mysitemap->total_links);
      $this->assertEquals(10, $mysitemap->total_links);
   }

   public function testBuildSitemapIndexContents()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = 'http://www.domain.com');
      $mysitemap->buildSitemapIndexContents();

      $this->assertIsString($mysitemap->sitemap_index_contents);
   }

   public function testBuildSitemapIndexContentsUrlsOnly()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = 'http://www.domain.com');
      $mysitemap->buildSitemapIndexContentsUrlsOnly();

      $this->assertIsString($mysitemap->sitemap_index_contents);
   }

   /*
   public function testSetUseMysqlDbModeFlag()
   {
      // Instantiate the GoogleXmlSitemap class
      $mysitemap = new GoogleXmlSitemap($http_host = 'https://phpgoogle-xml-sitemap.localhost/');

      //$mysitemap->($use_db_mode = true, $pdo, $sql_total);

   }
   

   public function testSetPathAdjustmentToRootDir()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = 'https://phpgoogle-xml-sitemap.localhost/');

      // test setting a valid value
      $this->assertRegExp( '(#\.\./){1,}#', $mysitemap->setPathAdjustmentToRootDir($path_adj = 2) );
   }
   */
}
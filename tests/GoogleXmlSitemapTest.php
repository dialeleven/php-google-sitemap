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
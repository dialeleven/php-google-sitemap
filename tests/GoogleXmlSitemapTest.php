<?php
namespace Dialeleven\PhpGoogleXmlSitemap;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

class GoogleXmlSitemapTest extends TestCase
{
   // tests go here
   private static $pdo; // MySQL PDO object if doing a query
   
   public function setUp(): void
   {
      // set up MySQL PDO object for use with DB mode
      $db_host = 'localhost';
      $db_name = 'test';
      $db_username = 'root';
      $db_password = '';
      $db_port = 3308;

      $dsn = 'mysql:host=' . $db_host . ';dbname=' . $db_name . ';port=' . $db_port;

      $options = [
         PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
         #PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         #PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_LAZY,
     
         PDO::ATTR_EMULATE_PREPARES   => false,
     ];

      self::$pdo = new PDO($dsn, $db_username, $db_password, $options);
   }


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
   */

   public function testSetPathAdjustmentToRootDir()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = 'https://phpgoogle-xml-sitemap.localhost/');
      $mysitemap->setPathAdjustmentToRootDir(2);

      // test setting a valid value
      $this->assertMatchesRegularExpression( '#(\.\./)*#', $mysitemap->getPathAdjustmentToRootDir());

      // test passing 5
      $this->assertTrue($mysitemap->setPathAdjustmentToRootDir(5));

      // test passing 1
      $this->assertTrue($mysitemap->setPathAdjustmentToRootDir(1));

      // test passing zero (should normally be >= 1)
      $this->assertFalse($mysitemap->setPathAdjustmentToRootDir(0));

      // test passing negative num
      $this->assertFalse($mysitemap->setPathAdjustmentToRootDir(-1));
   }

   public function testWriteSitemapIndexFile()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = 'https://phpgoogle-xml-sitemap.localhost/');

      $this->assertIsBool($mysitemap->writeSitemapIndexFile());
   }

   public function testSetUseMysqlDbModeFlag()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = 'https://phpgoogle-xml-sitemap.localhost/');
      /*
      // Create a mock PDO object
      $mockPDO = $this->getMockBuilder(PDO::class)
                      ->disableOriginalConstructor()
                      ->getMock();

      // Set up any expectations or method calls on the mock object
      $mockPDO->expects($this->once())
              ->method('prepare')
              ->willReturn($this->createMock(PDOStatement::class));
      */

      $this->assertIsBool($mysitemap->setUseMysqlDbModeFlag($use_db_mode = true, self::$pdo, $sql_total = 'SELECT 1 as total'));
   }

   public function testBuildSitemapContents()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = '');

      $this->assertIsString($mysitemap->buildSitemapContents($sql_limit = ''));
   }
}
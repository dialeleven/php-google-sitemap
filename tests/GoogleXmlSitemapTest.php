<?php
namespace Dialeleven\PhpGoogleXmlSitemap;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use ReflectionMethod;
use ReflectionProperty;

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

   public function testAddUrl()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = '');

      $this->assertIsBool($mysitemap->addUrl($url = 'http://www.google.com'));
      $this->assertIsBool($mysitemap->addUrl($url = 'http://www.google.com', $lastmod = '2024-03-24'));
      $this->assertIsBool($mysitemap->addUrl($url = 'http://www.google.com', $lastmod = '2024-03-24', $changefreq = 'weekly'));
      $this->assertIsBool($mysitemap->addUrl($url = 'http://www.google.com', $lastmod = '2024-03-24', $changefreq = 'weekly', $priority = '1.0'));
      #$this->assertIsBool($mysitemap->addUrl($url = 'http://www.google.com'));
   }

   public function testStartXmlDoc()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = '');

      // allow access to protected method for testing using ReflectionMethod - need "use ReflectionMethod;" at top
      $method = new ReflectionMethod('Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap', 'startXmlDoc');

      // make protected method accessible for testing
      $method->setAccessible(true);
  
      // invoke protected method and pass whatever param is needed
      $result = $method->invoke($mysitemap, $mode = 'memory');
      
      $this->assertTrue($result);
   }

   public function testStartXmlNsElement()
   {
      $myObject = new GoogleXmlSitemap($http_host = '');

      // Create a ReflectionProperty object for the private property
      $reflectionProperty = new ReflectionProperty(GoogleXmlSitemap::class, 'xml_writer');

      // Make the private property accessible
      $reflectionProperty->setAccessible(true);

      // Access the value of the private property
      $value = $reflectionProperty->getValue($myObject);

      // Assert the value or perform any necessary checks
      #$this->assertEquals('expectedValue', $value);
      $this->assertNotNull($value);


      

      // allow access to protected method for testing using ReflectionMethod - need "use ReflectionMethod;" at top
      $method = new ReflectionMethod('Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap', 'startXmlDoc');

      // make protected method accessible for testing
      $method->setAccessible(true);
  
      // invoke protected method and pass whatever param is needed
      $result = $method->invoke($myObject, $mode = 'memory');
      
      $this->assertTrue($result);



      // allow access to protected method for testing using ReflectionMethod - need "use ReflectionMethod;" at top
      $method = new ReflectionMethod('Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap', 'startXmlNsElement');

      // make protected method accessible for testing
      $method->setAccessible(true);
  
      // invoke protected method and pass whatever param is needed
      $result = $method->invoke($myObject, $xml_ns_type = 'sitemapindex');
      
      #$this->assertTrue($result);
   }


   public function testAddUrl2()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = '');

      // allow access to protected method for testing using ReflectionMethod - need "use ReflectionMethod;" at top
      $method = new ReflectionMethod('Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap', 'startXmlDoc');

      // make protected method accessible for testing
      $method->setAccessible(true);
  
      // invoke protected method and pass whatever param is needed
      $result = $method->invoke($mysitemap, $mode = 'memory');
      
      $this->assertTrue($result);

      // call addUrlNew() method
      $this->assertTrue($mysitemap->addUrlNew2($url = 'http://www.domain.com/yourpath/', $lastmod = '2024-01-01', $changefreq = 'weekly', $priority = '1.0'));
      
      // invalid test
      #$this->assertTrue($mysitemap->addUrlNew($url, $lastmod, $changefreq, $priority));


      
      // Create a ReflectionProperty object for the private property
      $reflectionProperty = new ReflectionProperty(GoogleXmlSitemap::class, 'url_count');

      // Make the private property accessible
      $reflectionProperty->setAccessible(true);

      // Access the value of the private property
      $value = $reflectionProperty->getValue($mysitemap);

      // Assert the value or perform any necessary checks
      #$this->assertEquals('expectedValue', $value);
      $this->assertEquals(1, $value);

   }
}
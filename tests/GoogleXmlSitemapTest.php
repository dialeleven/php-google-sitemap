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
      $mysitemap = new GoogleXmlSitemap($http_hostname = 'https://phpgoogle-xml-sitemap.localhost/');

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

   public function testCheckDirectoryTrailingSlash()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = 'https://phpgoogle-xml-sitemap.localhost/', $xml_files_dir = $_SERVER['DOCUMENT_ROOT'] . '/public/sitemaps');

      // allow access to protected method for testing using ReflectionMethod - need "use ReflectionMethod;" at top
      $method = new ReflectionMethod('Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap', 'checkDirectoryTrailingSlash');

      // make protected method accessible for testing
      $method->setAccessible(true);
  
      // invoke protected method and pass whatever param is needed
      $result = $method->invoke($mysitemap, $xml_ns_type = '/some/path');


      // Create a ReflectionProperty object for the private property
      $reflectionProperty = new ReflectionProperty(GoogleXmlSitemap::class, 'xml_files_dir');

      // Make the private property accessible
      $reflectionProperty->setAccessible(true);

      // Access the value of the private property
      $xml_files_dir_value = $reflectionProperty->getValue($mysitemap);

      $this->assertStringEndsWith('/', $xml_files_dir_value);
   }

   public function testSetUseHttpsUrls()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = '');
      $mysitemap->setUseHttpsUrls(true);

      // Create a ReflectionProperty object for the private property
      $reflectionProperty = new ReflectionProperty(GoogleXmlSitemap::class, 'http_host_use_https');

      // Make the private property accessible
      $reflectionProperty->setAccessible(true);

      // Access the value of the private property
      $value = $reflectionProperty->getValue($mysitemap);

      $this->assertTrue($value);


      // Create a ReflectionProperty object for the private property
      $reflectionProperty = new ReflectionProperty(GoogleXmlSitemap::class, 'url_scheme_host');

      // Make the private property accessible
      $reflectionProperty->setAccessible(true);

      // Access the value of the private property
      $value = $reflectionProperty->getValue($mysitemap);

      // use https was set to true, so url scheme should contain 'https://'
      $this->assertStringContainsString('https://', $value);

      

      $mysitemap->setUseHttpsUrls(false);

      // Create a ReflectionProperty object for the private property
      $reflectionProperty = new ReflectionProperty(GoogleXmlSitemap::class, 'http_host_use_https');

      // Make the private property accessible
      $reflectionProperty->setAccessible(true);

      // Access the value of the private property
      $value = $reflectionProperty->getValue($mysitemap);

      $this->assertFalse($value);


      // Create a ReflectionProperty object for the private property
      $reflectionProperty = new ReflectionProperty(GoogleXmlSitemap::class, 'url_scheme_host');

      // Make the private property accessible
      $reflectionProperty->setAccessible(true);

      // Access the value of the private property
      $value = $reflectionProperty->getValue($mysitemap);

      // use https was set to false, so url scheme should contain 'http://'
      $this->assertStringContainsString('http://', $value);
   }

   public function testSetUseGzip()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = '');
      $mysitemap->setUseGzip(true);

      // allow access to protected method for testing using ReflectionMethod - need "use ReflectionMethod;" at top
      $method = new ReflectionMethod('Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap', 'getUseGzip');

      // make protected method accessible for testing
      $method->setAccessible(true);
  
      // invoke protected method and pass whatever param is needed
      $result = $method->invoke($mysitemap, $param = '');
      
      $this->assertTrue($result);

      
      $mysitemap->setUseGzip(false);

      // allow access to protected method for testing using ReflectionMethod - need "use ReflectionMethod;" at top
      $method = new ReflectionMethod('Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap', 'getUseGzip');

      // make protected method accessible for testing
      $method->setAccessible(true);
  
      // invoke protected method and pass whatever param is needed
      $result = $method->invoke($mysitemap, $param = '');
      
      $this->assertFalse($result);
   }


   public function testSetUrlSchemeHost()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = 'https://phpgoogle-xml-sitemap.localhost/', $xml_files_dir = $_SERVER['DOCUMENT_ROOT'] . '/public/sitemaps');

      // allow access to protected method for testing using ReflectionMethod - need "use ReflectionMethod;" at top
      $method = new ReflectionMethod('Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap', 'setUrlSchemeHost');

      // make protected method accessible for testing
      $method->setAccessible(true);
  
      // invoke protected method and pass whatever param is needed
      $result = $method->invoke($mysitemap, $param = '');


      // Create a ReflectionProperty object for the private property
      $reflectionProperty = new ReflectionProperty(GoogleXmlSitemap::class, 'url_scheme_host');

      // Make the private property accessible
      $reflectionProperty->setAccessible(true);

      // Access the value of the private property
      $url_scheme_host_val = $reflectionProperty->getValue($mysitemap);

      // use https was set to false, so url scheme should contain 'http://'
      $this->assertStringContainsString('https://', $url_scheme_host_val);
   }


   public function testSetXmlMode()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = 'https://phpgoogle-xml-sitemap.localhost/', $xml_files_dir = $_SERVER['DOCUMENT_ROOT'] . '/public/sitemaps');

      $mysitemap->setXmlMode($xml_mode = 'file');
      $this->assertStringMatchesFormat('file', $mysitemap->getXmlMode());

      $mysitemap->setXmlMode($xml_mode = 'memory');
      $this->assertStringMatchesFormat('memory', $mysitemap->getXmlMode());

      // error testing
      /*
      $mysitemap->setXmlMode($xml_mode = 'invalid');
      $this->assertStringMatchesFormat('memory', $mysitemap->getXmlMode());
      */
   }
   public function testStartXmlDoc()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = '');

      // Create a ReflectionProperty object for the private property
      $reflectionProperty = new ReflectionProperty(GoogleXmlSitemap::class, 'xml_writer');

      // Make the private property accessible
      $reflectionProperty->setAccessible(true);

      // Access the value of the private property
      $value = $reflectionProperty->getValue($mysitemap);




      // allow access to protected method for testing using ReflectionMethod - need "use ReflectionMethod;" at top
      $method = new ReflectionMethod('Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap', 'startXmlDoc');

      // make protected method accessible for testing
      $method->setAccessible(true);
  
      // invoke protected method and pass whatever param is needed
      $result = $method->invoke($mysitemap, $xml_ns_type = 'urlset');
      
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

   public function testAddUrl()
   {
      $mysitemap = new GoogleXmlSitemap($http_host = '');

      // allow access to protected method for testing using ReflectionMethod - need "use ReflectionMethod;" at top
      $method = new ReflectionMethod('Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap', 'startXmlDoc');

      // make protected method accessible for testing
      $method->setAccessible(true);
  
      // invoke protected method and pass whatever param is needed
      $result = $method->invoke($mysitemap, $mode = 'memory');
      
      $this->assertTrue($result);

      // call addUrl() method
      $this->assertTrue($mysitemap->addUrl($url = 'http://www.domain.com/yourpath/', $lastmod = '2024-01-01', $changefreq = 'weekly', $priority = '1.0'));
      
      // invalid test
      #$this->assertTrue($mysitemap->addUrl($url, $lastmod, $changefreq, $priority));


      
      // Create a ReflectionProperty object for the private property
      $reflectionProperty = new ReflectionProperty(GoogleXmlSitemap::class, 'total_url_count');

      // Make the private property accessible
      $reflectionProperty->setAccessible(true);

      // Access the value of the private property
      $value = $reflectionProperty->getValue($mysitemap);

      // Assert the value or perform any necessary checks
      #$this->assertEquals('expectedValue', $value);
      $this->assertEquals(1, $value);
   }
   /*
   public function testStartNewUrlsetXmlFile()
   {
      
   }

   public function testEndXmlDoc()
   {

   }

   public function testGzipXmlFiles()
   {

   }

   public function testGenerateSitemapIndexFile()
   {

   }

   public function testOutputXml()
   {

   }
   */
}
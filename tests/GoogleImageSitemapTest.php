<?php
namespace Dialeleven\PhpGoogleSitemap;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use ReflectionMethod;
use ReflectionProperty;

class GoogleImageSitemapTest extends TestCase
{
   // tests go here
   private static $pdo; // MySQL PDO object if doing a query
   protected $xml_files_dir;
   
   public function setUp(): void
   {
      // Using $_SERVER['DOCUMENT_ROOT'] is not possible within PHPUnit because 
      // PHPUnit doesn't run within the context of a web server. 
      // Instead, we have to use an alternative method to get the base path.
      $this->xml_files_dir = dirname(__DIR__) . '/public/sitemaps';
   }

   public function testClassConstructor()
   {
      // Instantiate the GoogleImageSitemap class
      $mysitemap = new GoogleImageSitemap($sitemap_type = 'image', $http_hostname = 'https://phpgoogle-xml-sitemap.localhost/', $this->xml_files_dir);

      // Assert that the instantiated object is an instance of GoogleImageSitemap
      $this->assertInstanceOf(GoogleImageSitemap::class, $mysitemap);
   }

   public function testAddUrl()
   {
      $mysitemap = new GoogleImageSitemap($sitemap_type = 'image', $http_hostname = 'https://phpgoogle-xml-sitemap.localhost/', $this->xml_files_dir);

      // allow access to protected method for testing using ReflectionMethod - need "use ReflectionMethod;" at top
      $method = new ReflectionMethod('Dialeleven\PhpGoogleSitemap\GoogleImageSitemap', 'startXmlDoc');

      // make protected method accessible for testing
      $method->setAccessible(true);
  
      // invoke protected method and pass whatever param is needed
      $result = $method->invoke($mysitemap, $mode = 'memory');
      
      $this->assertTrue($result);
      
      // call addUrl() method
      $this->assertTrue($mysitemap->addUrl($loc = 'http://www.domain.com/yourpath/'));
      
      // invalid test
      #$this->assertTrue($mysitemap->addUrl($loc, $tags_arr = array('not' => 'allowed')));


      
      // Create a ReflectionProperty object for the private property
      $reflectionProperty = new ReflectionProperty(GoogleImageSitemap::class, 'image_sitemap_url_count');

      // Make the private property accessible
      $reflectionProperty->setAccessible(true);

      // Access the value of the private property
      $value = $reflectionProperty->getValue($mysitemap);

      // Assert the value or perform any necessary checks
      #$this->assertEquals('expectedValue', $value);
      $this->assertEquals(1, $value);
   }
}
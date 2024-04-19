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
      // Instead, you we have to use an alternative method to get the base path.
      $this->xml_files_dir = dirname(__DIR__) . '/public/sitemaps';
   }

   public function testClassConstructor()
   {
      // Instantiate the GoogleXmlSitemap class
      $mysitemap = new GoogleXmlSitemap($sitemap_type = 'image', $http_hostname = 'https://phpgoogle-xml-sitemap.localhost/', $this->xml_files_dir);

      // Assert that the instantiated object is an instance of GoogleXmlSitemap
      $this->assertInstanceOf(GoogleXmlSitemap::class, $mysitemap);
   }

   public function testAddUrl()
   {
      $mysitemap = new GoogleXmlSitemap($sitemap_type = 'image', $http_hostname = 'https://phpgoogle-xml-sitemap.localhost/', $this->xml_files_dir);

      // allow access to protected method for testing using ReflectionMethod - need "use ReflectionMethod;" at top
      $method = new ReflectionMethod('Dialeleven\PhpGoogleXmlSitemap\GoogleXmlSitemap', 'startXmlDoc');

      // make protected method accessible for testing
      $method->setAccessible(true);
  
      // invoke protected method and pass whatever param is needed
      $result = $method->invoke($mysitemap, $mode = 'memory');
      
      $this->assertTrue($result);
      
      // call addUrl() method
      $this->assertTrue($mysitemap->addUrl($url = 'http://www.domain.com/yourpath/', $tags_arr = array('name' => 'The Example Times', 'language' => 'en', 'publication_date' => '2024-04-01', 'title' => 'Sample Article Title')));
      
      // invalid test
      #$this->assertTrue($mysitemap->addUrl($url, $lastmod, $changefreq, $priority));


      
      // Create a ReflectionProperty object for the private property
      $reflectionProperty = new ReflectionProperty(GoogleXmlSitemap::class, 'url_count_total');

      // Make the private property accessible
      $reflectionProperty->setAccessible(true);

      // Access the value of the private property
      $value = $reflectionProperty->getValue($mysitemap);

      // Assert the value or perform any necessary checks
      #$this->assertEquals('expectedValue', $value);
      $this->assertEquals(1, $value);
   }
}
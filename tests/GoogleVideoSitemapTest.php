<?php
namespace Dialeleven\PhpGoogleSitemap;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use ReflectionMethod;
use ReflectionProperty;

class GoogleVideoSitemapTest extends TestCase
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
      $mysitemap = new GoogleVideoSitemap($sitemap_type = 'video', $http_hostname = 'https://phpgoogle-xml-sitemap.localhost/', $this->xml_files_dir);

      // Assert that the instantiated object is an instance of GoogleXmlSitemap
      $this->assertInstanceOf(GoogleVideoSitemap::class, $mysitemap);
   }

   public function testAddUrl()
   {
      $mysitemap = new GoogleVideoSitemap($sitemap_type = 'video', $http_hostname = 'https://phpgoogle-xml-sitemap.localhost/', $this->xml_files_dir);

      // allow access to protected method for testing using ReflectionMethod - need "use ReflectionMethod;" at top
      $method = new ReflectionMethod('Dialeleven\PhpGoogleSitemap\GoogleVideoSitemap', 'startXmlDoc');

      // make protected method accessible for testing
      $method->setAccessible(true);
  
      // invoke protected method and pass whatever param is needed
      $result = $method->invoke($mysitemap, $mode = 'memory');
      
      $this->assertTrue($result);
      
      // call addUrl() method
      $this->assertTrue(
         $mysitemap->addUrl($loc = 'http://www.domain.com/yourpath/', 
                            $tags_arr = array('thumbnail_loc' => 'https://example.com/thumbs/thumbnail.jpg', 
                                             'title' => 'My Video Title', 
                                             'description' => '2024-04-01', 
                                             'content_loc' => 'http://streamserver.example.com/video123.mp4',
                                             'player_loc' => 'https://www.example.com/videoplayer.php?video=123'),
                            $special_tags_arr = array(
                                                      array('restriction', 'relationship', 'allow', 'IE GB US CA'),
                                                      array('price', 'currency', 'EUR', '1.99'), 
                                                      array('uploader', 'info', 'https://www.example.com/users/grillymcgrillerson', 'GrillyMcGrillerson')
                                                    )
                           ));
      
      // invalid test
      #$this->assertTrue($mysitemap->addUrl($loc, $tags_arr));


      
      // Create a ReflectionProperty object for the private property
      $reflectionProperty = new ReflectionProperty(GoogleVideoSitemap::class, 'url_count_total');

      // Make the private property accessible
      $reflectionProperty->setAccessible(true);

      // Access the value of the private property
      $value = $reflectionProperty->getValue($mysitemap);

      // Assert the value or perform any necessary checks
      #$this->assertEquals('expectedValue', $value);
      $this->assertEquals(1, $value);
   }
}
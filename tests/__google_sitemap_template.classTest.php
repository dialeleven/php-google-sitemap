<?php
namespace Dialeleven\PhpGoogleXmlSitemap;

use PHPUnit\Framework\TestCase;

class GoogleSitemapTest extends TestCase
{
   // tests go here
   public function testClassConstructor()
   {
      $this->assertInstanceOf('GoogleSitemap::class', GoogleSitemap::class);

   }
}
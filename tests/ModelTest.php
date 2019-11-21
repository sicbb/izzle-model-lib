<?php
namespace Izzle\Tests;

use DateTime;
use Izzle\Model\Model;
use Izzle\Model\PropertyInfo;
use JsonSerializable;
use PHPUnit\Framework\TestCase;
use Serializable;

class ModelTest extends TestCase
{
    /**
     * @var Book
     */
    protected $book;
    
    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        
        $this->book = new Book(json_decode(file_get_contents(__DIR__ . '/Mocks/book_one.json'), true));
    }
    
    public function testCanBeCreatedWithData(): void
    {
        $this->assertInstanceOf(
            Model::class,
            $this->book
        );
        
        $this->checkProperties($this->book);
    }
    
    public function testCanGetProperties(): void
    {
        $this->assertCount(6, $this->book->properties()->toArray());
    }
    
    public function testCanGetPropertyByName(): void
    {
        $property = $this->book->property('name');
        
        $this->assertNotEmpty($property);
        $this->assertEquals('name', $property->getName());
    }
    
    public function testCanBeConvertedToArray(): void
    {
        $bookData = $this->book->toArray();
        $this->assertIsArray(
            $bookData
        );
        
        $data = json_decode(file_get_contents(__DIR__ . '/Mocks/book_one.json'), true);
        
        foreach ($data as $key => $value) {
            $this->assertArrayHasKey($key, $bookData);
        }
        
        $this->assertIsArray($bookData['pages']);
        $this->assertIsArray($bookData['pages'][0]);
        $this->assertEquals(1, $bookData['pages'][0]['page']);
    }
    
    public function testSnakeCaseKeysCanBeDisabled(): void
    {
        Model::$serializeWithSnakeKeys = false;
        $data = $this->book->toArray();
        $this->assertArrayHasKey('stockLevel', $data);
        $this->assertArrayNotHasKey('stock_level', $data);
        $this->assertArrayHasKey('pages', $data);
        $this->assertArrayHasKey('currentPage', $data);
        $this->assertArrayNotHasKey('current_page', $data);
        $this->assertArrayNotHasKey('created_at', $data);
        
        Model::$serializeWithSnakeKeys = true;
    }
    
    public function testCanBeSerializedWithDateTimeFormats(): void
    {
        $json = json_encode($this->book);
        $data = json_decode($json, true);
        $this->assertEquals('2019-03-20T08:30:31.461+00:00', $data['created_at']);
    }
    
    public function testImplementsJson(): void
    {
        $this->assertInstanceOf(
            JsonSerializable::class,
            $this->book
        );
    }
    
    public function testImplementsSerializable(): void
    {
        $this->assertInstanceOf(
            Serializable::class,
            $this->book
        );
    }
    
    public function testCanBeSerialized(): void
    {
        $this->assertNotEmpty(serialize($this->book));
    }
    
    public function testCanBeDeserialized(): void
    {
        $serialized = serialize($this->book);
        $book = unserialize($serialized);
        
        $this->assertInstanceOf(
            Book::class,
            $book
        );
        
        $this->assertEquals($this->book, $book);
        
        $this->checkProperties($book);
    }
    
    public function testCanCastValue(): void
    {
        $this->assertEquals(4, $this->book->cast('4', new PropertyInfo('foo', 'int', 0)));
        $this->assertEquals(
            new DateTime('2019-01-29T07:57:47.664+00:00'),
            $this->book->cast('2019-01-29T07:57:47.664+00:00', new PropertyInfo('createdAt', DateTime::class, null))
        );
        
        $this->assertEquals(
            new DateTime('2019-01-29T07:57:47.664+00:00'),
            $this->book->cast(new DateTime(
                '2019-01-29T07:57:47.664',
                new \DateTimeZone('UTC')
            ), new PropertyInfo('createdAt', DateTime::class, null))
        );
    }
    
    public function testCanCastableToString(): void
    {
        $this->assertNotEmpty((string)$this->book);
    }
    
    public function testImplementsArrayAccess(): void
    {
        $this->assertTrue(isset($this->book['name']));
        $this->assertEquals('Moby Dick', $this->book['name']);
    
        $this->book['name'] = 'FooBar';
        $this->assertEquals('FooBar', $this->book['name']);
        
        unset($this->book['name']);
        $this->assertNull($this->book['name']);
    }
    
    public function testCanInstatiateIncompleteModel(): void
    {
        $product = new Product([
            'id' => 'foobar',
            'description' => 'Test'
        ]);
        
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEmpty($product->getDescription());
    }
    
    /**
     * @param Book $book
     */
    private function checkProperties(Book $book): void
    {
        $this->assertEquals($book->getId(), 3);
        $this->assertEquals($book->getName(), 'Moby Dick');
        $this->assertEquals($book->getStockLevel(), 4);
        $this->assertIsArray($book->getPages());
        $this->assertCount(2, $book->getPages());
        
        foreach ($book->getPages() as $page) {
            $this->assertInstanceOf(
                Page::class,
                $page
            );
        }
    
        $this->assertInstanceOf(
            Page::class,
            $book->getCurrentPage()
        );
    
        $this->assertEquals($book->getCurrentPage()->getPage(), 1);
        $this->assertEquals($book->getCurrentPage()->getChapter(), 'intro');
    }
}

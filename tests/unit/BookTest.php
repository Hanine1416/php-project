<?php

use Doctrine\Common\Collections\ArrayCollection;
use MainBundle\Entity\Book;

class BookTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var Book $book
     */
    protected $book;

    protected function _before()
    {
        $this->book = new Book();
    }

    protected function _after()
    {
    }

    public function testBookConstructor(){
        $data = '<product isbn13="9780323597388">
	<businessunit>Nsg/Hlth Prof - BU8</businessunit>
	<contractorigin>US</contractorigin>
	<copsproducttype>003</copsproducttype>
	<copyrightyear>2021</copyrightyear>
	<ednumber>5</ednumber>
	<exporttowebflag>t</exporttowebflag>
	<extemeafeatures />
	<ieflag>f</ieflag>
	<imprint>Saunders</imprint>
	<stockavail />
	<isbn13>9780323597388</isbn13>
	<lepubdate>2020-12-01</lepubdate>
	<mscp_desc>HRP - Diag Med Sonography</mscp_desc>
	<multivolumeflag>f</multivolumeflag>
	<notforsale />
	<pagecountle>612</pagecountle>
	<pmccode>224</pmccode>
	<pmclongdesc>Rad/Imaging Technology</pmclongdesc>
	<pmgcode>026</pmgcode>
	<pmglongdesc>Health Professions II (HP2)</pmglongdesc>
	<pp_authoralistbyline>By &lt;STRONG&gt;M. Robert de Jong&lt;/STRONG&gt;, RDMS, RDCS, RVT, FSDMS, Radiology Technical Manager - Ultrasound, The Russell H. Mo ▶
Baltimore, Maryland\n
</pp_authoralistbyline>
	<pp_authorblistbyline>By M. Robert de Jong, RDMS, RDCS, RVT, FSDMS</pp_authorblistbyline>
	<pp_authortext>de Jong</pp_authortext>
	<pp_extrelatedestitles />
	<productweight>0</productweight>
	<profitctr>09 - Rad/Imaging technology</profitctr>
	<pubdateuk>2021-01-12</pubdateuk>
	<pubstatus>NYP - Not yet published</pubstatus>
	<saleableancillaryflag>f</saleableancillaryflag>
	<sbu>Health Professions II (HP2)</sbu>
	<sendtocopsflag>t</sendtocopsflag>
	<sendtodelta>t</sendtodelta>
	<intlstockavail />
	<title>Sonography Scanning</title>
	<subtitle>Principles and Protocols</subtitle>
	<titletype />
	<titlewsube>Sonography Scanning: Principles and Protocols, 5e</titlewsube>
	<trim>260 x 184 (7 1/4 x 10 1/4)</trim>
	<ukpubstatus>NYP - Not yet published</ukpubstatus>
	<weightuom>g</weightuom>
	<acquisitionseditor>Seigafuse, Sonya (ELS-PHI)</acquisitionseditor>
	<publisher />
	<marketingmanager>Major, Ed (ELS-STL)</marketingmanager>\n
	<marketrestriction />
	<anzpubdatele>2021-01-12</anzpubdatele>
	<anzpubstatus>NYP - Not Yet Published</anzpubstatus>
	<indpubdatele>2021-01-12</indpubdatele>
	<pubdateopco>2020-12-01</pubdateopco>
	<pubstatusopco>NYP</pubstatusopco>\n
	<madiscemea>10 - Textbooks</madiscemea>
	<essubjectcode>PROMISH0240015 Health Professions - Radiological &amp; Ultrasound Technology</essubjectcode>
	<essubjectcode>PROMISH0241003 Health Professions - Ultrasonography</essubjectcode>
	<cover>Book/Paperback</cover>
	<auedprimary>de Jong</auedprimary>
	<pp_copyillustrationstext>Approx. 2340 illustrations</pp_copyillustrationstext>
	<price_USD type="Contract/Proposal - BFM">115.00</price_USD>
	<price_USD type="Estimated/Transmittal - BFM">115.00</price_USD>
	<price_CAD type="Estimated/Transmittal - BFM">159.00</price_CAD>
	<price_GBP type="Estimated/Transmittal - BFM">84.99</price_GBP>
	<price_EUR type="Estimated/Transmittal - BFM">104.00</price_EUR>
	<price_AUD type="Actual">150.00</price_AUD>
	<price_NZD type="Actual">172.17</price_NZD>
	<price_USD type="Actual">125.00</price_USD>
	<price_GBP type="Actual">93.99</price_GBP>
	<price_EUR type="Actual">106.00</price_EUR>
	<price_CAD type="Actual">162.00</price_CAD>
	<price_INR type="NA" />
	<price_JYP type="NA" />
	<price_KRW type="NA" />
	<price_NTD type="NA" />
	<relatedISBN13>9780323776585</relatedISBN13>
	<relatedISBN13>9781455773213</relatedISBN13>
	<relatedISBN13>9780323764261</relatedISBN13>
	<relatedISBN13>9780323764261</relatedISBN13>
	<relatedISBN13>9780323764278</relatedISBN13>
	<relatedISBN13>9780323764278</relatedISBN13>
	<relatedISBN13>9780323764254</relatedISBN13>
	<prevedition>9781455773213</prevedition>
	<language>English (US)</language>
	<versiontype>Book - Paperback</versiontype>
	<deliverystatus>In production</deliverystatus>
	<pttr>Text</pttr>
	<deliverystatusopco>In production</deliverystatusopco>
</product>';
        $bookData = new \SimpleXMLElement($data);
        $book = new Book($bookData);

    }

    public function testBookDescription()
    {
        /**  Get and Set book description */
        $this->book->setDescription('description');
        $this->assertNotEquals('desc', $this->book->getDescription());
    }
    public function testBookIsbn()
    {
        /** Get and set book isbn */
        $this->book->setIsbn('9780702071041');
        $this->assertNotEquals('78956', $this->book->getIsbn());
    }
    public function testBookTitle()
    {
        /** Get and set book title */
        $this->book->setTitle('Book title');
        $this->assertEquals('Book title', $this->book->getTitle());
    }
    public function testBookAboutAuthor()
    {
        /** Get and set book about author */
        $this->book->setAboutAuthor('About author section');
        $this->assertEquals('About author section', $this->book->getAboutAuthor());
    }
    public function testBookAuthor()
    {
        /** Get and set book author */
        $this->book->setAuthor('Author name');
        $this->assertNotEquals('Author', $this->book->getAuthor());
    }
    public function testBookAvailableTypes()
    {
        /**  Test available types is not null */
        $availableTypes = new ArrayCollection();
        $this->assertNotNull($availableTypes);
        /**  Get and set book available types */
       /* $this->book->setAvailableTypes($availableTypes);*/
        $this->book->addAvailableType($availableTypes);
        /*$this->assertEquals($availableTypes, $this->book->getAvailableTypes());*/
    }
    public function testBookContentTable()
    {
        /** Get and set book Content Table */
        $this->book->setContentTable('content table');
        $this->assertNotEquals('content', $this->book->getContentTable());
    }
    public function testBookCopyRight()
    {
        /**  Get and set book copy right year */
        $this->book->setCopyrightYear('2000');
        $this->assertNotEquals('1999', $this->book->getCopyrightYear());
    }
    public function testBookDetails()
    {
        /** Get and set book details */
        $this->book->setDetails('Book details');
        $this->assertNotEquals('Book', $this->book->getDetails());
    }
    public function testBookDisciplines()
    {
        /** Get and set book disciplines */
        $disciplines = new stdClass();
        $this->book->setDisciplines($disciplines);
        $this->assertNotEquals('mlm', $this->book->getDisciplines());
    }
    public function testBookEditionNumber()
    {
        /** Get and set book edition number */
        $this->book->setEditionNumber(2);
        $this->assertEquals(2, $this->book->getEditionNumber());
    }
    public function testBookEditors()
    {
        /** Get and set book editors */
        $this->book->setEditors('editors');
        $this->assertNotEquals('editor', $this->book->getEditors());
    }
    public function testBookIllustrations()
    {
        /** Get and set book illustrations */
        $this->book->setIllustrations('Approx. 423 illustrations');
        $this->assertNotEquals('Approx. 587 illustrations', $this->book->getIllustrations());
    }
    public function testBookPageCount()
    {
        /** Get and set book page count */
        $this->book->setPageCount('100');
        $this->assertEquals('100', $this->book->getPageCount());
    }
    public function testBookKeyFeatures()
    {
        /** Get and set book key features */
        $this->book->setKeyFeatures('Key features');
        $this->assertEquals('Key features', $this->book->getKeyFeatures());
    }
    public function testBookNewFeatures()
    {
        /** Get and set book new features */
        $this->book->setNewFeatures('New Feature');
        $this->assertNotEquals('Old feature', $this->book->getNewFeatures());
    }
    public function testBookPrice()
    {
        /** Get and set book price */
        $price = ['12$','78£'] ;
        $this->book->setPrice($price);
        $this->assertNotEquals('45$', $this->book->getPrice());
        $this->assertCount(2, $this->book->getPrice());
    }
    public function testBookPublicationDate()
    {
        /** Get and set book publication date */
        $pubDate = ['17-05-2013'];
        $this->book->setPublicationDate($pubDate);
        $this->assertEquals($pubDate, $this->book->getPublicationDate());
    }
    public function testBookLanguage()
    {
        /** Get and set book language */
        $this->book->setLanguage('en');
        $this->assertNotEquals('es', $this->book->getLanguage());
    }
    public function testBookRegion()
    {
        /** Get and set book region */
        $this->book->setRegion('10');
        $this->assertNotEquals('7', $this->book->getRegion());
    }
    public function testBookReview()
    {
        /** Get and set book review */
        $this->book->setReviews('This book is great!');
        $this->assertNotEquals('Interesting book', $this->book->getReviews());
    }
    public function testBookShortAuthor()
    {
        /** Get and set book short author */
        $this->book->setShortAuthor('Short author');
        $this->assertNotEquals('Long Author', $this->book->getShortAuthor());
    }
    public function testBookStockCount()
    {
        /** Get and set book stock count */
        $this->book->setStockCount('50');
        $this->assertEquals('50', $this->book->getStockCount());
    }
    public function testBookSubCategory()
    {
        /** Get and set book subcategory */
        $subCategory = new ArrayCollection();
        $this->assertNotNull($subCategory);
        $this->book->setSubCategory($subCategory);
        $this->assertEquals($subCategory, $this->book->getSubCategory());
    }
    public function testBookSubtitle()
    {
        /** Get and set book subtitle */
        $this->book->setSubtitle('subtitle');
        $this->assertEquals('subtitle', $this->book->getSubtitle());
    }
    public function testBookSerialize()
    {
        $this->book->serialize();
    }

    public function testBookToString(){
        $isbn= '1921554474';
        $book = new Book();
        $book->setIsbn($isbn);
        $this->assertEquals($isbn,$book->__toString());
    }

    public function testBookAncillary() {
        /**  Ancillary is not null */
        $ancillaryContent= new ArrayCollection();
        $this->assertNotNull($ancillaryContent);
        /**  Get and set book ancillary */
        $this->book->setAncillary($ancillaryContent);
        $this->book->addAncillary(array());
        $this->assertEquals($ancillaryContent, $this->book->getAncillary());
    }
    public function testBookAudience()
    {
        /** Get and set book audience */
        $this->book->setAudience('Graduate students, scientists, and clinicians interested in molecular and cell biology interested in gene therapy.');
        $this->assertNotEquals('Graduate students.', $this->book->getAudience());
    }

    public function testBookCkAvailable()
    {
        /** Get and set book CkAvailable */
        $this->book->setCkAvailable(true);
        $this->assertEquals(true, $this->book->isCkAvailable());
        $this->assertNotEquals(false, $this->book->isCkAvailable());
    }

    public function testBookCkUrl()
    {
        /** Get and set book ckUrl */
        $this->book->setCkUrl('https://www.clinicalkey.com/student/content/toc/3-s2.0-C2017001217X');
        $this->assertEquals('https://www.clinicalkey.com/student/content/toc/3-s2.0-C2017001217X', $this->book->getCkUrl());
    }

    public function testBookTag()
    {
        /** Get and set book tag */
        $this->book->setTag('isNew');
        $this->assertEquals('isNew', $this->book->getTag());
    }

    public function testBooksExternalBookIdPrev()
    {
        /** Get and set book tag */
        $this->book->setExternalBookIdPrev('0013698192650157e029d');
        $this->assertEquals('0013698192650157e029d', $this->book->getExternalBookIdPrev());
    }
    public function testBookFeedBacks()
    {
        /**  Test feedBacks is not null */
        $feedBacks = new ArrayCollection();
        $this->assertNotNull($feedBacks);
        /**  Get and set book feedBacks */
        $this->book->setFeedBacks($feedBacks);
        $this->book->addFeedBack($feedBacks);
        $this->assertEquals($feedBacks, $this->book->getFeedBacks());
    }
}

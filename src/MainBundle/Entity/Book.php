<?php

/*
 * This file is part of the Inspection Copy.
 * Copyright (C) 2019 Elsevier.
 * Created by mobelite.
 *
 * Date: 4/11/18
 * Time: 17:38
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace MainBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Book
 * @package MainBundle\Entity
 */
class Book
{
    /**
     * @var string $isbn
     */
    private $isbn;

    /**
     * @var string $title
     */
    private $title;

    /**
     * @var string $subtitle
     */
    private $subtitle;

    /**
     * @var string $editors
     */
    private $editors;

    /**
     * @var string $language
     */
    private $language;

    /**
     * @var string $region
     */
    private $region;

    /**
     * @var string $description
     */
    private $description;

    /**
     * @var array $publicationDate
     */
    private $publicationDate;

    /**
     * @var string $disciplines
     */
    private $disciplines;

    /**
     * @var string pageCount
     */
    private $pageCount;

    /**
     * @var string $reviews
     */
    private $reviews;
    /**
     * @var string $aboutAuthor
     */
    private $aboutAuthor;
    /**
     * @var string $shortAuthor
     */
    private $shortAuthor;
    /**
     * @var string $details
     */
    private $details;
    /**
     * @var string $contentTable
     */
    private $contentTable;
    /**
     * @var integer $editionNumber
     */
    private $editionNumber;
    /**
     * @var string $keyFeatures
     */
    private $keyFeatures;
    /**
     * @var string $newFeatures
     */
    private $newFeatures;

    /**
     * @var string $author
     */
    private $author;

    /**
     * @var array $price
     */
    private $price;

    /**
     * @var ArrayCollection $availableTypes
     */
    private $availableTypes;

    /**
     * @var integer $stockCount
     */
    private $stockCount;

    /**
     * @var string
     */
    private $copyrightYear;

    /**
     * @var ArrayCollection $subCategory
     */
    private $subCategory;

    /**
     * @var string $illustrations
     */
    private $illustrations;

    /**
     * @var string $audience
     */
    private $audience;

    /**
     * @var ArrayCollection $ancillary
     */
    private $ancillary;

    /**
     * @var string $externalBookId
     */
    private $externalBookId;
    /**
     * @var string $externalBookIdPrev
     */
    private $externalBookIdPrev;
    /**
     * @var string $recommendedBy
     */
    private $recommendedBy;

    /**
     * @var float $rating
     */
    private $rating;

    /**
     * @var int $reviewsNumber
     */
    private $numReviews;

    /**
     * @var bool $ckAvailable
     */
    private $ckAvailable;

    /**
     * @var string $ckUrl
     */
    private $ckUrl;

    /**
     * @var string
     */
    private $tag;

    /**
     * @var ArrayCollection $feedBacks
     */
    private $feedBacks;

    /**
     * @var string
     */
    private $pubdateopco;

    /**
     * @var bool
     */
    private $HasStudentResources;

    /**
     * @var bool
     */
    private $HasProfessorResources;

    /**
     * Book constructor.
     * @param \SimpleXMLElement $bookXml
     */
    public function __construct(\SimpleXMLElement $bookXml = null)
    {
        if ($bookXml) {
            $this->isbn = (string)$bookXml->isbn13;
            $this->title = (string)$bookXml->title;
            $this->subtitle = (string)$bookXml->subtitle;
            $this->pageCount = (string)$bookXml->pagecountle;
            $this->illustrations = (string)$bookXml->pp_copyillustrationstext;
            $this->audience = (string)$bookXml->mktslsaudience;
            $this->editionNumber = (int)$bookXml->ednumber;
            $this->publicationDate = [
                'en' => (string)$bookXml->pubdateuk,
                'in' => (string)$bookXml->pubdateopco,
                'br' => (string)$bookXml->pubdateopco,
                'es' => (string)$bookXml->pubdateopco,
                'fr' => (string)$bookXml->pubdateopco,
                'de' => (string)$bookXml->pubdateopco,
                'us' => (string)$bookXml->pubdateopco,
                'anz' => (string)$bookXml->pubdateopco,
            ];
            $this->copyrightYear = (string)$bookXml->copyrightyear;
            $this->pubdateopco = (string)$bookXml->pubdateopco;
            $this->editors = (string)$bookXml->pp_authorblistbyline;
            $this->contentTable = (string)$bookXml->pp_toclong;
            $this->aboutAuthor = (string)$bookXml->pp_authoralistbyline;
            $this->shortAuthor = (string)$bookXml->pp_authortext;
            $this->details = (string)$bookXml->pp_extrelatedestitles;
            $this->reviews = (string)$bookXml->pp_reviews;
            if ((string)$bookXml->pp_CopyGeneralDescription != null) {
                $this->description = (string)$bookXml->pp_CopyGeneralDescription;
            } else {
                $this->description = (string)$bookXml->extukdescription;
            }
            $this->keyFeatures = (string)$bookXml->pp_copykeyfeatures;
            $this->newFeatures = (string)$bookXml->pp_newtoed;
            $this->price = [
                'GBP' => array_slice((array)$bookXml->price_GBP, 1),
                'EUR' => array_slice((array)$bookXml->price_EUR, 1),
                'USD' => array_slice((array)$bookXml->price_USD, 1),
                'MXN' => array_slice((array)$bookXml->price_MXN, 1),
                'BRL' => array_slice((array)$bookXml->price_BRL, 1),
                'AUD' => array_slice((array)$bookXml->price_AUD, 1),
                'NZD' => array_slice((array)$bookXml->price_NZD, 1)
            ];
            $this->stockCount = (int)$bookXml->stockavail;
            /** retrieve subCategory from book info and each one of it as array of id & name */
            $this->subCategory = new ArrayCollection();
            foreach ($bookXml->essubjectcode as $subCategory) {
                $this->subCategory->add([
                    'id' => substr($subCategory, 0, strpos($subCategory, ' ')),
                    'category' => substr(
                        $subCategory,
                        strpos($subCategory, ' ') + 1,
                        strpos($subCategory, '-') - strpos($subCategory, ' ') - 2
                    ),
                    'name' => substr($subCategory, strpos($subCategory, '-') + 2)]);
            }
        }
        $this->availableTypes = new ArrayCollection();
        $this->ancillary = new ArrayCollection();
        $this->ckAvailable = false;
        $this->ckUrl = "";
        $this->feedBacks = new ArrayCollection();
    }


    /**
     * @return string
     */
    public function getReviews(): string
    {
        return $this->reviews;
    }

    /**
     * @param string $reviews
     */
    public function setReviews(string $reviews)
    {
        $this->reviews = $reviews;
    }

    /**
     * @return string
     */
    public function getAboutAuthor(): string
    {
        return $this->aboutAuthor;
    }

    /**
     * @param string $aboutAuthor
     */
    public function setAboutAuthor(string $aboutAuthor)
    {
        $this->aboutAuthor = $aboutAuthor;
    }

    /**
     * @return string
     */
    public function getShortAuthor(): string
    {
        return $this->shortAuthor;
    }

    /**
     * @param string $shortAuthor
     */
    public function setShortAuthor(string $shortAuthor): void
    {
        $this->shortAuthor = $shortAuthor;
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return $this->details;
    }

    /**
     * @param string $details
     */
    public function setDetails(string $details)
    {
        $this->details = $details;
    }

    /**
     * @return string
     */
    public function getContentTable(): string
    {
        return $this->contentTable;
    }

    /**
     * @param string $contentTable
     */
    public function setContentTable(string $contentTable)
    {
        $this->contentTable = $contentTable;
    }

    /**
     * @return int
     */
    public function getEditionNumber(): int
    {
        return $this->editionNumber;
    }

    /**
     * @param int $editionNumber
     */
    public function setEditionNumber(int $editionNumber)
    {
        $this->editionNumber = $editionNumber;
    }

    /**
     * @return string
     */
    public function getKeyFeatures(): string
    {
        return $this->keyFeatures;
    }

    /**
     * @param string $keyFeatures
     */
    public function setKeyFeatures(string $keyFeatures)
    {
        $this->keyFeatures = $keyFeatures;
    }

    /**
     * @return string
     */
    public function getNewFeatures(): string
    {
        return $this->newFeatures;
    }

    /**
     * @param string $newFeatures
     */
    public function setNewFeatures(string $newFeatures)
    {
        $this->newFeatures = $newFeatures;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title?$this->title:'';
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getIsbn(): string
    {
        return $this->isbn;
    }

    /**
     * @param string $isbn
     */
    public function setIsbn(string $isbn)
    {
        $this->isbn = $isbn;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage(string $language)
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }


    /**
     * @return array
     */
    public function getPublicationDate(): ?array
    {
        return $this->publicationDate;
    }

    /**
     * @param array $publicationDate
     */
    public function setPublicationDate(array $publicationDate)
    {
        $this->publicationDate = $publicationDate;
    }

    /**
     * @return string
     */
    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * @param string $region
     */
    public function setRegion(string $region)
    {
        $this->region = $region;
    }

    /**
     * @return string
     */
    public function getDisciplines(): string
    {
        return $this->disciplines;
    }

    /**
     * @param \stdClass $disciplines
     */
    public function setDisciplines(\stdClass $disciplines)
    {
        $this->disciplines = isset($disciplines->Discipline) ? $disciplines->Discipline : '';
    }

    /**
     * @return string
     */
    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    /**
     * @param string $subtitle
     */
    public function setSubtitle(string $subtitle)
    {
        $this->subtitle = $subtitle;
    }

    /**
     * @return string
     */
    public function getEditors(): ?string
    {
        return $this->editors;
    }

    /**
     * @param string $editors
     */
    public function setEditors(string $editors)
    {
        $this->editors = $editors;
    }

    /**
     * @return ArrayCollection
     */
    public function getAvailableTypes(): ArrayCollection
    {
        return $this->availableTypes;
    }

    /**
     * @param ArrayCollection $availableTypes
     */
    public function setAvailableTypes(ArrayCollection $availableTypes)
    {
        $this->availableTypes = $availableTypes;
    }

    /**
     * @param $availableType
     */
    public function addAvailableType(string $availableType)
    {
        $this->availableTypes->add($availableType);
    }


    /**
     * @return array
     */
    public function getPrice(): array
    {
        return $this->price;
    }

    /**
     * @param array $price
     */
    public function setPrice(array $price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getPageCount(): string
    {
        return $this->pageCount;
    }

    /**
     * @param string $pageCount
     */
    public function setPageCount(string $pageCount)
    {
        $this->pageCount = $pageCount;
    }

    /**
     * @return string
     */
    public function getStockCount(): string
    {
        return $this->stockCount;
    }

    /**
     * @param string $stockCount
     */
    public function setStockCount(string $stockCount)
    {
        $this->stockCount = $stockCount;
    }

    /**
     * @return string
     */
    public function getCopyrightYear()
    {
        return $this->copyrightYear;
    }

    /**
     * @param string $copyrightYear
     */
    public function setCopyrightYear(string $copyrightYear)
    {
        $this->copyrightYear = $copyrightYear;
    }

    /**
     * @return string
     */
    public function getIllustrations(): string
    {
        return $this->illustrations;
    }

    /**
     * @param string $illustrations
     */
    public function setIllustrations(string $illustrations): void
    {
        $this->illustrations = $illustrations;
    }

    /**
     * @return string
     */
    public function getAuthor(): ?string
    {
        return $this->author ? $this->author:$this->editors;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author)
    {
        $this->author = $author;
    }

    /**
     * @return ArrayCollection
     */
    public function getSubCategory(): ArrayCollection
    {
        return $this->subCategory;
    }

    /**
     * @param ArrayCollection $subCategory
     */
    public function setSubCategory(ArrayCollection $subCategory)
    {
        $this->subCategory = $subCategory;
    }

    public function __toString()
    {
        return $this->isbn;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return [
            'description' => $this->description,
            'subCategory' => $this->subCategory,
            'author' => $this->author,
            'copyrightYear' => $this->copyrightYear,
            'stockCount' => $this->stockCount,
            'pageCount' => $this->pageCount,
            'price' => $this->price,
            'editors' => $this->editors,
            'subtitle' => $this->subtitle,
            'disciplines' => $this->disciplines,
            'region' => $this->region,
            'language' => $this->language,
            'details' => $this->details,
            'publicationDate' => $this->publicationDate,
            'isbn' => $this->isbn,
            'title' => $this->title,
            'newFeatures' => $this->newFeatures,
            'keyFeatures' => $this->keyFeatures,
            'reviews' => $this->reviews,
            'audience' => $this->audience,
            'ckAvailable' => $this->ckAvailable,
            'ckUrl' => $this->ckUrl,
        ];
    }

    /**
     * @return string
     */
    public function getAudience(): string
    {
        return $this->audience;
    }

    /**
     * @param string $audience
     */
    public function setAudience(string $audience): void
    {
        $this->audience = $audience;
    }

    /**
     * @return ArrayCollection
     */
    public function getAncillary(): ArrayCollection
    {
        return $this->ancillary;
    }

    /**
     * @param ArrayCollection $ancillary
     */
    public function setAncillary(ArrayCollection $ancillary)
    {
        $this->ancillary = $ancillary;
    }

    /**
     * @param $ancillary
     */
    public function addAncillary(array $ancillary)
    {
        $this->ancillary->add($ancillary);
    }

    /**
     * @return string
     */
    public function getExternalBookId(): string
    {
        return $this->externalBookId;
    }

    /**
     * @param string $externalBookId
     */
    public function setExternalBookId(string $externalBookId): void
    {
        $this->externalBookId = $externalBookId;
    }
    /**
     * @return string
     */
    public function getExternalBookIdPrev(): string
    {
        return $this->externalBookIdPrev;
    }

    /**
     * @param string $externalBookIdPrev
     */
    public function setExternalBookIdPrev(string $externalBookIdPrev): void
    {
        $this->externalBookIdPrev = $externalBookIdPrev;
    }

    /**
     * @return string
     */
    public function getRecommendedBy(): string
    {
        return $this->recommendedBy;
    }

    /**
     * @param string $recommendedBy
     */
    public function setRecommendedBy(string $recommendedBy): void
    {
        $this->recommendedBy = $recommendedBy;
    }

    /**
     * @return float
     */
    public function getRating(): float
    {
        return $this->rating;
    }

    /**
     * @param float $rating
     */
    public function setRating(float $rating): void
    {
        $this->rating = $rating;
    }

    /**
     * @return int
     */
    public function getNumReviews(): int
    {
        return $this->numReviews;
    }

    /**
     * @param int $numReviews
     */
    public function setNumReviews(int $numReviews): void
    {
        $this->numReviews = $numReviews;
    }

    /**
     * @return bool
     */
    public function isCkAvailable(): bool
    {
        return $this->ckAvailable;
    }

    /**
     * @param bool $ckAvailable
     */
    public function setCkAvailable(bool $ckAvailable): void
    {
        $this->ckAvailable = $ckAvailable;
    }

    /**
     * @return string
     */
    public function getCkUrl(): string
    {
        return $this->ckUrl;
    }

    /**
     * @param string $ckUrl
     */
    public function setCkUrl(string $ckUrl): void
    {
        $this->ckUrl = $ckUrl;
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     */
    public function setTag(string $tag): void
    {
        $this->tag = $tag;
    }

    /**
     * @return ArrayCollection
     */
    public function getFeedBacks(): ArrayCollection
    {
        return $this->feedBacks;
    }

    /**
     * @param ArrayCollection $feedBacks
     */
    public function setFeedBacks(ArrayCollection $feedBacks)
    {
        $this->feedBacks = $feedBacks;
    }

    /**
     * @param $availableType
     */
    public function addFeedBack(string $feedBack)
    {
        $this->feedBacks->add($feedBack);
    }

    /**
     * @return string
     */
    public function getPubdateopco(): string
    {
        return $this->pubdateopco;
    }

    /**
     * @param string $pubdateopco
     */
    public function setPubdateopco(string $pubdateopco): void
    {
        $this->pubdateopco = $pubdateopco;
    }

    /**
     * @return bool
     */
    public function getHasProfessorResources(): ?bool
    {
        return $this->HasProfessorResources ? $this->HasProfessorResources : false;
    }

    /**
     * @param bool $HasProfessorResources
     */
    public function setHasProfessorResources(bool $HasProfessorResources): void
    {
        $this->HasProfessorResources = $HasProfessorResources;
    }

    /**
     * @return bool
     */
    public function getHasStudentResources(): ?bool
    {
        return $this->HasStudentResources ? $this->HasStudentResources : false;
    }

    /**
     * @param bool $HasStudentResources
     */
    public function setHasStudentResources(bool $HasStudentResources): void
    {
        $this->HasStudentResources = $HasStudentResources;
    }

}

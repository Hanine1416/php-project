<?php
/**
 * Created by PhpStorm.
 * User: mobelite
 * Date: 21/05/2019
 * Time: 11:03
 */

namespace MBComponents\Twig\Extensions;

use Doctrine\Common\Collections\ArrayCollection;
use MainBundle\Services\BookService;
use UserBundle\Entity\Notification;

class BookExtension extends AppExtension {
    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('getRequestDetails', [$this, 'getRequestDetails']),
            new \Twig_SimpleFunction('create_mixed_book', [$this, 'createMixedArray']),
            new \Twig_SimpleFunction('bookHasNotif', [$this, 'isBookWithNotif']),
            new \Twig_SimpleFunction('subCategoryExist', [$this, 'subCategoryExist']),
            new \Twig_SimpleFunction('getSelectedCategoryType', [$this, 'getSelectedCategoryType']),
            new \Twig_SimpleFunction('getCategoryByType', [$this, 'getCategoryByType']),
            new \Twig_SimpleFunction('validCategory', [$this, 'validCategory']),
            new \Twig_SimpleFunction('getCoversByType', [$this, 'getCoversByType']),
            new \Twig_SimpleFunction('getUrlPurchaseBook', [$this,'getUrlPurchaseBook']),
            new \Twig_SimpleFunction('isBookHS', [$this,'isBookHS']),
            new \Twig_SimpleFunction('treeDisplayAncillary', [$this,'treeDisplayAncillary']),
            new \Twig_SimpleFunction('getDuplicateBook', [$this,'getDuplicateBook'])
        ];
    }

    /**
     * @return array
     */
    public function getFilters(): array {
        return [
            new \Twig_SimpleFilter('sort_preorder', array($this, 'sortByPreorderedLast')),
            new \Twig_SimpleFilter('sort_by_date', array($this, 'sortByDate')),
            new \Twig_SimpleFilter('sort_by_read_date', array($this, 'sortByReadAndDate'))
        ];
    }

    /**
     * return book request details (Digital|Print) status
     * @param string $isbn
     * @param BookService $bookService
     * @param string $type
     * @return array|null
     * @throws \Exception
     */
    public function getRequestDetails(string $isbn, BookService $bookService, string $type = 'Print'): ?array {
        /** check if the user is logged in */
        if ($this->isLoggedIn())
        {
            /** get user books */
            $userBooks = $bookService->getUserBooks($this->getLoggedInUser()->getUserId(), true, $isbn);
            foreach (array_merge($userBooks['Approved'], $userBooks['Pending'], $userBooks['Adopted'],  $userBooks['MixedAdoption']) as $book)
            {
                /** Test book requested */
                if ($isbn == $book["Isbn"] && (isset($book["Format"]) && $book['Format'] == $type))
                {
                    return $book;
                }
            }
        }
        return null;
    }

    /**
     * Sort notification by newest date
     * @param $notifications
     * @return ArrayCollection
     */
    public function sortByDate(ArrayCollection $notifications) {
        $iterator = $notifications->getIterator();
        $iterator->uasort(function (Notification $a, Notification $b)
        {
            if ($a->getDate() > $b->getDate())
            {
                return -1;
            } elseif ($a->getDate() < $b->getDate())
            {
                return 1;
            } else
            {
                return 0;
            }
        });
        return new ArrayCollection(iterator_to_array($iterator));
    }

    /**
     * Sort notification by is not read and newest date
     * @param $notifications
     * @return ArrayCollection
     */
    public function sortByReadAndDate(ArrayCollection $notifications) {
        $iterator = $notifications->getIterator();
        $iterator->uasort(function (Notification $a, Notification $b)
        {
            if ($a->getIsRead() && !$b->getIsRead())
            {
                $orderPosition = 1;
            } elseif (!$a->getIsRead() && $b->getIsRead())
            {
                $orderPosition = -1;
            } elseif ($a->getDate() < $b->getDate())
            {
                $orderPosition = 1;
            } elseif ($a->getDate() > $b->getDate())
            {
                $orderPosition = -1;
            } else
            {
                $orderPosition = 0;
            }
            return $orderPosition;
        });
        return new ArrayCollection(iterator_to_array($iterator));
    }

    /** Return if book has notification
     * @param $book
     * @param string $status
     * @param ArrayCollection $notifications
     * @return bool
     */
    public function isBookWithNotif($book, string $status, ArrayCollection $notifications) {
        if (is_array($book))
        {
            $book = (object)$book;
        }
        /** @var Notification $notification */
        foreach ($notifications as $notification)
        {
            /** Test if notification  */
            if ($book->Isbn == $notification->getIsbn() &&
                !$notification->getIsRead() &&
                $this->isBookWithNotification($book, $status, $notification))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if book has notification for his format and status
     * @param $book
     * @param $status
     * @param $notification
     * @return bool
     */
    private function isBookWithNotification($book, $status, Notification $notification) {
        $result = false;
        if ($status == 'Approved' && $book->Format == 'Digital' &&
            in_array($notification->getEventType(), ["DigitalApproved", "DigitalAvailable", "DigitalAboutExpire"]))
        {
            $result = true;
        }
        if ($status == 'Approved' && $book->Format == 'Print' &&
            in_array($notification->getEventType(), ["PrintApproved", "PrintStatusChanged"]))
        {
            $result = true;
        }
        if ($status == 'Expired' && $book->Format == 'Digital' &&
            in_array($notification->getEventType(), ["FeedbackDue", "DigitalAboutExpire"]))
        {
            $result = true;
        }
        if ($status == 'Adopted' && $notification->getEventType() == "ConfirmationDue")
        {
            $result = true;
        }
        if ($status == 'Declined' && $book->Format == 'Print' &&
            $notification->getEventType() == "PrintStatusChanged")
        {
            $result = true;
        }
        return $result;
    }

    /**
     * Create mixed book array that contain adopted & not adopted
     * @param $array
     * @param $isbn
     * @param string $status
     * @param array $adoptions
     * @return array
     */
    public function createMixedArray($array, $isbn, array $adoptions, $status = "Adopted") {
        if (!isset($array[$isbn]))
        {
            $array[$isbn] = ['Adopted' => [], 'NotAdopted' => []];
        }
        $array[$isbn][$status] = $adoptions;
        return $array;
    }

    /**
     * Sort book set pre ordered books in end of the array
     * @param $books
     * @return array
     */
    public function sortByPreorderedLast($books) {
        usort($books, function ($book1, $book2)
        {
            if ($book1['PreOrder'] == $book2['PreOrder'])
            {
                return 0;
            }
            return $book1['PreOrder'] ? 1 : -1;
        });
        return $books;
    }

    /**
     * @param $category
     * @param $subcategories
     * @return bool
     */
    public function subCategoryExist($category,$subcategories): bool {
        if (isset($category['subcategories']) && !empty($category['subcategories'] && !empty($subcategories)) )
        {
            foreach ($category['subcategories'] as $subcategory)
            {
                if(array_key_exists($subcategory['text'],$subcategories))
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $selectedCategory
     * @param $categories
     * @return false|string
     */
    public function getSelectedCategoryType($selectedCategory,$categories) {
        foreach ($categories as $key => $category)
        {
            if ($selectedCategory === $category['category'])
            {
                return strpos($key,'PROMISH') !==false ? 'SH' : 'ST';
            }
        }
        return 'all';
    }

    /**
     * return list of categories foreach main categories
     * @param $categories
     * @return array
     */
    public function getCategoryByType($categories) {
        $nbCategoryByType = ['SH' => [], 'ST' => []];
        foreach ($categories as $key => $category)
        {
            $category['code'] = $key;
             strpos($key,'PROMISH') !==false ? array_push($nbCategoryByType['SH'],$category) : array_push($nbCategoryByType['ST'],$category);
        }
        return $nbCategoryByType;
    }

    public function getCoversByType($covers) {
        $nbCoversByType = ['SH' => [], 'ST' => []];
        foreach ($covers as $key => $cover)
        {
            $cover->getCategory() === "hs" ? array_push($nbCoversByType['SH'],$cover) : array_push($nbCoversByType['ST'],$cover);
        }
        return $nbCoversByType;
    }

    /**
     * Return true if book category is Health & Sciences
     * @param $category
     * @return bool
     */
    public function isBookHS($category) {
        return strpos($category, 'PROMISH') !== false ;
    }

    /**
     * Return Url according to book category and catalogue
     * @param $isbn
     * @param $reg
     * @param $category
     * @return string
     */
    public function getUrlPurchaseBook($isbn, $reg, $category) {
        $purchaseUrl = null;
        /**  Get if book has health & science category or Science & Technology*/
        $healthScience  = strpos($category,'PROMISH') !== false ;
        /** Return purchase Url according to the region and category */
        switch ($healthScience)
        {
            /** Return url according to category */
            case false:
                $purchaseUrl = "https://www.elsevier.com/books/catalog/isbn/".$isbn;
                break;
            case true:
                /** Return url according to region */
                switch ($reg)
                {
                    case '7' :
                        $purchaseUrl = "https://www.uk.elsevierhealth.com/catalogsearch/result/?filter_product_type=78&q=".$isbn;
                        break;
                    case '4' :
                        $purchaseUrl = "https://www.uk.elsevierhealth.com/catalogsearch/result/?filter_product_type=78&q=".$isbn;
                        break;
                    case'11':
                        $purchaseUrl = "https://www.elsevier-masson.fr/catalogsearch/result/?filter_product_type=&q=".$isbn ;
                        break;
                    case '12':
                        $purchaseUrl = "https://shop.elsevier.de/search/category/fachgebiet?filter_product_type=&q=".$isbn;
                        break;
                    case '6':
                        $purchaseUrl = "https://tienda.elsevier.es/catalogsearch/result/?filter_product_type=&q=".$isbn;
                        break;
                    case '1':
                        $purchaseUrl = "https://www.elsevierhealth.com.au/catalogsearch/result/?q=".$isbn;
                        break;
                    case '8':
                        $purchaseUrl = "https://www.store.elsevierhealth.com/asia/catalogsearch/result/?q=".$isbn;
                        break;
                    case '10':
                        $purchaseUrl = "https://www.store.elsevierhealth.com/asia/catalogsearch/result/?q=".$isbn;
                        break;
                    default:
                        $purchaseUrl = "https://www.uk.elsevierhealth.com/catalogsearch/result/?filter_product_type=78&q=".$isbn;
                        break;
                }
                break;
        }
        return $purchaseUrl;
    }

    public function treeDisplayAncillary(array $ancillary,bool $hasAccess): string
    {
        $result = '';
        foreach ($ancillary as $key => $item){
            if(array_key_exists('url',$item)){
                $result .='<li>'.($hasAccess?'<a href="'.$item['url'].'" download>'.$item['name'].'</a>':'<a class="disbled-ressource">'.$item['name'].'</a>').'</li>';
            }else{
                $result.='<li><a class="folder-link closed" data-target="folder-'.$key.'"><i class="far fa-folder mr-10" aria-hidden="true"></i>'.$key.'</a>
                    <ul id="folder-'.$key.'">'.$this->treeDisplayAncillary($item,$hasAccess).'</ul></li>';
            }
        }
        return $result;
    }

    /**
     * @param $adoptedBook
     * @param $books
     * @return array
     * this function return an array of historic and status of book exist or not for the book with the given isbn
     */
    public function getDuplicateBook($adoptedBook, $books, $lang) {
        $isbn = $adoptedBook['Isbn'];
        $result = ['exist' => false, 'historic' => [], 'dateOfRequest' => []];

        if (array_key_exists($isbn, $books['Approved']) && $adoptedBook['RequestedDate'] !== $books['Approved'][$isbn]['RequestedDate']) {
            $books['Approved'][$isbn]['statusHistoric'] = 'Approved';
            $books['Approved'][$isbn]['timeLineDate'] = date('Y', strtotime($books['Approved'][$isbn]['RequestedDate']));
            array_push($result['historic'], $books['Approved'][$isbn]);
            array_push($result['dateOfRequest'], $books['Approved'][$isbn]['RequestedDate']);
        }

        if (array_key_exists($isbn, $books['Adopted']) && isset($adoptedBook['RequestedDate']) && $adoptedBook['RequestedDate'] !== $books['Adopted'][$isbn]['RequestedDate']) {
            $books['Adopted'][$isbn]['statusHistoric'] = 'Adopted';
            $books['Adopted'][$isbn]['timeLineDate'] = date('Y', strtotime($books['Adopted'][$isbn]['RequestedDate']));
            array_push($result['historic'], $books['Adopted'][$isbn]);
            array_push($result['dateOfRequest'], $books['Adopted'][$isbn]['RequestedDate']);
        }

        if (array_key_exists($isbn, $books['NotAdopted']) && isset($adoptedBook['RequestedDate'] ) && $adoptedBook['RequestedDate'] !== $books['NotAdopted'][$isbn]['RequestedDate']) {
            $books['NotAdopted'][$isbn]['statusHistoric'] = 'NotAdopted';
            $books['NotAdopted'][$isbn]['timeLineDate'] = date('Y', strtotime($books['NotAdopted'][$isbn]['RequestedDate']));
            array_push($result['historic'], $books['NotAdopted'][$isbn]);
            array_push($result['dateOfRequest'], $books['NotAdopted'][$isbn]['RequestedDate']);
        }

        if (array_key_exists($isbn, $books['MixedAdoption'])) {
            $books['MixedAdoption'][$isbn]['statusHistoric'] = 'MixedAdoption';
            $books['MixedAdoption'][$isbn]['timeLineDate'] = date('Y', strtotime($books['MixedAdoption'][$isbn]['RequestedDate']));
            array_push($result['historic'], $books['MixedAdoption'][$isbn]);
            array_push($result['dateOfRequest'], $books['MixedAdoption'][$isbn]['RequestedDate']);
        }

        if (array_key_exists($isbn, $books['Renewal']) ) {
            $books['Renewal'][$isbn]['statusHistoric'] = 'Renewal';
            $books['Renewal'][$isbn]['timeLineDate'] ='';
            array_push($result['historic'], $books['Renewal'][$isbn]);
            array_push($result['dateOfRequest'], $books['Renewal'][$isbn]['RequestedDate']);
        }

        if (array_key_exists($isbn, $books['Renewed'])) {
            $renewedYear =  date('Y', strtotime(end($books['Renewed'][$isbn]['adoptions'])->EstimatedClose));
            $books['Renewed'][$isbn]['statusHistoric'] = 'Renewed';
            $books['Renewed'][$isbn]['timeLineDate'] = $renewedYear;
            array_push($result['historic'], $books['Renewed'][$isbn]);
            array_push($result['dateOfRequest'], isset($books['Renewed'][$isbn]['RequestedDate']) ?$books['Renewed'][$isbn]['RequestedDate'] : end($books['Renewed'][$isbn]['adoptions'])->EstimatedClose);
        }

        if (array_key_exists($isbn, $books['NotRenewed'])){
            $notRenewedYear =   date('Y', strtotime(end($books['NotRenewed'][$isbn]['adoptions'])->EstimatedClose));
            $books['NotRenewed'][$isbn]['statusHistoric'] = 'NotRenewed';
            $books['NotRenewed'][$isbn]['timeLineDate'] = $notRenewedYear;
            array_push($result['historic'], $books['NotRenewed'][$isbn]);
            array_push($result['dateOfRequest'], isset($books['NotRenewed'][$isbn]['RequestedDate']) ? $books['NotRenewed'][$isbn]['RequestedDate'] : end($books['NotRenewed'][$isbn]['adoptions'])->EstimatedClose);
        }

        if (array_key_exists($isbn, $books['MixedRenewal'])) {
            $books['MixedRenewal'][$isbn]['statusHistoric'] = 'MixedRenewal';
            $books['MixedRenewal'][$isbn]['timeLineDate'] = date('Y', strtotime($books['MixedRenewal'][$isbn]['RequestedDate']));
            array_push($result['historic'], $books['MixedRenewal'][$isbn]);
            array_push($result['dateOfRequest'], $books['MixedRenewal'][$isbn]['RequestedDate']);
        }

        if (array_key_exists($isbn, $books['Expired'])) {
            $books['Expired'][$isbn]['statusHistoric'] = 'Expired';
            $books['Expired'][$isbn]['timeLineDate'] = date('Y', strtotime($books['Expired'][$isbn]['RequestedDate']));
            array_push($result['historic'], $books['Expired'][$isbn]);
            array_push($result['dateOfRequest'], $books['Expired'][$isbn]['RequestedDate']);
        }

        foreach ( $books['Declined'] as $declined) {
            if($isbn == $declined['Isbn'] && isset($adoptedBook['RequestedDate'])  && $adoptedBook['RequestedDate'] !== $declined['RequestedDate'] ){
                $declined['statusHistoric'] = 'Declined';
                $declined['timeLineDate'] = date('Y', strtotime($declined['RequestedDate']));
                array_push( $result['historic'], $declined);
                array_push($result['dateOfRequest'], $declined['RequestedDate']);
            }
        }

        foreach ($books['Cancelled'] as $cancelled) {

            if($isbn == $cancelled['Isbn'] && isset($adoptedBook['RequestedDate'])  && $adoptedBook['RequestedDate'] !== $cancelled['RequestedDate']) {
                $cancelled['statusHistoric'] = 'Cancelled';
                $cancelled['timeLineDate'] = date('Y', strtotime($cancelled['RequestedDate']));
                array_push( $result['historic'], $cancelled);
                array_push($result['dateOfRequest'], $cancelled['RequestedDate']);
            }
        }

        /** delete the current book */
        if(sizeof($result['historic']) > 0) {
            $result['exist'] = true;
            if(sizeof($result['historic']) > 1) {
                usort($result['historic'], static function ($a, $b) {
                    if(isset($a['timeLineDate']) && isset($b['timeLineDate']))  return $a['timeLineDate'] < $b['timeLineDate'];
                });
            }
        }

        return $result;
    }
}

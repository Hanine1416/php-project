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

namespace UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Exception;
use JMS\Serializer\SerializerBuilder;
use MainBundle\Entity\BooksNew;
use MainBundle\Entity\Cover;
use MainBundle\Services\BookService;
use MBComponents\Controller\Controller;
use MBComponents\Exceptions\NotFoundException;
use Slim\Http\Response;
use UserBundle\Entity\Admin;
use UserBundle\Entity\Banner;
use UserBundle\Entity\CookiePage;
use UserBundle\Entity\CookieTable;
use UserBundle\Entity\Faq;

class AdminController extends Controller
{
    /**
     * AdminController constructor.
     * @throws NotFoundException
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct(true);
        /**
         * If the admin is logged in then put his information into twig globals
         */
        if ($this->isLoggedIn())
        {
            $this->getApp()->sfTwig->addGlobal('admin', $this->getAdmin());
        }
        $this->language =  $this->getApp()->getCookie('admin_lang')??$this->getApp()->getCookie('site_lang')??$this->getApp()->getCookie('lang');
        $this->region   =  $this->getApp()->getCookie('admin_region')??$this->getApp()->getCookie('region')??$this->getApp()->getCookie('reg');
        $this->getApp()->session->set('reg', $this->getApp()->getCookie('reg'));
        $this->getApp()->session->set('lang', $this->language);
    }

    /**
     * Login admin action from ajax request
     * @throws Exception
     */
    public function login()
    {
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest() && $request->isMethod('POST'))
        {
            $password = $request->get('password');
            $username = $request->get('username');
            $authOk = false;
            $admin = null;
            if ($password && $username)
            {
                /**
                 * Check if the username exist
                 */
                $admin = $this->getEntityManager()->getRepository(Admin::class)->findOneBy(['username' => $username]);
                /**
                 * Check password
                 */
                $authOk = $admin ? password_verify($password, $admin->getPassword()) : null;
            }
            if ($authOk)
            {
                /**
                 * Save admin information into the session & return success authentication json response
                 */
                $this->getSession()->set('adminUser', ['username' => $username, 'id' => $admin->getId()]);
                $result = ['Result' => true, 'redirect' => $this->generateUrl('admin')];
            } else {
                /**
                 * Wrong credentials : return fails authentication json response
                 */
                $result = ['Result' => false, 'message' => 'Invalid user or password.'];
            }
            return $this->renderJson($result);
        }

        return $this->render('@UserBundle/admin/login.html.twig');
    }

    /**
     * Admin logout delete information from session
     * @return void
     * @throws Exception
     */
    public function logout(): void
    {
        $this->getSession()->remove('adminUser');
        $this->redirect($this->generateUrl('admin'));
    }

    /**
     * Admin dashboard page
     * @throws Exception
     */
    public function dashboard()
    {
        $lang = $this->language;
        $reg = $this->region;
        $lang == "en" && $reg == "1" ? $lang = "anz" : $lang = $this->language;
        $covers = $this->getEntityManager()->getRepository(Cover::class)
            ->findBy(['language' => $lang], ['position' => 'ASC']);
        return $this->render('@UserBundle/admin/dashboard.html.twig', ['covers' => $covers]);
    }

    /**
     * Admin faq page
     * @throws Exception
     */
    public function faqPage()
    {
        $lang = $this->language;
        $reg = $this->region;
        $lang =="en" && $reg == "1" ? $lang = "anz" : $lang = $this->language;
        $faq = $this->getEntityManager()->getRepository(Faq::class)
            ->findBy(['language' => $lang], ['order' => 'ASC']);
        return $this->render('@UserBundle/admin/faq/faq.html.twig', ['faqs' => $faq]);
    }

    /**
     * Admin edit order faq page
     * @throws Exception
     */
    public function editOrderFaq()
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $faqs = $this->getRequest()->get('faqs');
        foreach ($faqs as $faq)
        {
            /** @var Faq $dbFaq */
            $dbFaq = $em->getRepository(Faq::class)->findOneBy(['token' => trim($faq['token'])]);
            if ($dbFaq)
            {
                $dbFaq->setOrder($faq['newOrder']);
                $em->persist($dbFaq);
            }
        }
        $em->flush();
        /** Send json response with success status & the new persisted faq */
        return $this->renderJson(['success' => true]);
    }

    /**
     * Add faq into the faq page
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws Exception
     */
    public function addFaq()
    {
        $lang = $this->language;
        $reg = $this->region;
        $lang =="en" && $reg == "1" ? $lang = "anz" : $lang = $this->language;
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $request = $this->getRequest();
        $faq = new faq();
        /** Binding data from the post request */
        $faq->setQuestion($request->get('question'));
        $faq->setAnswer($request->get('answer'));
        $faq->setOrder($request->get('order'));
        $faq->setLanguage($lang);
        /** Save data into the DB */
        $em->persist($faq);
        $em->flush();
        /** Generate the URLS for the delete & edit */
        $deleteUrl = $this->generateUrl('admin-delete-faq', ['token' => $faq->getToken()]);
        $editUrl = $this->generateUrl('admin-edit-faq', ['token' => $faq->getToken()]);
        /** Serialize data from object to array */
        $serializer = SerializerBuilder::create()->build();
        $faq = $serializer->serialize($faq, 'json');
        /** Send json response with success status & the new persisted faq */
        return $this->renderJson(
            [
                'success' => true,
                'faq' => json_decode($faq),
                'deleteUrl' => $deleteUrl,
                'editUrl' => $editUrl]
        );
    }

    /**
     * @param $token
     * @return Response
     * @throws Exception
     */
    public function editFaq($token)
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        /**
         * Get the faq object by the token
         */
        $faq = $em->getRepository(Faq::class)->findOneBy(['token' => $token]);
        if ($faq)
        {
            /**
             * Binding data from the post request
             */
            $faq->setQuestion($request->get('question'));
            $faq->setAnswer($request->get('answer'));
            $em->persist($faq);
            $em->flush();

            /**
             * Serialize data from object to array
             */
            $serializer = SerializerBuilder::create()->build();
            $faq = $serializer->serialize($faq, 'json');
            /**
             * Send json response with success status & the new persisted faq
             */
            return $this->renderJson(['success' => true, 'faq' => json_decode($faq)]);
        } else {
            return $this->renderJson(['success' => false, 'message' => 'faq not found']);
        }
    }


    /**
     * Delete faq from the faq page
     * @param $token
     * @return Response
     * @throws Exception
     */
    public function deleteFaq($token)
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        /**
         * Get the faq object by the token
         */
        $faq = $em->getRepository(faq::class)->findOneBy(['token' => $token]);
        if ($faq)
        {
            /**
             * Delete faq from the DB
             */
            $em->remove($faq);
            $em->flush();
        }
        /**
         * Return json success
         */
        return $this->renderJson(['success' => true]);
    }

    /**
     * Admin edit cover
     * @throws Exception
     */
    public function editCover()
    {
        $em = $this->getEntityManager();
        $covers = $this->getRequest()->get('covers');
        foreach ($covers as $cover)
        {
            /** @var Cover $dbCover */
            $dbCover = $em->find(Cover::class, $cover['id']);
            if ($dbCover && $cover['image'] !== "")
            {
                $dbCover->setImage($cover['image']);
                $em->persist($dbCover);
                $em->flush();
            }
        }
        $this->redirect($this->generateUrl('admin'));
    }

    /**
     * Check admin
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        $loggedIn = $this->getSession()->get('adminUser') ?? null;
        return $loggedIn != null;
    }

    /**
     * Return loggedIn admin
     * @return array
     */
    public function getAdmin(): array
    {
        return $this->getSession()->get('adminUser');
    }

    /**
     * Show manageable whatNew page
     * @throws Exception
     */
    public function whatNew()
    {
        $lang = $this->language;
        $reg = $this->region;
        $lang == "en" && $reg == "1" ? $lang = "anz" : $lang = $this->language;
        $em = $this->getEntityManager();
        $request = $this->getRequest();
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        $errorIsbnList= [];
        /** If the admin submit edit whatNew page form */
        if ($request->isMethod('POST')) {
            $isbnArray = $request->request->get('isbn') ;
            foreach ($isbnArray as $key=> $url) {
                if (!empty($url)) {
                    $elements = explode('/', $url);
                    $isbn = end($elements);
                    $book = $bookService->getBookExistsISBN($isbn);
                    if (!$book){
                        $errorIsbnList[] = $isbn;
                    }else{
                        /** Get the books new object by the position & lang */
                        $bookNew = $em->getRepository(BooksNew::class)->findOneBy(['position' => $key, 'language'=> $lang]);
                        /** check if ISBN exist we do add else we do update  */
                        if (!$bookNew) {
                            $booksNew = new BooksNew($lang);
                            $booksNew->setIsbn($isbn);
                            $booksNew->setPosition($key);
                            $em->persist($booksNew);
                        } else {
                            $bookNew->setIsbn($isbn);
                        }
                        $em->flush();
                    }
                }
            }
            $positionArray = array_keys($isbnArray);
            /** Get existing BooksNew by lang */
            $existingBooksNew = $em->getRepository(BooksNew::class)->findBy(['language' => $lang]);

            /** Remove BooksNew that are not present in the form submission */
            foreach ($existingBooksNew as $bookNew) {
                $position = $bookNew->getPosition();
                /** check position booksNew  */
                if (!in_array($position, $positionArray)) {
                    $em->remove($bookNew);
                    $em->flush();
                }
            }
            if (count($errorIsbnList) === 0){
                $this->addFlash('success', 'Books updated successfully');
                $this->redirect($this->generateUrl('admin-what-new'));
            } else {
                if (count($errorIsbnList) === 1){
                    $message = "Book with ISBN ".$errorIsbnList[0]." does not exist";
                } else {
                    $message = "Books with ISBN : ".implode(',', $errorIsbnList)." does not exist";
                }
                $this->addFlash('error', $message);
                $this->redirect($this->generateUrl('admin-what-new'));
            }
        }
        $listBooksNew = $em->getRepository(BooksNew::class)->findBy(['language'=> $lang]);
        return $this->render(
            '@UserBundle/admin/whatNew/whatNew.html.twig',  ['listBooksNew' => $listBooksNew]);
    }

    /**
     * Add cookie into the cookie table
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws Exception
     */
    public function addCookie()
    {
        $lang = $this->language;
        $reg = $this->region;
        $lang =="en" && $reg == "1" ? $lang = "anz" : $lang = $this->language;
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $request = $this->getRequest();
        $cookie = new CookieTable();
        /** Binding data from the post request */
        $cookie->setServiceName($request->get('serviceName'));
        $cookie->setCookieNames($request->get('cookieNames'));
        $cookie->setDescription($request->get('description'));
        $cookie->setMoreInfo($request->get('moreInfo'));
        $cookie->setLanguage($lang);
        /** Save data into the DB */
        $em->persist($cookie);
        $em->flush();
        /** Generate the URLS for the delete & enable */
        $enableUrl = $this->generateUrl('admin-enable-cookie-table', ['token' => $cookie->getToken()]);
        $deleteUrl = $this->generateUrl('admin-delete-cookie-table', ['token' => $cookie->getToken()]);
        $editUrl = $this->generateUrl('admin-edit-cookie-table', ['token' => $cookie->getToken()]);
        /** Serialize data from object to array */
        $serializer = SerializerBuilder::create()->build();
        $cookie = $serializer->serialize($cookie, 'json');
        /** Send json response with success status & the new persisted cookie */
        return $this->renderJson(
            [
                'success' => true,
                'cookie' => json_decode($cookie),
                'enableUrl' => $enableUrl,
                'deleteUrl' => $deleteUrl,
                'editUrl' => $editUrl]
        );
    }

    /**
     * Show / Hide cookie from the cookie table show in the front office
     * @param $token
     * @return Response
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function enableCookie($token)
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        /** Get the cookie object by the token */
        $cookie = $em->getRepository(CookieTable::class)->findOneBy(['token' => $token]);
        /**
         * Switch cookie enable status
         */
        $cookie->setEnable(!$cookie->isEnable());
        $em->persist($cookie);
        $em->flush();
        /**
         * Return json response with the cookie new status (Enabled / Disabled)
         */
        return $this->renderJson(['success' => true, 'enable' => $cookie->isEnable()]);
    }

    /**
     * Delete cookie from the cookie table
     * @param $token
     * @return Response
     * @throws Exception
     */
    public function deleteCookie($token)
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        /**
         * Get the cookie object by the token
         */
        $cookie = $em->getRepository(CookieTable::class)->findOneBy(['token' => $token]);
        if ($cookie)
        {
            /**
             * Delete cookie from the DB
             */
            $em->remove($cookie);
            $em->flush();
        }
        /**
         * Return json success
         */
        return $this->renderJson(['success' => true]);
    }

    /**
     * @param $token
     * @return Response
     * @throws Exception
     */
    public function editCookie($token): ?Response
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        /**
         * Get the cookie object by the token
         */
        $cookie = $em->getRepository(CookieTable::class)->findOneBy(['token' => $token]);
        if ($cookie)
        {
            /**
             * Binding data from the post request
             */
            $cookie->setServiceName($request->get('serviceName'));
            $cookie->setCookieNames($request->get('cookieNames'));
            $cookie->setDescription($request->get('description'));
            $cookie->setMoreInfo($request->get('moreInfo'));

            $em->persist($cookie);
            $em->flush();

            /**
             * Serialize data from object to array
             */
            $serializer = SerializerBuilder::create()->build();
            $cookie = $serializer->serialize($cookie, 'json');
            /**
             * Send json response with success status & the new persisted cookie
             */
            return $this->renderJson(['success' => true, 'cookie' => json_decode($cookie)]);
        } else {
            return $this->renderJson(['success' => false, 'message' => 'cookie not found']);
        }
    }

    /**
     * Admin banner page
     * @throws \Exception
     */
    public function bannerPage()
    {
        $banner = $this->getEntityManager()->getRepository(Banner::class)->findAll();
        return $this->render('@UserBundle/admin/banner/banner.html.twig', ['banners' => $banner]);
    }

    /**
     * Add banner into the banner page
     * @throws \Exception
     */
    public function addBanner()
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $request = $this->getRequest();
        $banner = new Banner();

        /** Binding data from the post request */
        /** Set banner title for all languages */
        $banner->setTitleEN($request->get('titleEN'));
        $banner->setTitleDE($request->get('titleDE'));
        $banner->setTitleES($request->get('titleES'));
        $banner->setTitleFR($request->get('titleFR'));
        $banner->setTitleANZ($request->get('titleANZ'));

        /** Set banner content for all languages */
        $banner->setContentEN($request->get('contentEN'));
        $banner->setContentDE($request->get('contentDE'));
        $banner->setContentES($request->get('contentES'));
        $banner->setContentFR($request->get('contentFR'));
        $banner->setContentANZ($request->get('contentANZ'));

        /** Set banner type */
        $banner->setType($request->get('bannerType'));
        /** Set banner close info */
        $request->get('bannerClose') === false ? $banner->setIsClosed(false):  $banner->setIsClosed(true);
        /** Set banner disabled info */
        $request->get('bannerActive') === false ? $banner->setIsEnabled(false):$banner->setIsEnabled(true);

        /** Save data into the DB */
        $em->persist($banner);
        $em->flush();
        /** Generate the URLS for the delete & edit */
        $deleteUrl = $this->generateUrl('admin-delete-banner', ['token' => $banner->getToken()]);
        $editUrl = $this->generateUrl('admin-edit-banner', ['token' => $banner->getToken()]);
        $disableUrl = $this->generateUrl('admin-disable-banner', ['token' => $banner->getToken()]);
        /** Serialize data from object to array */
        $serializer = SerializerBuilder::create()->build();
        $banner = $serializer->serialize($banner, 'json');
        /** Send json response with success status & the new persisted faq */
        return $this->renderJson(
            [
                'success' => true,
                'banner' => json_decode($banner),
                'deleteUrl' => $deleteUrl,
                'editUrl' => $editUrl,
                'disableUrl' => $disableUrl]
        );
    }

    /**
     * @param $token
     * @return \Slim\Http\Response
     * @throws \Exception
     */
    public function editBanner($token)
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $request = $this->getRequest();

        /** Get the faq object by the token */
        $banner = $em->getRepository(banner::class)->findOneBy(['token' => $token]);
        if ($banner)
        {

            /** Binding data from the post request */
            $banner->setTitleEN($request->get('titleEN'));
            $banner->setTitleDE($request->get('titleDE'));
            $banner->setTitleES($request->get('titleES'));
            $banner->setTitleFR($request->get('titleFR'));
            $banner->setTitleANZ($request->get('titleANZ'));

            /** Set banner content for all languages */
            $banner->setContentEN($request->get('contentEN'));
            $banner->setContentDE($request->get('contentDE'));
            $banner->setContentES($request->get('contentES'));
            $banner->setContentFR($request->get('contentFR'));
            $banner->setContentANZ($request->get('contentANZ'));

            /** Set banner type */
            $banner->setType($request->get('bannerType'));
            /** Set banner close info */
            $request->get('bannerClose') == 0 ? $banner->setIsClosed(0):  $banner->setIsClosed(1);
            /** Set banner disabled info */
            $request->get('bannerActive') == 0 ? $banner->setIsEnabled(0):$banner->setIsEnabled(1);

            $em->persist($banner);
            $em->flush();

            /** Serialize data from object to array */
            $serializer = SerializerBuilder::create()->build();
            $banner = $serializer->serialize($banner, 'json');
            /** Send json response with success status & the new persisted banner */
            return $this->renderJson(['success' => true, 'banner' => json_decode($banner)]);
        } else {
            return $this->renderJson(['success' => false, 'message' => 'banner not found']);
        }
    }

    /**
     * Disable banner from the banner page
     * @param $token
     * @param $isEnabled
     * @return \Slim\Http\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function disableBanner($token, $isEnabled)
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();

        /** Get the faq object by the token */
        $banner = $em->getRepository(banner::class)->findOneBy(['token' => $token]);

        /** Binding data from the post request */
        $banner->setIsEnabled($isEnabled);
        $em->persist($banner);
        $em->flush();

        /** Send json response with success status */
        return $this->renderJson(['success' => true]);

    }

    /**
     * Delete banner from the banner page
     * @param $token
     * @return \Slim\Http\Response
     * @throws \Exception
     */
    public function deleteBanner($token)
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        /** Get the faq object by the token */
        $banner = $em->getRepository(banner::class)->findOneBy(['token' => $token]);
        if ($banner)
        {
            /** Delete banner from the DB */
            $em->remove($banner);
            $em->flush();
        }
        /** Return json success */
        return $this->renderJson(['success' => true]);
    }

    /**
     * Admin count active banner
     * @throws \Exception
     */
    public function getActiveBanner()
    {
        $activeBanner = $this->getEntityManager()->getRepository(Banner::class)
            ->findBy(['isEnabled' => true]);
        return $this->renderJson(['result' => count($activeBanner)]);
    }
}

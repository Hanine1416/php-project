<?php
/**
 * Created by PhpStorm.
 * User: mobelite
 * Date: 21/05/2019
 * Time: 11:03
 */

namespace MBComponents\Twig\Extensions;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use MBComponents\Helpers\Encryption;
use MBComponents\Helpers\TokenGenerator;
use MBComponents\Interfaces\CSRFToken as TOKEN;
use MBComponents\Services\SLX;
use MBComponents\Services\SlxWebService;
use lib\Config;
use UserBundle\Entity\Banner;
use UserBundle\Entity\Institution;
use UserBundle\Entity\Notification;


/**
 * Class UserExtension
 * @package MBComponents\Twig\Extensions
 */
class UserExtension extends AppExtension
{


    /**
     * Return available function defined in this extension
     * @return array
     */
    public function getFunctions(): array {
        return [
            new \Twig_SimpleFunction('_token_reset_password', [$this, 'tokenResetPassword']),
            new \Twig_SimpleFunction('_token_confirm_register', [$this, 'tokenConfirmRegister']),
            new \Twig_SimpleFunction('is_fp_recaptcha_on', [$this, 'isFPRecaptchaEnabled']),
            new \Twig_SimpleFunction('is_rr_recaptcha_on', [$this, 'isRRRecaptchaEnabled']),
            new \Twig_SimpleFunction('getNotifications', [$this, 'getNotifications']),
            new \Twig_SimpleFunction('isProfileUpdate', [$this, 'isProfileUpdate']),
            new \Twig_SimpleFunction('isPasswordUpdated', [$this, 'isPasswordUpdated']),
            new \Twig_SimpleFunction('getActiveBanner',[$this,'getActiveBanner']),
            new \Twig_SimpleFunction('decrypt',[$this,'decrypt']),
        ];
    }

    /**
     * Return available filters defined in this extension
     * @return array
     */
    public function getFilters(): array {
        return [
            new \Twig_SimpleFilter('institutionNotDisabled', [$this, 'getInstitutionNotDisabledNumber']),
            new \Twig_SimpleFilter('formatDate', [$this, 'showDateNotif']),
        ];
    }

    /**
     * return user notifications list
     * @param $region
     * @param $lang
     * @return ArrayCollection|null
     * @throws \Exception
     */
    public function getNotifications($region, $lang): ?ArrayCollection {
        $container = $this->slim->getContainer();
        /** @var SlxWebService $slxWebService */
        $slxWebService = $container->get(SLX::class)->getSlxWebService();
        if ($this->getLoggedInUser()) {
            $notifications = $slxWebService->getAllNotifications($this->getLoggedInUser()->getUserId(), $region, $lang);
            /** Add notification to user registered from old site & logged for the first time  */
            if ($this->getLoggedInUser()->getIcProfileVersion() !== "0" &&
                $this->getLoggedInUser()->getIcProfileVersion() !== null) {
                $notif = new Notification();
                $notif->setDate(new \DateTime());
                $notif->setLink($this->generateUrl('my-books'));
                $notif->setEventType('newUser');
                $notifications->add($notif);
            }

        } else {
            $notifications = null;
        }
        /** Get user notifications */
        return $notifications;
    }

    /**
     * Generate Token on session for the reset password form
     * The reset password form should only been accessible from web environment
     * This Token will be checked in ResettingController::index function
     * @return string
     */
    public function tokenResetPassword(): string {
        $this->slim->session->set(TOKEN::RP, TokenGenerator::generateToken());
        return $this->slim->session->get(TOKEN::RP);
    }

    /**
     * Generate Token on session for the registration confirm form
     * This Token will be checked in RegisterController::confirm function
     * @return string
     */
    public function tokenConfirmRegister(): string {
        $this->slim->session->set(TOKEN::CR, TokenGenerator::generateToken());

        return $this->slim->session->get(TOKEN::CR);
    }

    /**
     * Return number of enabled institutions
     * @param $institutions
     * @return int
     */
    public function getInstitutionNotDisabledNumber(?ArrayCollection $institutions): int {
        /** If not institution available return 0 */
        if (!$institutions) {
            return 0;
        }
        /** Check foreach institution if enabled or disabled */
        return ($institutions->filter(function (Institution $institution) {
            return $institution->isEnabled();
        }))->count();
    }

    /**
     * Check if forget password recaptcha should be enabled or not
     * @return bool
     */
    public function isFPRecaptchaEnabled(): bool {
        /** Get current date timestamp */
        $currentTime = (new \DateTime())->getTimestamp();
        /** forget password recaptcha */
        if ($this->slim->session->has('fp_attempts')) {
            /** @var array $fpAttempts */
            $fpAttempts = $this->slim->session->get('fp_attempts');
            if (count($fpAttempts) == 3 && $currentTime - $fpAttempts[0] <= 300) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if request register recaptcha should be enabled or not
     * @return bool
     */
    public function isRRRecaptchaEnabled(): bool {
        /** Get current date timestamp */
        $currentTime = (new \DateTime())->getTimestamp();
        /** forget password recaptcha */
        if ($this->slim->session->has('register_attempts')) {
            /** @var array $rrAttempts */
            $rrAttempts = $this->slim->session->get('register_attempts');
            if (count($rrAttempts) == 3 && $currentTime - $rrAttempts[0] <= 300) {
                return true;
            }
        }
        return false;
    }

    /**
     * Transform notif date to a specific format
     * @param \DateTime $date
     * @return string
     */
    public function showDateNotif(\DateTime $date) {
        $result = $date->format('H:i Y-m-d');
        $currentDate = new DateTime();
        $dateDiff = $currentDate->diff($date);
        $hourDiff = $dateDiff->format('%H');
        $minDiff = $dateDiff->format('%i');
        if ($dateDiff->d > 0 || $dateDiff->y > 0) {
            return $result;
        } else if ($hourDiff > 0 && $hourDiff < 25) {
            if ($hourDiff == 1) {
                $result = '1 hour ago';
            } else {
                $result = $hourDiff . ' hours ago';
            }
        } else if ($hourDiff == 0) {
            if ($minDiff < 1) {
                $result = "Seconds ago";
            } else if ($minDiff < 10) {
                $result = "Minutes ago";
            } else {
                $result = $minDiff . ' minutes ago';
            }
        }
        return $result;
    }

    /**
     * Check toaster update profile from the session
     * @return bool
     */
    public function isProfileUpdate(): bool {
        $res = $this->slim->session->get('profile_updated') ?? false;
        $this->slim->session->remove('profile_updated');
        return $res;
    }

    public function isPasswordUpdated(): bool {
        $res = $this->slim->session->get('password_updated') ?? false;
        $this->slim->session->remove('password_updated');
        return $res;
    }

    /**
     * Get activated banner to show
     * @param $siteReg
     * @return array
     */
    public function getActiveBanner($siteReg) {
        $siteLang =$this->slim->session->get('site-lang') ?? $this->slim->session->get('lang') ?? 'en';
        $siteReg =$siteReg ?? 7;
        $bannerForLang=[];
        /** Find active banner */
        $banner = Config::read('app')->sfEntityManager
            ->getRepository(Banner::class)
            ->findOneBy([
                'isEnabled' => 1
            ]);
        if ($banner != null) {
            switch (true) {
                case $siteLang=='en' && $siteReg!=1:
                    $bannerForLang = [
                        'title'=>$banner->getTitleEN(),
                        'content'=>$banner->getContentEN(),
                        'color' => $banner->getType(),
                        'close' => $banner->getIsClosed()];
                    break;
                case $siteLang=='es'&& $siteReg!=1 :
                    $bannerForLang= [
                        'title' => $banner->getTitleES(),
                        'content' => $banner->getContentES(),
                        'color' => $banner->getType(),
                        'close' => $banner->getIsClosed()];
                    break;
                case $siteLang=='fr'&& $siteReg!=1:
                    $bannerForLang= [
                        'title'=>$banner->getTitleFR(),
                        'content'=>$banner->getContentFR(),
                        'color' => $banner->getType(),
                        'close' => $banner->getIsClosed()];
                    break;
                case $siteLang=='de'&& $siteReg!=1:
                    $bannerForLang= [
                        'title'=>$banner->getTitleDE(),
                        'content'=>$banner->getContentDE(),
                        'color' => $banner->getType(),
                        'close' => $banner->getIsClosed()];
                    break;
                case $siteReg==1:
                    $bannerForLang= [
                        'title'=>$banner->getTitleANZ(),
                        'content'=>$banner->getContentANZ(),
                        'color' => $banner->getType(),
                        'close' => $banner->getIsClosed()];
                    break;
                default:
                    $bannerForLang = [
                        'title'=>$banner->getTitleEN(),
                        'content'=>$banner->getContentEN(),
                        'color' => $banner->getType(),
                        'close' => $banner->getIsClosed()];
            }
        }
        return $bannerForLang;
    }

    public function decrypt($string) {
        return Encryption::decrypt($string);
    }
}

<?php
/**
 * This file is part of the Inspection Copy.
 * Date: 3/23/18
 * Time: 17:41
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Form;

use lib\Config;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validation;

/**
 *
 */
define('VENDOR_DIR', realpath(__DIR__ . '/../../../vendor'));
/**
 *
 */
define('VENDOR_FORM_DIR', VENDOR_DIR . '/symfony/form');
/**
 *
 */
define('VENDOR_VALIDATOR_DIR', VENDOR_DIR . '/symfony/validator');
/**
 *
 */
define('VENDOR_TWIG_BRIDGE_DIR', VENDOR_DIR . '/symfony/twig-bridge');
/**
 *
 */
define('VIEWS_DIR', realpath(__DIR__ . '/../../../templates'));
/**
 *
 */
define('DEFAULT_FORM_THEME', 'form_div_layout.html.twig');

/**
 *
 */
define('TRANSLATIONS_DIR', VENDOR_DIR . '/symfony/validator/Resources/translations');
/**
 *
 */
define('TRANSLATIONS_FORM_DIR', VENDOR_DIR . '/symfony/form/Resources/translations');

/**
 * Class FormSetup
 * @package MBComponents\Form
 */
class FormSetup
{
    /**
     * @param \Twig_Environment $twigEnvironment
     * @param Translator $translator
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
    public function getFormFactory(
        \Twig_Environment $twigEnvironment,
        Translator $translator = null
    ):FormFactoryInterface {
        /**
         * Set up the CSRF Token Manager
         */
        $csrfManager = new CsrfTokenManager();
        /**
         * Set up the Validator component
         */
        $validator = Validation::createValidator();
        /**
         * Set up the Translation component
         */
        if (null !== $translator) {
            $languages = array_keys(Config::read('langFiles'));
            /**
             * Append validators translations files
             */
            $translator->addLoader('xlf', new XliffFileLoader());
            $files = scandir(TRANSLATIONS_DIR);
            unset($files[0]);
            unset($files[1]);
            foreach ($files as $file) {
                /** Add only defined locales */
                $fileParams = explode('.', $file);
                if (in_array($fileParams[1], $languages)) {
                    $fileName = realpath(TRANSLATIONS_DIR . '/' . basename($file));
                    $fileFormName = realpath(TRANSLATIONS_FORM_DIR . '/' . basename($file));
                    /** Add language files */
                    if (file_exists($fileName)) {
                        $translator->addResource('xlf', $fileName, Config::read('langFiles')[$fileParams[1]]);
                        $translator->addResource('xlf', $fileFormName, Config::read('langFiles')[$fileParams[1]]);
                    }
                }
            }
        }
        $formEngine = new TwigRendererEngine([DEFAULT_FORM_THEME], $twigEnvironment);
        /** Add twig Form extension  */
        $twigEnvironment->addExtension(new FormExtension());
        $twigEnvironment->addRuntimeLoader(new \Twig_FactoryRuntimeLoader(array(
            FormRenderer::class => function () use ($formEngine, $csrfManager) {
                return new FormRenderer($formEngine, $csrfManager);
            },
        )));

        /**
         * Set up the Form component
         * Add default extension
         * Csrf & HTTP & Validator
         */
        return Forms::createFormFactoryBuilder()
            ->addExtension(new CsrfExtension($csrfManager))
            ->addExtension(new ValidatorExtension($validator))
            ->addExtension(new HttpFoundationExtension())
            ->getFormFactory();
    }
}

<?php

namespace IngenicoClient;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\PoFileLoader;

class MailTemplate
{
    const TYPE_HTML = 'html';
    const TYPE_PLAIN_TEXT = 'text';

    const LAYOUT_DEFAULT = 'default';
    const LAYOUT_INGENICO = 'ingenico';

    const MAIL_TEMPLATE_REMINDER = 'reminder';
    const MAIL_TEMPLATE_REFUND_FAILED = 'refund_failed';
    const MAIL_TEMPLATE_ADMIN_REFUND_FAILED = 'admin_refund_failed';
    const MAIL_TEMPLATE_PAID_ORDER = 'order_paid';
    const MAIL_TEMPLATE_ADMIN_PAID_ORDER = 'admin_order_paid';
    const MAIL_TEMPLATE_AUTHORIZATION = 'authorization';
    const MAIL_TEMPLATE_ADMIN_AUTHORIZATION = 'admin_authorization';
    const MAIL_TEMPLATE_ONBOARDING_REQUEST = 'onboarding_request';
    const MAIL_TEMPLATE_SUPPORT = 'support';

    /**
     * @var string
     */
    private $locale = 'en_US';

    /**
     * @var string
     */
    private $layout = 'default';

    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $templates_directory;

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var Translator
     */
    private $translator;

    /**
     * MailTemplate constructor.
     *
     * @param $locale
     * @param $layout
     * @param $template
     * @param array $fields
     */
    public function __construct($locale, $layout, $template, array $fields = array())
    {
        $this->locale = $locale;
        $this->layout = $layout;
        $this->template = $template;
        $this->fields = $fields;

        // Initialize translations
        $this->translator = new Translator($this->locale);
        $this->translator->addLoader('po', new PoFileLoader());
        $this->translator->setFallbackLocales(['en_US']);
        $this->translator->setLocale($this->locale);

        // Load translations
        $directory = __DIR__ . '/../translations';
        $files = scandir($directory);
        foreach ($files as $file) {
            $file = $directory . DIRECTORY_SEPARATOR . $file;
            $info = pathinfo($file);
            if ($info['extension'] !== 'po') {
                continue;
            }

            $filename = $info['filename'];
            list($domain, $locale) = explode('.', $filename);
            $this->translator->addResource('po', $directory . DIRECTORY_SEPARATOR . $info['basename'], $locale, $domain);
        }
    }

    /**
     * Set Templates Directory
     *
     * @param string $templates_directory
     * @return $this
     */
    public function setTemplatesDirectory($templates_directory)
    {
        $this->templates_directory = $templates_directory;

        return $this;
    }

    /**
     * Get Message.
     *
     * @param $type
     * @param bool $includeLayout
     *
     * @return false|string
     *
     * @throws Exception
     */
    private function getMessage($type, $includeLayout = true)
    {
        if (!in_array($type, [self::TYPE_HTML, self::TYPE_PLAIN_TEXT])) {
            throw new Exception('Wrong type argument');
        }

        return $this->renderTemplate($includeLayout ? $this->layout : false, $this->template, $type, $this->fields);
    }

    /**
     * Get HTML.
     *
     * @param bool $includeLayout
     * @return false|string
     *
     * @throws Exception
     */
    public function getHtml($includeLayout = true)
    {
        return $this->getMessage(self::TYPE_HTML, $includeLayout);
    }

    /**
     * Get Plain Text.
     *
     * @param bool $includeLayout
     * @return false|string
     *
     * @throws Exception
     */
    public function getPlainText($includeLayout = true)
    {
        return $this->getMessage(self::TYPE_PLAIN_TEXT, $includeLayout);
    }

    /**
     * Lookup Template
     * @param string $template
     * @param string $type
     * @return string
     * @throws Exception
     */
    private function lookupTemplate($template, $type)
    {
        // Clean up variables
        $template = preg_replace('/[^a-zA-Z0-9_-]+/', '', $template);
        $type = preg_replace('/[^a-zA-Z0-9_-]+/', '', $type);

        // Default template directory
        $templatesDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates';
        $templateFile = $templatesDirectory . DIRECTORY_SEPARATOR . $template . DIRECTORY_SEPARATOR . $type . '.php';

        // Check template in custom directory
        if (!empty($this->templates_directory) && is_dir($this->templates_directory)) {
            $templateFile = $this->templates_directory . DIRECTORY_SEPARATOR . $template . DIRECTORY_SEPARATOR . $type . '.php';
            if (file_exists($templateFile)) {
                return $templateFile;
            }
        }

        if (!file_exists($templateFile)) {
            throw new Exception("Template {$template} doesn't exist");
        }

        return $templateFile;
    }

    /**
     * Lookup Layout
     * @param string $layout
     * @param string $type
     * @return string
     * @throws Exception
     */
    private function lookupLayout($layout, $type)
    {
        // Clean up variables
        $layout = preg_replace('/[^a-zA-Z0-9_-]+/', '', $layout);
        $type = preg_replace('/[^a-zA-Z0-9_-]+/', '', $type);

        // Default layout directory
        $templatesDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'layouts';
        $templateFile = $templatesDirectory . DIRECTORY_SEPARATOR . $layout . DIRECTORY_SEPARATOR . $type . '.php';

        // Check layout in custom directory
        if (!empty($this->templates_directory) && is_dir($this->templates_directory)) {
            $templateFile = $this->templates_directory . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layout . DIRECTORY_SEPARATOR . $type . '.php';
            if (file_exists($templateFile)) {
                return $templateFile;
            }
        }

        if (!file_exists($templateFile)) {
            throw new Exception("Layout {$layout} doesn't exist");
        }

        return $templateFile;
    }

    /**
     * Render template.
     *
     * @param string|false $layout
     * @param string $template
     * @param string $type
     * @param array $fields
     *
     * @return false|string
     *
     * @throws Exception
     */
    public function renderTemplate($layout, $template, $type, array $fields)
    {
        $template = preg_replace('/[^a-zA-Z0-9_-]+/', '', $template);
        $type = preg_replace('/[^a-zA-Z0-9_-]+/', '', $type);

        $fields = array_merge($this->fields, $fields);
        $fields['t'] = function ($id, $parameters = [], $domain = null, $locale = null) {
            echo $this->translator->trans($id, $parameters, $domain, $locale);
        };
        $fields['view'] = &$this;
        $fields['locale'] = $this->locale;
        extract($fields);

        // Render View
        ob_start();
        $templateFile = $this->lookupTemplate($template, $type);
        require $templateFile;
        $contents = ob_get_contents();
        ob_end_clean();

        // Return contents of template without layout
        if (!$layout) {
            return $contents;
        }

        // Override content
        $fields['contents'] = $contents;

        // Render layout
        ob_start();
        $layout = preg_replace('/[^a-zA-Z0-9_-]+/', '', $layout);
        $layoutFile = $this->lookupLayout($layout, $type);
        require $layoutFile;
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }

    /**
     * Translate.
     *
     * @param $id
     * @param array $parameters
     * @param string|null $domain
     * @param string|null $locale
     *
     * @return string
     */
    public function __($id, $parameters = [], $domain = null, $locale = null)
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Embed Image.
     *
     * @param $file
     *
     * @return string
     */
    public function embedImage($file)
    {
        $size = getimagesize($file);
        if ($size) {
            $contents = file_get_contents($file);

            return sprintf('data:%s;base64,%s', $size['mime'], base64_encode($contents));
        }

        return false;
    }
}

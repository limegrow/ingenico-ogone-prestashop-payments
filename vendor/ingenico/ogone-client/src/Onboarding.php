<?php
/**
 * Created by PhpStorm.
 * User: alexw
 * Date: 24/01/19
 * Time: 14:52.
 */

namespace IngenicoClient;

/**
 * Class Onboarding.
 */
class Onboarding
{
    protected $emails;

    /**
     * Onboarding constructor.
     * @param ConnectorInterface $extension
     * @param IngenicoCoreLibraryInterface $coreLibrary
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ConnectorInterface $extension,
        IngenicoCoreLibraryInterface $coreLibrary
    ) {
        $iniFile = __DIR__.'/../onboarding/emails.ini';
        if (!file_exists($iniFile)) {
            throw new Exception('Cannot find onboarding email file: onboarding/emails.ini');
        }

        if (!$data = parse_ini_file($iniFile, true)) {
            throw new Exception('Cannot parse onboarding email file: onboarding/emails.ini');
        }

        if (!isset($data[$extension->getPlatformEnvironment()])) {
            throw new Exception(
                'There is no "' . $extension->getPlatformEnvironment() . '" section in onboarding email file'
            );
        }

        $this->emails = $data[$extension->getPlatformEnvironment()];
    }

    /**
     * get array of emails by country code.
     *
     * @param string $countryCode - 2-chars country code
     *
     * @return array
     */
    public function getOnboardingEmailsByCountry($countryCode)
    {
        return isset($this->emails[$countryCode]) ? $this->emails[$countryCode] : array();
    }
}

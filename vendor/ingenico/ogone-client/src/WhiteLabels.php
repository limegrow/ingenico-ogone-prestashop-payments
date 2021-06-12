<?php

namespace IngenicoClient;

/**
 * Class WhiteLabels.
 * @method string getPlatform()
 * @method string getLogoUrl()
 * @method string getLogo()
 * @method string getTemplateGuidEcom()
 * @method string getTemplateGuidFlex()
 * @method string getTemplateGuidPaypal()
 * @method string getSupportEmail()
 * @method string getSupportName()
 * @method string getSupportPhone()
 * @method string getSupportUrl()
 * @method string getSupportTicketPlaceholder()
 */
class WhiteLabels extends Data
{

    /**
     * WhiteLabels constructor.
     *
     * @param ConnectorInterface $extension
     * @param IngenicoCoreLibraryInterface $coreLibrary
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ConnectorInterface $extension,
        IngenicoCoreLibraryInterface $coreLibrary
    ) {
        $iniFile = __DIR__.'/../whitelabels.ini';
        if (!file_exists($iniFile)) {
            throw new Exception('Cannot find file: whitelabels.ini');
        }

        if (!$data = parse_ini_file($iniFile, true)) {
            throw new Exception('Cannot parse file: whitelabels.ini');
        }

        if (!isset($data[$extension->getPlatformEnvironment()])) {
            throw new Exception(
                'There is no "' . $extension->getPlatformEnvironment() . '" section in onboarding email file'
            );
        }

        $this->setData($data[$extension->getPlatformEnvironment()]);
    }
}

<?php

namespace IngenicoClient;

use Detection\MobileDetect;

class DeviceDetect
{
    /**
     * Computer device type string
     */
    const DEVICE_TYPE_COMPUTER = 'computer';

    /**
     * Mobile device type string
     */
    const DEVICE_TYPE_MOBILE = 'mobile';

    /**
     * Tablet device type string
     */
    const DEVICE_TYPE_TABLET = 'tablet';

    /**
     * @var MobileDetect
     */
    private $mobileDetect;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->mobileDetect = new MobileDetect();
    }

    /**
     * Get Device Type.
     *
     * @return string
     */
    public function getDeviceType()
    {
        if ($this->mobileDetect->isMobile()) {
            return self::DEVICE_TYPE_MOBILE;
        } elseif ($this->mobileDetect->isTablet()) {
            return self::DEVICE_TYPE_TABLET;
        }

        return self::DEVICE_TYPE_COMPUTER;
    }
}

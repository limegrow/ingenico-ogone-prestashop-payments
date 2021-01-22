<?php

namespace IngenicoClient;

interface SessionInterface
{
    /**
     * Get all Session values in a key => value format
     *
     * @return array
     */
    public function getSessionValues();

    /**
     * Get value from Session.
     *
     * @param string $key
     * @return mixed
     */
    public function getSessionValue($key);

    /**
     * Store value in Session.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setSessionValue($key, $value);

    /**
     * Remove value from Session.
     *
     * @param $key
     * @return void
     */
    public function unsetSessionValue($key);
}

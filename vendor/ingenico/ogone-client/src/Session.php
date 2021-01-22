<?php

namespace IngenicoClient;

/**
 * Trait Session
 * @package IngenicoClient
 */
trait Session
{
    /**
     * Get all Session values in a key => value format
     *
     * @return array
     */
    public function getSessionValues()
    {
        $values = $this->extension->getSessionValues();

        foreach ($values as $key => $value) {
            if (false !== ($tmp = @unserialize($value))) {
                $values[$key] = $tmp;
            }
        }

        return $values;
    }

    /**
     * Get value from Session.
     *
     * @param string $key
     * @return mixed
     */
    public function getSessionValue($key)
    {
        $value = $this->extension->getSessionValue($key);

        if (false !== ($tmp = @unserialize($value))) {
            return $tmp;
        }

        return $value;
    }

    /**
     * Store value in Session.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setSessionValue($key, $value)
    {
        if (is_array($value) || is_object($value)) {
            $value = serialize($value);
        }

        $this->extension->setSessionValue($key, $value);
    }

    /**
     * Remove value from Session.
     *
     * @param $key
     * @return void
     */
    public function unsetSessionValue($key)
    {
        $this->extension->unsetSessionValue($key);
    }
}
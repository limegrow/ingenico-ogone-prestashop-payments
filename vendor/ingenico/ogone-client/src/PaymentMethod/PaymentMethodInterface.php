<?php

namespace IngenicoClient\PaymentMethod;

interface PaymentMethodInterface
{
    /**
     * Get ID
     * @return string
     */
    public function getId();

    /**
     * Get Name
     * @return string
     */
    public function getName();

    /**
     * Get Category
     * @return string
     */
    public function getCategory();

    /**
     * Get Category Name
     * @return string
     */
    public function getCategoryName();

    /**
     * Get PM
     * @return string
     */
    public function getPM();

    /**
     * Get Brand
     * @return string
     */
    public function getBrand();

    /**
     * Get Countries
     * @return array
     */
    public function getCountries();

    /**
     * Is Security Mandatory
     * @return bool
     */
    public function isSecurityMandatory();

    /**
     * Get Credit Debit Flag
     * @return string
     */
    public function getCreditDebit();

    /**
     * Is support Redirect only
     * @return bool
     */
    public function isRedirectOnly();
}

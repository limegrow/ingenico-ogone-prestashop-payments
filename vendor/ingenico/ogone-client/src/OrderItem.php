<?php

namespace IngenicoClient;

/**
 * Class OrderItem
 * @see https://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/additional-data/order-data
 *
 * @method mixed getType()
 * @method $this setType($value)
 * @method mixed getId()
 * @method $this setId($value)
 * @method mixed getName()
 * @method $this setName($value)
 * @method mixed getDescription()
 * @method $this setDescription($value)
 * @method mixed getUnitPrice()
 * @method $this setUnitPrice($value)
 * @method mixed getQty()
 * @method $this setQty($value)
 * @method mixed getQtyOrig()
 * @method $this setQtyOrig($value)
 * @method mixed getVatPercent()
 * @method $this setVatPercent($value)
 * @method mixed getUnitVat()
 * @method $this setUnitVat($value)
 * @method mixed getAttributes()
 * @method $this setAttributes($value)
 * @method mixed getCategory()
 * @method $this setCategory($value)
 * @method mixed getComments()
 * @method $this setComments($value)
 * @method mixed getDiscount()
 * @method $this setDiscount($value)
 * @method mixed getUnitOfMeasure()
 * @method $this setUnitOfMeasure($value)
 * @method mixed getWeight()
 * @method $this setWeight($value)
 * @method mixed getVatIncluded()
 * @method $this setVatIncluded($value)
 *
 * @package IngenicoClient
 */
class OrderItem extends Data
{
    /**
     * Item Type
     */
    const TYPE_PRODUCT = 'product';
    const TYPE_SHIPPING = 'shipping';
    const TYPE_DISCOUNT = 'discount';
    const TYPE_FEE = 'fee';

    /**
     * Item Fields
     */
    const ITEM_TYPE = 'type';
    const ITEM_ATTRIBUTES = 'attributes';
    const ITEM_CATEGORY = 'category';
    const ITEM_COMMENTS = 'comments';
    const ITEM_DESCRIPTION = 'description';
    const ITEM_DISCOUNT = 'discount';
    const ITEM_ID = 'id';
    const ITEM_NAME = 'name';
    const ITEM_UNIT_PRICE = 'unit_price';
    const ITEM_QTY = 'qty';
    const ITEM_QTY_ORIG = 'qty_orig';
    const ITEM_UNITOFMEASURE = 'unit_of_measure';
    const ITEM_UNIT_VAT = 'unit_vat';
    const ITEM_VATCODE = 'vat_percent';
    const ITEM_WEIGHT = 'weight';
    const ITEM_VAT_INCLUDED = 'vat_included';

    /**
     * Mapping: Ingenico's field => OrderItem's field
     */
    static $map = [
        'ITEMATTRIBUTES' => self::ITEM_ATTRIBUTES,
        'ITEMCATEGORY' => self::ITEM_CATEGORY,
        'ITEMCOMMENTS' => self::ITEM_COMMENTS,
        'ITEMDESC' => self::ITEM_DESCRIPTION,
        'ITEMDISCOUNT' => self::ITEM_DISCOUNT,
        'ITEMID' => self::ITEM_ID,
        'ITEMNAME' => self::ITEM_NAME,
        'ITEMPRICE' => self::ITEM_UNIT_PRICE,
        'ITEMQUANT' => self::ITEM_QTY,
        'ITEMQUANTORIG' => self::ITEM_QTY_ORIG,
        'ITEMUNITOFMEASURE' => self::ITEM_UNITOFMEASURE,
        'ITEMVAT' => self::ITEM_UNIT_VAT,
        'ITEMVATCODE' => self::ITEM_VATCODE,
        'ITEMWEIGHT' => self::ITEM_WEIGHT,
        'TAXINCLUDED' => self::ITEM_VAT_INCLUDED
    ];

    /**
     * OrderItem constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->setData($data);
    }

    /**
     * Get item array with Ingenico specific data
     *
     * @return array
     */
    public function exchange()
    {
        $result = [];
        foreach (self::$map as $key => $value) {
            $data = $this->getData($value);
            if (is_numeric($data) || $data) {
                $result[$key] = $data;

                // Transform data
                switch ($value) {
                    case self::ITEM_UNIT_PRICE:
                    case self::ITEM_UNIT_VAT:
                        // Round Price
                        $result[$key] = round($result[$key], 2);
                        break;
                    case self::ITEM_VATCODE:
                        // Add Percent Suffix
                        $result[$key] = $result[$key] . '%';
                        break;
                    case self::ITEM_ATTRIBUTES:
                    case self::ITEM_CATEGORY:
                    case self::ITEM_QTY:
                    case self::ITEM_QTY_ORIG:
                    case self::ITEM_UNITOFMEASURE:
                        $result[$key] = mb_strimwidth($result[$key], 0, 50, '...', 'UTF-8');
                        break;
                    case self::ITEM_COMMENTS:
                        $result[$key] = mb_strimwidth($result[$key], 0, 255, '...', 'UTF-8');
                        break;
                    case self::ITEM_DESCRIPTION:
                        $result[$key] = mb_strimwidth($result[$key], 0, 16, '...', 'UTF-8');
                        break;
                    case self::ITEM_DISCOUNT:
                    case self::ITEM_WEIGHT:
                        $result[$key] = mb_strimwidth($result[$key], 0, 10, '...', 'UTF-8');
                        break;
                    case self::ITEM_ID:
                        $result[$key] = mb_strimwidth($result[$key], 0, 15, '...', 'UTF-8');
                        break;
                    case self::ITEM_NAME:
                        $result[$key] = mb_strimwidth($result[$key], 0, 40, '...', 'UTF-8');
                        break;
                    case self::ITEM_VAT_INCLUDED:
                        $result[$key] = !in_array((int) $result[$key], [0, 1]) ? 0 : $result[$key];
                    default:
                        // no break
                }
            }
        }

        return $result;
    }
}

<?php

namespace Staatic\Vendor\AsyncAws\S3\Enum;

final class Type
{
    const AMAZON_CUSTOMER_BY_EMAIL = 'AmazonCustomerByEmail';
    const CANONICAL_USER = 'CanonicalUser';
    const GROUP = 'Group';
    public static function exists(string $value) : bool
    {
        return isset([self::AMAZON_CUSTOMER_BY_EMAIL => \true, self::CANONICAL_USER => \true, self::GROUP => \true][$value]);
    }
}

<?php

namespace DatingLibre\AppBundle\Behat\Util;

use Exception;

class TableUtil
{
    /**
     * @throws Exception
     */
    public static function parseCsvRow(string $row): array
    {
        $v = explode(',', $row);

        if ($v === false) {
            throw new Exception(sprintf('Could not parse [%s]', $row));
        }

        return array_map('trim', $v);
    }
}
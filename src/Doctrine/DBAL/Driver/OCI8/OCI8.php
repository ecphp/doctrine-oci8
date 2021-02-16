<?php

declare(strict_types=1);

namespace EcPhp\DoctrineOci8\Doctrine\DBAL\Driver\OCI8;

const PARAM_PREFIX = 0xA000;
const PARAM_MAX = 0xAFFF;

final class OCI8
{
    public const PARAM_AFC = 0xA060;

    public const PARAM_AVC = 0xA061;

    public const PARAM_BDOUBLE = 0xA016;

    public const PARAM_BFILEE = 0xA072;

    public const PARAM_BFLOAT = 0xA015;

    public const PARAM_BIN = 0xA017;

    public const PARAM_BLOB = 0xA071;

    public const PARAM_BOOL = 0xA0FC;

    public const PARAM_CFILEE = 0xA073;

    // OCI8::PARAM_* constants are prefixed with binary 1010 (0xA) in the -4th nibble.
    public const PARAM_CHR = 0xA001;

    public const PARAM_CLOB = 0xA070;

    public const PARAM_CURSOR = 0xA074;

    public const PARAM_FLT = 0xA004;

    public const PARAM_INT = 0xA003;

    public const PARAM_LBI = 0xA018;

    public const PARAM_LNG = 0xA008;

    public const PARAM_LVC = 0xA05E;

    public const PARAM_NTY = 0xA06C;

    public const PARAM_NUM = 0xA002;

    public const PARAM_ODT = 0xA09C;

    public const PARAM_ROWID = 0xA068;

    public const PARAM_STR = 0xA005;

    public const PARAM_UIN = 0xA044;

    public const PARAM_VCS = 0xA009;

    public const RETURN_CURSORS = 0x0200;

    public const RETURN_RESOURCES = 0x0100;

    public static function decodeParamConstant(int $value): int
    {
        return self::isParamConstant($value) ? ($value & ~PARAM_PREFIX) : $value;
    }

    public static function isParamConstant(int $value): bool
    {
        return PARAM_PREFIX <= $value && PARAM_MAX >= $value;
    }
}

<?php
declare(strict_types=1);

namespace App\Utils;

class FileUtil
{
    private const READ_LEN = 4096;

    public static function filesIdentical(string $fn1, string $fn2): bool
    {
        if (filetype($fn1) !== filetype($fn2)) {
            return false;
        }

        if (filesize($fn1) !== filesize($fn2)) {
            return false;
        }

        if (!$fp1 = fopen($fn1, 'rb')) {
            return false;
        }

        if (!$fp2 = fopen($fn2, 'rb')) {
            fclose($fp1);

            return false;
        }

        $same = true;
        while (!feof($fp1) and !feof($fp2)) {
            if (fread($fp1, self::READ_LEN) !== fread($fp2, self::READ_LEN)) {
                $same = false;
                break;
            }
        }

        if (feof($fp1) !== feof($fp2)) {
            $same = false;
        }

        fclose($fp1);
        fclose($fp2);

        return $same;
    }

    public static function deleteFile(string $filePath): void
    {
        unlink($filePath);
    }

}

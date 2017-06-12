<?php

namespace App\Services;

/**
 * Class LocalStorage
 *
 * @package App\Services
 */
class LocalStorage
{
    /**
     * Reads the temporary file with the saved data
     *
     * @param string $key
     * @param string $default
     * @return string|null
     */
    public static function readData($key, $default = null)
    {
        $filename = sys_get_temp_dir() . '/' . $key . '.json';
        $handler = @fopen($filename, 'rb');
        if (!$handler) {
            return $default;
        }

        $data = @fgets($handler);
        if (!$data) {
            return $default;
        }

        return $data;
    }

    /**
     * Writes the data to a temporary file
     *
     * @param string $key
     * @param string $data
     * @return bool
     */
    public static function writeData($key, $data)
    {
        $filename = sys_get_temp_dir() . '/' . $key . '.json';
        $handler = @fopen($filename, 'wb');
        if (!$handler) {
            return false;
        }

        fwrite($handler, $data);
        fclose($handler);
        return true;
    }
}
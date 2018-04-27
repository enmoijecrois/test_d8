<?php

namespace Drupal\test_d8\Helper;

/**
 * Classe de gestion de stockage de fichiers
 * parce que set_cookie() ne fonctionne pas !
 */

define('_TMP_DIR_', realpath(__DIR__.'/../../tmp/'));

class FileStorage
{

  const COOKIE_KEY = '5Up2JsMz9Wx5AdF3';

  const TMP_DIR = _TMP_DIR_ . DIRECTORY_SEPARATOR;

  /**
   * Set data into storage.
   *
   * @param string $name
   *   Name of file.
   * @param mixed $data
   *   Data to store.
   */
  public static function set($name, $data){
    $data = serialize($data);
    $encryptedData = self::encrypt($data);
    return (file_put_contents(self::TMP_DIR . $name, $encryptedData) ? true : false);
  }

  /**
   * Get data from storage.
   *
   * @param string $name
   *   Name of file to get.
   */
  public static function get($name){
    if (!self::exists($name)){
      return false;
    }
    $encryptedData = file_get_contents(self::TMP_DIR . $name);
    $data = self::decrypt($encryptedData);
    return unserialize($data);
  }

  /**
   * Unset data.
   *
   * @param string $name
   *   Name of file to remove.
   */
  public static function remove($name){
    if (!self::exists($name)){
      return false;
    }
    return unlink(self::TMP_DIR . $name);
  }

  /**
   * Is data exists.
   *
   * @param string $name
   *   Name of file to check.
   */
  public static function exists($name){

    //var_dump(self::TMP_DIR . $name);
    return file_exists(self::TMP_DIR . $name);
  }

  /**
   * Validates data.
   *
   * @param string $name
   *   Name of file.
   *
   * @return boolean
   *   True or False based on validation.
   */
  public static function validate($name){
    if (!self::exists($name)){
      return false;
    }
    $data = file_get_contents(self::TMP_DIR . $name);
    return (self::decrypt($data) ? true : false);
  }

  /**
   * Encrypts given data.
   *
   * @param string $data
   *   Serialized data for encryption.
   *
   * @return string
   *   Encrypted data.
   */
  private static function encrypt($data){
    $key = openssl_digest(self::COOKIE_KEY, 'sha256');
    $iv = openssl_random_pseudo_bytes(16);
    $encryptedData = openssl_encrypt($iv . $data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    $signature = hash_hmac('sha256', $encryptedData, $key);
    return base64_encode($signature . $encryptedData);
  }

  /**
   * Decrypts given data.
   *
   * @param string $data
   *   Encrypted data.
   *
   * @return bool|mixed
   *   False if retrieved signature doesn't matches
   *   or data.
   */
  private static function decrypt($data){
    $key = openssl_digest(self::COOKIE_KEY, 'sha256');
    $data = base64_decode($data);
    $signature = substr($data, 0, 64);
    $encryptedData = substr($data, 64);
    if ($signature !== hash_hmac('sha256', $encryptedData, $key)){
      return false;
    }
    $iv = substr($data, 64, 16);
    $encrypted = substr($data, 80);
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
  }

}

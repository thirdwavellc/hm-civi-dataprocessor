<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\DataProcessor\Utils;

class Sql {

  public static function convertTypeToSqlColumnType($strType) {
    switch($strType) {
      case 'Integer':
      case 'Int':
      case 'Timestamp':
        return 'INT (11)';
        break;
      case 'String':
      case 'Text':
      case 'Email':
        return 'TEXT';
        break;
      case 'Date':
        return 'DATE';
        break;
      case 'Time':
        return 'TIME';
        break;
      case 'Boolean':
        return 'TINYINT (1)';
        break;
      case 'Blob':
      case 'Mediumblob':
        return 'Blob';
        break;
      case 'Float':
      case 'Money':
        return 'FLOAT';
        break;
    }
    throw new \Exception('Invalid type given: '.$strType);
  }

}
<?php

  // Change this:
  define('APP_PATH', "/var/www/hdoc");

  define('WP_DEBUG', true);

  // AUTOLOADER

  define("CLASSES_PATH", __DIR__ . DIRECTORY_SEPARATOR . "classes");
  define("INTERFACES_PATH", __DIR__ . DIRECTORY_SEPARATOR . "interfaces");

  /**
   * Autoloader.
   * 
   * @param string $construct the name of the construct to load
   */
  function autoloader($construct) {
    
    // For third-party libraries, eg. Pear
    if (!defined("PHP_INCLUDE_PATH")) {
      define("PHP_INCLUDE_PATH", ini_get("include_path"));
    }
    
    $constructParts = explode('\\', $construct);
    
    // estrapolate the path from the construct name
    $count = count($constructParts);
    if ($count>1) {
      $i = 0;
      $constructPath = $constructParts[0];
      for ($i=1; $i<($count-1); $i++) {
        $constructPath .= DIRECTORY_SEPARATOR . $constructParts[$i];
      }
      $construct = $constructParts[$i];
    }

    if (!stripos($construct, "interface")) {
      // if it is a class
    
      //echo $construct;

      switch ($construct) {
        case "special_case":
          $incPath = PHP_INCLUDE_PATH . DIRECTORY_SEPARATOR . "path/to/special_case.php";
          break;
        case "QRcode":
          $incPath = CLASSES_PATH . DIRECTORY_SEPARATOR . "phpqrcode/qrlib.php";
          //echo "incPath = $incPath" . PHP_BR;
          break;
        case "WP_Error":
          $incPath = CLASSES_PATH . DIRECTORY_SEPARATOR . "WordPress/WordPress/class-wp-error.php";
          //echo "incPath = $incPath" . PHP_BR;
          break;
        case "WP_Image_Editor":
          $incPath = CLASSES_PATH . DIRECTORY_SEPARATOR . "WordPress/WordPress/class-wp-image-editor.php";
          //echo "incPath = $incPath" . PHP_BR;
          break;
        case "WP_Image_Editor_GD":
          $incPath = CLASSES_PATH . DIRECTORY_SEPARATOR . "WordPress/WordPress/class-wp-image-editor-gd.php";
          //echo "incPath = $incPath" . PHP_BR;
          break;
        default:
        
          if (isset($constructPath)) {
            $incPath = CLASSES_PATH . DIRECTORY_SEPARATOR . $constructPath . DIRECTORY_SEPARATOR . "class." . strtolower($construct) . ".inc";
          } else {
            $incPath = CLASSES_PATH . DIRECTORY_SEPARATOR . "class." . strtolower($construct) . ".inc";
          }
          
          break;
      }
      
    } else {
      // if it is an interface
      if (isset($constructPath)) {
        $incPath = INTERFACES_PATH . DIRECTORY_SEPARATOR  . $constructPath . DIRECTORY_SEPARATOR . strtolower($construct) . ".inc";
      } else {
        $incPath = INTERFACES_PATH . DIRECTORY_SEPARATOR . strtolower($construct) . ".inc";
      }  
    }
    
    if (is_readable($incPath)) {
      //echo "$incPath is readable" . PHP_BR;
      require $incPath;
    }
    
  }
  spl_autoload_register("autoloader", true, true);

  define("FUNCTIONS_PATH", __DIR__.DIRECTORY_SEPARATOR."functions");

  // WP FUNCTIONS
  require FUNCTIONS_PATH . DIRECTORY_SEPARATOR . "/wp/formatting.php";
  require FUNCTIONS_PATH . DIRECTORY_SEPARATOR . "/wp/functions.php";
  require FUNCTIONS_PATH . DIRECTORY_SEPARATOR . "/wp/l10n.php";
  require FUNCTIONS_PATH . DIRECTORY_SEPARATOR . "/wp/load.php";
  require FUNCTIONS_PATH . DIRECTORY_SEPARATOR . "/wp/media.php";
  require FUNCTIONS_PATH . DIRECTORY_SEPARATOR . "/wp/plugin.php";
  
  

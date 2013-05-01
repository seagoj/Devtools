<?php
/**
 * Autoload: Autoloader for PHP classes
 *
 * Autoloads instantiated classes across multiple namespaces using PSR-0 standard
 * 
 * @name      Autoload
 * @category  Seagoj
 * @package   Devtools
 * @author    Jeremy Seago <seagoj@gmail.com>
 * @copyright 2012 Jeremy Seago
 * @license   http://opensource.org/licenses/mit-license.php, MIT
 * @version   1.0
 * @link      https://github.com/seagoj/devtools
 */
namespace Devtools;

/**
 * autoload Class
 * Searches for and registers path to class definitions found in source folders.
 *
 * @category   Devtools
 * @package    Seagoj
 * @subpackage Autoload
 * @author     Jeremy Seago    <seagoj@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php, MIT
 * @version    1.0
 * @link       https://github.com/seagoj/devtools
 */
class Autoload
{
    private $_libPath;
    private $_runPath;

    /**
     * public Autoload::__construct
     *
     * Sets $_libPath and $_runPath
     *
     * @param string $currentDir Path to Dir where this script runs
     *
     * @return void
     */
    public function __construct($currentDir = __DIR__)
    {
        switch(self::checkEnv()) {
            case 'PHPUNIT_TRAVIS':
                $this->_runPath = $this->_getPath($currentDir);
                $this->_libPath = $this->_runPath.'/lib';
                break; 
            default:
                $this->_runPath = $this->_getPath($_SERVER['SCRIPT_FILENAME']);
                $this->_libPath = $this->_getPath($currentDir);
                break;
        }
    }

    public function checkEnv() {
        $path = explode('/',$_SERVER['SCRIPT_FILENAME']);
        if($path[2]=='travis' && $path[7]=='phpunit') {
            return 'PHPUNIT_TRAVIS';
        } else {
            return '';    
        }
    }

    /**
     * public Autoload.Register()
     * 
     * Registers the function to use as the autoloader
     *
     * @param bool $prepend Prepend value to pass on to spl_autoload_register
     *
     * @return void
     */
    public static function register($prepend = false)
    {
        spl_autoload_register(array(new self, '_autoload'), true, $prepend);

        var_dump(var_export(spl_autoload_functions()));


    }

    /**
     * Autoload.autoloader()
     *
     * @param string $class Class that the autoloader is searching for
     * 
     * @return void
     */
    private function _autoload($class)
    {
//        var_dump("RelPath: ".$this->_getRelPath().implode(DIRECTORY_SEPARATOR, explode('\\', $class)).'.php');

        if (is_file($file = $this->_getRelPath().implode(DIRECTORY_SEPARATOR, explode('\\', $class)).'.php')) {
            //var_dump("File found");
            include $file;
            //var_dump($file);
        } else {
            $file = $class . '.php';
  //          var_dump("Looking for $file");
            if (file_exists($file)) {
                require $file;
  //              var_dump("Found $file");
            } else if (file_exists('lib/Devtools/'.$file)) {
  //              var_dump("found lib/Devtools/$file");
                require 'lib/Devtools/'.$file;        
            }
        }
        /*else {
            throw new \Exception("$file does not exist.");
        }*/
    }

    /**
     * Private Static Autoload::_getPath
     *
     * Returns Path to $file
     *
     * @param string $file File to return the path of
     *
     * @return string Path to $file
     */
    private function _getPath($file)
    {
        return substr($file, 0, strripos($file, DIRECTORY_SEPARATOR));
    }

    /**
     * Private Autoload::_getRelPath
     *
     * Returns Path to $_libPath relative to $_runPath
     *
     * @return string Path to $file
     */
private function _getRelPath()
    {
        if ($this->_runPath==$$this->_libPath) {
            return '';
        } else {
            $runPathArray = explode(DIRECTORY_SEPARATOR, $this->_runPath);
            $libPathArray = explode(DIRECTORY_SEPARATOR, $this->_libPath);
//            print "<div>RunPath: ".var_dump($runPathArray)."</div>";
//            print "<div>LibPath: ".var_dump($libPathArray)."</div>";
            $runPathDepth = sizeof($runPathArray);
            $libPathDepth = sizeof($libPathArray);
            $poppedFromRun = $poppedFromLib = $relPath = array();

            if ($runPathDepth==$libPathDepth) {
//                print "<div>Same Depth</div>";
                while ($runPathArray!=$libPathArray) {
                    array_push($poppedFromRun, array_pop($runPathArray));
                    array_push($poppedFromLib, array_pop($libPathArray));
                }
            } else {
                if ($runPathDepth>$libPathDepth) {
                    $longArray = 'run';
                    $shortArray = 'lib';
                } else {
                    $longArray = 'lib';
                    $shortArray = 'run';
                }

//                print $longArray."Path is deeper.";

                $longArrayDepth = sizeof(${$longArray.'PathArray'});
                $shortArrayDepth = sizeof(${$shortArray.'PathArray'});

                $i=0;

                while ($runPathArray[$i]==$libPathArray[$i] && $i<($runPathDepth-1) && $i<($libPathDepth-1)) {
                    $i++;
                }
                if($runPathArray[$i]!==$libPathArray[$i])
                    $i--;

//                print "<div>Same to index $i</div>";

                for ($i; $i<$longArrayDepth-1; $i++) {
                    if($i<$shortArrayDepth-1) {
                        array_push(${"poppedFrom".ucfirst($shortArray)}, array_pop(${$shortArray."PathArray"}));
                    }
                    array_push(${"poppedFrom".ucfirst($longArray)}, array_pop(${$longArray."PathArray"}));
                }
//                print "<div>PoppedFromRun: ".var_dump($poppedFromRun)."</div>";
//                print "<div>PoppedFromLib: ".var_dump($poppedFromLib)."</div>";
            }

            foreach ($poppedFromRun AS $pop) {
                array_push($relPath, "..");
            }

            foreach (array_reverse($poppedFromLib) AS $pop) {
                array_push($relPath, $pop);
            }
            return implode(DIRECTORY_SEPARATOR, $relPath).DIRECTORY_SEPARATOR;
        }
    }




    private function _getRelPath_OLD()
    {
        if ($this->_runPath==$this->_libPath) {
            return '';
        } else {
            $runPathArray = explode(DIRECTORY_SEPARATOR, $this->_runPath);
            $libPathArray = explode(DIRECTORY_SEPARATOR, $this->_libPath);
            $runPathDepth = sizeof($runPathArray);
            $libPathDepth = sizeof($libPathArray);
            $poppedFromRun = $poppedFromLib = $relPath = array();

            if ($runPathDepth==$libPathDepth) {
                while ($runPathArray!=$libPathArray) {
                    array_push($poppedFromRun, array_pop($runPathArray));
                    array_push($poppedFromLib, array_pop($libPathArray));
                }
            } else {
                $i=0;

                while ($runPathArray[$i]==$libPathArray[$i] && $i<($runPathDepth-1)) {
                    $i++;
                }

                if ($runPathDepth>$libPathDepth) {
                    $longArray = 'run';
                    $shortArray = 'lib';
                } else {
                    $longArray = 'lib';
                    $shortArray = 'run';
                }

                $longArrayDepth = sizeof(${$longArray.'PathArray'});
                $shortArrayDepth = sizeof(${$shortArray.'PathArray'});

                for ($i; $i<$shortArrayDepth; $i++) {
                    array_push($poppedFromRun, array_pop($runPathArray));
                    array_push($poppedFromLib, array_pop($libPathArray));
                }

                for ($j=$shortArrayDepth; $j<$longArrayDepth; $j++) {
                    array_push(${"poppedFrom".ucfirst($longArray)}, array_pop(${$longArray."PathArray"}));
                }
            }

            foreach ($poppedFromRun AS $pop) {
                array_push($relPath, "..");
            }

            foreach (array_reverse($poppedFromLib) AS $pop) {
                array_push($relPath, $pop);
            }
            return implode(DIRECTORY_SEPARATOR, $relPath).DIRECTORY_SEPARATOR;
        }
    }

    // /**
    //  * Autoload::_stripProjectNS()
    //  *
    //  * Strips project namespace when autoloading
    //  *
    //  * @param string $class Namespace include project namespace
    //  *
    //  * @return string Namespace without project namespace
    //  */
    // private function _stripProjectNS($class)
    // {
    //     $classArray = explode('\\', $class);
    //     $dirs = array();

    //     if ($handle = opendir($this->_libPath)) {
    //         while (false !== ($entry = readdir($handle))) {
    //             if ($entry!="." && $entry!="..") {
    //                 array_push($dirs, $entry);
    //             }
    //         }
    //         closedir($handle);
    //     }

    //     // var_dump($dirs);

    //     while (!in_array($pop = array_pop($dirs), $classArray)) {
    //     }
        
    //     $classArray = array_reverse($classArray);
    //     //var_dump($classArray);
    //     while (($classPop = array_pop($classArray)) != $pop) {
    //     }
    //     array_push($classArray, $classPop);
    //     $classArray = array_reverse($classArray);
    //     //var_dump($classArray);
    //     //print $pop;

    //     // var_dump($dirs);
    //     //die(var_dump($classArray));
    //     return implode("\\", $classArray);
    // }
}

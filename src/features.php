<?php
/**
 * File containing the ezcBaseFeatures class.
 *
 * @package Base
 * @version //autogentag//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Provides methods needed to check for features.
 *
 * Example:
 * <code>
 * <?php
 * echo "supports uid: " . ezcBaseFeatures::supportsUserId() . "\n";
 * echo "supports symlink: " . ezcBaseFeatures::supportsSymLink() . "\n";
 * echo "supports hardlink: " . ezcBaseFeatures::supportsLink() . "\n";
 * echo "has imagemagick identify: " . ezcBaseFeatures::hasImageIdentify() . "\n";
 * echo " identify path: " . ezcBaseFeatures::getImageIdentifyExecutable() . "\n";
 * echo "has imagemagick convert: " . ezcBaseFeatures::hasImageConvert() . "\n";
 * echo " convert path: " . ezcBaseFeatures::getImageConvertExecutable() . "\n";
 * echo "has gzip extension: " . ezcBaseFeatures::hasExtensionSupport( 'zlib' ) . "\n";
 * echo "has pdo_mysql 1.0.2: " . ezcBaseFeatures::hasExtensionSupport( 'pdo_mysql', '1.0.2' ) . "\n"
 * ?>
 * </code>
 *
 * @package Base
 * @version //autogentag//
 */
class ezcBaseFeatures
{
    /**
      * Used to store the path of the ImageMagick convert utility.
      * 
      * It is initialized in the {@link getImageConvertExecutable()} function.
      * 
      * @var string
      */
    private static $imageConvert = null;

    /**
      * Used to store the path of the ImageMagick identify utility.
      * 
      * It is initialized in the {@link getImageIdentifyExecutable()} function.
      * 
      * @var string
      */
    private static $imageIdentify = null;

    /**
      * Used to store the operating system.
      * 
      * It is initialized in the {@link os()} function.
      * 
      * @var string
      */
    private static $os = null;

    /**
     * Determines if hardlinks are supported.
     *
     * @return bool
     */
    public static function supportsLink()
    {
        return function_exists( 'link' );
    }

    /**
     * Determines if symlinks are supported.
     *
     * @return bool
     */
    public static function supportsSymLink()
    {
        return function_exists( 'symlink' );
    }

    /**
     * Determines if posix uids are supported.
     *
     * @return bool
     */
    public static function supportsUserId()
    {
        return function_exists( 'posix_getpwuid' );
    }

    /**
     * Determines if the ImageMagick convert utility is installed.
     *
     * @return bool
     */
    public static function hasImageConvert()
    {
        return !is_null( self::getImageConvertExecutable() );
    }

    /**
     * Returns the path to the ImageMagick convert utility.
     *
     * On Linux, Unix,... it will return something like: /usr/bin/convert
     * On Windows it will return something like: C:\Windows\System32\convert.exe
     *
     * @return string
     */
    public static function getImageConvertExecutable()
    {
        if ( !is_null( self::$imageConvert ) )
        {
            return self::$imageConvert;
        }
        return ( self::$imageConvert = self::getPath( 'convert' ) );
    }

    /**
     * Determines if the ImageMagick identify utility is installed.
     *
     * @return bool
     */
    public static function hasImageIdentify()
    {
        return !is_null( self::getImageIdentifyExecutable() );
    }

    /**
     * Returns the path to the ImageMagick identify utility.
     *
     * On Linux, Unix,... it will return something like: /usr/bin/identify
     * On Windows it will return something like: C:\Windows\System32\identify.exe
     *
     * @return string
     */
    public static function getImageIdentifyExecutable()
    {
        if ( !is_null( self::$imageIdentify ) )
        {
            return self::$imageIdentify;
        }
        return ( self::$imageIdentify = self::getPath( 'identify' ) );
    }

    /**
     * Determines if the specified extension is loaded.
     *
     * If $version is specified, the specified extension will be tested also
     * against the version of the loaded extension.
     *
     * Examples:
     * <code>
     * hasExtensionSupport( 'gzip' );
     * </code>
     * will return true if gzip extension is loaded.
     *
     * <code>
     * hasExtensionSupport( 'pdo_mysql', '1.0.2' );
     * </code>
     * will return true if pdo_mysql extension is loaded and its version is at least 1.0.2.
     *
     * @param string $extension
     * @param string $version
     * @return bool
     */
    public static function hasExtensionSupport( $extension, $version = null )
    {
        if ( is_null( $version ) )
        {
            return extension_loaded( $extension );
        }
        return extension_loaded( $extension ) && version_compare( phpversion( $extension ), $version, ">=" ) ;
    }

    /**
     * Determines if the specified function is available.
     *
     * Examples:
     * <code>
     * ezcBaseFeatures::hasFunction( 'imagepstext' );
     * </code>
     * will return true if support for Type 1 fonts is available with your GD
     * extension.
     *
     * @param string $functionName
     * @return bool
     */
    public static function hasFunction( $functionName )
    {
        return function_exists( $functionName );
    }

    /**
     * Returns the operating system on which php is running.
     *
     * @return string
     */
    private static function os()
    {
        if ( is_null( self::$os ) )
        {
            $uname = php_uname( 's' );
            if ( substr( $uname, 0, 7 ) == 'Windows' )
            {
                self::$os = 'Windows';
            }
            elseif ( substr( $uname, 0, 3 ) == 'Mac' )
            {
                self::$os = 'Mac';
            }
            elseif ( strtolower( $uname ) == 'linux' )
            {
                self::$os = 'Linux';
            }
            elseif ( strtolower( substr( $uname, 0, 7 ) ) == 'freebsd' )
            {
                self::$os = 'FreeBSD';
            }
            else
            {
                self::$os = PHP_OS;
            }
        }
        return self::$os;
    }

    /**
     * Returns the path to the specified filename based on the os.
     *
     * It scans the PATH enviroment variable based on the os to find the $fileName.
     * For Windows, the path is with \, not /.
     * If $fileName is not found, it returns null.
     *
     * @todo: consider using getenv( 'PATH' ) instead of $_ENV['PATH']
     *        (but that won't work under IIS)
     *
     * @param string $fileName
     * @return string
     */
    private static function getPath( $fileName )
    {
        if ( array_key_exists( 'PATH', $_ENV ) )
        {
            $envPath = $_ENV['PATH'];
            if ( strlen( trim( $envPath ) ) == 0 )
            {
                $envPath = false;
            }
        }
        else
        {
            $envPath = false;
        }
        switch ( self::os() )
        {
            case 'Unix':
            case 'FreeBSD':
            case 'Mac':
            case 'MacOS':
            case 'Darwin':
            case 'Linux':
                if ( $envPath )
                {
                    $dirs = explode( ':', $envPath );
                    foreach ( $dirs as $dir )
                    {
                        if ( file_exists( "{$dir}/{$fileName}" ) )
                        {
                            return "{$dir}/{$fileName}";
                        }
                    }
                }
                elseif ( file_exists( "./{$fileName}" ) )
                {
                    return $fileName;
                }
                break;
            case 'Windows':
                if ( $envPath )
                {
                    $dirs = explode( ';', $envPath );
                    foreach ( $dirs as $dir )
                    {
                        if ( file_exists( "{$dir}\\{$fileName}.exe" ) )
                        {
                            return "{$dir}\\{$fileName}";
                        }
                    }
                }
                elseif ( file_exists( "{$fileName}.exe" ) )
                {
                    return $fileName;
                }
                break;
        }
        return null;
    }
}
?>
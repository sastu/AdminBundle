<?php
/*
 * jQuery File Upload Plugin PHP Class 6.4.2
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

namespace Optisoop\Bundle\AdminBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Optisoop\Bundle\CoreBundle\Entity\Image as ProductImage;
use Optisoop\Bundle\BlogBundle\Entity\Image as PostImage;
use Optisoop\Bundle\CoreBundle\Entity\Project;

/**
 * Class UploadHandler
 *
 * Forked from https://github.com/blueimp/jQuery-File-Upload
 * The constructor and the post method are the only customized methods from the original.
 * The post method only customizes the $fileName variable.
 * Everything else is like the original, but with coding standard errors fixed.
 */
class UploadHandler
{
    private $entityManager;

    protected $options;
    // PHP File Upload error message codes:
    // http://php.net/manual/en/features.file-upload.errors.php
    protected $error_messages = array(
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload',
        'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
        'max_file_size' => 'File is too big',
        'min_file_size' => 'File is too small',
        'accept_file_types' => 'Filetype not allowed',
        'max_number_of_files' => 'Maximum number of files exceeded',
        'max_width' => 'Image exceeds maximum width',
        'min_width' => 'Image requires a minimum width',
        'max_height' => 'Image exceeds maximum height',
        'min_height' => 'Image requires a minimum height'
    );

    /**
     * Class constructor
     *
     * @param string        $rootDir       Symfony root dir
     * @param string        $type          Image type
     * @param Router        $router        Symfony router
     * @param Request       $request       Symfony request
     * @param EntityManager $entityManager Entity Manager
     * @param array|null    $options       Options to merge
     * @param bool          $initialize    Initialize flag
     * @param array|null    $errorMessages Error messages
     */
    public function __construct($rootDir, Router $router, Request $request, EntityManager $entityManager, $options = null, $initialize = true, $errorMessages = null)
    {
        $this->entityManager = $entityManager;

        $this->options = array(
            'script_url' => $router->generate($request->get('route'), array(
                        'id' => $request->get('id'), 
                        'slug' => $request->get('slug'), 
                        'type' => $request->get('type'), 
                        'route' => $request->get('route'),
                        'entity' => $request->get('entity'),
                        'image_entity' => $request->get('image_entity')
                    ), true),
            'upload_dir' => $rootDir.'/../web/uploads/images/'.$request->get('type').'/'.$request->get('id').'/',
            'upload_url' => $this->get_full_url().'/uploads/images/'.$request->get('type').'/'.$request->get('id').'/',
            'entity_id' => $request->get('id'),
            'entity_slug' => $request->get('slug'),
            'entity_path' => $request->get('entity'),
            'entity_image_path' => $request->get('image_entity'),
            'user_dirs' => false,
            'mkdir_mode' => 0755,
            'param_name' => 'files',
            // Set the following option to 'POST', if your server does not support
            // DELETE requests. This is a parameter sent to the client:
            'delete_type' => 'DELETE',
            'access_control_allow_origin' => '*',
            'access_control_allow_credentials' => false,
            'access_control_allow_methods' => array(
                'OPTIONS',
                'HEAD',
                'GET',
                'POST',
                'PUT',
                'PATCH',
                'DELETE'
            ),
            'access_control_allow_headers' => array(
                'Content-Type',
                'Content-Range',
                'Content-Disposition'
            ),
            // Enable to provide file downloads via GET requests to the PHP script:
            'download_via_php' => false,
            // Defines which files can be displayed inline when downloaded:
            'inline_file_types' => '/\.(gif|jpe?g|png)$/i',
            // Defines which files (based on their names) are accepted for upload:
            'accept_file_types' => '/.+$/i',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            // The maximum number of files for the upload directory:
            'max_number_of_files' => null,
            // Image resolution restrictions:
            'max_width' => null,
            'max_height' => null,
            'min_width' => 1,
            'min_height' => 1,
            // Set the following option to false to enable resumable uploads:
            'discard_aborted_uploads' => true,
            // Set to true to rotate images based on EXIF meta data, if available:
            'orient_image' => false,
            'image_versions' => array(
                // Uncomment the following version to restrict the size of
                // uploaded images:
                /*
                '' => array(
                    'max_width' => 1920,
                    'max_height' => 1200,
                    'jpeg_quality' => 95
                ),
                */
                // Uncomment the following to create medium sized images:
                /*
                'medium' => array(
                    'max_width' => 800,
                    'max_height' => 600,
                    'jpeg_quality' => 80
                ),
                */
                'thumbnail' => array(
                    // Uncomment the following to force the max
                    // dimensions and e.g. create square thumbnails:
                    //'crop' => true,
                    'max_width' => 200,
                    'max_height' => 200
                )
            )
        );
        if ($options) {
            $this->options = array_merge($this->options, $options);
        }
        if ($errorMessages) {
            $this->error_messages = array_merge($this->error_messages, $errorMessages);
        }
        if ($initialize) {
            $this->initialize();
        }
    }

    protected function initialize()
    {
        switch ($this->get_server_var('REQUEST_METHOD')) {
            case 'OPTIONS':
            case 'HEAD':
                $this->head();
                break;
            case 'GET':
                $this->get();
                break;
            case 'PATCH':
            case 'PUT':
            case 'POST':
                $this->post();
                break;
            case 'DELETE':
                $this->delete();
                break;
            default:
                $this->header('HTTP/1.1 405 Method Not Allowed');
        }
    }

    protected function get_full_url()
    {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        return
            ($https ? 'https://' : 'http://').
            (!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
            ($https && $_SERVER['SERVER_PORT'] === 443 ||
            $_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
            substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }

    protected function get_user_id()
    {
        @session_start();

        return session_id();
    }

    protected function get_user_path()
    {
        if ($this->options['user_dirs']) {
            return $this->get_user_id().'/';
        }

        return '';
    }

    protected function get_upload_path($fileName = null, $version = null)
    {
        $fileName = $fileName ? $fileName : '';
        $versionPath = empty($version) ? '' : $version.'/';

        return $this->options['upload_dir'].$this->get_user_path()
            .$versionPath.$fileName;
    }

    protected function get_query_separator($url)
    {
        return strpos($url, '?') === false ? '?' : '&';
    }

    protected function get_download_url($fileName, $version = null)
    {
        if ($this->options['download_via_php']) {
            $url = $this->options['script_url']
                .$this->get_query_separator($this->options['script_url'])
                .'file='.rawurlencode($fileName);
            if ($version) {
                $url .= '&version='.rawurlencode($version);
            }

            return $url.'&download=1';
        }
        $versionPath = empty($version) ? '' : rawurlencode($version).'/';

        return $this->options['upload_url'].$this->get_user_path()
            .$versionPath.rawurlencode($fileName);
    }

    protected function set_file_delete_properties($file)
    {
        $file->deleteUrl = $this->options['script_url']
            .$this->get_query_separator($this->options['script_url'])
            .'file='.rawurlencode($file->name);
        $file->deleteType = $this->options['delete_type'];
        if ($file->deleteType !== 'DELETE') {
            $file->deleteUrl .= '&_method=DELETE';
        }
        if ($this->options['access_control_allow_credentials']) {
            $file->delete_with_credentials = true;
        }
    }

    // Fix for overflowing signed 32 bit integers,
    // works for sizes up to 2^32-1 bytes (4 GiB - 1):
    protected function fix_integer_overflow($size)
    {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }

        return $size;
    }

    protected function get_file_size($filePath, $clearStatCache = false)
    {
        if ($clearStatCache) {
            clearstatcache(true, $filePath);
        }

        return $this->fix_integer_overflow(filesize($filePath));
    }

    protected function is_valid_file_object($fileName)
    {
        $filePath = $this->get_upload_path($fileName);
        if (is_file($filePath) && $fileName[0] !== '.') {
            return true;
        }

        return false;
    }

    protected function get_file_object($fileName)
    {
        if ($this->is_valid_file_object($fileName)) {
            $file = new \stdClass();
            $file->name = $fileName;
            $file->size = $this->get_file_size(
                $this->get_upload_path($fileName)
            );
            $file->url = $this->get_download_url($file->name);
            foreach ($this->options['image_versions'] as $version => $options) {
                if (!empty($version)) {
                    if (is_file($this->get_upload_path($fileName, $version))) {
                        $file->{$version.'_url'} = $this->get_download_url(
                            $file->name,
                            $version
                        );
                    }
                }
            }
            $this->set_file_delete_properties($file);

            return $file;
        }

        return null;
    }

    protected function get_file_objects($iterationMethod = 'get_file_object')
    {
        $uploadDir = $this->get_upload_path();
        
        if (!is_dir($uploadDir)) {
            return array();
        }

        return array_values(array_filter(array_map(
            array($this, $iterationMethod),
            scandir($uploadDir)
        )));
    }

    protected function count_file_objects()
    {
        return count($this->get_file_objects('is_valid_file_object'));
    }

    protected function create_scaled_image($fileName, $version, $options)
    {
        $filePath = $this->get_upload_path($fileName);
        if (!empty($version)) {
            $versionDir = $this->get_upload_path(null, $version);
            if (!is_dir($versionDir)) {
                mkdir($versionDir, $this->options['mkdir_mode'], true);
            }
            $newFilePath = $versionDir.'/'.$fileName;
        } else {
            $newFilePath = $filePath;
        }
        if (!function_exists('getimagesize')) {
            error_log('Function not found: getimagesize');

            return false;
        }
        list($imgWidth, $imgHeight) = @getimagesize($filePath);
        if (!$imgWidth || !$imgHeight) {
            return false;
        }
        $maxWidth = $options['max_width'];
        $maxHeight = $options['max_height'];
        $scale = min(
            $maxWidth / $imgWidth,
            $maxHeight / $imgHeight
        );
        if ($scale >= 1) {
            if ($filePath !== $newFilePath) {
                return copy($filePath, $newFilePath);
            }

            return true;
        }
        if (!function_exists('imagecreatetruecolor')) {
            error_log('Function not found: imagecreatetruecolor');

            return false;
        }
        if (empty($options['crop'])) {
            $newWidth = $imgWidth * $scale;
            $newHeight = $imgHeight * $scale;
            $dstX = 0;
            $dstY = 0;
            $newImg = @imagecreatetruecolor($newWidth, $newHeight);
        } else {
            if (($imgWidth / $imgHeight) >= ($maxWidth / $maxHeight)) {
                $newWidth = $imgWidth / ($imgHeight / $maxHeight);
                $newHeight = $maxHeight;
            } else {
                $newWidth = $maxWidth;
                $newHeight = $imgHeight / ($imgWidth / $maxWidth);
            }
            $dstX = 0 - ($newWidth - $maxWidth) / 2;
            $dstY = 0 - ($newHeight - $maxHeight) / 2;
            $newImg = @imagecreatetruecolor($maxWidth, $maxHeight);
        }
        switch (strtolower(substr(strrchr($fileName, '.'), 1))) {
            case 'jpg':
            case 'jpeg':
                $srcImg = @imagecreatefromjpeg($filePath);
                $writeImage = 'imagejpeg';
                $imageQuality = isset($options['jpeg_quality']) ?
                    $options['jpeg_quality'] : 75;
                break;
            case 'gif':
                @imagecolortransparent($newImg, @imagecolorallocate($newImg, 0, 0, 0));
                $srcImg = @imagecreatefromgif($filePath);
                $writeImage = 'imagegif';
                $imageQuality = null;
                break;
            case 'png':
                @imagecolortransparent($newImg, @imagecolorallocate($newImg, 0, 0, 0));
                @imagealphablending($newImg, false);
                @imagesavealpha($newImg, true);
                $srcImg = @imagecreatefrompng($filePath);
                $writeImage = 'imagepng';
                $imageQuality = isset($options['png_quality']) ?
                    $options['png_quality'] : 9;
                break;
            default:
                $srcImg = null;
        }
        $success = $srcImg && @imagecopyresampled(
            $newImg,
            $srcImg,
            $dstX,
            $dstY,
            0,
            0,
            $newWidth,
            $newHeight,
            $imgWidth,
            $imgHeight
        ) && $writeImage($newImg, $newFilePath, $imageQuality);
        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($srcImg);
        @imagedestroy($newImg);

        return $success;
    }

    protected function get_error_message($error)
    {
        return array_key_exists($error, $this->errorMessages) ?
            $this->errorMessages[$error] : $error;
    }

    /**
     * @param string $val
     *
     * @return float
     */
    public function get_config_bytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $this->fix_integer_overflow($val);
    }

    protected function validate($uploadedFile, $file, $error, $index)
    {
        if ($error) {
            $file->error = $this->get_error_message($error);

            return false;
        }
        $contentLength = $this->fix_integer_overflow(intval(
            $this->get_server_var('CONTENT_LENGTH')
        ));
        $postMaxSize = $this->get_config_bytes(ini_get('post_max_size'));
        if ($postMaxSize && ($contentLength > $postMaxSize)) {
            $file->error = $this->get_error_message('post_max_size');

            return false;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            $file->error = $this->get_error_message('accept_file_types');

            return false;
        }
        if ($uploadedFile && is_uploaded_file($uploadedFile)) {
            $fileSize = $this->get_file_size($uploadedFile);
        } else {
            $fileSize = $contentLength;
        }
        if ($this->options['max_file_size'] && (
            $fileSize > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
        ) {
            $file->error = $this->get_error_message('max_file_size');

            return false;
        }
        if ($this->options['min_file_size'] &&
            $fileSize < $this->options['min_file_size']) {
            $file->error = $this->get_error_message('min_file_size');

            return false;
        }
        if (is_int($this->options['max_number_of_files']) && (
            $this->count_file_objects() >= $this->options['max_number_of_files'])
        ) {
            $file->error = $this->get_error_message('max_number_of_files');

            return false;
        }
        list($imgWidth, $imgHeight) = @getimagesize($uploadedFile);
        if (is_int($imgWidth)) {
            if ($this->options['max_width'] && $imgWidth > $this->options['max_width']) {
                $file->error = $this->get_error_message('max_width');

                return false;
            }
            if ($this->options['max_height'] && $imgHeight > $this->options['max_height']) {
                $file->error = $this->get_error_message('max_height');

                return false;
            }
            if ($this->options['min_width'] && $imgWidth < $this->options['min_width']) {
                $file->error = $this->get_error_message('min_width');

                return false;
            }
            if ($this->options['min_height'] && $imgHeight < $this->options['min_height']) {
                $file->error = $this->get_error_message('min_height');

                return false;
            }
        }

        return true;
    }

    protected function upcount_name_callback($matches)
    {
        $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';

        return ' ('.$index.')'.$ext;
    }

    protected function upcount_name($name)
    {
        return preg_replace_callback(
            '/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
            array($this, 'upcount_name_callback'),
            $name,
            1
        );
    }

    protected function get_unique_filename($name, $type, $index, $contentRange)
    {
        while (is_dir($this->get_upload_path($name))) {
            $name = $this->upcount_name($name);
        }
        // Keep an existing filename if this is part of a chunked upload:
        $uploadedBytes = $this->fix_integer_overflow(intval($contentRange[1]));
        while (is_file($this->get_upload_path($name))) {
            if ($uploadedBytes === $this->get_file_size(
                $this->get_upload_path($name))) {
                break;
            }
            $name = $this->upcount_name($name);
        }

        return $name;
    }

    protected function trim_file_name($name, $type, $index, $contentRange)
    {
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $name = trim(basename(stripslashes($name)), ".\x00..\x20");
        // Use a timestamp for empty filenames:
        if (!$name) {
            $name = str_replace('.', '-', microtime(true));
        }
        // Add missing file extension for known image types:
        if (strpos($name, '.') === false &&
            preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
            $name .= '.'.$matches[1];
        }

        return $name;
    }

    protected function get_file_name($name, $type, $index, $contentRange)
    {
        return $this->get_unique_filename(
            $this->trim_file_name($name, $type, $index, $contentRange),
            $type,
            $index,
            $contentRange
        );
    }

    protected function handle_form_data($file, $index)
    {
        // Handle form data, e.g. $_REQUEST['description'][$index]
    }

    protected function orient_image($filePath)
    {
        if (!function_exists('exif_read_data')) {
            return false;
        }
        $exif = @exif_read_data($filePath);
        if ($exif === false) {
            return false;
        }
        $orientation = intval(@$exif['Orientation']);
        if (!in_array($orientation, array(3, 6, 8))) {
            return false;
        }
        $image = @imagecreatefromjpeg($filePath);
        switch ($orientation) {
            case 3:
                $image = @imagerotate($image, 180, 0);
                break;
            case 6:
                $image = @imagerotate($image, 270, 0);
                break;
            case 8:
                $image = @imagerotate($image, 90, 0);
                break;
            default:
                return false;
        }
        $success = imagejpeg($image, $filePath);
        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($image);

        return $success;
    }

    protected function handle_image_file($filePath, $file)
    {
        if ($this->options['orient_image']) {
            $this->orient_image($filePath);
        }
        $failedVersions = array();
        foreach ($this->options['image_versions'] as $version => $options) {
            if ($this->create_scaled_image($file->name, $version, $options)) {
                if (!empty($version)) {
                    $file->{$version.'_url'} = $this->get_download_url(
                        $file->name,
                        $version
                    );
                } else {
                    $file->size = $this->get_file_size($filePath, true);
                }
            } else {
                $failedVersions[] = $version;
            }
        }
        switch (count($failedVersions)) {
            case 0:
                break;
            case 1:
                $file->error = 'Failed to create scaled version: '
                    .$failedVersions[0];
                break;
            default:
                $file->error = 'Failed to create scaled versions: '
                    .implode($failedVersions, ', ');
        }
    }

    protected function handle_file_upload($uploadedFile, $name, $size, $type, $error,
                                          $index = null, $contentRange = null)
    {
        $file = new \stdClass();
        $file->name = $this->get_file_name($name, $type, $index, $contentRange);
        $file->size = $this->fix_integer_overflow(intval($size));
        $file->type = $type;
        if ($this->validate($uploadedFile, $file, $error, $index)) {
            $this->handle_form_data($file, $index);
            $uploadDir = $this->get_upload_path();
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, $this->options['mkdir_mode'], true);
            }
            $filePath = $this->get_upload_path($file->name);
            $appendFile = $contentRange && is_file($filePath) &&
                $file->size > $this->get_file_size($filePath);
            if ($uploadedFile && is_uploaded_file($uploadedFile)) {
                // multipart/formdata uploads (POST method uploads)
                if ($appendFile) {
                    file_put_contents(
                        $filePath,
                        fopen($uploadedFile, 'r'),
                        FILE_APPEND
                    );
                } else {
                    move_uploaded_file($uploadedFile, $filePath);
                }
            } else {
                // Non-multipart uploads (PUT method support)
                file_put_contents(
                    $filePath,
                    fopen('php://input', 'r'),
                    $appendFile ? FILE_APPEND : 0
                );
            }
            $fileSize = $this->get_file_size($filePath, $appendFile);
            if ($fileSize === $file->size) {
                $file->url = $this->get_download_url($file->name);
                list($imgWidth, $imgHeight) = @getimagesize($filePath);
                if (is_int($imgWidth)) {
                    $this->handle_image_file($filePath, $file);
                }
            } else {
                $file->size = $fileSize;
                if (!$contentRange && $this->options['discard_aborted_uploads']) {
                    unlink($filePath);
                    $file->error = 'abort';
                }
            }
            $this->set_file_delete_properties($file);
        }

        return $file;
    }

    protected function readfile($filePath)
    {
        return readfile($filePath);
    }

    protected function body($str)
    {
        echo $str;
    }

    protected function header($str)
    {
        header($str);
    }

    protected function get_server_var($id)
    {
        return isset($_SERVER[$id]) ? $_SERVER[$id] : '';
    }

    protected function generate_response($content, $printResponse = true)
    {
        if ($printResponse) {
            $json = json_encode($content);
            $redirect = isset($_REQUEST['redirect']) ?
                stripslashes($_REQUEST['redirect']) : null;
            if ($redirect) {
                $this->header('Location: '.sprintf($redirect, rawurlencode($json)));

                return;
            }
            $this->head();
            if ($this->get_server_var('HTTP_CONTENT_RANGE')) {
                $files = isset($content[$this->options['param_name']]) ?
                    $content[$this->options['param_name']] : null;
                if ($files && is_array($files) && is_object($files[0]) && $files[0]->size) {
                    $this->header('Range: 0-'.(
                        $this->fix_integer_overflow(intval($files[0]->size)) - 1
                    ));
                }
            }
            $this->body($json);
        }

        return $content;
    }

    protected function get_version_param()
    {
        return isset($_GET['version']) ? basename(stripslashes($_GET['version'])) : null;
    }

    protected function get_file_name_param()
    {
        return isset($_GET['file']) ? basename(stripslashes($_GET['file'])) : null;
    }

    protected function get_file_type($filePath)
    {
        switch (strtolower(pathinfo($filePath, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            default:
                return '';
        }
    }

    protected function download()
    {
        if (!$this->options['download_via_php']) {
            $this->header('HTTP/1.1 403 Forbidden');

            return;
        }
        $fileName = $this->get_file_name_param();
        if ($this->is_valid_file_object($fileName)) {
            $filePath = $this->get_upload_path($fileName, $this->get_version_param());
            if (is_file($filePath)) {
                if (!preg_match($this->options['inline_file_types'], $fileName)) {
                    $this->header('Content-Description: File Transfer');
                    $this->header('Content-Type: application/octet-stream');
                    $this->header('Content-Disposition: attachment; filename="'.$fileName.'"');
                    $this->header('Content-Transfer-Encoding: binary');
                } else {
                    // Prevent Internet Explorer from MIME-sniffing the content-type:
                    $this->header('X-Content-Type-Options: nosniff');
                    $this->header('Content-Type: '.$this->get_file_type($filePath));
                    $this->header('Content-Disposition: inline; filename="'.$fileName.'"');
                }
                $this->header('Content-Length: '.$this->get_file_size($filePath));
                $this->header('Last-Modified: '.gmdate('D, d M Y H:i:s T', filemtime($filePath)));
                $this->readfile($filePath);
            }
        }
    }

    protected function send_content_type_header()
    {
        $this->header('Vary: Accept');
        if (strpos($this->get_server_var('HTTP_ACCEPT'), 'application/json') !== false) {
            $this->header('Content-type: application/json');
        } else {
            $this->header('Content-type: text/plain');
        }
    }

    protected function send_access_control_headers()
    {
        $this->header('Access-Control-Allow-Origin: '.$this->options['access_control_allow_origin']);
        $this->header('Access-Control-Allow-Credentials: '
            .($this->options['access_control_allow_credentials'] ? 'true' : 'false'));
        $this->header('Access-Control-Allow-Methods: '
            .implode(', ', $this->options['access_control_allow_methods']));
        $this->header('Access-Control-Allow-Headers: '
            .implode(', ', $this->options['access_control_allow_headers']));
    }

    /**
     * Head method
     */
    public function head()
    {
        $this->header('Pragma: no-cache');
        $this->header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->header('Content-Disposition: inline; filename="files.json"');
        // Prevent Internet Explorer from MIME-sniffing the content-type:
        $this->header('X-Content-Type-Options: nosniff');
        if ($this->options['access_control_allow_origin']) {
            $this->send_access_control_headers();
        }
        $this->send_content_type_header();
    }

    /**
     * Get method
     *
     * @param bool $printResponse
     *
     * @return array
     */
    public function get($printResponse = true)
    {
        if ($printResponse && isset($_GET['download'])) {
            
            return $this->download();
        }
        
        $fileName = $this->get_file_name_param();
        
        if ($fileName) {
            $response = array(
                substr($this->options['param_name'], 0, -1) => $this->get_file_object($fileName)
            );
        } else {
            $response = array(
                $this->options['param_name'] => $this->get_file_objects()
            );
        }

        return $this->generate_response($response, $printResponse);
    }

    /**
     * Post method
     *
     * @param bool $printResponse
     *
     * @throws NotFoundHttpException
     * @return mixed
     */
    public function post($printResponse = true)
    {
        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            return $this->delete($printResponse);
        }
        $upload = isset($_FILES[$this->options['param_name']]) ?
            $_FILES[$this->options['param_name']] : null;
        // Parse the Content-Disposition header, if available:
        /*$fileName = $this->get_server_var('HTTP_CONTENT_DISPOSITION') ?
            rawurldecode(preg_replace(
                '/(^[^"]+")|("$)/',
                '',
                $this->get_server_var('HTTP_CONTENT_DISPOSITION')
            )) : null;*/
        $fileName = $this->options['entity_slug'].'-'.md5(uniqid());
        // Parse the Content-Range header, which has the following form:
        // Content-Range: bytes 0-524287/2000000
        $contentRange = $this->get_server_var('HTTP_CONTENT_RANGE') ?
            preg_split('/[^0-9]+/', $this->get_server_var('HTTP_CONTENT_RANGE')) : null;
        $size =  $contentRange ? $contentRange[3] : null;
        $files = array();
        if ($upload && is_array($upload['tmp_name'])) {

            /** @var Product $entity */
            $entity = $this->entityManager->getRepository($this->options['entity_path'])->find($this->options['entity_id']);

            if (!$entity) {
                throw new NotFoundHttpException('Unable to find entity.');
            }


            // param_name is an array identifier like "files[]",
            // $_FILES is a multi-dimensional array:
            foreach ($upload['tmp_name'] as $index => $value) {
                $files[] = $this->handle_file_upload(
                    $upload['tmp_name'][$index],
                    $fileName ? $fileName : $upload['name'][$index],
                    $size ? $size : $upload['size'][$index],
                    $upload['type'][$index],
                    $upload['error'][$index],
                    $index,
                    $contentRange
                );

                // save the file name in the database
                /** @var Image $image */
                if($this->options['entity_path'] == 'BlogBundle:Post') {
                    $image = new PostImage();
                }elseif($this->options['entity_path'] == 'EcommerceBundle:Product'){
                    $image = new ProductImage();
                }
                $image->setPath($files[$index]->name);
                $this->entityManager->persist($image);

                $entity->addImage($image);

                //add crop center thumbmail image
                // '260'=>array('w'=>260,'h'=>123),
                // '160'=>array('w'=>160,'h'=>100),      
                // '104'=>array('w'=>104,'h'=>56),
                // '142'=>array('w'=>142,'h'=>88)
                //create source image
                if(isset($upload['type'][0]) && isset($upload['name'][0])){
                    $extension = $upload['type'][0];
                    if($extension=='image/jpeg') $source = imagecreatefromjpeg($this->options['upload_dir'].$files[$index]->name);
                    else if ($extension=='image/gif') $source = imagecreatefromgif($this->options['upload_dir'].$files[$index]->name);
                    else if ($extension=='image/png') $source = imagecreatefrompng($this->options['upload_dir'].$files[$index]->name);
                    $this->resizeImage($source, $files[$index]->name.'_260', 260, 123);
                }
                
                
            }

            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        } else {
            // param_name is a single object identifier like "file",
            // $_FILES is a one-dimensional array:
            $files[] = $this->handle_file_upload(
                isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                $fileName ? $fileName : (isset($upload['name']) ?
                    $upload['name'] : null),
                $size ? $size : (isset($upload['size']) ?
                    $upload['size'] : $this->get_server_var('CONTENT_LENGTH')),
                isset($upload['type']) ?
                $upload['type'] : $this->get_server_var('CONTENT_TYPE'),
                isset($upload['error']) ? $upload['error'] : null,
                null,
                $contentRange
            );
        }

        return $this->generate_response(
            array($this->options['param_name'] => $files),
            $printResponse
        );
    }

    /**
     * Delete method
     *
     * @param bool $printResponse
     *
     * @throws NotFoundHttpException
     * @return mixed
     */
    public function delete($printResponse = true)
    {
        $fileName = $this->get_file_name_param();
        $filePath = $this->get_upload_path($fileName);
       
        
        /** @var Image $entity */
        $image = $this->entityManager->getRepository($this->options['entity_image_path'])->findOneBy(array('path' => $fileName));
        /** @var Product $entity */
        $entity = $this->entityManager->getRepository($this->options['entity_path'])->find($this->options['entity_id']);

        if (!$image) {
            throw new NotFoundHttpException('Unable to find Image entity.');
        }
        if (!$entity) {
            throw new NotFoundHttpException('Unable to find Product entity.');
        }

        //this line reutn true when all is false ...&& 
        $success = is_file($filePath) && $fileName[0] !== '.' && unlink($filePath);
        
        if ($success) {
            foreach ($this->options['image_versions'] as $version => $options) {
               
                if (!empty($version)) {
                    $file = $this->get_upload_path($fileName, $version);
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                // remove the file name from the database
                $entity->removeImage($image);
                $this->entityManager->remove($image);
            }

            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        }

        return $this->generate_response(array('success' => $success), $printResponse);
    }
    
           
    public function resizeImage($source, $name, $newImageWidth, $newImageHeight) {
        //imagen vertical u horizontal
        $width  = imagesx($source);
        $height = imagesy($source);    
        
        if($newImageWidth==null){
          $ratio = $newImageHeight / $height;
          $newImageWidth = round($width * $ratio);
        }

        if($newImageHeight==null){
          $ratio = $newImageWidth / $width;
          $newImageHeight = round($height * $ratio);
        }

        $source_ratio=$width/$height;
        $new_ratio=$newImageWidth/$newImageHeight;

        //imagen horizontal ajustar al alto   
        if($new_ratio<$source_ratio){
          $ratio = $newImageHeight / $height;
          $width_aux = round($width * $ratio);
          $height_aux = $newImageHeight;
        }else{//imagen vertical ajustar al ancho
          $ratio = $newImageWidth / $width;
          $height_aux = round($height * $ratio);
          $width_aux = $newImageWidth;
        }  
        
        $newImage = imagecreatetruecolor($width_aux,$height_aux);       
        imagecopyresampled($newImage,$source,0,0,0,0,$width_aux,$height_aux,$width,$height);
        //imagedestroy($source);

        //recortar al centro
        if($width_aux==$newImageWidth && $height_aux==$newImageHeight){
          $newImage2=$newImage;
        }else{
          
          $centreX = ceil($width_aux / 2);
          $centreY = ceil($height_aux / 2);

          $cropWidth  = $newImageWidth;
          $cropHeight = $newImageHeight;
          $cropWidthHalf  = ceil($cropWidth / 2); // could hard-code this but I'm keeping it flexible
          $cropHeightHalf = ceil($cropHeight / 2);

          $x1 = max(0, $centreX - $cropWidthHalf);
          $y1 = max(0, $centreY - $cropHeightHalf);

          $x2 = min($width, $centreX + $cropWidthHalf);
          $y2 = min($height, $centreY + $cropHeightHalf);

          $newImage2 = imagecreatetruecolor($cropWidth,$cropHeight);
          //echo 'recorta '.$cropWidth.' '.$cropHeight.' '.$x1.' '.$y1.' '.$x2.' '.$y2.' '.$newImageWidth.' '.$newImageHeight;
          imagecopy($newImage2, $newImage, 0, 0, $x1, $y1, $newImageWidth, $newImageHeight); 

        }
        //save image
        imagejpeg($newImage2, $this->options['upload_dir'].'/thumbnail/'.$name.'.jpg',90);
        
        return $newImage2;
       
    }
    
}
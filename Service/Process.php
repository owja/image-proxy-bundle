<?php
namespace Owja\ImageProxyBundle\Service;

use Owja\ImageProxyBundle\Exception\ConfigurationException;
use Owja\ImageProxyBundle\Exception\ProcessingException;

class Process
{
    const RESIZE         = 'resize';
    const CROP           = 'crop';

    const COMPRESSION    = true;
    const NO_COMPRESSION = false;

    /**
     * @var string
     */
    protected $temp;

    /**
     * @var string
     */
    protected $type = self::RESIZE;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var int
     */
    protected $width;

    /**
     * @var bool
     */
    protected $compression;

    /**
     * Constructor
     *
     * @param string $temp          local writable temp directory for image processing
     * @param bool $compression     compress image after resize?
     * @throws ConfigurationException
     */
    public function __construct(string $temp, bool $compression = self::NO_COMPRESSION)
    {
        if (!is_dir($temp)) {
            throw new ConfigurationException('Can not access temp directory.');
        }

        $this->temp         = $temp;
        $this->compression  = $compression;
    }

    /**
     * Set destination Height
     *
     * @param int $height
     * @return Process
     */
    public function setHeight(int $height) : Process
    {
        $this->height = $height ?: 0;
        return $this;
    }

    /**
     * Set destination Width
     *
     * @param int $width
     * @return Process
     */
    public function setWidth(int $width) : Process
    {
        $this->width = $width ?: 0;
        return $this;
    }

    /**
     * Set Type of Resizing
     *
     * - Process::Resize     Resize and Crop
     * - Process::Crop       Crop only
     *
     * @param string $type
     * @return Process
     */
    public function setType(string $type) : Process
    {
        switch ($type) {
            case self::CROP:
                $this->type = self::CROP;
                break;
            default:
                $this->type = self::RESIZE;
                break;
        }

        return $this;
    }

    /**
     * Processing Image Blob
     *
     * @param string $content           original image blob
     * @return string                   precessed image blob
     * @throws ConfigurationException
     * @throws ProcessingException
     */
    public function processContent(string $content) : string
    {
        try {
            $file = $this->temp . DIRECTORY_SEPARATOR . md5(uniqid('owja_ip_', true));
            file_put_contents($file, $content);
        } catch (\Exception $e) {
            throw new ConfigurationException('Can\'t access temp file.');
        }

        try {
            ( $this->height || $this->width )   &&  $this->resize($file);
            ( $this->compression )              &&  $this->compress($file);
        } catch (ProcessingException $e) {
            unlink($file);
            throw $e;
        } catch (\Exception $e) {
            unlink($file);
            throw new ProcessingException('Error while processing the image', 500, $e);
        }

        $image = file_get_contents($file);
        unlink($file);

        return $image;
    }

    /**
     * Processing Image File
     *
     * @param string $file              original image file
     * @throws ProcessingException
     */
    public function processFile(string $file)
    {
        $content = file_get_contents($file);

        if (empty($content)) {
            throw new ProcessingException('File is empty.', 404);
        }

        if (file_put_contents($file, $this->processContent($content))) {
            return;
        }

        throw new ProcessingException('Could not save to file.');
    }

    /**
     * Resize/Crop Image
     *
     * @param string $file
     * @throws ProcessingException
     */
    protected function resize(string $file)
    {
        // ToDo Before Resizing Event

        $im = new \Imagick();

        // Load
        if (!$im->readImage($file))
        {
            $im->clear();
            $im->destroy();
            throw new ProcessingException('Could not load file for resizing.');
        }

        // Resize
        if ($this->type === self::RESIZE)
        {
            $originalWidth = $im->getImageWidth();
            $originalHeight = $im->getImageHeight();

            if ($originalWidth / $originalHeight < $this->width / $this->height) {
                $resizeWidth = $this->width;
                $resizeHeight = 0;
            } else {
                $resizeWidth = 0;
                $resizeHeight = $this->height;
            }

            if (!$im->resizeImage($resizeWidth, $resizeHeight, \Imagick::FILTER_LANCZOS, 1, false))
            {
                $im->clear();
                $im->destroy();
                throw new ProcessingException('Image resizing failed.');
            }
        }

        // Crop
        if (!$im->cropImage(
                $this->width,
                $this->height,
                ($im->getImageWidth() - $this->width) / 2,
                ($im->getImageHeight() - $this->height) / 2
            ))
        {
            $im->clear();
            $im->destroy();
            throw new ProcessingException('Image cropping failed.');
        }

        $im->writeImage();
        $im->clear();
        $im->destroy();

        // ToDo After Resizing Event
    }

    protected function compress(string $file)
    {
        // ToDo Before Compressing Event

        // ToDo Compress Image

        // ToDo After Compressing Event
    }
}
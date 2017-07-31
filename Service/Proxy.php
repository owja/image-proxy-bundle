<?php
namespace Owja\ImageProxyBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;

use Owja\ImageProxyBundle\Exception\ConfigurationException;
use Owja\ImageProxyBundle\Exception\NotFoundException;
use Owja\ImageProxyBundle\Exception\ProcessingException;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Proxy
{
    /**
     * @var int
     */
    private $timeout;

    /**
     * @var string
     */
    private $namespace = 'default';

    /**
     * @var string
     */
    private $token;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var Process
     */
    private $processor;

    /**
     * @var string
     */
    protected $processType = Process::RESIZE;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var int
     */
    protected $width;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $original;

    /**
     * @var string
     */
    protected $processed;

    /**
     * @var string
     */
    protected $originalFile;

    /**
     * @var string
     */
    protected $processedFile;

    /**
     * @var array
     */
    protected $allowedMimeTypes = [
        'image/png',
        'image/jpeg',
        'image/gif',
    ];

    /**
     * Constructor
     *
     * @param FilesystemInterface   $filesystem
     * @param Process               $processor
     * @param int                   $timeout
     */
    public function __construct(FilesystemInterface $filesystem, Process $processor, int $timeout = 10)
    {
        $this->filesystem   = $filesystem;
        $this->processor    = $processor;
        $this->timeout      = $timeout ?: 10;
    }

    /**
     * Set namespace
     *
     * @param   string  $namespace
     * @return  Proxy
     * @throws  ConfigurationException
     */
    public function setNamespace(string $namespace) : Proxy
    {
        if (empty($namespace)) {
            throw new ConfigurationException('Namespace not defined.');
        }

        $this->namespace = $namespace;

        $this->reset(true);
        $this->reset(false);

        return $this;
    }

    /**
     * Set token header
     *
     * @param   string|null $token
     * @return  Proxy
     */
    public function setToken(string $token = null) : Proxy
    {
        $this->token = empty($token) ? null : $token;
        return $this;
    }

    /**
     * Set remote url
     *
     * @param   string $url
     * @return  Proxy
     */
    public function setUrl(string $url) : Proxy
    {
        $this->url = $url;

        $this->reset(true);
        $this->reset(false);

        return $this;
    }

    /**
     * Set image width
     *
     * @param   int|null $width
     * @return  Proxy
     */
    public function setWidth(int $width = null) : Proxy
    {
        $this->width = $width ?: 0;
        $this->processor->setWidth($this->width);

        $this->reset(false);

        return $this;
    }

    /**
     * Set image height
     *
     * @param   int|null $height
     * @return  Proxy
     */
    public function setHeight(int $height = null) : Proxy
    {
        $this->height = $height ?: 0;
        $this->processor->setHeight($this->height);

        $this->reset(false);

        return $this;
    }

    /**
     * Set image process type
     *
     * @param   string|null $type
     * @return  Proxy
     */
    public function setProcessType(string $type = null) : Proxy
    {
        switch ($type) {
            case Process::CROP:
                $this->processType = Process::CROP;
                break;
            default:
                $this->processType = Process::RESIZE;
                break;
        }

        $this->processor->setType($this->processType);

        $this->reset(false);

        return $this;
    }

    /**
     * Get mime type of image
     *
     * @return string
     */
    public function getMimeType() : string
    {
        $this->loadImage();
        return $this->filesystem->getMimetype($this->getPath(false));
    }

    /**
     * Get modified of image
     *
     * @return string
     */
    public function getTimestamp() : string
    {
        $this->loadImage();
        return $this->filesystem->getTimestamp($this->getPath(false));
    }

    /**
     * Get cache tag
     *
     * @return string
     */
    public function getCacheTag() : string
    {
        $this->loadImage();
        return md5($this->processed);
    }

    /**
     * Get image blob
     *
     * @return string
     */
    public function getContent() : string
    {
        $this->loadImage();
        return $this->processed;
    }

    /**
     * Create Response
     *
     * @param SymfonyRequest $request
     * @return SymfonyResponse
     */
    public function createResponse(SymfonyRequest $request = null)
    {
        $response = new SymfonyResponse($this->getContent(), 200, [
            'Content-Type' => $this->getMimeType()
        ]);

        $response->setPublic();
        $response->setExpires(new \DateTime('now +7 days'));
        $response->setEtag($this->getCacheTag());

        if ($request !== null) {
            $response->isNotModified($request);
        }

        return $response;
    }

    /**
     * Clean Image Cache
     */
    public function cleanImageCache()
    {
        foreach ($this->filesystem->listContents() as $item)
        {
            if ($item['type'] === 'dir') {
                $this->filesystem->deleteDir($item['path']);
            }
        }
    }

    /**
     * Load image
     */
    protected function loadImage()
    {
        $this->loadProcessed()
        || ( $this->loadOriginal() && $this->process() );
    }

    /**
     * Load processed image from filesystem
     *
     * @return bool
     */
    protected function loadProcessed() : bool
    {
        if ($this->processed) {
            return true;
        }

        try {
            $this->processed = $this->filesystem->read($this->getPath(false));
            return true;
        } catch (FileNotFoundException $e) {
            $this->processed = null;
            return false;
        }
    }

    /**
     * Load original image
     * @return bool
     * @throws NotFoundException
     * @throws ProcessingException
     */
    protected function loadOriginal() : bool
    {
        $path = $this->getPath(true);

        try {
            $this->original = $this->filesystem->read($path);
            return true;
        } catch (FileNotFoundException $e) {
            $this->original = null;
        }

        $headers =  [
            'Accept' => implode(', ', $this->allowedMimeTypes),
            'User-Agent' => "OWJA!bot ImageProxy"
        ];

        if ($this->token) {
            $headers['owja-image-proxy'] = $this->token;
        }

        try {
            $client = new Client([ 'headers' => $headers ]);
            $request = new Request('GET', $this->url);

            $response = $client->send($request, [
                'timeout' => $this->timeout
            ] );

            $content = (string) $response->getBody();

        } catch (\Exception $e) {
            throw new NotFoundException('Could not load image from URL.', 404, $e);
        }

        if (empty($response->getHeader('Content-Type'))
            || !in_array($response->getHeader('Content-Type')[0], $this->allowedMimeTypes)) {
            throw new ProcessingException('File ist not a Image of type png, jpeg or gif.', 500, $e);
        }

        $this->original = $content;
        $this->filesystem->write($path, $content);

        return true;
    }

    /**
     * Process image
     *
     * @return bool
     */
    protected function process() : bool
    {
        $this->processed = $this->processor->processContent($this->original);
        $this->filesystem->write($this->getPath(false), $this->processed);

        return true;
    }

    /**
     * Get image path
     *
     * @param bool $original
     * @return string
     */
    protected function getPath(bool $original = false) : string
    {
        if ($original && $this->originalFile) {
            return $this->originalFile;
        } else if (!$original && $this->processedFile) {
            return $this->processedFile;
        }

        $path  = $this->namespace . DIRECTORY_SEPARATOR;

        if ($original) {
            $path .= 'original' . DIRECTORY_SEPARATOR;
            $path .= md5($this->url);
            $this->originalFile = $path;
        } else {
            $path .= $this->processType . DIRECTORY_SEPARATOR;
            $path .= $this->width . 'x' . $this->height . DIRECTORY_SEPARATOR;
            $path .= md5($this->url);
            $this->processedFile = $path;
        }

        return $path;
    }

    /**
     * Reset
     *
     * @param bool $original
     */
    protected function reset(bool $original)
    {
        if ($original) {
            $this->originalFile  = null;
            $this->original      = null;
        } else {
            $this->processedFile = null;
            $this->processed     = null;
        }
    }
}
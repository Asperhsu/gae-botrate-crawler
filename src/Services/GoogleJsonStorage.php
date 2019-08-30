<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Google\Cloud\Storage\StorageClient;

class GoogleJsonStorage
{
    protected $client;
    protected $storagePath;
    protected $publicPath;
    protected $isPublic;
    protected $data = [];

    /**
     * create new instance
     *
     * @param string $objectName     filename
     * @param boolean $isPublic      can be public access
     */
    public function __construct(string $objectName, bool $isPublic = false)
    {
        $bucketName = sprintf('%s.appspot.com', getenv('GOOGLE_CLOUD_PROJECT'));
        $this->storagePath = sprintf('gs://%s/%s', $bucketName, $objectName);
        $this->publicUrl = sprintf('https://storage.googleapis.com/%s/%s', $bucketName, $objectName);

        $this->isPublic = $isPublic;
        $this->registerStreamWrapper();
    }

    /**
     * register stream wrapper
     *
     * @return void
     */
    protected function registerStreamWrapper() : void
    {
        $projectId = getenv('GOOGLE_CLOUD_PROJECT');

        $this->client = new StorageClient(['projectId' => $projectId]);
        $this->client->registerStreamWrapper();
    }

    /**
     * load object json content
     *
     * @return array
     */
    protected function load() : array
    {
        if (!file_exists($this->storagePath)) {
            return [];
        }

        $json = file_get_contents($this->storagePath);
        return json_decode($json, true);
    }

    /**
     * save object json content
     *
     * @return self
     */
    protected function save() : self
    {
        $json = json_encode($this->data);

        if (!$this->isPublic) {
            file_put_contents($this->storagePath, $json);
            return $this;
        }

        $options = [
            'gs' => ['predefinedAcl' => 'publicRead']
        ];
        $context = stream_context_create($options);
        file_put_contents($this->storagePath, $json, 0, $context);

        return $this;
    }

    /**
     * retrive public url when is public access
     *
     * @return string|null
     */
    public function publicUrl() : ?string
    {
        return $this->isPublic ? $this->publicUrl : null;
    }

    /**
     * retrive storage path
     *
     * @return string
     */
    public function storagePath() : string
    {
        return $this->storagePath;
    }

    /**
     * get all data
     *
     * @return array
     */
    public function all() : array
    {
        if (!$this->data) {
            $this->data = $this->load();
        }

        return $this->data;
    }

    /**
     * get item
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return Arr::get($this->all(), $key, $default);
    }

    /**
     * set item
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function set(string $key, $value) : self
    {
        Arr::set($this->data, $key, $value);
        $this->save();

        return $this;
    }

    /**
     * store data
     *
     * @param array $data
     * @return self
     */
    public function store(array $data) : self
    {
        $this->data = $data;
        $this->save();

        return $this;
    }
}

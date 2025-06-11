<?php

namespace App\Entity;

use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\VideosRepository;
use App\State\VideosProcessor;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\Model\Response;
use ArrayObject;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

##[Post(processor: VideosProcessor::class)]
#[Get()]
#[GetCollection()]
#[ORM\Entity(repositoryClass: VideosRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            inputFormats: ['multipart' => ['multipart/form-data']],
            processor: VideosProcessor::class,
            deserialize: false,
            messenger: 'output',
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary'
                                    ]
                                ],
                                'required' => ['file']
                            ]
                        ]
                    ])
                ),
            )
        )
    ]
)]
class Videos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_video = null;

    #[ORM\Column]
    private ?bool $processado = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    private ?string $storage_key = null;


    #[ORM\Column(nullable: true)]
    private ?int $duration = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resolution = null;


    #[ORM\Column]
    private ?\DateTime $create_time = null;

    #[ORM\Column]
    private ?\DateTime $update_time = null;



    public function getIdVideo(): ?int
    {
        return $this->id_video;
    }

    public function setIdVideo(int $id_video): static
    {
        $this->id_video = $id_video;

        return $this;
    }

    public function isProcessado(): ?bool
    {
        return $this->processado;
    }

    public function setProcessado(bool $processado): static
    {
        $this->processado = $processado;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getCreateTime(): ?\DateTime
    {
        return $this->create_time;
    }

    public function setCreateTime(\DateTime $create_time): static
    {
        $this->create_time = $create_time;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getStorageKey(): ?string
    {
        return $this->storage_key;
    }

    public function setStorageKey(string $storage_key): static
    {
        $this->storage_key = $storage_key;

        return $this;
    }

    public function getResolution(): ?string
    {
        return $this->resolution;
    }

    public function setResolution(?string $resolution): static
    {
        $this->resolution = $resolution;

        return $this;
    }

    public function getUpdateTime(): ?\DateTime
    {
        return $this->update_time;
    }

    public function setUpdateTime(\DateTime $update_time): static
    {
        $this->update_time = $update_time;

        return $this;
    }



}

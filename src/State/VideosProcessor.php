<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Videos;
use Aws\S3\S3Client;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use FFMpeg\FFMpeg;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use SplFileInfo;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception as HttpException;

/**
 * @implements ProcessorInterface<Videos, Videos>
 */
class VideosProcessor implements ProcessorInterface
{
    public const RESPONSES = [];
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RequestStack $requestStack,

        #[Autowire(service: 's3Client')]
        private S3Client $s3,

        #[Autowire(service: 'mq_producer')]
        private Producer $mq
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $request = $this->requestStack->getCurrentRequest();
        /** @var UploadedFile */
        $file = $request->files->get('file');

        if (!$file) {
            throw new HttpException\BadRequestHttpException('Arquivo é obrigatório.');
        }

        $allowedMimeTypes = ['video/mp4', 'video/avi', 'video/mkv'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes, true)) {
            throw new HttpException\UnsupportedMediaTypeHttpException('Tipo de arquivo inválido.');
        }

        $maxSize = 100 * 1024 * 1024;
        if ($file->getSize() > $maxSize) {
            throw new HttpException\HttpException(413, 'Arquivo muito grande. Máximo permitido: 100MB.');
        }

        $storageKey = $this->parseFile($file);

        $video = (new Videos())
            ->setFilename($file->getClientOriginalName())
            ->setCreateTime(new DateTime())
            ->setUpdateTime(new DateTime())
            ->setStorageKey($storageKey)
            ->setProcessado(false);

        $this->em->persist($video);
        $this->em->flush();

        $this->mq->publish(json_encode([
            'id_video' => $video->getIdVideo()
        ]));

        return $video;
    }

    protected function parseFile(UploadedFile $file): string
    {

        $originalName = new SplFileInfo($file->getClientOriginalName());
        $ext = $originalName->getExtension();
        $key = sprintf(
            '%s/%s-%s.%s',
            uuid_create(),
            str_replace('.' . $ext, '', $originalName),
            time(),
            $ext
        );

        $this->s3->putObject([
            'Bucket' => 'videos',
            'Key'    =>  $key,
            'ACL'    => 'public-read',
            'Body'   => fopen($file->getPathname(), 'r')
        ]);

        return $key;
    }
}

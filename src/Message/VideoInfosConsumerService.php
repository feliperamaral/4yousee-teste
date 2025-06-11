<?php

namespace App\Message;

use App\Entity\Videos;
use App\Kernel;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use FFMpeg\FFMpeg;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class VideoInfosConsumerService implements ConsumerInterface
{
    public function __construct(
        private readonly Kernel $kernel,
        private readonly EntityManagerInterface $em,
        #[Autowire(service: 's3Client')]
        private S3Client $s3
    ) {}

    public function execute(AMQPMessage $msg)
    {
        $body = $msg->getBody();
        if (!json_validate($body)) {
            echo "json \$body inválido: " . $body . "\n";
            return self::MSG_REJECT;
        }
        $videoInfo = new VideoInfosMessage($body);
        if (!$videoInfo->isValid()) {
            echo "\$videoInfo \inválido: " . $body . "\n";
            return self::MSG_REJECT;
        }
        $idVideo = $videoInfo->getIdVideo();
        /** @var Videos|null */
        $video = $this->em->find(Videos::class, $idVideo);
        if (!$video) {
            echo "Video não encontrado: " . $idVideo . "\n";
            return self::MSG_REJECT;
        }

        if ($video->isProcessado()) {
            echo "Video já processado: " . $idVideo . "\n";
            return self::MSG_ACK;
        }

        $videoFilenameTmp = $this->getVideoFromS3($video->getStorageKey());

        $infos = $this->getVideoInfos($videoFilenameTmp);
        $video
            ->setProcessado(true)
            ->setDuration($infos['duration'])
            ->setResolution($infos['resolution']);

        $this->em->persist($video);
        $this->em->flush();

        echo "Vídeo processado: " . $video->getStorageKey() . "\n";

        return self::MSG_ACK;
    }

    private function getVideoFromS3(string $key): string
    {
        $count = 0;
        $videoFilenameTmpArr = [
            sys_get_temp_dir(),
            'video_tmp',
            $count,
            $key
        ];

        while (file_exists(implode(DIRECTORY_SEPARATOR, $videoFilenameTmpArr))) {
            $videoFilenameTmpArr[2] = $count++;
        }

        $videoFilenameTmp = implode(DIRECTORY_SEPARATOR, $videoFilenameTmpArr);

        $dirname = dirname($videoFilenameTmp);
        if (!file_exists($dirname)) {
            mkdir($dirname, 0777, true);
        }
        $this->s3->getObject([
            'Bucket' => 'videos',
            'Key' => $key,
            'SaveAs' => $videoFilenameTmp
        ]);
        return $videoFilenameTmp;
    }

    /**
     * @param string $filename
     * @return array{resolution:string,duration:int}
     */
    private function getVideoInfos(string $filename): array
    {
        $ffmpeg = FFMpeg::create();
        $video = $ffmpeg->open($filename);
        $streamF = $video->getStreams()->videos()->first();
        $dimensions = $streamF->getDimensions();

        return [
            'resolution' => $dimensions->getWidth() . 'x' . $dimensions->getHeight(),
            'duration' => (int)$streamF->get('duration')
        ];
    }
}

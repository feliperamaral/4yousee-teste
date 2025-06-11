<?php

namespace App\Message;

use Exception;
use JSON_THROW_ON_ERROR;

class VideoInfosMessage
{

    protected ?array $msgParsed = null;
    public function __construct(protected string $msg)
    {
        try {
            $this->msgParsed = json_decode($msg, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
        }
    }

    public function getIdVideo(): int|false
    {
        if (!$this->isValid()) {
            return false;
        }
        return $this->msgParsed['id_video'];
    }
    public function isValid(): bool
    {
        if (!$this->msgParsed) {
            return false;
        }
        return
            isset($this->msgParsed['id_video'])
            &&
            is_int($this->msgParsed['id_video']);
    }
}

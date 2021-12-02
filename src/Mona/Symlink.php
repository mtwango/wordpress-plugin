<?php

namespace Druidfi\Mona;

class Symlink
{
    /**
     * @var string
     */
    protected $originalTarget = '';

    /**
     * @var string
     */
    protected $originalLink = '';

    /**
     * @var string
     */
    protected $target = '';

    /**
     * @var string
     */
    protected $link = '';

    /**
     * @var bool
     */
    protected $absolutePath = false;

    /**
     * @var bool
     */
    protected $forceCreate = false;

    /**
     * @return string
     */
    public function getOriginalTarget(): string
    {
        return $this->originalTarget;
    }

    /**
     * @param string $target
     *
     * @return Symlink
     */
    public function setOriginalTarget(string $target): self
    {
        $this->originalTarget = $target;

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalLink(): string
    {
        return $this->originalLink;
    }

    /**
     * @param string $link
     *
     * @return Symlink
     */
    public function setOriginalLink(string $link): self
    {
        $this->originalLink = $link;

        return $this;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @param string $target
     *
     * @return Symlink
     */
    public function setTarget(string $target): self
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     *
     * @return Symlink
     */
    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAbsolutePath(): bool
    {
        return $this->absolutePath;
    }

    /**
     * @param bool $absolutePath
     *
     * @return Symlink
     */
    public function setAbsolutePath(bool $absolutePath): self
    {
        $this->absolutePath = $absolutePath;

        return $this;
    }

    /**
     * @return bool
     */
    public function isForceCreate(): bool
    {
        return $this->forceCreate;
    }

    /**
     * @param bool $forceCreate
     *
     * @return Symlink
     */
    public function setForceCreate(bool $forceCreate): self
    {
        $this->forceCreate = $forceCreate;

        return $this;
    }
}

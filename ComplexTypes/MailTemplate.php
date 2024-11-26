<?php

namespace Aldaflux\AldafluxIdsSanteBundle\ComplexTypes;


use Symfony\Component\Serializer\Annotation\Groups;

class MailTemplate
{
    #[Groups(['getUser'])]
    public string $Alias;

    #[Groups(['getUser'])]
    public string $Subject;

    #[Groups(['getUser'])]
    public string $Body;

    /** @codeCoverageIgnore */
    public function getAlias(): string
    {
        return $this->Alias;
    }

    /** @codeCoverageIgnore */
    public function setAlias(string $Alias): self
    {
        $this->Alias = $Alias;

        return $this;
    }

    /** @codeCoverageIgnore */
    public function getSubject(): string
    {
        return $this->Subject;
    }

    /** @codeCoverageIgnore */
    public function setSubject(string $Subject): self
    {
        $this->Subject = $Subject;

        return $this;
    }

    /** @codeCoverageIgnore */
    public function getBody(): string
    {
        return $this->Body;
    }

    /** @codeCoverageIgnore */
    public function setBody(string $Body): self
    {
        $this->Body = $Body;

        return $this;
    }
}

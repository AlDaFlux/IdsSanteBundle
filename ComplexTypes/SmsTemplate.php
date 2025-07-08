<?php

namespace Aldaflux\AldafluxIdsSanteBundle\ComplexTypes;


class SmsTemplate
{
    public string $From;
    public string $Body;

 
    public function getFrom(): string
    {
        return $this->From;
    }

 
    public function setFrom(string $From): self
    {
        $this->From = $From;
        return $this;
    }

 
    public function getBody(): string
    {
        return $this->Body;
    }

 
    public function setBody(string $Body): self
    {
       $this->Body = $Body;
       return $this;
    }
}

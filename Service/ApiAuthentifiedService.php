<?php

namespace Aldaflux\AldafluxIdsSanteBundle\Service;

use stdClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class ApiAuthentifiedService
{
    private \stdClass $ApiAuthenticationToken;

    private string $apiRootUrl='';
    private string $applicationName='';
    private string $applicationKey='';
    private string $readWriteToken='';

    public function __construct(private HttpClientInterface $httpClientInterface, ParameterBagInterface $parameter) 
    {
        $this->apiRootUrl=$parameter->get("aldaflux_ids_sante.api_root_url");
        $this->applicationName=$parameter->get("aldaflux_ids_sante.application_name");
        $this->readWriteToken=$parameter->get("aldaflux_ids_sante.token");
        $this->applicationKey=$parameter->get("aldaflux_ids_sante.application_key");
        
        
        $this->ApiAuthenticationToken = new \stdClass();
        $this->ApiAuthenticationToken->Application = $this->applicationName;
        $this->ApiAuthenticationToken->AccessToken = $this->readWriteToken;
        
    }

    public function thirdPartLogin(
        string $user,
        string $userInfo = '{}',
        string $ipAddress = '1.2.3.4'
    ): string {
        $body = new \stdClass();
        $body->UserInfo = $userInfo;
        $body->Authentifier = $user;
        $body->IpAddress = $ipAddress;
        $response = json_decode($this->send41Request('third-part-login/login', $body)->getContent(false));

        return $response->SessionId;
    }

    private function getEncodedToken(?string $session = null)
    {
        return base64_encode(
            $session == null
                ? '{"application":"' . $this->applicationName . '","token":"' . $this->applicationKey . '"}'
                : '{"session":"' . $session . '","token":"' . $this->readWriteToken . '"}'
        );
    }

    public function send41Request(
        string $url,
        ?stdClass $body = null,
        ?string $session = null
    ) {

       
        return $this->httpClientInterface->request(
            $body === null ? 'GET' : 'POST',
            $this->apiRootUrl . $url,
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Accept' => 'application/json',
                    'X-Api-Auth-Token' => $this->getEncodedToken($session),
                ],
                'body' => $body === null ? $body : json_encode($body),
            ]
        );
    }

    public function post41File(string $url, string $session, UploadedFile $file): ResponseInterface
    {
        $formFields = [
            'file' => DataPart::fromPath($file->getPathname(), $file->getClientOriginalName(), $file->getMimeType()),
        ];
        $formData = new FormDataPart($formFields);
        $headers = $formData->getPreparedHeaders()->toArray();
        $headers[] = 'X-Api-Auth-Token: ' . base64_encode('{"session":"' . $session . '","token":"' . $this->readWriteToken . '"}');

        return $this->httpClientInterface->request(
            'POST',
            $this->apiRootUrl.$url,
            [
                'headers' => $headers,
                'body' => $formData->bodyToIterable(),
            ]
        );
    }
}

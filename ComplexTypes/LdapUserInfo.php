<?php

namespace Aldaflux\AldafluxIdsSanteBundle\ComplexTypes;

class LdapUserInfo
{
    public ?string $Authentifier;
    public ?string $OrganizationalUnit;
    public ?string $Label;
    public ?string $CounterCall;
    public ?string $Email;
    public ?string $PresentedAuthentifier;
    public $IsOTP;
    public $RightsIDS;
    public string $Hash;
    public string $Other;

    public function __construct()
    {
    }

    public function setAuthentifier($authentifier): void
    {
        $this->Authentifier = $authentifier;
    }

    public function ToString()
    {
        $ret = '';
        $array = get_object_vars($this);
        foreach ($array as $key => $value) {
            $ret = $ret.$key.'='.$value.';';
        }
        if ('' == $ret) {
            return null;
        } else {
            return substr($ret, 0, strlen($ret) - 1);
        }
    }

    public static function createFromString(string $string): LdapUserInfo
    {
        $userInfo = new LdapUserInfo();

        $elts = preg_split('/;/', $string);
        foreach ($elts as $value) {
            $prop = preg_split('/=/', $value);
            if (property_exists($userInfo, $prop[0])) {
                $var = $prop[0];
                $userInfo->$var = $prop[1];
            }
        }

        return $userInfo;
    }
}

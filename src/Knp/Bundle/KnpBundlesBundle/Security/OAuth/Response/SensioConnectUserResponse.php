<?php

namespace Knp\Bundle\KnpBundlesBundle\Security\OAuth\Response;

use HWI\Bundle\OAuthBundle\OAuth\Response\SensioConnectUserResponse as BaseResponse;

class SensioConnectUserResponse extends BaseResponse
{
    private $accounts = array();

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        $this->getOnlineAccounts();

        return isset($this->accounts['sensio']) ? $this->accounts['sensio'] : $this->getNodeValue('./foaf:name', $this->response);
    }

    public function getLinkedAccount($name)
    {
        return isset($this->accounts[$name]) ? $this->accounts[$name] : null;
    }

    protected function getOnlineAccounts()
    {
        if (0 < count($this->accounts)) {
            return $this->accounts;
        }

        $accounts = $this->xpath->query('./foaf:account/foaf:OnlineAccount', $this->response);
        for ($i = 0; $i < $accounts->length; $i++) {
            $account = $accounts->item($i);
            switch ($this->getNodeValue('./foaf:name', $account)) {
                case 'SensioLabs Connect':
                    $this->accounts['sensio'] = $this->getNodeValue('foaf:accountName', $account);
                    break;

                case 'github':
                    $this->accounts['github'] = $this->getNodeValue('foaf:accountName', $account);
                    break;

                case 'facebook':
                    $this->accounts['facebook'] = $this->getNodeValue('foaf:accountName', $account);
                    break;

                case 'twitter':
                    $this->accounts['twitter'] = $this->getNodeValue('foaf:accountName', $account);
                    break;

                case 'linkedin':
                    $this->accounts['linkedin'] = $this->getNodeValue('foaf:accountName', $account);
                    break;
            }
        }

        return $this->accounts;
    }
}

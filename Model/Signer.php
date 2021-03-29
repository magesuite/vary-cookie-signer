<?php
declare(strict_types=1);

namespace MageSuite\VaryCookieSigner\Model;

class Signer
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     */
    public function __construct(\Magento\Framework\App\DeploymentConfig $deploymentConfig)
    {
        $this->deploymentConfig = $deploymentConfig;
    }

    public function sign(string $key): string
    {
        return sha1($key . $this->getCryptKey());
    }

    protected function getCryptKey(): string
    {
        return (string)$this->deploymentConfig->get('vary_cookie_sign/key', '');
    }
}

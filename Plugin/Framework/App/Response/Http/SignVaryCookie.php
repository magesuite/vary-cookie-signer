<?php

declare(strict_types=1);

namespace MageSuite\VaryCookieSigner\Plugin\Framework\App\Response\Http;

class SignVaryCookie
{
    public const COOKIE_VARY_SIGN_STRING = 'X-Magento-Vary-Sign';

    protected \Magento\Framework\App\Request\Http $request;
    protected \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager;
    protected \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory;
    protected \Magento\Framework\App\Http\Context $context;
    protected \Magento\Framework\Session\Config\ConfigInterface $sessionConfig;
    protected \MageSuite\VaryCookieSigner\Model\Signer $signer;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\Http\Context $context,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \MageSuite\VaryCookieSigner\Model\Signer $signer
    ) {
        $this->request = $request;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->context = $context;
        $this->sessionConfig = $sessionConfig;
        $this->signer = $signer;
    }

    public function afterSendVary(
        \Magento\Framework\App\Response\Http $subject,
        $result
    ) {
        $varyString = $this->context->getVaryString();

        if ($varyString) {
            $cookieLifeTime = $this->sessionConfig->getCookieLifetime();
            $metaData = [\Magento\Framework\Stdlib\Cookie\CookieMetadata::KEY_DURATION => $cookieLifeTime];
            $sensitiveCookMetadata = $this->cookieMetadataFactory
                ->createSensitiveCookieMetadata($metaData)
                ->setPath('/');
            $cookieValue = $this->signer->sign($varyString);
            $this->cookieManager->setSensitiveCookie(self::COOKIE_VARY_SIGN_STRING, $cookieValue, $sensitiveCookMetadata);
        } elseif ($this->request->get(self::COOKIE_VARY_SIGN_STRING)) {
            $cookieMetadata = $this->cookieMetadataFactory->createSensitiveCookieMetadata()->setPath('/');
            $this->cookieManager->deleteCookie(self::COOKIE_VARY_SIGN_STRING, $cookieMetadata);
        }
    }
}

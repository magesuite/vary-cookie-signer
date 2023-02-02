<?php

namespace MageSuite\VaryCookieSigner\Test\Integration\Plugin\Framework\App\Response\Http;

class SignVaryCookieTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookie;

    /**
     * @var \MageSuite\VaryCookieSigner\Model\Signer
     */
    protected $signer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->session = $this->_objectManager->get(\Magento\Customer\Model\Session::class);
        $this->cookie = $this->_objectManager->get(\Magento\Framework\Stdlib\CookieManagerInterface::class);
        $this->signer = $this->_objectManager->get(\MageSuite\VaryCookieSigner\Model\Signer::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSignCookieValue()
    {
        $this->session->loginById(1);
        $this->dispatch('/customer/account/');
        $response = $this->getResponse();
        $response->sendVary();

        $cookieVarySign = $this->cookie->getCookie(\MageSuite\VaryCookieSigner\Plugin\Framework\App\Response\Http\SignVaryCookie::COOKIE_VARY_SIGN_STRING);
        $expectedValue = '894055169b156e4757125e8807136b09ad5228f7';

        $this->assertEquals($expectedValue, $cookieVarySign);
    }
}

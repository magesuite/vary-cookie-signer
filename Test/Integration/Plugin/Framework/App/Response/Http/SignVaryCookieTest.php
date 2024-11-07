<?php

namespace MageSuite\VaryCookieSigner\Test\Integration\Plugin\Framework\App\Response\Http;

class SignVaryCookieTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected ?\Magento\Customer\Model\Session $session;
    protected ?\Magento\Framework\Stdlib\CookieManagerInterface $cookie;
    protected ?\MageSuite\VaryCookieSigner\Model\Signer $signer;

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
    public function testSignCookieValue(): void
    {
        $this->session->loginById(1);
        $this->dispatch('/customer/account/');
        $response = $this->getResponse();
        $response->sendVary();

        $cookieVarySign = $this->cookie->getCookie(\MageSuite\VaryCookieSigner\Plugin\Framework\App\Response\Http\SignVaryCookie::COOKIE_VARY_SIGN_STRING);
        $expectedValue = $this->signer->sign($this->cookie->getCookie(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING));

        $this->assertEquals($expectedValue, $cookieVarySign);
    }
}

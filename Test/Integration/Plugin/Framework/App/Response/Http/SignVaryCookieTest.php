<?php

namespace MageSuite\VaryCookieSigner\Test\Integration\Plugin\Framework\App\Response\Http;

class SignVaryCookieTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected ?\Magento\Customer\Model\Session $session = null;
    protected ?\Magento\Framework\Stdlib\CookieManagerInterface $cookie = null;
    protected ?\MageSuite\VaryCookieSigner\Model\Signer $signer = null;

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
        $expectedValue = '124cb58093b10efb42e74a0db1dc328e4f9dd718';

        $this->assertEquals($expectedValue, $cookieVarySign);
    }
}

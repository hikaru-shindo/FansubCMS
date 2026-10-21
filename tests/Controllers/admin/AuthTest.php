<?php
class Admin_AuthTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp()
    {
        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
    }

    public function tearDown()
    {
        $this->resetRequest();
        $this->resetResponse();
        parent::tearDown();
    }

    public function appBootstrap()
    {
        UnitBootstrapHelper::bootstrap();
    }

    public function testLogin()
    {
        $this->dispatch('/admin/auth/login');
        $this->assertModule('admin');
        $this->assertController('auth');
        $this->assertAction('login');
    }

    public function testLoginAction()
    {
        $request = $this->getRequest();

        $request->setMethod('POST');

        // try incorrect login
        $request->setPost(array(
          'username' => 'foobar',
          'password' => 'huba',
        ));

        $this->dispatch('/admin/auth/login');
        $this->assertNotRedirect();

        // try correct login
        $request->setPost(array(
          'username' => 'PHPUnit',
          'password' => '12345678',
        ));

        $this->dispatch('/admin/auth/login');
        $this->assertRedirectTo('/admin');
    }
}

<?php
class InitTests extends Zend_Test_PHPUnit_ControllerTestCase
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

    public function testDatabase()
    {
        // first migrate database if needed
        if(Install_Api_Migration::getInstance()->getCurrentVersion() < Install_Api_Migration::getInstance()->getLatestVersion()) {
            Install_Api_Migration::getInstance()->migrate();
        }

        $api = new Devtools_Api_DoctrineTool();
        // truncate all tables or import data if there is some
        $api->importFixtures(APPLICATION_PATH . '/resource/fixtures');

        // create an admin user for unit testing
        $user = new User_Model_User;
        $user->name = 'PHPUnit';
        $user->password = '12345678';
        $user->email = 'phpunit@testsuite.de';
        $user->description = 'PHPUnit Testuser';
        $user->activated = 'yes';
        $user->active = 'no';
        $user->show_team = 'no';
        $user->save();

        $role = new User_Model_Role();
        $role->link('User_Model_User', array($user->id));
        $role->role_name = 'admin_admin';
        $role->save();
    }
}

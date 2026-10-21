<?php
/**
 * generates the admin menu
 *
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 * @package FansubCMS
 * @subpackage View_Helper
 * @version SVN: $Id
 *
 */
class FansubCMS_View_Helper_AdminNavigation extends Zend_View_Helper_Navigation {
    public function adminNavigation() {
        $envConf = Zend_Registry::get('environmentSettings');
        $locale = $envConf->locale;
        $trans = Zend_Registry::get('Zend_Translate');

        // add the module and addon admin menus
        $modConf = glob(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'configs'. DIRECTORY_SEPARATOR . 'module.ini');
        foreach($modConf as $nav) {
            try {
                $nav = new Zend_Config_Ini($nav,'adminnav',true);
                if($adminNav instanceof Zend_Config_Ini)
                    $adminNav->merge($nav);
                else
                    $adminNav = $nav;
                // now we need the locale to add
            } catch(Zend_Config_Exception $e) {
                // do nothing on error, just ignore
            }
        }
        // add necessary translations
        $modConf = glob(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'locale'. DIRECTORY_SEPARATOR . 'navigation_'.$locale.'.ini');
        foreach($modConf as $translation) {
            $trans->addTranslation($translation,$locale);
        }

        Zend_Registry::set('Zend_Translate',$trans);
        $adminNav = new Zend_Navigation($adminNav);
        $this->setAcl(Zend_Registry::get('Zend_Acl'));
        $this->setRole('user');
        return $this->render($adminNav);

    }
}
?>

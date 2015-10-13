<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function _initSessions() {
        Zend_Session::start();
    }
    
    public function _initLogger() {
        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/logs/log.htm');
        $logger = new Zend_Log($writer);
        Zend_Registry::set('logger', $logger);
    }
    
    public function _initAutoload() {
        $moduleLoader = new Zend_Application_Module_Autoloader(array(
            'basePath' => APPLICATION_PATH,
            'namespace' => ''));
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->registerNamespace('VDF');
        return $moduleLoader;
    }
    
    public function _initPluginLoaders() {
        $loader = new Zend_Loader_PluginLoader(array(
            'Vdf' => VDF_PATH
        ));
    }
    
    public function _initTranslate() {
        try {
            $translator = new Zend_Translate('array', RESOURCES_PATH . '/languages/', null, array('scan' => Zend_Translate::LOCALE_DIRECTORY));
            Zend_Validate_Abstract::setDefaultTranslator($translator);
        } catch (Zend_Translate_Exception $e) {
            echo 'Erreur dans le module de traduction: ' . $e->getCode() . ' - ' . $e->getMessage();
        } catch (Exception $e) {
            echo $e->getCode() . ' - ' . $e->getMessage();
        }
    }
    
    public function _initViewHelpers() {
        $this->bootstrap('view');                                               // Force l'initialisation de la vue avant celle des aides de vue
        $view = $this->getResource('view');
        $view->doctype('XHTML1_STRICT');
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');
        $view->headTitle()->setSeparator(' - ');
        $view->headTitle('GesTra');
        
        $view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
        $view->jQuery()->addStylesheet('/js/jquery-ui-1.11.2/jquery-ui.min.css');
        $view->jQuery()->setLocalPath('/js/jquery-1.11.1.min.js');
        $view->jQuery()->setUiLocalPath('/js/jquery-ui-1.11.2/jquery-ui.min.js');
        $view->jQuery()->enable();                                              // On en aura besoin dans toutes les pages. Mais si on voulait etre plus efficient on pourrait appeler enable() dans les View concernees
        $view->jQuery()->uiEnable();
        
        $view->headLink()->prependStylesheet('/css/site.css')
                ->headLink()->prependStylesheet('/css/print-site.css', 'print')
                ->headLink()->prependStylesheet('/js/colpick/css/colpick.css')
        //        ->headLink()->prependStylesheet('http://fonts.googleapis.com/css?family=Open+Sans:400,700')
        //        ->headLink()->prependStylesheet('http://yui.yahooapis.com/pure/0.5.0/pure-min.css')
        //        ->headLink()->prependStylesheet('//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css')
        //        ->headLink()->prependStylesheet('http://fonts.googleapis.com/css?family=Bevan')
                ->headLink()->prependStylesheet('/semantic-ui/dist/semantic.min.css');
        $view->headScript()//->prependFile('/js/jquery-dateFormat-master/dist/dateFormat.min.js')
                ->prependFile('/js/jquery-dateFormat-master/dist/jquery-dateFormat.min.js')
        //        ->prependFile('/js/jquery.metadata.js')
        //        ->prependFile('/js/jquery.tablesorter.pager.js')
                ->prependFile('/js/colpick/js/colpick.js')
                ->appendFile('/js/jquery.print.js')
                ->prependFile('/js/html2canvas.js')
                ->headScript()->prependFile('/js/site.js')
                ->prependFile('/semantic-ui/dist/semantic.min.js'); // @todo change semantic ui installation -> dist on site folder
    }
}


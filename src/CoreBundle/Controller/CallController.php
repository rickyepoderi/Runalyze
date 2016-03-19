<?php
namespace Runalyze\Bundle\CoreBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Runalyze\View\Activity\Context;
use Runalyze\View\Activity\Linker;
use Runalyze\View\Activity\Dataview;
use Runalyze\Model\Activity;
use Runalyze\View\Window\Laps\Window;

require_once '../inc/class.Frontend.php';
require_once '../inc/class.FrontendShared.php';

/**
 * @Route("/call")
 */
class CallController extends Controller
{
    /**
    * @Route("/call.DataBrowser.display.php", name="databrowser")
    */
    public function dataBrowserAction()
    {
        $Frontend = new \Frontend(true);
        $DataBrowser = new \DataBrowser();
        $DataBrowser->display();
        return new Response;
    }
    
    /**
    * @Route("/call.garminCommunicator.php")
    */
    public function garminCommunicatorAction()
    {
        $Frontend = new \Frontend(true);
        include '../call/call.garminCommunicator.php';
        return new Response;
    }
    
    /**
    * @Route("/savePng.php")
    */
    public function savePngAction()
    {
        $Frontend = new \Frontend(true);
        header("Content-type: image/png");
        header("Content-Disposition: attachment; filename=".strtolower(str_replace(' ', '_', $_POST['filename'])));
        
        $encodeData = substr($_POST['image'], strpos($_POST['image'], ',') + 1);
        echo base64_decode($encodeData);
        return new Response;
    }
    
    /**
    * @Route("/call.MetaCourse.php")
    */
    public function metaCourseAction() {
        $Frontend = new \FrontendShared(true);
        
        $Meta = new HTMLMetaForFacebook();
        $Meta->displayCourse();
        return new Response;
    }
    
    /**
    * @Route("/window.config.php", name="config")
    */
    public function windowConfigAction() {
        $Frontend = new \Frontend(true);
        $ConfigTabs = new \ConfigTabs();
        $ConfigTabs->addDefaultTab(new  \ConfigTabGeneral());
        $ConfigTabs->addTab(new \ConfigTabPlugins());
        $ConfigTabs->addTab(new \ConfigTabDataset());
        $ConfigTabs->addTab(new \ConfigTabSports());
        $ConfigTabs->addTab(new \ConfigTabTypes());
        $ConfigTabs->addTab(new \ConfigTabEquipment());
        $ConfigTabs->addTab(new \ConfigTabAccount());
        $ConfigTabs->display();
        
        echo \Ajax::wrapJSforDocumentReady('Runalyze.Overlay.removeClasses();');
        return new Response;
    }

    /**
    * @Route("/ajax.saveTcx.php")
    */
    public function ajaxSaveTcxAction()
    {
        $Frontend = new \Frontend(true);
        
        \Filesystem::writeFile('../data/import/'.$_POST['activityId'].'.tcx', $_POST['data']);
        
        return new Response;
    }
    
    /**
     * @Route("/ajax.change.Config.php")
     */
    public function ajaxChanceConfigAction()
    {
        $Frontend = new \Frontend(true);
        switch ($_GET['key']) {
        	case 'garmin-ignore':
        		\Runalyze\Configuration::ActivityForm()->ignoreActivityID($_GET['value']);
        		break;
        
        	case 'leaflet-layer':
        		\Runalyze\Configuration::ActivityView()->updateLayer($_GET['value']);
        		break;
        
        	default:
        		if (substr($_GET['key'], 0, 5) == 'show-') {
        			$key = substr($_GET['key'], 5);
        			\Runalyze\Configuration::ActivityForm()->update($key, $_GET['value']);
        		}
        }
        return new Response;
    }
    
    /**
     * @Route("/window.delete.php")
     */
     public function windowDeleteAction()
     {
        $Frontend = new \Frontend();
        $Errors   = array();
        \AccountHandler::setAndSendDeletionKeyFor($Errors);
        
        echo \HTML::h1( __('Delete your account.') );
        
        if (!empty($Errors)) {
        	foreach ($Errors as $Error)
        		echo \HTML::error($Error);
        } else {
        	echo \HTML::info(
        			__('<em>A confirmation has been sent via mail.</em><br>'.
        				'How sad, that you\'ve decided to delete your account.<br>'.
        				'Your account will be deleted as soon as you click on the confirmation link in your mail.')
        	);
        }
        return new Response;
     }
     
    /**
     * @Route("window.search.php")
     */
    public function windowSearchAction()
    {
        $showResults = !empty($_POST);
        
        if (isset($_GET['get']) && $_GET['get'] == 'true') {
        	$_POST = array_merge($_POST, $_GET);
        	$showResults = true;
        
        	\SearchFormular::transformOldParamsToNewParams();
        }
        
        if (empty($_POST) || \Request::param('get') == 'true') {
        	echo '<div class="panel-heading">';
        	echo '<h1>'.__('Search for activities').'</h1>';
        	echo '</div>';
        
        	$Formular = new \SearchFormular();
        	$Formular->display();
        }
        
        $Results = new \SearchResults($showResults);
        $Results->display();
        return new Response;
    }
    
    protected function plotSumData() {
        if (!isset($_GET['y']))
        	$_GET['y'] = \PlotSumData::LAST_12_MONTHS;
        
        if (!isset($_GET['type']))
        	$_GET['type'] = 'month';
        
        if ($_GET['type'] == 'week') {
        	$Plot = new \PlotWeekSumData();
        	$Plot->display();
        } elseif ($_GET['type'] == 'month') {
        	$Plot = new \PlotMonthSumData();
        	$Plot->display();
        } else {
        	echo \HTML::error( __('There was a problem.') );
        }
    }
    
    /**
     * @Route("/window.plotSumData.php")
     */
    public function windowsPlotSumDataAction()
    {
        $Frontend = new \Frontend();
        $this->plotSumData();
        return new Response;
    }
    
    /**
     * @Route("/window.plotSumDataShared.php")
     */
    public function windowsPlotSumDataSharedAction()
    {
        $Frontend = new \FrontendSharedList();
        $this->plotSumData();
        return new Response;
    }
    
    /**
     * @Route("/login.php")
     */
    public function loginAction()
    {
        $Frontend = new \Frontend();
        echo '<p class="error">';
    	_e('You are not logged in anymore.');
    	echo '<br><br><a href="login" title="Runalyze: Login"><strong>&raquo; '. _e('Login').'</strong></a></p>';
    	return new Response;
    }
}
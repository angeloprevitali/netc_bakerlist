<?php
namespace Netcbakerlist\NetcBakerlist\Controller;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * AddressesController
 */
class AddressesController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * addressesRepository
	 *
	 * @var \Netcbakerlist\NetcBakerlist\Domain\Repository\AddressesRepository
	 * @inject
	 */
	protected $addressesRepository = NULL;

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {

		$mydata = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_netcbakerlist_netcbakerlist');

		$backeryname = $mydata['backeryname'];
		$ort = $mydata['ort'];
		$country = $mydata['country1'];
		$GlutenfreiesBrot = $mydata['GlutenfreiesBrot'];
		if($mydata['startpos']==''){
				$startpos = '';
		}else{
			$startpos = $mydata['startpos'];

			$backeryname = $mydata['backeryname2'];
			$ort = $mydata['ort2'];
			$country = $mydata['country2'];
			$GlutenfreiesBrot = $mydata['GlutenfreiesBrot2'];
		}

		if($backeryname!='' || $ort!='' || $country!='' || $GlutenfreiesBrot!='' || $startpos!=''){
			$searchrecord = $this->addressesRepository->findortBy($backeryname,$ort,$country,$GlutenfreiesBrot,$startpos);

			$this->view->assign('ortaddresses', $searchrecord);
		}
		
		$allrecord = $this->addressesRepository->findAll();
		$country = array();
        $country['']="Auswählen";
        foreach($allrecord as $key => $allrec){
        	$reg = $allrec->getRegion();
        	if(!in_array($reg,$country)){
        		$country[$reg] = $reg;
        	}
        }
       
		$this->view->assign('country', $country);
	}

	/**
	 * action belist
	 *
	 * @return void
	 */
	public function belistAction() {
		$addresses = $this->addressesRepository->findAll();
		$this->view->assign('addresses', $addresses);
	}

	/**
	 * action show
	 *
	 * @param \Netcbakerlist\NetcBakerlist\Domain\Model\Addresses $addresses
	 * @return void
	 */
	public function showAction(\Netcbakerlist\NetcBakerlist\Domain\Model\Addresses $addresses) {
		$this->view->assign('addresses', $addresses);
	}

	/**
	 * action import
	 *
	 * @return void
	 */
	public function importAction() {
		
		$mycsvfile = $_FILES;
		//$csv = $this->addressesRepository->importcsv($filedata);
		//$this->view->assign('csv', $csv);

		$tmp = $mycsvfile['tx_netcbakerlist_web_netcbakerlistnetcbakerlistfile']['tmp_name']['file'];
    	$name = $mycsvfile['tx_netcbakerlist_web_netcbakerlistnetcbakerlistfile']['name']['file'];
    	$newlocation = $_SERVER['DOCUMENT_ROOT'].'html/netc/fileadmin/csvfile/'.$name;

    	if(move_uploaded_file($tmp, $newlocation)){
    		 $csvext = pathinfo($newlocation, PATHINFO_EXTENSION); 

    		 if($csvext == 'csv'){
    		 	$file = fopen($newlocation,"r");

    		 	// once upload new csv file first truncate table.and insert new csv records.
    		 	$GLOBALS['TYPO3_DB']->exec_TRUNCATEquery('tx_netcbakerlist_domain_model_addresses');
	    		while(!feof($file))
	  			{
	  				//$csvdata = fgetcsv($file,0,';');
	  				$csvdata = fgetcsv($file,0,',');
	  				$records = NULL;
	    			if($csvdata[0]!='' && $csvdata[0]!='Title'){

	    				$records = \TYPO3\CMS\Core\Utility\GeneralUtility::makeinstance("Netcbakerlist\\NetcBakerlist\\Domain\\Model\\Addresses");

	    				// Remove (Deleted = 1) row to table
	    				// $foundrecord = $this->addressesRepository->findByTitle($csvdata[0])->toArray();
	    				// foreach($foundrecord as $c){
	    				// 	$this->addressesRepository->remove($c);
	    				// }    

						$csvdata[0]!=''?$records->setTitle($csvdata[0]):'';
						$csvdata[1]!=''?$records->setDescription($csvdata[1]):'';
						$csvdata[6]!=''?$records->setName($csvdata[6]):'';
						$csvdata[7]!=''?$records->setVorname($csvdata[7]):'';
						$csvdata[9]!=''?$records->setAdresse($csvdata[9]):'';
						$csvdata[10]!=''?$records->setPlz($csvdata[10]):'';
						$csvdata[11]!=''?$records->setOrt($csvdata[11]):'';
						$csvdata[12]!=''?$records->setRegion($csvdata[12]):'';
						$csvdata[13]!=''?$records->setEmail($csvdata[13]):'';
						$csvdata[14]!=''?$records->setWwwLink($csvdata[14]):'';
						$csvdata[15]!=''?$records->setTel($csvdata[15]):'';
						$csvdata[16]!=''?$records->setFax($csvdata[16]):'';
						
						$this->addressesRepository->add($records);
	    			}
	    		}
	    		$this->view->assing("data", $count1);
				fclose($file);
				unlink($newlocation);
				$this->addFlashMessage('Records are successfully uploaded. Thank you!');
				$this->redirect('belist');
    		 }else{
    		 	$this->addFlashMessage('uploaded file are not csv file format Please upload csv file.');
    		 	unlink($newlocation);
    		 	$this->redirect('belist');
    		 }
	    }else{
	        $this->addFlashMessage('Failed to move uploaded files');
	        $this->redirect('belist');
	    }
	}

}